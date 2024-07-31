<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\DashboardRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class DashboardService
{

    public function __construct(
        private Security              $security,
        private DashboardRepository   $dashboardRepository,

    )
    {
    }

    public function fetchDashboard(string $month, string $year): array
    {
        /** @var User $user */
        $user = $this->security->getUser();
        return $this->dashboardRepository->fetchDashboard($user, $year, $month);

    }

}