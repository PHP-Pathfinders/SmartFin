<?php

namespace App\Service;

use App\Dto\CategoryQueryDto;
use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CategoryService
{
    public function __construct(private Security $security, private CategoryRepository $categoryRepository)
    {
    }
    public function findCategoriesByType(?CategoryQueryDto $categoryQueryDto):array
    {
        // Check if no query params is passed and if not, go for default values
        if(null === $categoryQueryDto) {
            $type = 'income';
            $page = 1;
        }else {
            $type = $categoryQueryDto->type;
            $page = $categoryQueryDto->page;
        }
        // Get the logged-in user
        $user = $this->security->getUser();

        return $this->categoryRepository->findCategoriesByType($type, $page, $user);
    }
}