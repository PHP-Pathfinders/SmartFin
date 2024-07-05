<?php

namespace App\Dto\Category;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryQueryDto
{
    //TODO fix validation autowire error
    public function __construct(
        #[Assert\Positive(message: 'Page must be a positive integer')]
        public int $page,
        #[Assert\Type(type: 'integer', message: 'Limit must be an integer')]
        #[Assert\Positive(message: 'Limit must be a positive integer')]
        #[Assert\LessThanOrEqual(30, message: 'Limit cannot be greater than 30')]
        public int $limit,
        #[Assert\Choice(['income', 'expense'], message: 'Type must be \'income\' or \'expense\'.')]
        public string $type
    ) {}
}