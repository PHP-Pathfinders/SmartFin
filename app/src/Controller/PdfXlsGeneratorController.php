<?php

namespace App\Controller;

use App\Service\PdfXlsGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class PdfXlsGeneratorController extends AbstractController
{
    #[Route('/pdf/generator', name: 'api_pdf_generator', methods: ['GET'])]
    public function generatePDF(
        PdfXlsGeneratorService $pdfXlsGeneratorService,
    ): Response
    {
        $dompdf = $pdfXlsGeneratorService->generatePDF();

        return new Response (
            $dompdf->stream('transactions', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Route('/xls/generator', name: 'api_xls_generator', methods: ['GET'])]
    public function generateXLS(
        PdfXlsGeneratorService $pdfXlsGeneratorService
    ) :Response
    {
        $response = $pdfXlsGeneratorService->generateXLS();
        // Redirect output to a client’s web browser (Xls)
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Transactions.xls"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }

}
