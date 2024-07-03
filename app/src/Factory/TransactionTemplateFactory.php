<?php

namespace App\Factory;

use App\Entity\TransactionTemplate;
use App\Repository\CategoryRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<TransactionTemplate>
 */
final class TransactionTemplateFactory extends PersistentProxyObjectFactory
{

    public function __construct()
    {
    }

    public static function class(): string
    {
        return TransactionTemplate::class;
    }


    protected function defaults(): array|callable
    {
        return [
            'category' => self::faker()->randomElement([null, CategoryFactory::random()]),
            'cash_or_card' => self::faker()->optional()->randomElement(['cash', 'card']),
            'money_amount' => self::faker()->optional()->randomFloat(3, max: 10000),
            'transaction_name' => self::faker()->optional()->text(20),
            'party_name' => self::faker()->optional()->name(),
            'transaction_notes' => self::faker()->optional()->paragraph(1)
        ];
    }


    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(TransactionTemplate $transactionTemplate): void {})
        ;
    }
}
