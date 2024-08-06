<?php

namespace App\Service;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            $type = null;
            $page = 1;
            $maxResults = 200;
        } else {
            $type = $categoryQueryDto->type;
            $page = $categoryQueryDto->page;
            $maxResults = $categoryQueryDto->maxResults;
        }
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->categoryRepository->search($type, $page, $maxResults,$user);
    }

    public function create(CategoryCreateDto $categoryCreateDto): Category
    {
        /** @var User $user */
        $user = $this->security->getUser();
        /* Check if user already has category with given name for that type
         before adding a new category to the database */
        $userHasCategory = $this->categoryRepository->userHasCategory(
            $categoryCreateDto->categoryName,
            $categoryCreateDto->type, $user
        );
        if($userHasCategory){
            throw new ConflictHttpException('You have already a category with the given name for that type.');
        }

        return $this->categoryRepository->create($categoryCreateDto,$user);
    }

    public function update(CategoryUpdateDto $categoryUpdateDto): array
    {
        $id = $categoryUpdateDto->id;
        $categoryName = $categoryUpdateDto->categoryName;
        $color = $categoryUpdateDto->color;
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$categoryName && !$color) {
            return ['message'=>'Nothing to update'];
        }
//        ---
        $category = $this->categoryRepository->findByIdAndUser($id,$user);

        if (!$category) {
            throw new NotFoundHttpException('Category not found or does not belongs to you');
        }
        if ($category->getUser() === null) {
            throw new AccessDeniedHttpException('You cannot modify default category.');
        }
        if($categoryName) {
            $userHasCategory = $this->categoryRepository->userHasCategory($categoryName,$category->getType(),$user);
            //Check if category exists with given name and exclude if category name matches the one with given id
            if($userHasCategory  && strtolower($categoryName) !== strtolower($category->getCategoryName())){
                throw new ConflictHttpException('You have already a category with the given name for that type.');
            }
            $category->setCategoryName($categoryName);
        }
        if($color){
            $category->setColor($color);
        }
//        ---
        $category = $this->categoryRepository->update($category);
        return ['message'=>'Update successful', 'category'=>$category];
    }

    public function delete(int $id): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $category = $this->categoryRepository->findByIdAndUser($id,$user);

        if (!$category) {
            throw new NotFoundHttpException('Category not found or does not belong to the user.');
        }
        if ($category->getUser() === null) {
            throw new AccessDeniedHttpException('You cannot delete default category.');
        }
        $this->categoryRepository->delete($category);
    }
}