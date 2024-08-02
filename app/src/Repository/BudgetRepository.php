<?php

namespace App\Repository;

use App\Dto\Budget\BudgetCreateDto;
use App\Dto\Budget\BudgetQueryDto;
use App\Dto\Budget\BudgetUpdateDto;
use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function Zenstruck\Foundry\set;
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


    public function search(?BudgetQueryDto $budgetQueryDto, User $user): array
    {
        $page = $budgetQueryDto->page ?? '1';
        $maxResults = $budgetQueryDto->maxResults ?? '200';
        $dateStart = $budgetQueryDto->dateStart ?? (new \DateTime('first day of this month'))->format('Y-m-d');
        $dateEnd = $budgetQueryDto->dateEnd ?? (new \DateTime('last day of this month'))->format('Y-m-d');

        if ($dateStart > $dateEnd) {
            throw new NotFoundHttpException('Invalid date format');
        }

        $qb = $this->createQueryBuilder('b')
            ->select('b.id, b.monthlyBudget, b.monthlyBudgetDate, c.id as categoryID, c.categoryName, c.color, COALESCE(SUM(t.moneyAmount), 0) as total, 
                  CASE WHEN b.monthlyBudget > 0 THEN ((COALESCE(SUM(t.moneyAmount), 0) / b.monthlyBudget) * 100) ELSE 0 END as percent')
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

    public function create(BudgetCreateDto $budgetCreateDto, User $user, Category $category, string $date): void
    {
        $monthlyBudget = $budgetCreateDto->monthlyBudgetAmount;

        $newBudget = new Budget();
        $newBudget->setMonthlyBudget($monthlyBudget);
        $newBudget->setCategory($category);
        $newBudget->setUser($user);
        $newBudget->setMonthlyBudgetDate(new \DateTimeImmutable($date));

        $this->entityManager->persist($newBudget);
        $this->entityManager->flush();

    }


    public function update(Budget $budget, BudgetUpdateDto $budgetUpdateDto, User $user, Category $category, string $date): void
    {
        $monthlyBudget = $budgetUpdateDto->monthlyBudgetAmount;

        $budget->setCategory($category);

        if ($monthlyBudget) {
            $budget->setMonthlyBudget($monthlyBudget);
        }

        $budget->setMonthlyBudgetDate(new \DateTimeImmutable($date));


        $this->entityManager->flush();

    }

    public function delete(int $id, User $user): void
    {
        $budget = $this->findByIdAndUser($id, $user);


        if (!$budget) {
            throw new NotFoundHttpException('Budget not found or not owned by you');
        }

        $this->entityManager->remove($budget);
        $this->entityManager->flush();

    }

    /**
     * Fetches $amount number of categories by $month and $year
     * - Calculates a sum of expense transactions ONLY if
     * - budget exists for category where transaction is made
     * @param string $month
     * @param string $year
     * @param string $amount
     * @param User $user
     * @return array
     */
    public function fetchRandomBudgets(string $month, string $year, string $amount, User $user): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.id, b.monthlyBudget, b.monthlyBudgetDate, c.id as categoryId, c.categoryName, c.color, 
            SUM(t.moneyAmount) as totalSpent,
            (SUM(t.moneyAmount) / b.monthlyBudget) * 100 AS percentageSpent
            ')
            ->innerJoin('b.category', 'c')
            ->innerJoin('c.transactions', 't')
            ->andWhere('b.user = :user')
            ->andWhere('MONTH(b.monthlyBudgetDate) = :month')
            ->andWhere('YEAR(b.monthlyBudgetDate) = :year')
            ->andWhere('c.type = \'expense\'')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->groupBy('b.id')
            ->setMaxResults((int)$amount)
            ->addOrderBy('RAND()')
            ->getQuery()
            ->getResult();
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
