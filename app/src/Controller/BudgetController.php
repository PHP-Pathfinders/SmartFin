<?php

namespace App\Controller;

use App\Dto\Budget\BudgetCreateDto;
use App\Dto\Budget\BudgetUpdateDto;
use App\Dto\Budget\RandomDto;
use App\Dto\Budget\BudgetQueryDto;
use App\Entity\Budget;
use App\Service\BudgetService;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Security;


#[Route('/api/budgets')]
class BudgetController extends AbstractController
{
    #[Route('', name: 'api_find_budgets', methods: ['GET'])]
    #[OA\Get(
        summary: "Finds budgets in certain time period for logged user"
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
    )]
    #[OA\Tag(name: 'Budgets')]
    #[Security(name: 'Bearer')]
    public function search(
        #[MapQueryString] ?BudgetQueryDto $budgetQueryDto,
        BudgetService                     $budgetService
    ): JsonResponse
    {

        $data = $budgetService->search($budgetQueryDto);

        if (empty($data['budgets'])) {
            return $this->json([
                'success' => false,
                'message' => 'No budgets found'
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/random', name: 'api_budget', methods: ['GET'])]
    #[OA\Get(
        summary: "Random 3 budgets",

    )]
    #[OA\Tag(name: 'Budgets')]
    public function random(
        #[MapQueryString] ?RandomDto $randomDto,
        BudgetService                $budgetService
    ): JsonResponse
    {
        $data = $budgetService->random($randomDto);
        if (empty($data)) {
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

    #[Route('', name: 'api_add_budget', methods: ['POST'])]
    #[OA\Post(
        summary: "Adds budget for this month for logged user"
    )]
    #[OA\Tag(name: 'Budgets')]
    public function create(
        #[MapRequestPayload] BudgetCreateDto $budgetCreateDto,
        BudgetService                        $budgetService
    ): JsonResponse
    {
        $budgetService->create($budgetCreateDto);

        return $this->json([
            'success' => true,
            'message' => 'New budget created'
        ]);

    }


    #[Route('', name: 'api_update_budget', methods: ['PATCH'])]
    #[OA\Patch(
        summary: "Makes changes to certain budget for logged user"
    )]
    #[OA\Tag(name: 'Budgets')]
    public function update(
        #[MapRequestPayload] BudgetUpdateDto $budgetUpdateDto,
        BudgetService $budgetService
    ):JsonResponse
    {
        $message = $budgetService->update($budgetUpdateDto);
        return $this->json([
            'success' => true,
            'message' => $message
        ]);

    }

    #[Route('/{id<\d+>}', name: 'api_delete_budget', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Deletes specific budget based on given id of it"
    )]
    #[OA\Tag(name: 'Budgets')]
    public function delete(int $id, BudgetService $budgetService): JsonResponse
    {
        $budgetService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Budget with id $id has been deleted"
        ]);

    }

}