<?php

namespace App\Service;

use App\Dto\Budget\BudgetCreateDto;
use App\Dto\Budget\BudgetQueryDto;
use App\Dto\Budget\BudgetUpdateDto;
use App\Dto\Budget\RandomDto;
use App\Entity\User;
use App\Repository\BudgetRepository;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BudgetService
{
    public function __construct(
        private BudgetRepository   $budgetRepository,
        private Security           $security,
        private CategoryRepository $categoryRepository
    )
    {
    }

    public function random(?RandomDto $randomDto): array
    {
        if ($randomDto === null) {
            $month = date('m');
            $year = date('Y');
            $amount = '3';
        } else {
            $month = $randomDto->month ?? date('m');
            $year = $randomDto->year ?? date('Y');
            $amount = $randomDto->amount ?? '3';
        }

        /** @var User $user */
        $user = $this->security->getUser();
        return $this->budgetRepository->fetchRandomBudgets($month, $year, $amount, $user);
    }

    public function search(?BudgetQueryDto $budgetQueryDto): array
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->budgetRepository->search($budgetQueryDto, $user);
    }

    public function create(BudgetCreateDto $budgetCreateDto): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $category = $this->categoryRepository->findByIdUserAndType($budgetCreateDto->categoryId, $user, 'expense');

        if (!$category) {
            throw new NotFoundHttpException("Invalid category given");
        }

        $date = (new \DateTime())->format('Y-m-d');

        $potentialExistingBudget = $this->budgetRepository->findByCategoryAndDate($category, $date, $user);

        if($potentialExistingBudget){
            throw new ConflictHttpException('You already have this budget for this month');
        }

        $this->budgetRepository->create($budgetCreateDto, $user, $category, $date);
    }

    public function update(BudgetUpdateDto $budgetUpdateDto): string
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $budget = $this->budgetRepository->findByIdAndUser($budgetUpdateDto->id, $user);

        if(!$budget){
            throw new NotFoundHttpException("Budget not owned by you or does not exist");
        }

        $currentCategory = $budget->getCategory();

        $category = $budgetUpdateDto->categoryId ? $this->categoryRepository->findByIdUserAndType($budgetUpdateDto->categoryId, $user, 'expense') : $currentCategory;

        if(!$category){
            throw new NotFoundHttpException("Category could not be found");
        }


        if($category === $currentCategory && (!$budgetUpdateDto->monthlyBudgetAmount || $budgetUpdateDto->monthlyBudgetAmount === $budget->getMonthlyBudget())){
            return 'Nothing to update';
        }

        $this->budgetRepository->update($budget, $budgetUpdateDto, $user, $category);

        return 'Update successful';


    }

    public function delete(int $id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $this->budgetRepository->delete($id, $user);

    }

}