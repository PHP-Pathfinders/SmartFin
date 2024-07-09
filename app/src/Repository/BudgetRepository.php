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
