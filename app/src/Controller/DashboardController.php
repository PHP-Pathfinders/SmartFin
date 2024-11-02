<?php

namespace App\Controller;

use App\Dto\Transaction\DashboardDto;
use App\Service\DashboardService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as AO;

//#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{

//    #[Route(name: 'api_dashboard_fetch', methods: ['GET'])]
    #[AO\Get]
    #[Security(name: 'Bearer')]
    public function fetch(
        DashboardService $dashboardService,
        #[MapQueryString] ?DashboardDto $dashboardDto
    ): JsonResponse
    {
        $month = $dashboardDto->month ?? date('m');
        $year = $dashboardDto->year ?? date('Y');
        $data = $dashboardService->fetchDashboard($month, $year);

        if (empty($data)){
            return $this->json([
                'success' => false,
                'message' => 'No data found'
            ],Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

}