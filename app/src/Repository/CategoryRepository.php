<?php

namespace App\Repository;

use App\Dto\Category\CategoryCreateDto;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry                         $registry,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaginatorInterface $paginator
    )
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find categories by their type (Income or Expense)
     * @param string $type
     * @param int $page
     * @param int $limit
     * @param User $user
     * @return array
     */
    public function search(string $type,int $page,int $limit,User $user): array
    {
        // Get paginated results
        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c.id, c.categoryName, c.type, c.color')
            ->where('c.type = :type')
            ->andWhere('c.user = :user OR c.user IS NULL')
            ->setParameter('type', $type)
            ->setParameter('user', $user)
            ->orderBy('c.categoryName', 'ASC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            $limit
        );

        // Calculate total pages
        $totalPages = (int) ceil($pagination->getTotalItemCount() / $limit);
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
            'categories' => $pagination->getItems()
        ];
    }

    /**
     * Create a new category
     * @param CategoryCreateDto $categoryCreateDto
     * @param User $user
     * @return void
     */
    public function create(CategoryCreateDto $categoryCreateDto,User $user):void
    {
        $categoryName = $categoryCreateDto->categoryName;
        $type = $categoryCreateDto->type;
        $color = $categoryCreateDto->color;

        /* Check if user already has category with given name for that type
         before adding a new category to the database */
        $userHasCategory = $this->userHasCategory($categoryName,$type,$user);
        if($userHasCategory){
            throw new ConflictHttpException('You have already a category with the given name for that type.');
        }
        $newCategory = new Category();
        $newCategory->setCategoryName($categoryName);
        $newCategory->setType($type);
        $newCategory->setColor($color);
        $newCategory->setUser($user);

        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
    }

    public function update(int $id, ?string $categoryName, ?string $color,User $user):void
    {
        $category = $this->findByIdAndUser($id,$user);

        if (!$category) {
            throw new NotFoundHttpException('Category not found or does not belongs to you');
        }
        if ($category->getUser() === null) {
            throw new AccessDeniedHttpException('You cannot modify default category.');
        }
        if($categoryName) {
            $userHasCategory = $this->userHasCategory($categoryName,$category->getType(),$user);
            //Check if category exists with given name and exclude if category name matches the one with given id
            if($userHasCategory  && strtolower($categoryName) !== strtolower($category->getCategoryName())){
                throw new ConflictHttpException('You have already a category with the given name for that type.');
            }
            $category->setCategoryName($categoryName);
        }
        if($color){
            $category->setColor($color);
        }
        $this->entityManager->flush();
    }

    /**
     * Delete selected category
     * @param int $id
     * @param User $user
     * @return void
     */
    public function delete(int $id,User $user):void
    {
        $category = $this->findByIdAndUser($id,$user);

        if (!$category) {
            throw new NotFoundHttpException('Category not found or does not belong to the user.');
        }
        if ($category->getUser() === null) {
            throw new AccessDeniedHttpException('You cannot delete default category.');
        }
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * Find category by id and user
     * @param int $id
     * @param User $user
     * @return Category|null
     */
    private function findByIdAndUser(int $id,User $user):?Category
    {
//      Get the category using id and user or null user
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->andWhere('c.user = :user OR c.user IS NULL')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Check if a user already has a category with the given name
     * @param string $categoryName
     * @param string $type
     * @param User $user
     * @return bool
     */
    private function userHasCategory(string $categoryName,string $type, User $user): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->andWhere('c.categoryName = :categoryName')
            ->andWhere('c.type = :type')
            ->andWhere('c.user = :user')
            ->setParameter(':categoryName', $categoryName)
            ->setParameter(':type',$type)
            ->setParameter(':user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
