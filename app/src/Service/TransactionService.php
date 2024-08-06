<?php

namespace App\Service;

use App\Dto\Transaction\SpendingsDto;
use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Dto\Transaction\TransactionUpdateDto;
use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\DashboardRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class TransactionService
{
    public function __construct(
        private Security              $security,
        private TransactionRepository $transactionRepository,
        private CategoryRepository    $categoryRepository,
        private DashboardRepository   $dashboardRepository
    )
    {
    }

    public function search(?TransactionQueryDto $transactionQueryDto): array
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();

        if (null === $transactionQueryDto) {
            throw new NotFoundHttpException('No parameters given');
        }

        return $this->transactionRepository->search($transactionQueryDto, $user);
    }

    public function transactionOverview(int $year): array
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        return $this->transactionRepository->transactionOverview($user, $year);
    }

    public function spendingByCategories(string $month, string $year): array
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        return $this->transactionRepository->spendingByCategories($month, $year, $user);
    }


    public function create(TransactionCreateDto $transactionCreateDto): Transaction
    {

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var Category $category */
        $category = $this->categoryRepository->findByIdUserAndType($transactionCreateDto->categoryId, $user, $transactionCreateDto->categoryType);

        if (!$category) {
            throw new NotFoundHttpException("Category could not be found or doesn't match given Type");
        }

        $paymentType = $category->getType() === "expense" ? $transactionCreateDto->paymentType : null;

        $newTransaction = new Transaction();
        $newTransaction->setUser($user);
        $newTransaction->setCategory($category);
        $newTransaction->setPaymentType($paymentType);
        $newTransaction->setTransactionDate(new \DateTimeImmutable($transactionCreateDto->transactionDate));
        $newTransaction->setMoneyAmount($transactionCreateDto->moneyAmount);
        $newTransaction->setTransactionName($transactionCreateDto->transactionName);
        if (null !== $transactionCreateDto->transactionNotes) {
            $newTransaction->setTransactionNotes($transactionCreateDto->transactionNotes);
        }
        if (null !== $transactionCreateDto->partyName) {
            $newTransaction->setPartyName($transactionCreateDto->partyName);
        }

//        $dashboard = $this->dashboardRepository->findByDateAndUser($user, $transactionCreateDto->transactionDate);


//        $prevDashboard = $this->dashboardRepository->findByDateAndUser($user,(new \DateTime($transactionCreateDto->transactionDate))->modify('last day of previous month')->format('Y-m-d'));


        $this->transactionRepository->create($newTransaction);

        return $newTransaction;

    }


    public function update(TransactionUpdateDto $transactionUpdateDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $transaction = $this->transactionRepository->findByIdAndUser($transactionUpdateDto->id, $user);

        if (!$transaction) {
            throw new NotFoundHttpException("Transaction not owned by you or does not exist");
        }

        /** @var Category $currentCategory */
        $currentCategory = $transaction->getCategory();
        $currentCategoryType = $currentCategory->getType();

        $category = $transactionUpdateDto->categoryId ? $this->categoryRepository->findByIdAndUser($transactionUpdateDto->categoryId, $user) : $currentCategory;

        if (!$category) {
            throw new NotFoundHttpException( "Category could not be found or doesn't match given Type");
        }

        if ($currentCategoryType !== $category->getType()) {
            throw new ConflictHttpException("Can't change income to expense and vice versa");
        }

        $transactionName = $transactionUpdateDto->transactionName;
        $moneyAmount = $transactionUpdateDto->moneyAmount;
        $transactionDate = $transactionUpdateDto->transactionDate ? new \DateTimeImmutable($transactionUpdateDto->transactionDate) : null;
        $paymentType = $category->getType() === 'expense' ? $transactionUpdateDto->paymentType : null;
        $partyName = $transactionUpdateDto->partyName;
        $transactionNotes = $transactionUpdateDto->transactionNotes;

        if (!$transactionName && !$moneyAmount && !$transactionDate && !$paymentType && !$partyName && !$transactionNotes && $category->getId() === $currentCategory->getId()) {
            return ['message' => 'Nothing to update'];
        }


        if ($category->getType() === 'expense') {
            $transaction->setCategory($category);
        }

        if ($category->getType() === 'income') {
            $transaction->setCategory($category);
            $transaction->setPaymentType(null);
        }

        if ($transactionName) {
            $transaction->setTransactionName($transactionName);
        }

        if ($moneyAmount) {
            $transaction->setMoneyAmount($moneyAmount);
        }

        if ($transactionDate) {
            $transaction->setTransactionDate($transactionDate);
        }

        if ($paymentType) {
            $transaction->setPaymentType($paymentType);
        }

        if ($partyName) {
            $transaction->setPartyName($partyName);
        }

        if ($transactionNotes) {
            $transaction->setTransactionNotes($transactionNotes);
        }

        $this->transactionRepository->update();

        return ['message' => 'Update successful', 'transaction' => $transaction];


    }


    public function delete(int $id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $transaction = $this->transactionRepository->findByIdAndUser($id, $user);

        if (!$transaction) {
            throw new NotFoundHttpException('No transaction found.');
        }

        $this->transactionRepository->delete($transaction);
    }


}