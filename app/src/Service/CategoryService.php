<?php

namespace App\Service;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CategoryService
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private Security $security
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
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->categoryRepository->search($type, $page, $limit,$user);
    }

    public function create(CategoryCreateDto $categoryCreateDto):void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $this->categoryRepository->create($categoryCreateDto,$user);
    }

    public function update(CategoryUpdateDto $categoryUpdateDto):string
    {
        $id = $categoryUpdateDto->id;
        $categoryName = $categoryUpdateDto->categoryName;
        $color = $categoryUpdateDto->color;
        if (!$categoryName && !$color) {
            return 'Nothing to update';
        }
        /** @var User $user */
        $user = $this->security->getUser();
        $this->categoryRepository->update($id,$categoryName,$color,$user);
        return 'Update successful';
    }

    public function delete(int $id):void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $this->categoryRepository->delete($id,$user);
    }
}