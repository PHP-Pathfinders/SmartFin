<?php

namespace App\Repository;

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
        ManagerRegistry                $registry,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface     $paginator
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
     * Create new transaction
     * @param TransactionCreateDto $transactionCreateDto
     * @param User $user
     * @param Category $category
     * @return void
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
