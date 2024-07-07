<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, Transaction::class);
    }

    public function findAllByParameters(int $limit, int $page, User $user,string|null $paymentType, string|null $transactionDate, string|null $transactionName, string|null $partyName, string|null $transactionNotes, int|null $categoryId, string|null $categoryName, string|null $categoryType): array
    {
        $totalResults = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->leftJoin('t.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user);


        //TODO add category color to select and also update pagination
        $qb = $this->createQueryBuilder('t')
            ->select(' t.id, t.paymentType, t.transactionDate, t.moneyAmount, t.transactionName, t.partyName, t.transactionNotes, c.type, c.categoryName')
            ->leftJoin('t.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('c.user = :user')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->setParameter('user', $user);


        if ($paymentType !== null) {
            $qb->andWhere('t.paymentType = :paymentType')
                ->setParameter('paymentType', $paymentType);
            $totalResults->andWhere('t.paymentType = :paymentType')
                ->setParameter('paymentType', $paymentType);
        }

        if ($transactionDate !== null) {
            $qb->andWhere('MONTH(t.transactionDate) = MONTH(:transactionDate)')
                ->andWhere('YEAR(t.transactionDate) = YEAR(:transactionDate)')
                ->setParameter('transactionDate', $transactionDate);
            $totalResults->andWhere('MONTH(t.transactionDate) = MONTH(:transactionDate)')
                ->andWhere('YEAR(t.transactionDate) = YEAR(:transactionDate)')
                ->setParameter('transactionDate', $transactionDate);
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
            $qb->andWhere('c.categoryName = :categoryName')
                ->setParameter('categoryName', $categoryName);
            $totalResults->andWhere('c.categoryName = :categoryName')
                ->setParameter('categoryName', $categoryName);
        }

        if ($categoryType !== null) {
            $qb->andWhere('c.type = :categoryType')
                ->setParameter('categoryType', $categoryType);
            $totalResults->andWhere('c.type = :categoryType')
                ->setParameter('categoryType', $categoryType);
        }

        if($categoryId !== null){
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
            $totalResults->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        $transactions = $qb->getQuery()->getArrayResult();
        $totalResults = $totalResults->getQuery()->getSingleScalarResult();

        $totalPages = (int) ceil($totalResults / $limit);
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

    public function create(string $transactionName,Category $category,float $moneyAmount, string $paymentType, $transactionDate): void
    {
        $newTransaction = new Transaction();
        $newTransaction->setTransactionName($transactionName);
        $newTransaction->setCategory($category);
        $newTransaction->setMoneyAmount($moneyAmount);
        $newTransaction->setPaymentType($paymentType);
        $newTransaction->setTransactionDate($transactionDate);

        $this->entityManager->persist($newTransaction);
        $this->entityManager->flush();
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
