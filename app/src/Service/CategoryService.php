<?php

namespace App\Service;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
readonly class CategoryService
{
    public function __construct(
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

        return $this->categoryRepository->search($type, $page, $limit);
    }

    public function create(CategoryCreateDto $categoryCreateDto):void
    {
        $name = $categoryCreateDto->categoryName;
        $type = $categoryCreateDto->type;
        $color = $categoryCreateDto->color;

        $this->categoryRepository->create($name,$type,$color);
    }

    public function update(CategoryUpdateDto $categoryUpdateDto):string
    {
//        dd($categoryUpdateDto);
        $id = $categoryUpdateDto->id;
        $categoryName = $categoryUpdateDto->categoryName;
        $color = $categoryUpdateDto->color;
        if (!$categoryName && !$color) {
            return 'Nothing to update';
        }
        $this->categoryRepository->update($id,$categoryName,$color);
        return 'Update successful';
    }

    public function delete(int $id):void
    {
        $this->categoryRepository->delete($id);
    }
}