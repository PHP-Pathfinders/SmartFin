<?php

namespace App\Dto\Category;

use App\Validator\IntegerType;
use App\Validator\LessThanOrEqual;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryQueryDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Page cannot be blank')]
        #[IntegerType]
        #[PositiveNumber]
        public string $page='1',
        #[Assert\NotBlank(message: 'Limit cannot be blank')]
        #[IntegerType]
        #[PositiveNumber]
        #[LessThanOrEqual(30)]
        public string $limit='10',
        #[Assert\Choice(['income', 'expense'], message: 'Type must be \'income\' or \'expense\'')]
        public string $type='income'
    ) {}
}