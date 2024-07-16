<?php

namespace App\Dto\Transaction;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

class SpendingsDto
{
    public function __construct(
        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Year cannot be blank')]
        public ?string $year,
        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Month cannot be blank')]
        #[Assert\Range(
            notInRangeMessage: 'Month must be between {{ min }} and {{ max }} ',
            min: 1,
            max: 12,
        )]
        public ?string $month
    )
    {
    }
}