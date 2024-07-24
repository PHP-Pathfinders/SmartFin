<?php

namespace App\Controller;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Service\CategoryService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

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
    #[OA\Get(
        description: 'Returns list of categories based on chosen type',
        summary: 'Find categories by income or expenses using query params',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/Category')
            ),
            new OA\Response(
                response: 404,
                description: 'No categories found',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryNotFound')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryQueryError')
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function search(
        #[MapQueryString] ?CategoryQueryDto $categoryQueryDto,
        CategoryService                     $categoryService
    ): JsonResponse
    {
        //Search
        $data = $categoryService->search($categoryQueryDto);

        // If no categories are found
        if (empty($data['categories'])) {
            return $this->json([
                'success' => false,
                'message' => 'No categories found'
            ], 404);
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
    #[OA\Post(
        description: 'Creates a new category with logged-in user as its owner',
        summary: 'Create a new category for logged-in user',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful category insertion',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryInputSuccess')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 409,
                description: 'You already have a category with the given name for that type.'
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    public function create(
        #[MapRequestPayload] CategoryCreateDto $categoryCreateDto,
        CategoryService                        $categoryService
    ): JsonResponse
    {
        $categoryService->create($categoryCreateDto);
        return $this->json([
            'success' => true,
            'message' => 'New category created'
        ]);
    }

    #[Route('', name: 'api_update_category', methods: ['PATCH'])]
    #[OA\Patch(
        description: 'Makes changes to existing category',
        summary: 'Update existing category',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful category update',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryUpdateSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Cannot change default category',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryForbidden')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 404,
                description: 'Category you selected is either not owned by you or does not exist',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryFailed')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryInputError')
            ),
        ]
    )]
    #[Security(name: 'Bearer')]
    public function update(
        #[MapRequestPayload] CategoryUpdateDto $categoryUpdateDto,
        CategoryService                        $categoryService,
    ): JsonResponse
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
    #[OA\Delete(
        description: "Removes category that is in ownership of logged user",
        summary: "Deletes specific category based on given id",
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful category deletion',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryDeleteSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 404,
                description: 'Category you selected is either not owned by you or does not exist',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryFailed')
            ),
            new OA\Response(
                response: 403,
                description: 'Cannot delete default category',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryForbiddenDelete')
            )

        ]
    )]
    #[Security(name: 'Bearer')]
    public function delete(int $id, CategoryService $categoryService): JsonResponse
    {
        $categoryService->delete($id);
        return $this->json([
            'success' => true,
            'message' => "Category deleted with id=$id"
        ]);
    }
}
