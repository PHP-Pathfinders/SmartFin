<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $entityManager
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
        // Get the total count of results
        $totalResults = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.type = :type')
            ->andWhere('c.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        // Calculate total pages
        $totalPages = (int) ceil($totalResults / $limit);

        // Get paginated results
        $categories = $this->createQueryBuilder('c')
            ->select('c.id, c.categoryName, c.type, c.color, c.isCustom')
            ->where('c.type = :type')
            ->andWhere('c.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

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
            'categories' => $categories
        ];
    }

    /**
     * Create new category
     * @param string $categoryName
     * @param string $type
     * @param User $user
     * @return void
     */
    public function create(string $categoryName, string $type,string $color ,User $user):void
    {
        /* Check if user already has category with given name for that type
         before adding a new category to the database */
        $userHasCategory = $this->userHasCategory($categoryName,$type,$user);
        if($userHasCategory){
            throw new ConflictHttpException('User already has a category with the given name for this type.');
        }
        $newCategory = new Category();
        $newCategory->setCategoryName($categoryName);
        $newCategory->setType($type);
        $newCategory->setColor($color);
        $newCategory->setUser($user);

        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
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

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
