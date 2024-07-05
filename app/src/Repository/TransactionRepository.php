<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

        public function findAllByParameters($paymentType, User $user, $transactionDate): array
        {
            $qb = $this->createQueryBuilder('t')
                ->leftJoin('t.category', 'c')
                ->leftJoin('c.user', 'u')
                ->andWhere('c.user = :user')
                ->setParameter('user', $user);

            if($paymentType !== null){
                $qb->andWhere('t.paymentType = :paymentType')
                    ->setParameter('paymentType', $paymentType);
            }

            if($transactionDate !== null){
                $qb->andWhere('MONTH(t.transactionDate) = MONTH(:transactionDate)')
                    ->andWhere('YEAR(t.transactionDate) = YEAR(:transactionDate)')
                    ->setParameter('transactionDate', $transactionDate);
            }

            return $qb->getQuery()->getArrayResult();
        }



    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Transaction
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
