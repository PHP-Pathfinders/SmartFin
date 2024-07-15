<?php

namespace App\Dto\TransactionTemplate;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;


readonly class TransactionTemplateCreateDto
{
    public function __construct(

        #[Assert\NotBlank(message: 'Money amount cannot be blank', allowNull: true)]
        public ?float $moneyAmount,

        #[Assert\NotBlank(message: 'Transaction name cannot be blank', allowNull: true)]
        #[Assert\Length(max: 50, maxMessage: 'Transaction name cannot be longer than 50 characters')]
        public ?string $transactionName = null,

        #[Assert\Choice(options: ['cash','card'],message: 'Payment type must only be cash or card')]
        #[Assert\NotBlank(message: 'Payment type cannot be blank', allowNull: true)]
        public ?string $paymentType = null,

        #[Assert\NotBlank(message: 'Category Type cannot be blank', allowNull: true)]
        #[Assert\Choice(options: ['income', 'expense'], message: 'Category type must be \'income\' or \'expense\'.')]
        public ?string $categoryType = null,

        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Category id cannot be blank', allowNull: true)]
        public ?int $categoryId = null,

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