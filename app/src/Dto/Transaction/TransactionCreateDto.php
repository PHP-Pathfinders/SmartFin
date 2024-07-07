<?php

namespace App\Dto\Transaction;

use Symfony\Component\Validator\Constraints as Assert;
class TransactionCreateDto
{
    public function __construct(

        #[Assert\NotBlank(message: 'Transaction date cannot be blank')]
        public \DateTimeInterface $transactionDate,

        #[Assert\NotBlank(message: 'Transaction name cannot be blank')]
        public string $transactionName = '',

        #[Assert\NotBlank(message: 'Payment type cannot be blank')]
        public string $paymentType = '',

        #[Assert\NotBlank(message: 'Money amount cannot be blank')]
        public float $moneyAmount = 0,

        #[Assert\NotBlank(message: 'Category Type cannot be blank')]
        #[Assert\Choice(options: ['income', 'expense'], message: 'Category type must be \'income\' or \'expense\'.')]
        public string $categoryType = 'income',

        #[Assert\NotBlank(message: 'Category id cannot be blank')]
        public int $categoryId = 1,

        #[Assert\NotBlank(message: 'Party name cannot be blank', allowNull: true)]
        public ?string $partyName = null,

        #[Assert\NotBlank(message: 'Transaction notes cannot be blank', allowNull: true)]
        public ?string $transactionNotes = null,



    )
    {



    }

}