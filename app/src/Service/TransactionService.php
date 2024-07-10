<?php

namespace App\Service;

use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Dto\Transaction\TransactionUpdateDto;
use App\Entity\Category;
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
        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        return $this->transactionRepository->search($transactionQueryDto, $user);
    }


    public function create(TransactionCreateDto $transactionCreateDto): void
    {

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var Category $category */
        $category = $this->categoryRepository->findOneBy(['id' => $transactionCreateDto->categoryId]);


        $this->transactionRepository->create($transactionCreateDto, $user, $category);


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

        $this->transactionRepository->update($id, $transactionName, $category, $moneyAmount, $transactionDate, $paymentType, $partyName, $transactionNotes, $user);

        return 'Update successful';


    }


    public function delete(int $id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $this->transactionRepository->delete($id, $user);
    }


}