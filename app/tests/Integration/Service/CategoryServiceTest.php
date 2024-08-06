<?php

namespace App\Tests\Integration\Service;

use App\Dto\Category\CategoryCreateDto;
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
    }
    public function testCreate(): void
    {
        $user = $this->mock->login();
        $categoryCreateDto = new CategoryCreateDto('Festival','expense','#f02');
        $this->categoryService->create($categoryCreateDto);
//        TODO finish off this test
    }
}
