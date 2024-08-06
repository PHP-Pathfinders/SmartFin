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
     * Find transaction templates by different parameters
     * @param TransactionTemplateQueryDto $transactionTemplateQueryDto
     * @param User $user
     * @return array
     */
    public function search(TransactionTemplateQueryDto $transactionTemplateQueryDto, User $user): array
    {


        $qb = $this->createQueryBuilder('tt')
            ->select('tt.id, tt.transactionName, tt.paymentType, tt.moneyAmount, tt.partyName, tt.transactionNotes,c.id as categoryId, c.type, c.categoryName, c.color')
            ->leftJoin('tt.category', 'c')
            ->leftJoin('c.user', 'u')
            ->andWhere('tt.user = :user')
            ->orderBy('tt.transactionName', 'ASC')
            ->setParameter('user', $user);

        if ($transactionTemplateQueryDto->paymentType !== null) {
            $qb->andWhere('tt.paymentType = :paymentType')
                ->setParameter('paymentType', $transactionTemplateQueryDto->paymentType);

        }

        if ($transactionTemplateQueryDto->transactionName !== null) {
            $qb->andWhere('tt.transactionName LIKE :transactionName')
                ->setParameter('transactionName', "%" . $transactionTemplateQueryDto->transactionName . "%");
        }

        if ($transactionTemplateQueryDto->partyName !== null) {
            $qb->andWhere('tt.partyName LIKE :partyName')
                ->setParameter('partyName', "%" . $transactionTemplateQueryDto->partyName . "%");
        }

        if ($transactionTemplateQueryDto->transactionNotes !== null) {
            $qb->andWhere('tt.transactionNotes LIKE :transactionNotes')
                ->setParameter('transactionNotes', "%" . $transactionTemplateQueryDto->transactionNotes . "%");
        }

        if ($transactionTemplateQueryDto->categoryName !== null) {
            $qb->andWhere('c.categoryName LIKE :categoryName')
                ->setParameter('categoryName', "%" . $transactionTemplateQueryDto->categoryName . "%");
        }

        if ($transactionTemplateQueryDto->categoryType !== null) {
            $qb->andWhere('c.type = :categoryType')
                ->setParameter('categoryType', $transactionTemplateQueryDto->categoryType);
        }

        if ($transactionTemplateQueryDto->categoryId !== null) {
            $qb->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $transactionTemplateQueryDto->categoryId);
        }

        $pagination = $this->paginator->paginate(
            $qb,
            $transactionTemplateQueryDto->page,
            $transactionTemplateQueryDto->maxResults
        );

        $transactionTemplates = $pagination->getItems();

        // Calculate total pages
        $totalPages = (int)ceil($pagination->getTotalItemCount() / $transactionTemplateQueryDto->maxResults);

        // Calculate the previous and next page
        $previousPage = ($transactionTemplateQueryDto->page > 1) ? $transactionTemplateQueryDto->page - 1 : null;
        $nextPage = ($transactionTemplateQueryDto->page < $totalPages) ? (int)$transactionTemplateQueryDto->page + 1 : null;


        return [
            'pagination' => [
                'currentPage' => $transactionTemplateQueryDto->page,
                'previousPage' => $previousPage,
                'nextPage' => $nextPage,
                'totalPages' => $totalPages
            ],
            'totalResults' => $pagination->getTotalItemCount(),
            'transactionTemplates' => $transactionTemplates
        ];
    }

    /**
     * Create new transaction template
     * @param TransactionTemplate $newTemplate
     * @return void
     */
    public function create(TransactionTemplate $newTemplate): void
    {
        $this->entityManager->persist($newTemplate);
        $this->entityManager->flush();
    }

    public function update(): void
    {
        $this->entityManager->flush();
    }


    /**
     * Delete selected transaction template
     * @param TransactionTemplate $transactionTemplate
     * @return void
     */
    public function delete(TransactionTemplate $transactionTemplate): void
    {
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
