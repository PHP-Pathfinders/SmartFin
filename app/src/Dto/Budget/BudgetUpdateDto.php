<?php

namespace App\Dto\Budget;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

class BudgetUpdateDto
{
    public function __construct(

        #[Assert\Positive]
        #[IntegerType]
        #[Assert\NotBlank(message: 'Id cannot be blank', allowNull: true)]
        public int $id,

        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Category id cannot be blank', allowNull: true)]
        public ?int     $categoryId,

        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Monthly budget amount cannot be blank', allowNull: true)]
        public ?float $monthlyBudgetAmount
    )
    {

    }
}