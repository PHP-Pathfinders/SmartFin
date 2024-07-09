<?php

namespace App\Service;

use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Dto\Transaction\TransactionUpdateDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TransactionService
{
    public function __construct(
        private Security              $security,
        private TransactionRepository $transactionRepository,
        private CategoryRepository    $categoryRepository,
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

        return $this->transactionRepository->search(limit: $limit, page: $page, user: $user, paymentType: $paymentType, transactionDate: $transactionDate, transactionName: $transactionName, partyName: $partyName, transactionNotes: $transactionNotes, categoryId: $categoryId, categoryName: $categoryName, categoryType: $categoryType);
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

        $this->transactionRepository->create($transactionName, $category, $moneyAmount, $paymentType, $transactionDate, $partyName, $transactionNotes);


    }


    public function update(TransactionUpdateDto $transactionUpdateDto): string
    {
        $id = $transactionUpdateDto->id;
        $transactionName = $transactionUpdateDto->transactionName;
        $category = $this->categoryRepository->findOneBy(['id' => $transactionUpdateDto->categoryId]);
        $moneyAmount = $transactionUpdateDto->moneyAmount;
        $transactionDate = $transactionUpdateDto->transactionDate;
        $paymentType = $transactionUpdateDto->paymentType;
        $partyName = $transactionUpdateDto->partyName;
        $transactionNotes = $transactionUpdateDto->transactionNotes;

        if (!$transactionName && !$category && !$moneyAmount && !$transactionDate && !$paymentType && !$partyName && !$transactionNotes) {
            return 'Nothing to update';
        }
        /** @var User $user */
        $user = $this->security->getUser();
        if(!$category){
            throw new NotFoundHttpException('Category not found');
        }
        if (!$this->categoryRepository->findOneBy(['id' => $category->getId(), 'user' => $user])) {
            throw new NotFoundHttpException('Category not owned by user');
        }
        $userHasCategory = true;
        $this->transactionRepository->update($id, $transactionName, $category, $moneyAmount, $transactionDate, $paymentType, $partyName, $transactionNotes, $user, $userHasCategory);
        return 'Update successful';


    }


    public function delete(int $id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $this->transactionRepository->delete($id, $user);
    }


}