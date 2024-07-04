<?php

namespace App\Controller;

use App\Dto\CategoryQueryDto;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
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
    #[Route('/find-categories-by-type', name: 'api_find_categories_by_type', methods: ['GET'])]
    public function findCategoriesByType(
        #[MapQueryString] ?CategoryQueryDto $categoryQueryDto,
        CategoryService $categoryService
    ): JsonResponse
    {
        $categories = $categoryService->findCategoriesByType($categoryQueryDto);

        // If no categories are found
        if (empty($categories)) {
            return $this->json([
                'success' => false,
                'message' => 'No categories found'
            ]);
        }

        return $this->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
}
