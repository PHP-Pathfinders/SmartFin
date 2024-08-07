<?php

namespace App\Controller;

use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryQueryDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Service\CategoryService;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/api/categories', name: 'api_categories')]
class CategoryController extends AbstractController
{
    public function __construct(readonly private CategoryService $categoryService)
    {
    }

    /**
     * Find categories by income or expenses using query params
     * - Example url: localhost:8080/api/categories?page=1&type=expense&limit=15
     */
    #[OA\Get(
        description: 'Returns list of default and custom categories based on query parameters. If no query parameters are specified, query will return all default and custom categories of logged-in user',
        summary: 'Find custom and default categories by income or expenses or both using query params',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/Category')
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
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    #[Route(name: 'api_find_categories_by_type', methods: ['GET'])]
    public function search(#[MapQueryString] ?CategoryQueryDto $categoryQueryDto): JsonResponse
    {
        //Search
        $data = $this->categoryService->search($categoryQueryDto);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Create a new category for logged-in user
     */
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
                description: 'Name conflict',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryNameConflict')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            )
        ]
    )]
    #[Security(name: 'Bearer')]
    #[Route(name: 'api_add_category', methods: ['POST'])]
    public function create(#[MapRequestPayload] CategoryCreateDto $categoryCreateDto): JsonResponse
    {
        $category = $this->categoryService->create($categoryCreateDto);
        return $this->json([
            'success' => true,
            'message' => 'New category created',
            'data' => $category
        ],Response::HTTP_CREATED,
        context:[
                ObjectNormalizer::GROUPS => ['category']
            ]
        );
    }

    #[OA\Patch(
        description: 'Make changes to existing category that is in ownership of user',
        summary: 'Update existing category',
        tags: ['Categories'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful category update or nothing to change',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryUpdateSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden default category change',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryForbidden')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 404,
                description: 'Invalid category given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryFailed')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryInputError')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route(name: 'api_update_category', methods: ['PATCH'])]
    #[Security(name: 'Bearer')]
    public function update(#[MapRequestPayload] CategoryUpdateDto $categoryUpdateDto): JsonResponse
    {
        $dataArr = $this->categoryService->update($categoryUpdateDto);
        if (isset($dataArr['category'])) {
            return $this->json(
                [
                    'success' => true,
                    'message' => $dataArr['message'],
                    'data' => $dataArr['category']
                ], context: [
                ObjectNormalizer::GROUPS => ['category']
            ]
            );
        }
        return $this->json(
            [
                'success' => true,
                'message' => $dataArr['message']
            ]
        );
    }

    /**
     * Delete category if it belongs to user
     * - Example url: localhost:8080/api/categories/123
     */
    #[OA\Delete(
        description: "Removes specific category that is in ownership of logged user",
        summary: "Delete specific category based on given id",
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
                description: 'Invalid category given',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryFailed')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden default category deletion',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryForbiddenDelete')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )

        ]
    )]
    #[Security(name: 'Bearer')]
    #[Route('/{id<\d+>}', name: 'api_delete_category', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->categoryService->delete($id);
        return $this->json([
            'success' => true,
            'message' => "Category deleted with id=$id"
        ]);
    }
}
