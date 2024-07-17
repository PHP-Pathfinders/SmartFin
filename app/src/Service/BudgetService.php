<?php

namespace App\Service;

use App\Dto\Budget\RandomDto;
use App\Entity\User;
use App\Repository\BudgetRepository;
use Symfony\Bundle\SecurityBundle\Security;

class BudgetService
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private Security $security
    )
    {}

    public function random(?RandomDto $randomDto): array
    {
        if ($randomDto === null) {
            $month = date('m');
            $year = date('Y');
            $amount = '3';
        }
        else {
            $month = $randomDto->month ?? date('m');
            $year = $randomDto->year ?? date('Y');
            $amount = $randomDto->amount ?? '3';
        }

        /** @var User $user */
        $user = $this->security->getUser();
        return $this->budgetRepository->fetchRandomBudgets($month, $year, $amount,$user);
    }
}