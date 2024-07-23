<?php

namespace App\Dto\Budget;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class BudgetCreateDto
{
    public function __construct(
        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Category id cannot be blank')]
        public int   $categoryId,

        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Monthly budget amount cannot be blank')]
        public float $monthlyBudgetAmount,

        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Year cannot be blank')]
        public ?string $year = null,

        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Month cannot be blank')]
        #[Assert\Range(
            notInRangeMessage: 'Month must be between {{ min }} and {{ max }} ',
            min: 1,
            max: 12,
        )]
        public ?string $month = null
    )
    {

    }

}