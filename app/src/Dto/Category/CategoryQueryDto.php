<?php

namespace App\Dto\Category;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryQueryDto
{
    public function __construct(
    #[Assert\NotBlank]
    #[Assert\Choice(['income','expense'], message: 'Type must be \'income\' or \'expense\'.')]
    public string $type='income',

    #[Assert\NotBlank]
    #[Assert\Positive(message: 'Page must be a positive integer')]
    public int $page = 1
    ){}
}