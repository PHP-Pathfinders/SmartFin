<?php

namespace App\Factory;

use App\Entity\Budget;
use App\Entity\Category;
use App\Repository\BudgetRepository;
use App\Repository\CategoryRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Budget>
 */
final class BudgetFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private BudgetRepository       $budgetRepository,
        private CategoryRepository     $categoryRepository
    )
    {
    }

    public static function class(): string
    {
        return Budget::class;
    }

    protected function defaults(): array|callable
    {
        $attempts = 0;
        do {
            $categoryProxy = CategoryFactory::random();
            $categoryId = $categoryProxy->_get('id');
            $monthlyBudgetDate = self::faker()->dateTimeBetween('-4 months', '+4 months');

            if (!$this->budgetRepository->doesBudgetExistForCategoryAndMonth($this->categoryRepository->findOneBy(['id' => $categoryId]), $monthlyBudgetDate)) {
                return [
                    'category' => $categoryProxy,
                    'monthlyBudget' => self::faker()->randomFloat(2, 100, 1000),
                    'monthlyBudgetDate' => $monthlyBudgetDate,
                ];
            }
            $attempts++;
        } while ($attempts <= 5000);

        throw new \RuntimeException("Unable to generate a monthly budget after several attempts. Please try again later.");
    }

    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Budget $budget): void {})
            ;
    }
}
