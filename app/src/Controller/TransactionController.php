<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class TransactionController extends AbstractController
{

    public function __construct()
    {
        
    }


    /**
     * Find transactions by month and type
     */
    #[Route('/find-transactions-by-month-and-type', name: 'api_find_transactions_by_month_and_type', methods: ['GET'])]
    public function findTransactionsByMonthAndType(): JsonResponse
    {

    }
    
    
    

}