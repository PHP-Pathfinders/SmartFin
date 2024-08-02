<?php

namespace App\Dto\Budget;

use App\Validator\IntegerType;
use App\Validator\LessThanOrEqual;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class BudgetQueryDto
{
    public function __construct(

        #[Assert\Date(message: 'Given date must be in format YYYY-MM-DD')]
        #[Assert\NotBlank(allowNull: true)]
        public ?string $dateStart = '2024-01-01',

        #[Assert\Date(message: 'Given date must be in format YYYY-MM-DD')]
        #[Assert\NotBlank(allowNull: true)]
        public ?string $dateEnd = '2024-12-31',

        #[PositiveNumber]
        #[IntegerType]
        #[Assert\NotBlank(message: 'Page cannot be blank')]
        public string $page = '1',

        #[PositiveNumber]
        #[IntegerType]
        #[Assert\NotBlank(message: 'Max results cannot be blank')]
        #[LessThanOrEqual(200)]
        public string $maxResults = '200',

    )
    {

    }

}