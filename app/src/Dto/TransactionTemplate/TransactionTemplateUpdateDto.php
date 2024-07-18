<?php

namespace App\Dto\TransactionTemplate;

use App\Validator\IntegerType;
use App\Validator\NotEmptyString;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class TransactionTemplateUpdateDto
{
    public function __construct(
        #[Assert\Positive]
        #[Assert\NotBlank(message: 'Id cannot be blank', allowNull: true)]
        #[IntegerType]
        public int $id,

        #[IntegerType]
        #[PositiveNumber]
        #[NotEmptyString]
        public ?int $categoryId,

        #[Assert\NotBlank(message: 'Transaction name cannot be blank', allowNull: true)]
        #[NotEmptyString]
        #[Assert\Length(max: 50, maxMessage: 'Transaction name cannot be longer than 50 characters')]
        public ?string $transactionName = null,

        #[Assert\NotBlank(message: 'Payment type cannot be blank', allowNull: true)]
        #[NotEmptyString]
        #[Assert\Choice(options: ['cash','card'],message: 'Payment type must only be cash or card')]
        public ?string $paymentType = null,

        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Money amount cannot be blank', allowNull: true)]
        public ?float $moneyAmount = null,

        #[Assert\NotBlank(message: 'Party name cannot be blank', allowNull: true)]
        #[NotEmptyString]
        #[Assert\Length(max: 50, maxMessage: 'Party name cannot be longer than 50 characters')]
        public ?string $partyName = null,

        #[Assert\NotBlank(message: 'Transaction notes cannot be blank', allowNull: true)]
        #[NotEmptyString]
        #[Assert\Length(max: 255, maxMessage: 'Transaction note cannot be longer than 255 characters')]
        public ?string $transactionNotes = null,
    )
    {

    }

}