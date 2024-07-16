<?php

namespace App\Repository;

use App\Dto\Budget\BudgetQueryDto;
use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use function Zenstruck\Foundry\set;
use App\Entity\Transaction;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                $registry,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface     $paginator,
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
        $page = $transactionQueryDto->page ?? '1';
        $maxResults = $transactionQueryDto->maxResults ?? '200';
        $date = $budgetQueryDto->date ?? (new \DateTime())->format('Y-m-d');

        $qb = $this->createQueryBuilder('b')
            ->select('b.id, b.monthlyBudget, b.monthlyBudgetDate, c.id as categoryID, c.categoryName, c.color, COALESCE(SUM(t.moneyAmount), 0) as total, 
                  CASE WHEN b.monthlyBudget > 0 THEN ((COALESCE(SUM(t.moneyAmount), 0) / b.monthlyBudget) * 100) ELSE 0 END as percent')
            ->innerJoin('b.category', 'c')
            ->leftJoin(Transaction::class, 't', 'WITH', 't.category = c.id AND t.user = :user AND MONTH(t.transactionDate) = MONTH(:date) AND YEAR(t.transactionDate) = YEAR(:date)')
            ->andWhere('b.user = :user')
            ->andWhere('MONTH(b.monthlyBudgetDate) = MONTH(:date) AND YEAR(b.monthlyBudgetDate) = YEAR(:date)')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
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

    //    /**
    //     * @return Budget[] Returns an array of Budget objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Budget
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
