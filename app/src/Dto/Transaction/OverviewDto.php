<?php

namespace App\Dto\Transaction;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

class OverviewDto
{
    public function __construct(
        #[IntegerType(message:'Year must be integer')]
        #[PositiveNumber]
        #[Assert\NotBlank(message: 'Year cannot be blank')]
        public ?string $year,
    ){}
}