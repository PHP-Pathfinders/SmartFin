<?php

namespace App\Controller;

use App\Dto\Category\CategoryQueryDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class TransactionController extends AbstractController
{

    /**
     * Finds transactions by category, payment type, month, transaction name, party name
     */
    #[Route('/transactions', name: 'api_categories',methods: ['GET']),]
    public function search(
        #[MapQueryString] ?TransactionQueryDto $transactionQueryDto,
        TransactionService $transactionService
    ): JsonResponse
    {
        $transactions = $transactionService->search($transactionQueryDto);

        // If no transactions are found
        if (empty($transactions)) {
            return $this->json([
                'success' => false,
                'message' => 'No transactions found'
            ]);
        }

        return $this->json([
            'success' => true,
            'categories' => $transactions
        ]);
    }
}
