<?php

namespace App\Controller;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class CategoryController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * Find categories by income or expenses using query params
     * - Example url: localhost:8080/api/find-categories-by-type?page=1&type=expense
     */
    #[Route('/categories', name: 'api_find_categories_by_type', methods: ['GET'])]
    public function search(
        #[MapQueryString] ?CategoryQueryDto $categoryQueryDto,
        CategoryService $categoryService
    ): JsonResponse
    {
        //Search
        $data = $categoryService->search($categoryQueryDto);

        // If no categories are found
        if (empty($data['categories'])) {
            return $this->json([
                'success' => false,
                'message' => 'No categories found'
            ]);
        }

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/categories', name: 'api_add_category', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CategoryCreateDto $categoryCreateDto,
        CategoryService $categoryService
    ):JsonResponse
    {
        $categoryService->create($categoryCreateDto);

        return $this->json([
            'success' => true,
            'message' => 'New category created'
        ]);
    }
}
