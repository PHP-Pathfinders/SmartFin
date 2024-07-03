<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find categories by their type (Income or Expense)
     * @param string $type
     * @return array
     */
    public function findCategoriesByType(string $type, User $user, int $page): array
    {
        $limit = 10;

        return $this->createQueryBuilder('c')
            ->select('c.categoryName, c.isCustom')
            ->where('c.incomeOrExpense = :type')
            ->andWhere('c.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Check if a user already has a category with the given name.
     *
     * @param int $userId
     * @param string $categoryName
     * @return bool
     */
    public function userHasCategory(int $userId, string $categoryName): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.user = :user')
            ->andWhere('c.category_name = :category_name')
            ->setParameter('user', $userId)
            ->setParameter('category_name', $categoryName)
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
