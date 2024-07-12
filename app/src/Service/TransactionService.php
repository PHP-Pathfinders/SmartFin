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
        $category = $this->categoryRepository->findByIdUserAndType($transactionCreateDto->categoryId, $user, $transactionCreateDto->categoryType);

        if (!$category) {
            throw new NotFoundHttpException("Invalid category given");
        }

        $this->transactionRepository->create($transactionCreateDto, $user, $category);


    }


    public function update(TransactionUpdateDto $transactionUpdateDto): string
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $id = $transactionUpdateDto->id;
        $transaction = $this->transactionRepository->findByIdAndUser($id, $user);

        /** @var Category $currentCategory */
        $currentCategory = $this->transactionRepository->findbyIdAndUser($id, $user)->getCategory();
        $currentCategoryType = $currentCategory->getType();


        if (!$transaction) {
            throw new NotFoundHttpException("Transaction not owned by you or does not exist");
        }

        $category = $transactionUpdateDto->categoryId ? $this->categoryRepository->findByIdAndUser($transactionUpdateDto->categoryId, $user) : $currentCategory ;

        if (!$category) {
            throw new NotFoundHttpException("Category could not be found");
        }

        if($currentCategoryType !== $category->getType()){
            throw new NotFoundHttpException("Can't change income to expense and vice versa");
        }

        $transactionName = $transactionUpdateDto->transactionName;
        $moneyAmount = $transactionUpdateDto->moneyAmount;
        $transactionDate = $transactionUpdateDto->transactionDate ? new \DateTimeImmutable($transactionUpdateDto->transactionDate) : null;
        $paymentType = $category->getType() === 'expense' ? $transactionUpdateDto->paymentType : null;
        $partyName = $transactionUpdateDto->partyName;
        $transactionNotes = $transactionUpdateDto->transactionNotes;

        if (!$transactionName && !$moneyAmount && !$transactionDate && !$paymentType && !$partyName && !$transactionNotes && $category === $currentCategory) {
            return 'Nothing to update';
        }

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