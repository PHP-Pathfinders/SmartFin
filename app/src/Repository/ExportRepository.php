<?php

namespace App\Repository;

use App\Entity\Export;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Export>
 */
class ExportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Export::class);
    }

    public function create(string $fileName, string $fileType, User $user): void
    {
        $export = new Export();
        $export->setUser($user);
        $export->setFileName($fileName);
        $export->setFileType($fileType);

        $this->getEntityManager()->persist($export);
        $this->getEntityManager()->flush();
    }

    public function fetchExports(User $user, ?string $fileType = null, ?string $fileName = null): array
    {

        $queryBuilder = $this->createQueryBuilder('e')
            ->select('e.fileName, e.fileType, e.createdAt')
            ->where('e.user = :user')
            ->setParameter('user', $user)
            ->orderBy('e.fileType', 'ASC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->addOrderBy('e.fileName', 'ASC');

        if ($fileType) {
            $queryBuilder->andWhere('e.fileType = :fileType')
                ->setParameter('fileType', $fileType);
        }
        if ($fileName) {
            $queryBuilder->andWhere('e.fileName = :fileName')
                ->setParameter('fileName', $fileName);
        }
        return $queryBuilder->getQuery()->getResult();
    }

    public function findLatestExportByType(User $user, string $fileType): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('e.createdAt')
            ->where('e.user = :user')
            ->andWhere('e.fileType = :fileType')
            ->setParameter('user', $user)
            ->setParameter('fileType', $fileType)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
