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
            'cash_or_card' => self::faker()->randomElement(['cash', 'card']),
            'category' => CategoryFactory::random(),
            'money_amount' => self::faker()->randomFloat(3, max: 10000),
            'transaction_date' => self::faker()->dateTimeBetween('-40 days'),
            'transaction_name' => self::faker()->text(20),
            'party_name' => self::faker()->optional()->name(),
            'transaction_notes' => self::faker()->optional()->paragraph(1)
        ];
    }

    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Transaction $transaction): void {})
        ;
    }
}
