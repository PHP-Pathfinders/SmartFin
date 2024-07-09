<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                $registry,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Find transactions by different parameters
     * @param int $limit
     * @param int $page
     * @param User $user
     * @param string|null $paymentType
     * @param \DateTimeImmutable|null $transactionDate
     * @param string|null $transactionName
     * @param string|null $partyName
     * @param string|null $transactionNotes
     * @param int|null $categoryId
     * @param string|null $categoryName
     * @param string|null $categoryType
     * @return array
     */
    public function search(int $limit, int $page, User $user, string|null $paymentType, \DateTimeImmutable|null $transactionDate, string|null $transactionName, string|null $partyName, string|null $transactionNotes, int|null $categoryId, string|null $categoryName, string|null $categoryType): array
    {
        $totalResults = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->leftJoin('t.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('c.user = :user')
            ->setParameter('user', $user);


        $qb = $this->createQueryBuilder('t')
            ->select(' t.id, t.paymentType, t.transactionDate, t.moneyAmount, t.transactionName, t.partyName, t.transactionNotes, c.type, c.categoryName, c.color')
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

        $transactions = $qb->getQuery()->getArrayResult();
        $totalResults = $totalResults->getQuery()->getSingleScalarResult();

        $totalPages = (int)ceil($totalResults / $limit);
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
     * Create new transaction
     * @param string $transactionName
     * @param Category $category
     * @param float $moneyAmount
     * @param string $paymentType
     * @param \DateTimeImmutable $transactionDate
     * @param string|null $partyName
     * @param string|null $transactionNotes
     * @return void
     */
    public function create(string $transactionName, Category $category, float $moneyAmount, string $paymentType, \DateTimeImmutable $transactionDate, ?string $partyName, ?string $transactionNotes): void
    {
        $newTransaction = new Transaction();
        $newTransaction->setTransactionName($transactionName);
        $newTransaction->setCategory($category);
        $newTransaction->setMoneyAmount($moneyAmount);
        $newTransaction->setPaymentType($paymentType);
        $newTransaction->setTransactionDate($transactionDate);
        if (null !== $transactionNotes) {
            $newTransaction->setTransactionNotes($transactionNotes);
        }
        if (null !== $partyName) {
            $newTransaction->setPartyName($partyName);
        }

        $this->entityManager->persist($newTransaction);
        $this->entityManager->flush();
    }


    public function update(int $id, ?string $transactionName, ?Category $category, ?float $moneyAmount, ?\DateTimeImmutable $transactionDate, $paymentType, ?string $partyName, ?string $transactionNotes, User $user, bool $userHasCategory): void
    {
        $transaction = $this->findOneBy(['id' => $id]);


        if (!$transaction) {
            throw new NotFoundHttpException('Transaction not found.');
        }

        if ($user !== $transaction->getCategory()->getUser()) {
            throw new NotFoundHttpException('Transaction not owned by this user.');
        }

        if($category && $userHasCategory){
            $transaction->setCategory($category);
        }

        if($transactionName){
            $transaction->setTransactionName($transactionName);
        }

        if($moneyAmount){
            $transaction->setMoneyAmount($moneyAmount);
        }

        if($transactionDate){
            $transaction->setTransactionDate($transactionDate);
        }

        if($paymentType){
            $transaction->setPaymentType($paymentType);
        }

        if($partyName){
            $transaction->setPartyName($partyName);
        }

        if($transactionNotes){
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
        $transaction = $this->findOneBy(['id' => $id]);

        if (!$transaction) {
            throw new NotFoundHttpException('Transaction not found.');
        }

        $category = $transaction->getCategory();
        if ($user !== $category->getUser()) {
            throw new NotFoundHttpException('Transaction not owned by this user.');
        }

        $this->entityManager->remove($transaction);
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
