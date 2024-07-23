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
        description: 'Returns array of budgets in certain date period if no parameters given gives results for current month.',
        summary: "Finds budgets in certain time period for logged user",
        tags: ['Budgets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/Budget')
            ),
            new OA\Response(
                response: 404,
                description: 'No budgets found',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetNotFound')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetDateError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            )
        ]
    )]
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
            ], 404);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/random', name: 'api_budget', methods: ['GET'])]
    #[OA\Get(
        description: 'Returns random amount of budgets for logged user',
        summary: "Random budgets",
        tags: ['Overview'],
        responses: [
            new OA\Response(
                response: 404,
                description: 'Budgets not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: "Budgets not found")
                    ]
                )
            ),
        ]

    )]
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
        summary: "Adds budget for this month for logged user",
        tags: ['Budgets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful budget insertion',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputSuccess')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid category given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputFail')
            ),
            new OA\Response(
                response: 409,
                description: 'Same budget already exists in same month',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetConflict')
            )
        ]
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
        description: "Make changes to any budget that is in ownership of logged in user",
        summary: "Makes changes to certain budget for logged user",
        tags: ['Budgets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful budget update',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetUpdateSuccess')
            ),
            new OA\Response(
                response: 409,
                description: 'Same budget already exists in same month',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetConflict')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetInputError')
            ),
            new OA\Response(
                response: 404,
                description: 'Budget you selected is either not owned by you or does not exist or invalid category was given',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetUpdateFail')
            ),
        ]
    )]
    public function update(
        #[MapRequestPayload] BudgetUpdateDto $budgetUpdateDto,
        BudgetService                        $budgetService
    ): JsonResponse
    {
        $message = $budgetService->update($budgetUpdateDto);
        return $this->json([
            'success' => true,
            'message' => $message
        ]);

    }

    #[Route('/{id<\d+>}', name: 'api_delete_budget', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Removes budget that is in ownership of logged user",
        summary: "Deletes specific budget based on given id",
        tags: ['Budgets'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful budget deletion',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetDeleteSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 404,
                description: 'Budget you selected is either not owned by you or does not exist',
                content: new OA\JsonContent(ref: '#/components/schemas/BudgetUpdateFail')
            ),

        ]
    )]
    public function delete(int $id, BudgetService $budgetService): JsonResponse
    {
        $budgetService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Budget with id $id has been deleted"
        ]);

    }

}