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

#[Route('/api/exports')]
class ExportController extends AbstractController
{
    /**
     * Get a list of all exports made by a user
     */
    #[Route('', name: 'api_export_list', methods: ['GET'])]
    public function search(
        #[MapQueryString] SearchDto $searchDto,
        ExportService $exportService
    ): JsonResponse
    {
        $data = $exportService->search($searchDto);
        if(!$data){
            return $this->json([
                'success' => false,
                'message' => 'No exports found'
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Download exported file
     */
    #[Route('/download/{fileName}', name: 'api_export_download', methods: ['GET'])]
    public function downloadFile(
        string $fileName,
        ExportService $exportService
    ): BinaryFileResponse
    {
        $filePath = $exportService->download($fileName);
        return new BinaryFileResponse($filePath);
    }
}
