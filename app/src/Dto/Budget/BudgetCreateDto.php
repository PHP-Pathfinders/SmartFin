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
        public float $monthlyBudgetAmount
    )
    {

    }

}