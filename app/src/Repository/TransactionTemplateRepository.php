<?php

namespace App\Repository;

use App\Dto\TransactionTemplate\TransactionTemplateCreateDto;
use App\Dto\TransactionTemplate\TransactionTemplateQueryDto;
use App\Entity\Category;
use App\Entity\TransactionTemplate;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<TransactionTemplate>
 */
class TransactionTemplateRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                $registry,
        private EntityManagerInterface $entityManager,
        private PaginatorInterface     $paginator
    )
    {
        parent::__construct($registry, TransactionTemplate::class);
    }

    /**
     * Find transacton templates by different parameters
     * @param TransactionTemplateQueryDto|null $transactionTemplateQueryDto
     * @param User $user
     * @return array
     */
    public function search(?TransactionTemplateQueryDto $transactionTemplateQueryDto, User $user): array
    {
        $transactionName = $transactionTemplateQueryDto->transactionName ?? null;
        $paymentType = $transactionTemplateQueryDto->paymentType ?? null;
        $categoryName = $transactionTemplateQueryDto->categoryName ?? null;
        $categoryType = $transactionTemplateQueryDto->categoryType ?? null;
        $categoryId = $transactionTemplateQueryDto->categoryId ?? null;
        $partyName = $transactionTemplateQueryDto->partyName ?? null;
        $transactionNotes = $transactionTemplateQueryDto->transactionNotes ?? null;
        $page = $transactionTemplateQueryDto->page ?? '1';
        $maxResults = $transactionTemplateQueryDto->maxResults ?? '200';

        $totalResults = $this->createQueryBuilder('tt')
            ->select('COUNT(tt.id)')
            ->leftJoin('tt.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('tt.user = :user')
            ->setParameter('user', $user);

        $qb = $this->createQueryBuilder('tt')
            ->select('tt.id, tt.transactionName, tt.paymentType, tt.moneyAmount, tt.partyName, tt.transactionNotes, c.type, c.categoryName, c.color')
            ->leftJoin('tt.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('tt.user = :user')
            ->orderBy('tt.transactionName', 'ASC')
            ->setParameter('user', $user);

        if ($paymentType !== null) {
            $qb->andWhere('tt.paymentType = :paymentType')
                ->setParameter('paymentType', $paymentType);
            $totalResults->andWhere('tt.paymentType = :paymentType')
                ->setParameter('paymentType', $paymentType);
        }

        if ($transactionName !== null) {
            $qb->andWhere('tt.transactionName LIKE :transactionName')
                ->setParameter('transactionName', "%" . $transactionName . "%");
            $totalResults->andWhere('tt.transactionName LIKE :transactionName')
                ->setParameter('transactionName', "%" . $transactionName . "%");
        }

        if ($partyName !== null) {
            $qb->andWhere('tt.partyName LIKE :partyName')
                ->setParameter('partyName', "%" . $partyName . "%");
            $totalResults->andWhere('tt.partyName LIKE :partyName')
                ->setParameter('partyName', "%" . $partyName . "%");
        }

        if ($transactionNotes !== null) {
            $qb->andWhere('tt.transactionNotes LIKE :transactionNotes')
                ->setParameter('transactionNotes', "%" . $transactionNotes . "%");
            $totalResults->andWhere('tt.transactionNotes LIKE :transactionNotes')
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

        $transactionTemplates = $pagination->getItems();
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
                'totalPages' => $totalPages
            ],
            'totalResults' => $totalResults,
            'transactionTemplates' => $transactionTemplates
        ];
    }

    /**
     * Create new transaction template
     * @param TransactionTemplateCreateDto $transactionTemplateCreateDto
     * @param User $user
     * @param Category|null $category
     * @return void
     */
    public function create(TransactionTemplateCreateDto $transactionTemplateCreateDto, User $user, ?Category $category): void
    {
        $transactionName = $transactionTemplateCreateDto->transactionName;
        $paymentType = $category ? $category->getType() === "expense" ? $transactionTemplateCreateDto->paymentType : null : $transactionTemplateCreateDto->paymentType;
        $moneyAmount = $transactionTemplateCreateDto->moneyAmount;
        $partyName = $transactionTemplateCreateDto->partyName;
        $transactionNotes = $transactionTemplateCreateDto->transactionNotes;

        if (!$transactionName && !$paymentType && !$moneyAmount && !$partyName && !$transactionNotes && !$category) {
            throw new BadRequestHttpException("Template cannot be completly blank...");
        }


        $newTemplate = new TransactionTemplate();
        $newTemplate->setUser($user);
        $newTemplate->setCategory($category);
        $newTemplate->setTransactionName($transactionName);
        $newTemplate->setPaymentType($paymentType);
        $newTemplate->setMoneyAmount($moneyAmount);
        $newTemplate->setPartyName($partyName);
        $newTemplate->setTransactionNotes($transactionNotes);


        $this->entityManager->persist($newTemplate);
        $this->entityManager->flush();

    }

    public function update(TransactionTemplate $template, ?string $transactionName, ?Category $category, ?string $paymentType, ?string $partyName, ?string $transactionNotes, ?float $moneyAmount, User $user): void
    {

        if ($transactionName) {
            $template->setTransactionName($transactionName);
        }

        if ($category && $category->getType() === 'expense') {
            $template->setCategory($category);
        }

        if ($category && $category->getType() === 'income') {
            $template->setCategory($category);
            $template->setPaymentType(null);
        }

        if ($paymentType) {
            $template->setPaymentType($paymentType);
        }

        if ($partyName) {
            $template->setPartyName($partyName);
        }

        if ($moneyAmount) {
            $template->setMoneyAmount($moneyAmount);
        }

        if ($transactionNotes) {
            $template->setTransactionNotes($transactionNotes);
        }

        $this->entityManager->flush();

    }


    /**
     * Delete selected transaction template
     * @param int $id
     * @param User $user
     * @return void
     */
    public function delete(int $id, User $user): void
    {
        $transactionTemplate = $this->findByIdAndUser($id, $user);

        if (!$transactionTemplate) {
            throw new NotFoundHttpException('Template not found or doesn\'t belong to you');
        }

        $this->entityManager->remove($transactionTemplate);
        $this->entityManager->flush();

    }

    public function findBYIdAndUser(int $id, User $user): ?TransactionTemplate
    {
        return $this->createQueryBuilder('tt')
            ->where('tt.id = :id')
            ->andWhere('tt.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

    }

}
