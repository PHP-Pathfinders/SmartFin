<?php

namespace App\Dto\Transaction;

use App\Validator\IntegerType;
use App\Validator\NotEmptyString;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class TransactionUpdateDto
{
    public function __construct(
        #[Assert\Positive]
        #[Assert\NotBlank(message: 'Id cannot be blank')]
        #[IntegerType]
        public int $id,

        #[Assert\NotBlank(message: 'Transaction date cannot be blank')]
        public \DateTimeInterface $transactionDate,

        #[NotEmptyString]
        #[Assert\Length(max: 50, maxMessage: 'Transaction name cannot be longer than 50 characters')]
        public ?string $transactionName = '',

        #[NotEmptyString]
        public ?string $paymentType = '',

        #[Assert\NotBlank(message: 'Money amount cannot be blank')]
        public ?float $moneyAmount = 0,

        #[IntegerType]
        #[PositiveNumber]
        #[NotEmptyString]
        public ?int $categoryId = 1,

        #[NotEmptyString]
        #[Assert\Length(max: 50, maxMessage: 'Party name cannot be longer than 50 characters')]
        public ?string $partyName = null,

        #[NotEmptyString]
        #[Assert\Length(max: 255, maxMessage: 'Transaction note cannot be longer than 255 characters')]
        public ?string $transactionNotes = null,
    )
    {

    }

}