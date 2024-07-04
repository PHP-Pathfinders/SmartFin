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
            'paymentType' => self::faker()->optional()->randomElement(['cash', 'card']),
            'moneyAmount' => self::faker()->optional()->randomFloat(3, max: 10000),
            'transactionName' => self::faker()->optional()->text(20),
            'partyName' => self::faker()->optional()->name(),
            'transactionNotes' => self::faker()->optional()->paragraph(1)
        ];
    }


    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(TransactionTemplate $transactionTemplate): void {})
        ;
    }
}
