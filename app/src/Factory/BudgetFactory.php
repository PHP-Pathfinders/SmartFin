<?php

namespace App\Factory;

use App\Entity\Budget;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Budget>
 */
final class BudgetFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public static function class(): string
    {
        return Budget::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'category' => self::faker()->unique()->randomElement(CategoryFactory::all()),
            'monthly_budget' => self::faker()->randomFloat(2, 100, 1000),
            'monthly_budget_date' => self::faker()->dateTimeBetween('-2 months', '+2 months'),
        ];


    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Budget $budget): void {})
        ;
    }
}
