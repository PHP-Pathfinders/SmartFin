<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
    )
    {
    }

    #[Route('/find-categories-by-type', name: 'app_category', methods: ['GET'])]
    public function findCategoriesByType(): JsonResponse
    {
        //TODO finish off DTO mapping and validation for query params . . .
        return $this->json([

        ]);
    }
}
