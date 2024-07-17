<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Zenstruck\Foundry\set;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    public function doesBudgetExistForCategoryAndMonth(Category $category,User $user, \DateTimeInterface $dateTime ): bool
    {

        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.category = :category')
            ->andWhere('YEAR(b.monthlyBudgetDate) = YEAR(:date)')
            ->andWhere('MONTH(b.monthlyBudgetDate) = MONTH(:date)')
            ->andWhere('b.user = :user')
            ->setParameter('category', $category)
            ->setParameter('date' , $dateTime)
            ->setParameter('user', $user);

        $count =  $qb->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function fetchRandomBudgets(string $month, string $year, string $amount,User $user): array
    {
        return $this->createQueryBuilder('b')
            ->select('b.id, b.monthlyBudget, b.monthlyBudgetDate, c.id as categoryId, c.categoryName, c.color')
            ->leftJoin('b.category', 'c')
            ->andWhere('b.user = :user')
            ->andWhere('MONTH(b.monthlyBudgetDate) = :month')
            ->andWhere('YEAR(b.monthlyBudgetDate) = :year')
            ->andWhere('c.type = \'expense\'')
            ->setParameter('user', $user)
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->setMaxResults((int)$amount)
            ->orderBy('RAND()')
            ->getQuery()
            ->getResult();
    }
}
