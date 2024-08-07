<?php

namespace App\Controller;

use App\Dto\Export\SearchDto;
use App\Service\ExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api/exports')]
class ExportController extends AbstractController
{
    public function __construct(private readonly ExportService $exportService)
    {}
    /**
     * Get a list of all exports made by a user
     */
    #[Route(name: 'api_export_list', methods: ['GET'])]
    #[OA\Get(
        description: 'Gives a list of all pdf and xls files that are associated with user',
        summary: 'Get a list of all exports made by a user',
        tags: ['Exports'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/Export')
            ),
            new OA\Response(
                response: 500,
                description: 'Wrong user id or user not authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/ExportsUserNotFound')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            )
        ]
    )]
    public function search(
        #[MapQueryString] ?SearchDto $searchDto): JsonResponse
    {
        $data = $this->exportService->search($searchDto);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Download exported file
     */
    #[Route('/download/{fileName}', name: 'api_export_download', methods: ['GET'])]
    #[OA\Get(
        description: 'Download exported files made by a user, this endpoint might not be fully functional here but you can try it out at http://localhost:8080/api/exports/download/{fileName} with correct fileName from /api/exports',
        summary: 'Enables downloading of exported files associated with user',
        tags: ['Exports'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
            ),
            new OA\Response(
                response: 404,
                description: 'File not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ExportsFileNotFound')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/ExportInvalidRequest')
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
    public function downloadFile(string $fileName): BinaryFileResponse
    {
        $filePath = $this->exportService->download($fileName);
        return new BinaryFileResponse($filePath);
    }
}
