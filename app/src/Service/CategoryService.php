<?php

namespace App\Service;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CategoryService
{
    public function __construct(
        private Security $security,
        private CategoryRepository $categoryRepository
    ){}
    public function search(?CategoryQueryDto $categoryQueryDto):array
    {
        // Check if no query params is passed and if not, go for default values
        if (null === $categoryQueryDto) {
            $type = 'income';
            $page = 1;
            $limit = 10;
        } else {
            $type = $categoryQueryDto->type;
            $page = $categoryQueryDto->page;
            $limit = $categoryQueryDto->limit;
        }
        // Get the logged-in user
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->categoryRepository->search($type, $page, $limit,$user);
    }

    public function create(CategoryCreateDto $categoryCreateDto):void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $name = $categoryCreateDto->categoryName;
        $type = $categoryCreateDto->type;
        $this->categoryRepository->create($name,$type,$user);
    }
}