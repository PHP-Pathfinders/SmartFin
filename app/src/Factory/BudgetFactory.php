<?php

namespace App\Factory;

use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\BudgetRepository;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Budget>
 */
final class BudgetFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private BudgetRepository       $budgetRepository,
        private CategoryRepository     $categoryRepository,
        private UserRepository         $userRepository
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
            $userProxy = UserFactory::random();

            /** @var Category $category */
            $category = $this->categoryRepository->findOneBy(['id' => $categoryProxy->_get('id')]);

            /** @var User $user */
            $user = $this->userRepository->findOneBy(['id' => $userProxy->_get('id')]);

            $monthlyBudgetDate = self::faker()->dateTimeBetween('-2 months', '+2 months');

            if (!$this->budgetRepository->doesBudgetExistForCategoryAndMonth($category, $user, $monthlyBudgetDate) && $category->getType() === 'expense') {
                return [
                    'user' => $userProxy,
                    'category' => $categoryProxy,
                    'monthlyBudget' => self::faker()->randomFloat(2, 100, 5000),
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
