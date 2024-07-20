<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ExportRepository;
use App\Repository\TransactionRepository;
use DateTime;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Twig\Environment;
use Yectep\PhpSpreadsheetBundle\Factory;

class PdfXlsService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly ExportRepository      $exportRepository,
        private readonly Security              $security,
        private readonly string                $avatarDir,
        private readonly string                $exportsDir,
        private readonly Factory               $factory,
        private readonly Environment           $twig
    )
    {}

    public function generatePDF(): Dompdf
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user){
           throw new NotFoundHttpException('User not found');
        }

        $this->limitGenerate($user, 'pdf', 'You can only generate one PDF export every 24 hours.');
        $transactions = $this->transactionRepository->fetchSpecificColumns(user: $user,
            categoryName: true, type: true, color: true, paymentType: true, transactionDate: true, moneyAmount: true
        );
        if (empty($transactions)){
            throw new NotFoundHttpException('Transactions not found');
        }
        $image = $user->getAvatarFileName() ? $this->imageToBase64($this->avatarDir . '/' . $user->getAvatarFileName()) : null;

        $data = [
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'imageSrc'  => $image,
            'transactions' => $transactions
        ];

        $html =  $this->twig->render('pdf_generator/index.html.twig', $data);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Save the PDF to a specific location
        $uniqueFileName = uniqid('', true).'.pdf';
        $filePath = $this->exportsDir . '/pdf/' . $uniqueFileName;
        $file = fopen($filePath, 'wb');
        fwrite($file, $dompdf->output());
        fclose($file);

        $this->exportRepository->create($uniqueFileName,'pdf', $user);

        return $dompdf;
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function generateXLS(): StreamedResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user){
            throw new NotFoundHttpException('User not found');
        }

        $this->limitGenerate($user, 'xlsx', 'You can only generate one XLSX export every 24 hours.');

         $results =  $this->transactionRepository->fetchSpecificColumns(user: $user,
            categoryName: true, type: true, paymentType: true, transactionDate: true, moneyAmount: true
        );
        if (empty($results)){
            throw new NotFoundHttpException('Transactions not found');
        }
        foreach ($results as &$result) {
            if (isset($result['day'])) {
                $result['day'] = (int) $result['day'];
            }
            if (isset($result['month'])) {
                $result['month'] = (int) $result['month'];
            }
            if (isset($result['year'])) {
                $result['year'] = (int) $result['year'];
            }
        }
        unset($result);

        // Create spreadsheet
        $spreadsheet = $this->factory->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transactions');

        // Define the headers for each column
        $headers = ['Category Name', 'Type', 'Money Amount', 'Payment Type', 'Day', 'Month', 'Year'];
        // Set the headers in the first row
        $sheet->fromArray($headers, null, 'B2');
        // Apply bold style to headers
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('B2:H2')->applyFromArray($headerStyle);

        $sheet->fromArray($results,null,'B3');
        // Apply border style to data cells
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('B3:H'.(count($results) + 2))->applyFromArray($dataStyle);

        // Save the file to local storage
        $writer = new Xlsx($spreadsheet);
        $uniqueFileName = uniqid('', true).'.xlsx';
        $filePath = $this->exportsDir . '/xlsx/' . $uniqueFileName;
        $writer->save($filePath);

        /** @var User $user */
        $user = $this->security->getUser();
        if(!$user){
            throw new NotFoundHttpException('User not found');
        }

        // Save metadata in database
        $this->exportRepository->create($uniqueFileName,'xlsx', $user);

        return $this->factory->createStreamedResponse($spreadsheet, 'Xls');
    }

    /*
     * To prevent spam, limit users how often they can generate an export
     */
    private function limitGenerate(User $user, string $fileType, $message, string $time = '-24 hours'): void
    {
        $data = $this->exportRepository->findLatestExportByType($user,$fileType);
        if (!empty($data) && $data['createdAt']  > (new DateTime())->modify($time)){
            throw new BadRequestHttpException($message);
        }
    }

    private function imageToBase64($path): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}