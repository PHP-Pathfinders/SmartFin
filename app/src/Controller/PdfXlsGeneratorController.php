<?php

namespace App\Controller;

use App\Service\PdfXlsGeneratorService;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class PdfXlsGeneratorController extends AbstractController
{
    #[Route('/pdf/generator', name: 'app_pdf_generator')]
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

}
