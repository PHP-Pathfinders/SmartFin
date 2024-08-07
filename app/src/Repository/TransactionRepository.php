<?php

namespace App\Repository;

use App\Dto\Transaction\TransactionQueryDto;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                         $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaginatorInterface     $paginator
    )
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Find transactions by different parameters
     * @param TransactionQueryDto $transactionQueryDto
     * @param User $user
     * @return array
     */
    public function search(TransactionQueryDto $transactionQueryDto, User $user): array
    {

        $qb = $this->createQueryBuilder('t')
            ->select(' t.id, t.paymentType, t.transactionDate, t.moneyAmount, t.transactionName, t.partyName, t.transactionNotes,c.id as categoryId, c.type, c.categoryName, c.color')
            ->leftJoin('t.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('t.user = :user')
            ->orderBy('t.transactionName', 'ASC')
            ->setParameter('user', $user);


        if ($transactionQueryDto->paymentType !== null) {
            $qb->andWhere('t.paymentType = :paymentType')
                ->setParameter('paymentType', $transactionQueryDto->paymentType);
        }

        if ($transactionQueryDto->dateStart !== null && $transactionQueryDto->dateEnd !== null) {
            $qb->andWhere('t.transactionDate >= :dateStart')
                ->andWhere('t.transactionDate <= :dateEnd')
                ->orderBy('t.transactionDate', 'ASC')
                ->setParameter('dateStart', $transactionQueryDto->dateStart)
                ->setParameter('dateEnd', $transactionQueryDto->dateEnd);
        }

        if ($transactionQueryDto->transactionName !== null) {
            $qb->andWhere('t.transactionName LIKE :transactionName')
                ->setParameter('transactionName', "%" . $transactionQueryDto->transactionName . "%");
        }

        if ($transactionQueryDto->partyName !== null) {
            $qb->andWhere('t.partyName LIKE :partyName')
                ->setParameter('partyName', "%" . $transactionQueryDto->partyName . "%");
        }

        if ($transactionQueryDto->transactionNotes !== null) {
            $qb->andWhere('t.transactionNotes LIKE :transactionNotes')
                ->setParameter('transactionNotes', "%" . $transactionQueryDto->transactionNotes . "%");
        }

        if ($transactionQueryDto->categoryName !== null) {
            $qb->andWhere('c.categoryName LIKE :categoryName')
                ->setParameter('categoryName', "%" . $transactionQueryDto->categoryName . "%");
        }

        if ($transactionQueryDto->categoryType !== null) {
            $qb->andWhere('c.type = :categoryType')
                ->setParameter('categoryType', $transactionQueryDto->categoryType);
        }

        if ($transactionQueryDto->categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $transactionQueryDto->categoryId);
        }
        if ($transactionQueryDto->orderBy !== null) {
            // Input is validated so no sql injection is possible
            $qb->orderBy('t.' . $transactionQueryDto->orderBy, $transactionQueryDto->sortBy);
        } else {
            $qb->orderBy('t.transactionName', 'ASC');
        }
        $pagination = $this->paginator->paginate(
            $qb,
            $transactionQueryDto->page,
            $transactionQueryDto->maxResults
        );

        $transactions = $pagination->getItems();

        // Calculate total pages
        $totalPages = (int)ceil($pagination->getTotalItemCount() / $transactionQueryDto->maxResults);
        // Calculate the previous and next page
        $previousPage = ($transactionQueryDto->page > 1) ? $transactionQueryDto->page - 1 : null;
        $nextPage = ($transactionQueryDto->page < $totalPages) ? $transactionQueryDto->page + 1 : null;


        return [
            'pagination' => [
                'currentPage' => $transactionQueryDto->page,
                'previousPage' => $previousPage,
                'nextPage' => $nextPage,
                'totalPages' => $totalPages,
            ],
            'totalResults' => $pagination->getTotalItemCount(),
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
            ->innerJoin('t.category', 'c')
            ->where('t.user = :user')
            ->andWhere('c.user = :user OR c.user IS NULL')
            ->andWhere('YEAR(t.transactionDate) = :year')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
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

    public function fetchDashboard(User $user, string $year, string $month): array
    {
        if ($month == 1) {
            $previousMonth = 12;
            $previousYear = $year - 1;
        } else {
            $previousMonth = $month - 1;
            $previousYear = $year;
        }


        $dashboardData = $this->createQueryBuilder('t')
            ->select('
            MONTH(t.transactionDate) AS month, YEAR(t.transactionDate) AS year, 
            SUM(CASE WHEN c.type = \'income\' THEN t.moneyAmount ELSE 0 END) AS totalIncome, 
            SUM(CASE WHEN c.type = \'expense\' THEN t.moneyAmount ELSE 0 END) AS totalExpense,
            (SUM(CASE WHEN c.type = \'income\' THEN t.moneyAmount ELSE 0 END)) - (SUM(CASE WHEN c.type = \'expense\' THEN t.moneyAmount ELSE 0 END)) AS savings
            ')
            ->leftJoin('t.category', 'c')
            ->where('t.user = :user')
            ->andWhere('YEAR(t.transactionDate) = :year AND MONTH(t.transactionDate) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->groupBy('year, month')
            ->orderBy('year', 'ASC')
            ->addOrderBy('month', 'ASC');

        $results['current'] = $dashboardData->getQuery()->getResult();
        $results['previous'] = $dashboardData->where('t.user = :user')
            ->andWhere('YEAR(t.transactionDate) = :year AND MONTH(t.transactionDate) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $previousYear)
            ->setParameter('month', $previousMonth)
            ->getQuery()
            ->getResult();
        return $results;
    }

    /**
     * Create new transaction
     * @param Transaction $newTransaction
     * @return void
     */
    public function create(Transaction $newTransaction): void
    {
        $this->entityManager->persist($newTransaction);
        $this->entityManager->flush();
    }


    public function update(): void
    {
        $this->entityManager->flush();
    }


    /**
     * Delete selected transaction
     * @param Transaction $transaction
     * @return void
     */
    public function delete(Transaction $transaction): void
    {
        $this->entityManager->remove($transaction);
        $this->entityManager->flush();
    }

    public function fetchSpecificColumns(
        User $user,
        bool $categoryName = false,
        bool $type = false,
        bool $color = false,
        bool $paymentType = false,
        bool $transactionDate = false,
        bool $moneyAmount = false,
        bool $transactionName = false,
        bool $partyName = false,
        bool $transactionNotes = false,
    ): array
    {

        $columns = [
            'c.color' => $color,
            'c.categoryName' => $categoryName,
            'c.type' => $type,
            't.moneyAmount' => $moneyAmount,
            't.paymentType' => $paymentType,
            't.transactionName' => $transactionName,
            't.partyName' => $partyName,
            't.transactionNotes' => $transactionNotes,
        ];

        // Filter the columns array and return keys where values are true
        $selectedColumns = array_keys(array_filter($columns));

        $transactions = $this->createQueryBuilder('t');

        // If only one or more category fields are true, join category
        if ($categoryName || $type || $color) {
            $transactions->leftJoin('t.category', 'c');
        }
        // Add separate day, month, and year fields if $transactionDate is true

        if ($transactionDate) {
            $selectedColumns = array_merge(
                $selectedColumns,
                [
                    "DATE_FORMAT(t.transactionDate, '%d') AS day",
                    "DATE_FORMAT(t.transactionDate, '%m') AS month",
                    "DATE_FORMAT(t.transactionDate, '%Y') AS year"
                ]
            );
        }
        if (!empty($selectedColumns)) {
            $transactions->select(implode(', ', $selectedColumns));
        }


        $transactions->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.transactionDate', 'DESC');

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
