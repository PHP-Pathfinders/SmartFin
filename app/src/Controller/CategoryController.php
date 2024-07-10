<?php

namespace App\Controller;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', name: 'api_categories')]
class CategoryController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * Find categories by income or expenses using query params
     * - Example url: localhost:8080/api/categories?page=1&type=expense&limit=15
     */
    #[Route('', name: 'api_find_categories_by_type', methods: ['GET'])]
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

    /**
     * Create a new category for logged-in user
     */
    #[Route('', name: 'api_add_category', methods: ['POST'])]
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

    #[Route('', name: 'api_update_category', methods: ['PATCH'])]
    public function update(
        #[MapRequestPayload] CategoryUpdateDto $categoryUpdateDto,
        CategoryService $categoryService,
    ):JsonResponse
    {
        $message = $categoryService->update($categoryUpdateDto);
        return $this->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Delete category if it belongs to user
     * - Example url: localhost:8080/api/categories/123
     */
    #[Route('/{id<\d+>}', name: 'api_delete_category', methods: ['DELETE'])]
    public function delete(int $id, CategoryService $categoryService):JsonResponse
    {
        $categoryService->delete($id);
        return $this->json([
            'success' => true,
            'message' => "Category deleted with id=$id"
        ]);
    }
}
