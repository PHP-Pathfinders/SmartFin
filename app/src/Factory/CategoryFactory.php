<?php

namespace App\Factory;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    )
    {
    }

    public static function class(): string
    {
        return Category::class;
    }

    protected function defaults(): array|callable
    {
        return [
//            'category_name' => self::faker()->randomElement([
//                'Groceries', 'Transportation', 'Utilities', 'Rent', 'Entertainment',
//                'Healthcare', 'Dining Out', 'Travel', 'Savings', 'Miscellaneous'
//            ]),
//            'income_or_expense' => self::faker()->randomElement(['income', 'expense']),
//            'is_custom' => true,
//            'user' => UserFactory::random(),
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Category $category): void {})
        ;
    }
}
