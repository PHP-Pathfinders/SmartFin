<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Transaction;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                $registry,
        private PaginatorInterface     $paginator,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, Budget::class);
    }

    public function doesBudgetExistForCategoryAndMonth(Category $category, User $user, \DateTimeInterface $dateTime): bool
    {

        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.category = :category')
            ->andWhere('YEAR(b.monthlyBudgetDate) = YEAR(:date)')
            ->andWhere('MONTH(b.monthlyBudgetDate) = MONTH(:date)')
            ->andWhere('b.user = :user')
            ->setParameter('category', $category)
            ->setParameter('date', $dateTime)
            ->setParameter('user', $user);

        $count = $qb->getQuery()
            ->getSingleScalarResult();

        return $count > 0;


    }


    public function searchWithStats(string $page, string $maxResults, string $dateStart, string $dateEnd, User $user): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.id, b.monthlyBudget, b.monthlyBudgetDate, c.id as categoryId, c.categoryName, c.color,
            COALESCE(SUM(CASE WHEN MONTH(t.transactionDate) = MONTH(b.monthlyBudgetDate) AND YEAR(t.transactionDate) = YEAR(b.monthlyBudgetDate) THEN t.moneyAmount ELSE 0 END),0) as total,
                  COALESCE((SUM(CASE WHEN MONTH(t.transactionDate) = MONTH(b.monthlyBudgetDate) AND YEAR(t.transactionDate) = YEAR(b.monthlyBudgetDate) THEN t.moneyAmount ELSE 0 END) / b.monthlyBudget) * 100, 0) as percent')
            ->innerJoin('b.category', 'c')
            ->leftJoin(Transaction::class, 't', 'WITH', 't.category = c.id AND t.user = :user AND t.transactionDate >= :dateStart AND t.transactionDate <= :dateEnd')
            ->andWhere('b.user = :user')
            ->andWhere('b.monthlyBudgetDate >= :dateStart AND b.monthlyBudgetDate <= :dateEnd')
            ->setParameter('user', $user)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->groupBy('b.id')
            ->orderBy('b.id', 'ASC');


        $pagination = $this->paginator->paginate(
            $qb,
            $page,
            $maxResults
        );

        $budgets = $pagination->getItems();

        $totalPages = (int)ceil($pagination->getTotalItemCount() / $maxResults);

        $previousPage = ($page > 1) ? $page - 1 : null;
        $nextPage = ($page < $totalPages) ? $page + 1 : null;

        return [
            'pagination' => [
                'currentPage' => $page,
                'previousPage' => $previousPage,
                'nextPage' => $nextPage,
                'totalPages' => $totalPages,
            ],
            'budgets' => $budgets
        ];

    }

    public function create(Budget $newBudget): void
    {
        $this->entityManager->persist($newBudget);
        $this->entityManager->flush();
    }


    public function update(): void
    {
        $this->entityManager->flush();
    }

    public function delete(Budget $budget): void
    {
        $this->entityManager->remove($budget);
        $this->entityManager->flush();
    }

    /**
     * Fetches $amount number of random budgets by $month and $year
     * - Calculates a sum of expense transactions for budgets that have same category like transaction in $month and $year
     * @param string $month
     * @param string $year
     * @param string $amount
     * @param User $user
     * @return array
     */
    public function fetchRandomBudgets(string $month, string $year, string $amount, User $user): array
    {
        // Get all id's for budgets with given criteria
        $ids = $this->createQueryBuilder('b')
            ->select('b.id')
            ->innerJoin('b.category', 'c')
            ->andWhere('b.user = :user')
            ->andWhere('c.user = :user or c.user IS NULL')
            ->andWhere('MONTH(b.monthlyBudgetDate) = :month')
            ->andWhere('YEAR(b.monthlyBudgetDate) = :year')
            ->andWhere('c.type = \'expense\'')
            ->setParameter('user', $user)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->getQuery()
            ->getArrayResult();

        // Randomly pick the desired number of IDs
        $selectedIds = array_column($ids, 'id');
        shuffle($selectedIds);
        $selectedIds = array_slice($selectedIds, 0, (int)$amount);
        // Fetch the random budgets by selected IDs
        $query = $this->getEntityManager()->createQuery('
        SELECT 
            b.id,
            b.monthlyBudget,
            b.monthlyBudgetDate,
            c.id AS categoryId,
            c.categoryName,
            c.color,
            COALESCE(SUM(CASE WHEN t.paymentType IN (\'cash\', \'card\') THEN t.moneyAmount ELSE 0 END), 0) AS totalSpent,
            COALESCE((SUM(CASE WHEN t.paymentType IN (\'cash\', \'card\') THEN t.moneyAmount ELSE 0 END) / b.monthlyBudget) * 100, 0) AS percentageSpent
        FROM 
            App\Entity\Budget b
        LEFT JOIN 
            b.category c
        LEFT JOIN 
            App\Entity\Transaction t WITH t.category = c.id AND t.user = b.user
            AND MONTH(t.transactionDate) = :month AND YEAR(t.transactionDate) = :year
        WHERE 
            b.user = :user
            AND MONTH(b.monthlyBudgetDate) = :month
            AND YEAR(b.monthlyBudgetDate) = :year
            AND b.id IN (:ids)
        GROUP BY 
            b.id
        ORDER BY
            c.categoryName
    ')
            ->setParameter('user', $user)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->setParameter('ids', $selectedIds);

        return $query->getResult();
    }

    public function findByCategoryAndDate(Category $category, string $budgetDate, User $user): ?Budget
    {
        return $this->createQueryBuilder('b')
            ->where('b.category = :category')
            ->andWhere('b.user = :user')
            ->andWhere('MONTH(b.monthlyBudgetDate) = MONTH(:date) AND YEAR(b.monthlyBudgetDate) = YEAR(:date)')
            ->setParameter('category', $category)
            ->setParameter('user', $user)
            ->setParameter('date', $budgetDate)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findByIdAndUser(int $id, User $user): ?Budget
    {
        return $this->createQueryBuilder('b')
            ->where('b.id = :id')
            ->andWhere('b.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();


    }


}
