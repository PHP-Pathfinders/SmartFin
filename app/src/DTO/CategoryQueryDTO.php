<?php

namespace App\DTO;


use Symfony\Component\Validator\Constraints as Assert;

class CategoryQueryDTO
{
    #[Assert\NotBlank]
    #[Assert\Choice(['income','expense'], message: 'Type must be \'income\' or \'expense\'.')]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Positive(message: 'Page must be a positive integer')]
    public int $page;
}