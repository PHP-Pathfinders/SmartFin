<?php

namespace App\Controller;

use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Dto\Transaction\DashboardDto;
use App\Dto\Transaction\OverviewDto;
use App\Dto\Transaction\SpendingsDto;
use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Dto\Transaction\TransactionUpdateDto;
use App\Service\TransactionService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


#[Route('/api/transactions')]
class TransactionController extends AbstractController
{

    public function __construct(
        private TransactionService $transactionService,
    )
    {
    }


    /**
     * Finds transactions by category, payment type, month, transaction name, party name, transaction notes
     * - Example url: localhost:8080/api/transactions?transactionDate=2024-05-01&paymentType=cash&limit=5
     */
    #[Route(name: 'api_find_transactions',methods: ['GET'])]
    #[OA\Get(
        description: 'Returns array of transactions filtered by different parameters, at least one parameter is needed to successfully perform a search',
        summary: 'Finds transactions by category, payment type, month, transaction name, party name, transaction notes and much more',
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/Transaction')
            ),
            new OA\Response(
                response: 404,
                description: 'Incorrect configuration',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionNotFound')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function search(
        #[MapQueryString] ?TransactionQueryDto $transactionQueryDto,
    ): JsonResponse
    {
        $data = $this->transactionService->search($transactionQueryDto);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/overview', name: 'api_transactions_overview', methods: ['GET'])]
    #[OA\Get(
        description: 'Data used for bar chart for overall monthly incomes and expenses at the overview dashboard',
        summary: 'Gives a overall view for each month of given year, by default it gives view for current year',
        tags: ['Overview'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/OverviewSuccess')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/YearInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function transactionsOverview(
        #[MapQueryString] ?OverviewDto $overviewDto
    ): JsonResponse
    {
        $year = $overviewDto->year ?? date('Y');
        $data = $this->transactionService->transactionOverview((int) $year);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/spendings', methods: ['GET'])]
    #[OA\Get(
        description: 'Data used for overall expenses by categories pie chart',
        summary: 'Returns a list of spendings for certain month and year',
        tags: ['Overview'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/SpendingsSuccess')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/YearInputError')
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
    #[Security(name: 'Bearer')]
    public function spendingByCategories(
        #[MapQueryString] ?SpendingsDto $spendingsDto
    ): JsonResponse
    {
        $month = $spendingsDto->month ?? date('m');
        $year = $spendingsDto->year ?? date('Y');
        $data = $this->transactionService->spendingByCategories($month, $year);

        return $this->json(
            [
                'success'=>true,
                'month'=>(int)$month,
                'year'=>(int)$year,
                'data'=> $data
            ]
        );
    }




    #[Route(name: 'api_add_transaction', methods: ['POST'])]
    #[OA\Post(
        description: 'Used for creating new transaction with current logged user as its owner',
        summary: "Creates a income/expense transaction for logged user",
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful transaction creation',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionInputSuccess')
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
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionInputFail')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload] TransactionCreateDto $transactionCreateDto,
    ): JsonResponse
    {
        $transaction = $this->transactionService->create($transactionCreateDto);

        return $this->json([
            'success' => true,
            'message' => 'New transaction created',
            'data' => $transaction
        ], status: Response::HTTP_CREATED, context: [
            ObjectNormalizer::GROUPS => ['transaction']
        ]);
    }

    #[Route(name: 'api_update_transactions', methods: ['PATCH'])]
    #[OA\Patch(
        description: "Make changes to any transaction that is in ownership of logged in user",
        summary: "Makes changes to certain transaction for logged user",
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful transaction update or nothing to change',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionUpdateSuccess')
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
                description: 'Invalid category or transaction given',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionInputFail')
            ),
            new OA\Response(
                response: 409,
                description: 'Can\'t change income to expense',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionConflict')
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
    #[Security(name: 'Bearer')]
    public function update(
        #[MapRequestPayload] TransactionUpdateDto $transactionUpdateDto,
    ): JsonResponse
    {
        $data = $this->transactionService->update($transactionUpdateDto);

        if(isset($data['transaction'])){
            return $this->json([
                'success' => true,
                'message' => $data['message'],
                'data' => $data['transaction']
            ], context: [
                ObjectNormalizer::GROUPS => ['transaction']
            ]);
        }

        return $this->json([
            'success' => true,
            'message' => $data['message']
        ]);
    }

    #[Route('/{id<\d+>}', name: 'api_delete_transaction', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Removes transaction that is in ownership of logged user",
        summary: "Deletes specific transaction based on given id",
        tags: ['Transactions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful transaction deletion',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionDeleteSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 404,
                description: 'Transaction you selected is either not owned by you or does not exist',
                content: new OA\JsonContent(ref: '#/components/schemas/TransactionDeleteNotFound')
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
    #[Security(name: 'Bearer')]
    public function delete(int $id): JsonResponse
    {
        $this->transactionService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Transition with id $id has been deleted"
        ]);

    }
}
