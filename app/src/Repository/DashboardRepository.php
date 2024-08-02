<?php

namespace App\Repository;

use App\Entity\Dashboard;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dashboard>
 */
class DashboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dashboard::class);
    }

    public function fetchDashboard(User $user, string $year, string $month)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.User = :user')
            ->andWhere('YEAR(d.dashboardDate) = :year AND MONTH(d.dashboardDate) = :month')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->getResult();
    }

    public function findByDateAndUser(User $user, string $date)
    {
        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('YEAR(d.dashboardDate) = YEAR(:date) AND MONTH(d.dashboardDate) = MONTH(:date)')
            ->setParameter('user', $user)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();

    }

}
