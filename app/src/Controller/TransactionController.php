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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/transactions')]
class TransactionController extends AbstractController
{

    /**
     * Finds transactions by category, payment type, month, transaction name, party name, transaction notes
     * - Example url: localhost:8080/api/transactions?transactionDate=2024-05-01&paymentType=cash&limit=5
     */
    #[Route('', name: 'api_find_transactions',methods: ['GET'])]
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
    public function transactionsOverview(
        TransactionService $transactionService,
        #[MapQueryString] OverviewDto $overviewDto
    ): JsonResponse
    {
        $data = $transactionService->transactionOverview((int) $overviewDto->year);
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
    public function spendingByCategories(
        TransactionService $transactionService,
        #[MapQueryString] SpendingsDto $spendingsDto
    ): JsonResponse
    {
        $data = $transactionService->spendingByCategories($spendingsDto);

        if(empty($data)){
            return $this->json(
                [
                    'success'=>false,
                    'message'=>'No spending\'s found'
                ]
            );
        }

        return $this->json(
            [
                'success'=>true,
                'data'=> $data
            ]
        );
    }

    #[Route('', name: 'api_add_transaction', methods: ['POST'])]
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
    public function delete(int $id, TransactionService $transactionService): JsonResponse
    {
        $transactionService->delete($id);

        return $this->json([
            'success' => true,
            'message' => "Transition with id $id has been deleted"
        ]);

    }
}
