<?php

namespace App\Controller;

use App\Service\PdfXlsGeneratorService;
use Dompdf\Dompdf;
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

//        dd($data);
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
        //TODO add table headers for each column
        $data = $pdfXlsGeneratorService->generateXLS();
        $spreadsheet = $factory->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transactions');
//        $sheet->setCellValue('B2', 'This is test value');
        $sheet->fromArray($data,null,'B2');

        $response = $factory->createStreamedResponse($spreadsheet, 'Xls');

        // Redirect output to a client’s web browser (Xls)
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Transactions.xls"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }

}
