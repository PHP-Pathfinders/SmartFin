<?php

namespace App\Factory;

use App\Entity\Transaction;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use function Zenstruck\Foundry\faker;

/**
 * @extends PersistentProxyObjectFactory<Transaction>
 */
final class TransactionFactory extends PersistentProxyObjectFactory
{
    public function __construct(
    )
    {
    }

    public static function class(): string
    {
        return Transaction::class;
    }


    protected function defaults(): array|callable
    {
        return [
            'user' => UserFactory::random(),
            'category' => CategoryFactory::random(),
            'paymentType' => self::faker()->randomElement(['cash', 'card']),
            'moneyAmount' => self::faker()->randomFloat(3, max: 10000),
            'transactionDate' => self::faker()->dateTimeBetween('-40 days'),
            'transactionName' => self::faker()->text(20),
            'partyName' => self::faker()->optional()->name(),
            'transactionNotes' => self::faker()->optional()->paragraph(1)
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Transaction $transaction): void {})
        ;
    }
}
