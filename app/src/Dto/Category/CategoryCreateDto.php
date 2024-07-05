<?php

namespace App\Dto\Category;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryCreateDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Category name must be provided and cannot be blank')]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Category name cannot be longer than 50 characters.'
        )]
        public string $categoryName='',

        #[Assert\NotBlank(message: 'Type must be provided and cannot be blank.')]
        #[Assert\Choice(
            choices: ['income', 'expense'],
            message: 'Type must be either income or expense.'
        )]
        public string $type=''
    ) {}
}