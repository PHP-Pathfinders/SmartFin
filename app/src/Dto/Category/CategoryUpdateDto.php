<?php

namespace App\Dto\Category;

use App\Validator\NotEmptyString;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryUpdateDto
{
    public function __construct(
        #[Assert\Positive]
        public int $id,
        #[NotEmptyString]
        #[Assert\Length(
            max: 50,
            maxMessage: 'Category name cannot be longer than 50 characters.'
        )]
        public ?string $categoryName,
        #[NotEmptyString]
        #[Assert\Regex(
            pattern: '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
            message: 'Color must be a valid hexadecimal color code in format: #00ff00 or #0f0.'
        )]
        public ?string $color
    ){}
}