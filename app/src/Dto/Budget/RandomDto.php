<?php

namespace App\Dto\Budget;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RandomDto
{
    public function __construct(
        #[IntegerType]
        #[Assert\Range(notInRangeMessage: 'Month must be between 1 and 12', min: 1, max: 12)]
        public ?string $month=null,
        #[IntegerType]
        #[PositiveNumber]
        #[Assert\NotBlank(allowNull: true)]
        public ?string $year=null,
        #[IntegerType]
        #[Assert\LessThanOrEqual(10)]
        #[Assert\NotBlank]
        #[PositiveNumber]
        public ?string $amount='3'
    )
    {}
}