<?php

namespace App\Controller;

use App\Service\PdfXlsGeneratorService;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Yectep\PhpSpreadsheetBundle\Factory;

#[Route('/api')]
class PdfXlsGeneratorController extends AbstractController
{
    #[Route('/pdf/generator', name: 'api_pdf_generator', methods: ['GET'])]
    public function generatePDF(
        PdfXlsGeneratorService $pdfXlsGeneratorService
    ): Response
    {
        $data= $pdfXlsGeneratorService->generatePDF();
        $html =  $this->renderView('pdf_generator/index.html.twig', $data);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response (
            $dompdf->stream('transactions', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Route('/xls/generator', name: 'api_xls_generator', methods: ['GET'])]
    public function generateXLS(
        Factory $factory,
        PdfXlsGeneratorService $pdfXlsGeneratorService
    ) :Response
    {
        $data = $pdfXlsGeneratorService->generateXLS();
        $spreadsheet = $factory->createSpreadsheet();
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

        $sheet->fromArray($data,null,'B3');
        // Apply border style to data cells
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('B3:H'.(count($data) + 2))->applyFromArray($dataStyle);

        $response = $factory->createStreamedResponse($spreadsheet, 'Xls');

        // Redirect output to a client’s web browser (Xls)
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Transactions.xls"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }

}
