<?php

namespace App\Repository;

use App\Dto\Transaction\SpendingsDto;
use App\Dto\Transaction\TransactionCreateDto;
use App\Dto\Transaction\TransactionQueryDto;
use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                         $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaginatorInterface $paginator
    )
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Find transactions by different parameters
     * @param TransactionQueryDto|null $transactionQueryDto
     * @param User $user
     * @return array
     */
    public function search(?TransactionQueryDto $transactionQueryDto, User $user): array
    {
        $paymentType = $transactionQueryDto->paymentType ?? null;
        $dateStart = $transactionQueryDto->dateStart ?? null;
        $dateEnd = $transactionQueryDto->dateEnd ?? null;
        $transactionName = $transactionQueryDto->transactionName ?? null;
        $partyName = $transactionQueryDto->partyName ?? null;
        $transactionNotes = $transactionQueryDto->transactionNotes ?? null;
        $categoryName = $transactionQueryDto->categoryName ?? null;
        $categoryType = $transactionQueryDto->categoryType ?? null;
        $categoryId = $transactionQueryDto->categoryId ?? null;
        $page = $transactionQueryDto->page ?? '1';
        $maxResults = $transactionQueryDto->maxResults ?? '200';

        $orderBy = $transactionQueryDto->orderBy ?? null;
        $sortBy = $transactionQueryDto->sortBy ?? null;

        if ( (!$dateStart && $dateEnd) || $dateStart > $dateEnd ) {
            throw new NotFoundHttpException('Invalid date format');
        }


        $totalResults = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->leftJoin('t.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user);


        $qb = $this->createQueryBuilder('t')
            ->select(' t.id, t.paymentType, t.transactionDate, t.moneyAmount, t.transactionName, t.partyName, t.transactionNotes, c.type, c.categoryName, c.color')
            ->leftJoin('t.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('t.user = :user')
            ->orderBy('t.transactionName', 'ASC')
            ->setParameter('user', $user);


        if ($paymentType !== null) {
            $qb->andWhere('t.paymentType = :paymentType')
                ->setParameter('paymentType', $paymentType);
            $totalResults->andWhere('t.paymentType = :paymentType')
                ->setParameter('paymentType', $paymentType);
        }

        if ($dateStart !== null && $dateEnd !== null) {
            $qb->andWhere('t.transactionDate >= :dateStart')
                ->andWhere('t.transactionDate <= :dateEnd')
                ->orderBy('t.transactionDate', 'ASC')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
            $totalResults->andWhere('t.transactionDate >= :dateStart')
                ->andWhere('t.transactionDate <= :dateEnd')
                ->setParameter('dateStart', $dateStart)
                ->setParameter('dateEnd', $dateEnd);
        }

        if ($transactionName !== null) {
            $qb->andWhere('t.transactionName LIKE :transactionName')
                ->setParameter('transactionName', "%" . $transactionName . "%");
            $totalResults->andWhere('t.transactionName LIKE :transactionName')
                ->setParameter('transactionName', "%" . $transactionName . "%");
        }

        if ($partyName !== null) {
            $qb->andWhere('t.partyName LIKE :partyName')
                ->setParameter('partyName', "%" . $partyName . "%");
            $totalResults->andWhere('t.partyName LIKE :partyName')
                ->setParameter('partyName', "%" . $partyName . "%");
        }

        if ($transactionNotes !== null) {
            $qb->andWhere('t.transactionNotes LIKE :transactionNotes')
                ->setParameter('transactionNotes', "%" . $transactionNotes . "%");
            $totalResults->andWhere('t.transactionNotes LIKE :transactionNotes')
                ->setParameter('transactionNotes', "%" . $transactionNotes . "%");
        }

        if ($categoryName !== null) {
            $qb->andWhere('c.categoryName LIKE :categoryName')
                ->setParameter('categoryName', "%" . $categoryName . "%");
            $totalResults->andWhere('c.categoryName LIKE :categoryName')
                ->setParameter('categoryName', "%" . $categoryName . "%");
        }

        if ($categoryType !== null) {
            $qb->andWhere('c.type = :categoryType')
                ->setParameter('categoryType', $categoryType);
            $totalResults->andWhere('c.type = :categoryType')
                ->setParameter('categoryType', $categoryType);
        }

        if ($categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
            $totalResults->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }
        if ($orderBy !== null) {
            // Input is validated so no sql injection is possible
            $qb->orderBy('t.' . $orderBy, $sortBy);
        } else {
            $qb->orderBy('t.transactionName', 'ASC');
        }
        $pagination = $this->paginator->paginate(
            $qb,
            $page,
            $maxResults
        );

        $transactions = $pagination->getItems();
        $totalResults = $totalResults->getQuery()->getSingleScalarResult();

        // Calculate total pages
        $totalPages = (int)ceil($pagination->getTotalItemCount() / $maxResults);
        // Calculate the previous and next page
        $previousPage = ($page > 1) ? $page - 1 : null;
        $nextPage = ($page < $totalPages) ? $page + 1 : null;


        return [
            'pagination' => [
                'currentPage' => $page,
                'previousPage' => $previousPage,
                'nextPage' => $nextPage,
                'totalPages' => $totalPages,
            ],
            'totalResults' => $totalResults,
            'transactions' => $transactions
        ];
    }

    /**
     * Returns transaction overview by months and year with totalIncome and totalExpense per each month
     * - Example:
     * - [
     * - "month" => 3,
     * - "year"=> 2024,
     * - "totalIncome" => 3254.036,
     * - "totalExpense" => 3872.847
     * - ] ...
     * @param User $user
     * @param int $year
     * @return array
     */
    public function transactionOverview(User $user, int $year): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('
            MONTH(t.transactionDate) AS month, YEAR(t.transactionDate) AS year, 
            SUM(CASE WHEN c.type = \'income\' THEN t.moneyAmount ELSE 0 END) AS totalIncome, 
            SUM(CASE WHEN c.type = \'expense\' THEN t.moneyAmount ELSE 0 END) AS totalExpense')
            ->leftJoin('t.category', 'c')
            ->where('t.user = :user OR c.user IS NULL')
            ->andWhere('YEAR(t.transactionDate) = :year')
            ->setParameter('user', $user)
            ->setParameter('year',$year)
            ->groupBy('year, month')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns sum of transactions per category that user made
     * @param string $month
     * @param string $year
     * @param User $user
     * @return array
     */
    public function spendingByCategories(string $month, string $year, User $user): array
    {
        // Calculate the total expenses for the given month
        $totalExpenses = $this->createQueryBuilder('t')
            ->select('SUM(t.moneyAmount) as totalMonthlyExpense')
            ->leftJoin('t.category', 'c')
            ->where('c.type = \'expense\'')
            ->andWhere('t.user = :user')
            ->andWhere('YEAR(t.transactionDate) = :year')
            ->andWhere('MONTH(t.transactionDate) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getSingleScalarResult();

        // Get total Expense for each category individually
        $results = $this->createQueryBuilder('t')
            ->select('c.categoryName, SUM(t.moneyAmount) as totalExpense')
            ->leftJoin('t.category', 'c')
            ->where('c.type = \'expense\'')
            ->andWhere('t.user = :user')
            ->andWhere('YEAR(t.transactionDate) = :year')
            ->andWhere('MONTH(t.transactionDate) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->groupBy('c.categoryName')
            ->orderBy('c.categoryName', 'ASC')
            ->getQuery()
            ->getResult();

        // Calculate the percentage for each category (&$result is a reference to the current element of $results) this
        // allows direct modification of original array $results
        foreach ($results as &$result) {
            $result['percentage'] = ($totalExpenses > 0) ? ($result['totalExpense'] / $totalExpenses) * 100 : 0;
        }

        return $results;
    }

    /**
     * Create new transaction
     * @param TransactionCreateDto $transactionCreateDto
     * @param User $user
     * @param Category $category
     * @return void
     * @throws \Exception
     */
    public function create(TransactionCreateDto $transactionCreateDto, User $user, Category $category): void
    {
        $moneyAmount = $transactionCreateDto->moneyAmount;
        $transactionDate = $transactionCreateDto->transactionDate;
        $paymentType = $category->getType() === "expense" ? $transactionCreateDto->paymentType : null;
        $partyName = $transactionCreateDto->partyName;
        $transactionNotes = $transactionCreateDto->transactionNotes;
        $transactionName = $transactionCreateDto->transactionName;


        $newTransaction = new Transaction();
        $newTransaction->setUser($user);
        $newTransaction->setCategory($category);
        $newTransaction->setPaymentType($paymentType);
        $newTransaction->setTransactionDate(new \DateTimeImmutable($transactionDate));
        $newTransaction->setMoneyAmount($moneyAmount);
        $newTransaction->setTransactionName($transactionName);
        if (null !== $transactionNotes) {
            $newTransaction->setTransactionNotes($transactionNotes);
        }
        if (null !== $partyName) {
            $newTransaction->setPartyName($partyName);
        }

        $this->entityManager->persist($newTransaction);
        $this->entityManager->flush();
    }


    public function update(int $id, ?string $transactionName, ?Category $category, ?float $moneyAmount, ?\DateTimeImmutable $transactionDate, $paymentType, ?string $partyName, ?string $transactionNotes, User $user): void
    {
        $transaction = $this->findByIdAndUser($id, $user);


        if (!$transaction) {
            throw new NotFoundHttpException('Transaction not found or doesn\'t belong to you.');
        }

        if ($category && $category->getType() === 'expense') {
            $transaction->setCategory($category);
        }

        if ($category && $category->getType() === 'income') {
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

        $this->entityManager->flush();

    }


    /**
     * Delete selected transaction
     * @param int $id
     * @param User $user
     * @return void
     */
    public function delete(int $id, User $user): void
    {
        $transaction = $this->findByIdAndUser($id, $user);

        if (!$transaction) {
            throw new NotFoundHttpException('Transaction not found.');
        }

        $this->entityManager->remove($transaction);
        $this->entityManager->flush();

    }

    public function fetchSpecificColumns(
        User $user,
        bool $categoryId = false,
        bool $paymentType = false,
        bool $transactionDate = false,
        bool $moneyAmount = false,
        bool $transactionName = false,
        bool $partyName = false,
        bool $transactionNotes = false,
    ): array
    {
        $columns = [
            't.paymentType' => $paymentType ,
            't.moneyAmount' => $moneyAmount,
            't.transactionName' => $transactionName,
            't.partyName' => $partyName,
            't.transactionNotes' => $transactionNotes,
        ];

        // Filter the columns array and return keys where values are true
        $selectedColumns = array_keys(array_filter($columns));

        $transactions = $this->createQueryBuilder('t');

        // If categoryId is true, do a left join and pull 3 more columns from categories
        if ($categoryId){
            $transactions->leftJoin('t.category','c');
            $selectedColumns = array_merge($selectedColumns, ['c.categoryName', 'c.type', 'c.color']);
        }

        // Add separate day, month, and year fields if $transactionDate is true
        if ($transactionDate) {
            $selectedColumns = array_merge(
                $selectedColumns,
                [
                    "DATE_FORMAT(t.transactionDate, '%Y') AS year",
                    "DATE_FORMAT(t.transactionDate, '%m') AS month",
                    "DATE_FORMAT(t.transactionDate, '%d') AS day"
                ]
            );
        }

        if (!empty($selectedColumns)) {
            $transactions->select(implode(', ', $selectedColumns));
        }


        $transactions->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.transactionDate','ASC');

        return $transactions->getQuery()->getResult();

    }

    public function findByIdAndUser(int $id, User $user): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->where('t.id = :id')
            ->andWhere('t.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
