<?php

namespace App\Service;

use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class TransactionService
{
    public function __construct(
        private Security              $security,
        private TransactionRepository $transactionRepository,
        private CategoryRepository $categoryRepository,
    )
    {
    }

    public function search(?TransactionQueryDto $transactionQueryDto): array
    {
        $paymentType = null;
        $transactionDate = null;
        $transactionName = null;
        $partyName = null;
        $transactionNotes = null;
        $categoryName = null;
        $categoryType = null;
        $categoryId = null;
        $page = '1';
        $limit = '10';

        if ($transactionQueryDto !== null) {
            $paymentType = $transactionQueryDto->paymentType ?? null;
            $transactionDate = $transactionQueryDto->transactionDate ?? null;
            $transactionName = $transactionQueryDto->transactionName ?? null;
            $partyName = $transactionQueryDto->partyName ?? null;
            $transactionNotes = $transactionQueryDto->transactionNotes ?? null;
            $categoryName = $transactionQueryDto->categoryName ?? null;
            $categoryType = $transactionQueryDto->categoryType ?? null;
            $categoryId = $transactionQueryDto->categoryId ?? null;
            $page = $transactionQueryDto->page;
            $limit = $transactionQueryDto->limit;
        }


        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        return $this->transactionRepository->findAllByParameters(limit: $limit, page: $page, user: $user, paymentType: $paymentType, transactionDate: $transactionDate, transactionName: $transactionName, partyName: $partyName, transactionNotes: $transactionNotes, categoryId: $categoryId, categoryName: $categoryName, categoryType: $categoryType);
    }


    public function create(TransactionCreateDto $transactionCreateDto): void
    {

        $transactionName = $transactionCreateDto->transactionName;
        $category = $this->categoryRepository->findOneBy(['id' => $transactionCreateDto->categoryId]);
        $moneyAmount = $transactionCreateDto->moneyAmount;
        $transactionDate = $transactionCreateDto->transactionDate;
        $paymentType = $transactionCreateDto->paymentType;
        $partyName = $transactionCreateDto->partyName ?? null;
        $transactionNotes = $transactionCreateDto->transactionNotes ?? null;
        $user = $this->security->getUser();

        $this->transactionRepository->create($transactionName,$category,$moneyAmount,$paymentType,$transactionDate);


    }


}