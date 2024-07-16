<?php

namespace App\Service;

use App\Dto\Budget\BudgetQueryDto;
use App\Entity\User;
use App\Repository\BudgetRepository;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;

class BudgetService
{
    public function __construct(
        private Security           $security,
        private BudgetRepository   $budgetRepository,
        private TransactionRepository $transactionRepository
    )
    {

    }

    public function search(?BudgetQueryDto $budgetQueryDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->budgetRepository->search($budgetQueryDto, $user);
    }

}