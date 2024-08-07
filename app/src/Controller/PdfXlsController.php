<?php

namespace App\Controller;

use App\Service\PdfXlsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
class PdfXlsController extends AbstractController
{
    public function __construct(private readonly PdfXlsService $pdfXlsService)
    {}
    #[Route('/pdf/generator', name: 'api_pdf_generator', methods: ['GET'])]
    #[OA\Get(
        description: 'Used for generating pdf file of all transactions for logged user',
        summary: 'PDF generator for user\'s transactions',
        tags: ['Generators'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful pdf generation',
            ),
            new OA\Response(
                response: 400,
                description: 'Daily generation limit reached',
                content: new OA\JsonContent(ref: '#/components/schemas/GeneratorPDFError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            )
        ]
    )]
    public function generatePDF(): Response
    {
        $dompdf = $this->pdfXlsService->generatePDF();

        return new Response (
            $dompdf->stream('transactions', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Route('/xls/generator', name: 'api_xls_generator', methods: ['GET'])]
    #[OA\Get(
        description: 'Used for generating xlsx file of all transactions for logged user',
        summary: 'XLS generator for user\'s transactions',
        tags: ['Generators'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful xls generation',
            ),
            new OA\Response(
                response: 400,
                description: 'Daily generation limit reached',
                content: new OA\JsonContent(ref: '#/components/schemas/GeneratorXLSError')
            ),
            new OA\Response(
                response: 401,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            )
        ]
    )]
    public function generateXLS() :Response
    {
        $response = $this->pdfXlsService->generateXLS();
        // Redirect output to a client’s web browser (Xls)
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="Transactions.xls"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }

}
