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
        $data = [
            'user' => UserFactory::random(),
            'category' => self::faker()->randomElement([null, CategoryFactory::random()]),
            'moneyAmount' => self::faker()->optional()->randomFloat(3, max: 10000),
            'transactionName' => self::faker()->optional()->text(20),
            'partyName' => self::faker()->optional()->name(),
            'transactionNotes' => self::faker()->optional()->paragraph(1)
        ];


        $data['paymentType'] = $data['category'] ? $data['category']->_get('type') === 'expense' ? self::faker()->optional()->randomElement(['cash', 'card']) : null : null;

        return $data;
    }


    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(TransactionTemplate $transactionTemplate): void {})
        ;
    }
}
