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
     * @return array
     */
    public function findCategoriesByType(string $type,int $page, User $user): array
    {
        // Pagination
        $limit = 10;

        return $this->createQueryBuilder('c')
            ->select('c.id, c.categoryName, c.type, c.isCustom')
            ->where('c.type = :type')
            ->andWhere('c.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Create new category
     * @param string $categoryName
     * @param string $type
     * @param User $user
     * @return void
     */
    public function createCategory(string $categoryName, string $type, User $user):void
    {
        //TODO check if user already has category with given name for that type
        // before adding new category to database
        $userHasCategory = $this->userHasCategory($categoryName,$type,$user);
        if($userHasCategory){
            throw new ConflictHttpException('User already has a category with the given name for this type.');
        }
        $newCategory = new Category();
        $newCategory->setCategoryName($categoryName);
        $newCategory->setType($type);
        $newCategory->setUser($user);

        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
    }
    /**
     * Check if a user already has a category with the given name
     * @param string $categoryName
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
