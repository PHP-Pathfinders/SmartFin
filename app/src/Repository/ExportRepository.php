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
}
