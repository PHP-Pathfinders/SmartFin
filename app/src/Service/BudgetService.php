<?php

namespace App\Service;

use App\Dto\Budget\BudgetCreateDto;
use App\Dto\Budget\BudgetQueryDto;
use App\Dto\Budget\BudgetUpdateDto;
use App\Dto\Budget\RandomDto;
use App\Entity\Budget;
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

        if(null === $budgetQueryDto){
            $page = '1';
            $maxResults = '200';
            $dateStart = (new \DateTime('first day of this month'))->format('Y-m-d');
            $dateEnd = (new \DateTime('last day of this month'))->format('Y-m-d');
        }else{
            $page = $budgetQueryDto->page;
            $maxResults = $budgetQueryDto->maxResults;
            $dateStart = $budgetQueryDto->dateStart;
            $dateEnd = $budgetQueryDto->dateEnd;
        }

        if ($dateStart > $dateEnd) {
            throw new NotFoundHttpException('Invalid date format');
        }

        return $this->budgetRepository->search($page,$maxResults,$dateStart,$dateEnd, $user);
    }

    public function create(BudgetCreateDto $budgetCreateDto): Budget
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $category = $this->categoryRepository->findByIdUserAndType($budgetCreateDto->categoryId, $user, 'expense');

        if (!$category) {
            throw new NotFoundHttpException("Invalid category given");
        }

        $month = $budgetCreateDto->month ?? date('m');
        $year = $budgetCreateDto->year ?? date('Y');


        $date = (new \DateTime())->format("$year-$month-d");


        $potentialExistingBudget = $this->budgetRepository->findByCategoryAndDate($category, $date, $user);

        if($potentialExistingBudget){
            throw new ConflictHttpException('You already have a budget for this category in given month');
        }

        $newBudget = new Budget();
        $newBudget->setMonthlyBudget($budgetCreateDto->monthlyBudgetAmount);
        $newBudget->setCategory($category);
        $newBudget->setUser($user);
        $newBudget->setMonthlyBudgetDate(new \DateTimeImmutable($date));


        $this->budgetRepository->create($newBudget);

        return $newBudget;
    }

    public function update(BudgetUpdateDto $budgetUpdateDto): array
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


        if($category === $currentCategory && (!$budgetUpdateDto->monthlyBudgetAmount || $budgetUpdateDto->monthlyBudgetAmount === $budget->getMonthlyBudget()) && ($budgetUpdateDto->year === $budget->getMonthlyBudgetDate()->format('Y') && (int) $budgetUpdateDto->month === (int) $budget->getMonthlyBudgetDate()->format('m'))){
            return ['message' => 'Nothing to update'];
        }

        $month = $budgetUpdateDto->month ? date($budgetUpdateDto->month) : $budget->getMonthlyBudgetDate()->format('m');
        $year = $budgetUpdateDto->year ? date($budgetUpdateDto->year) : $budget->getMonthlyBudgetDate()->format('Y');


        $date = (new \DateTime())->format("$year-$month-d");


        $potentialSameBudget = $this->budgetRepository->findByCategoryAndDate($category, $date, $user);



        if($potentialSameBudget && $potentialSameBudget->getId() !== $budget->getId()){
            throw new ConflictHttpException('You already have budget for this category in this month');
        }


        $budget->setCategory($category);

        if ($budgetUpdateDto->monthlyBudgetAmount) {
            $budget->setMonthlyBudget($budgetUpdateDto->monthlyBudgetAmount);
        }

        $budget->setMonthlyBudgetDate(new \DateTimeImmutable($date));


        $this->budgetRepository->update();

        return ['message' => 'Update successful', 'budget' => $budget];


    }

    public function delete(int $id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $budget = $this->budgetRepository->findByIdAndUser($id, $user);

        if (!$budget) {
            throw new NotFoundHttpException('Budget not found or not owned by you');
        }

        $this->budgetRepository->delete($budget);

    }

}