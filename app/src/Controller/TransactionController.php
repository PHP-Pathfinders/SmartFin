<?php

namespace App\Controller;

use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Dto\Transaction\OverviewDto;
use App\Dto\Transaction\SpendingsDto;
use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Dto\Transaction\TransactionUpdateDto;
use App\Service\TransactionService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;


#[Route('/api/transactions')]
class TransactionController extends AbstractController
{

    /**
     * Finds transactions by category, payment type, month, transaction name, party name, transaction notes
     * - Example url: localhost:8080/api/transactions?transactionDate=2024-05-01&paymentType=cash&limit=5
     */
    #[Route('', name: 'api_find_transactions',methods: ['GET'])]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function search(
        #[MapQueryString] ?TransactionQueryDto $transactionQueryDto,
        TransactionService $transactionService
    ): JsonResponse
    {
        $data = $transactionService->search($transactionQueryDto);

        // If no transactions are found
        if (empty($data['transactions'])) {
            return $this->json([
                'success' => false,
                'message' => 'No transactions found'
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/overview', name: 'api_transactions_overview', methods: ['GET'])]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function transactionsOverview(
        TransactionService $transactionService,
        #[MapQueryString] ?OverviewDto $overviewDto
    ): JsonResponse
    {
        $year = $overviewDto->year ?? date('Y');
        $data = $transactionService->transactionOverview((int) $year);
        if (empty($data)){
            return $this->json([
                'success' => false,
                'message' => 'No transactions found'
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/spendings', methods: ['GET'])]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function spendingByCategories(
        TransactionService $transactionService,
        #[MapQueryString] ?SpendingsDto $spendingsDto
    ): JsonResponse
    {
        $month = $spendingsDto->month ?? date('m');
        $year = $spendingsDto->year ?? date('Y');
        $data = $transactionService->spendingByCategories($month, $year);

        if(empty($data)){
            return $this->json(
                [
                    'success'=>false,
                    'month'=>(int)$month,
                    'year'=>(int)$year,
                    'message'=>'No spending\'s found for this period'
                ]
            );
        }

        return $this->json(
            [
                'success'=>true,
                'month'=>(int)$month,
                'year'=>(int)$year,
                'data'=> $data
            ]
        );
    }

    #[Route('', name: 'api_add_transaction', methods: ['POST'])]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload] TransactionCreateDto $transactionCreateDto,
        TransactionService $transactionService
    ): JsonResponse
    {
        $transactionService->create($transactionCreateDto);

        return $this->json([
            'success' => true,
            'message' => 'New transaction created'
        ]);
    }

    #[Route('', name: 'api_update_transactions', methods: ['PATCH'])]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function update(
        #[MapRequestPayload] TransactionUpdateDto $transactionUpdateDto,
        TransactionService $transactionService,
    ): JsonResponse
    {
        $message = $transactionService->update($transactionUpdateDto);
        return $this->json([
            'success' => true,
            'message' => $message
        ]);
    }

    #[Route('/{id<\d+>}', name: 'api_delete_transaction', methods: ['DELETE'])]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    public function delete(int $id, TransactionService $transactionService): JsonResponse
    {
        $transactionService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Transition with id $id has been deleted"
        ]);

    }
}
