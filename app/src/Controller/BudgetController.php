<?php

namespace App\Controller;

use App\Dto\Budget\RandomDto;
use App\Service\BudgetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/budgets')]
class BudgetController extends AbstractController
{
    #[Route('/random', name: 'api_budget',methods: ['GET'])]
    public function random(
        #[MapQueryString] ?RandomDto $randomDto,
        BudgetService $budgetService
    ): JsonResponse
    {
       $data = $budgetService->random($randomDto);
       if(empty($data)){
           return $this->json([
              'success' => false,
              'message' => 'Budgets not found'
           ], Response::HTTP_NOT_FOUND);
       }
       return $this->json([
           'success' => true,
           'data' => $data
       ]);
    }
}
