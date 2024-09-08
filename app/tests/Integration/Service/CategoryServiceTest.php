<?php

namespace App\Tests\Integration\Service;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\CategoryService;

use App\Tests\Mock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class CategoryServiceTest extends KernelTestCase
{
    private CategoryService $categoryService;
    private CategoryRepository $categoryRepository;
    private Mock $mock;
    private User $user;
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->categoryService = $container->get(CategoryService::class);
        $this->categoryRepository = $container->get(CategoryRepository::class);
        // Instantiate Mock
        $userRepository = $container->get(UserRepository::class);
        $tokenStorage = $container->get(TokenStorageInterface::class);
        $this->mock = new Mock($userRepository, $tokenStorage);
        $this->user = $this->mock->login();
    }
    public function testCreateAndUpdate(): void
    {
        $categoryCreateDto = new CategoryCreateDto('Festival','expense','#f02');
        $newCategory = $this->categoryService->create($categoryCreateDto);
        $dbCategory = $this->categoryRepository->find($newCategory->getId());
//        Check if new category is stored in database
        $this->assertSame('Festival',$dbCategory->getCategoryName());
        $this->assertSame('expense', $dbCategory->getType());
        $this->assertSame('#f02', $dbCategory->getColor());

        $categoryUpdateDto = new CategoryUpdateDto($dbCategory->getId(),'Zoo','#0f0');
        $updatedCategory = $this->categoryService->update($categoryUpdateDto);
        $dbCategory = $this->categoryRepository->find($dbCategory->getId());
        $this->assertSame('Zoo', $updatedCategory['category']->getCategoryName());
        $this->assertSame('expense', $updatedCategory['category']->getType());
        $this->assertSame('#0f0', $updatedCategory['category']->getColor());

        $this->assertSame('Zoo', $dbCategory->getCategoryName());
        $this->assertSame('expense', $dbCategory->getType());
        $this->assertSame('#0f0', $dbCategory->getColor());
    }
    public function testSearch(): void
    {
        $categoryQueryDto = new CategoryQueryDto(type:'income');
        $categories = $this->categoryService->search($categoryQueryDto);
        $expectedArray = [
            "pagination" => [
                "currentPage" => 1,
                "previousPage" => null,
                "nextPage" => null,
                "totalPages" => 1,
            ],
            "categories" => [
                [
                    "id" => 3,
                    "categoryName" => "Gift",
                    "type" => "income",
                    "color" => "#2196F3",
                    "isDefault" => 1
                ],
                [
                    "id" => 4,
                    "categoryName" => "Other",
                    "type" => "income",
                    "color" => "#9C27B0",
                    "isDefault" => 1
                ],
                [
                    "id" => 1,
                    "categoryName" => "Salary",
                    "type" => "income",
                    "color" => "#4CAF50",
                    "isDefault" => 1
                ],
                [
                    "id" => 2,
                    "categoryName" => "Scholarship",
                    "type" => "income",
                    "color" => "#FFC107",
                    "isDefault" => 1
                ],
            ],
        ];
        $this->assertSame($expectedArray,$categories);
    }
}
