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
        public ?string $dateStart = null,

        #[Assert\Date(message: 'Given date must be in format YYYY-MM-DD')]
        public ?string $dateEnd = null,

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