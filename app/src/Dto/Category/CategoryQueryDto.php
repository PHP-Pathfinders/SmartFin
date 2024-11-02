<?php

namespace App\Dto\Category;

use App\Validator\IntegerType;
use App\Validator\LessThanOrEqual;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CategoryQueryDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Page cannot be blank')]
        #[IntegerType]
        #[PositiveNumber]
        public string $page='1',
        #[Assert\NotBlank(message: 'Limit cannot be blank')]
        #[IntegerType]
        #[PositiveNumber]
        #[LessThanOrEqual(200)]
        public string $maxResults='200',
        #[Assert\Choice(['income', 'expense'], message: 'Type must be \'income\' or \'expense\'')]
        public ?string $type=null,
        #[Assert\Choice(['id','categoryName', 'type', 'color','user'], message: 'orderBy can be by id, categoryName, type, color or user (in case of user, query will sort by default and custom categories)')]
        public ?string $orderBy = 'categoryName',
        #[Assert\Choice(['asc','desc'], message: 'SortBy must be asc or desc')]
        public ?string $sortBy = 'asc'
    ) {}
}