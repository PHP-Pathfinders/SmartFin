<?php

namespace App\Service;

use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class TransactionService
{
    public function __construct(private Security $security, private TransactionRepository $transactionRepository)
    {
    }

    public function findTransactionsByMonthAndType()
    {

    }





}