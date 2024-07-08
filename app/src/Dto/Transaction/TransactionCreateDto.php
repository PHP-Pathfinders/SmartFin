<?php

namespace App\Dto\Transaction;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;
class TransactionCreateDto
{
    public function __construct(

        #[Assert\NotBlank(message: 'Transaction date cannot be blank')]
        public \DateTimeInterface $transactionDate,

        #[Assert\NotBlank(message: 'Money amount cannot be blank')]
        public float $moneyAmount,


        #[Assert\NotBlank(message: 'Transaction name cannot be blank')]
        #[Assert\Length(max: 50, maxMessage: 'Transaction name cannot be longer than 50 characters')]
        public string $transactionName = '',

        #[Assert\NotBlank(message: 'Payment type cannot be blank')]
        public string $paymentType = '',


        #[Assert\NotBlank(message: 'Category Type cannot be blank')]
        #[Assert\Choice(options: ['income', 'expense'], message: 'Category type must be \'income\' or \'expense\'.')]
        public string $categoryType = 'income',

        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Category id cannot be blank')]
        public int $categoryId = 1,

        #[Assert\NotBlank(message: 'Party name cannot be blank', allowNull: true)]
        #[Assert\Length(max: 50, maxMessage: 'Party name cannot be longer than 50 characters')]
        public ?string $partyName = null,

        #[Assert\NotBlank(message: 'Transaction notes cannot be blank', allowNull: true)]
        #[Assert\Length(max: 255, maxMessage: 'Transaction note cannot be longer than 255 characters')]
        public ?string $transactionNotes = null,



    )
    {



    }

}