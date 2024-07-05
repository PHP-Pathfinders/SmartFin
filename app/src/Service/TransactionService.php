<?php

namespace App\Service;

use App\Dto\Transaction\TransactionQueryDto;
use App\Entity\User;
use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use function Symfony\Component\String\u;

readonly class TransactionService
{
    public function __construct(
        private Security $security,
        private TransactionRepository $transactionRepository
    )
    {
    }

    public function search(?TransactionQueryDto $transactionQueryDto)
    {

        if ($transactionQueryDto !== null) {
            $paymentType = $transactionQueryDto->paymentType ?? null;
            $transactionDate = $transactionQueryDto->transactionDate ?? null;
        }


        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        return $this->transactionRepository->findAllByParameters($paymentType,$user,$transactionDate);
    }





}