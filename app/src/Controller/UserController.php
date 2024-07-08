<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // This endpoint doesn't need to do anything server-side
        return $this->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function createUser(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'message' => 'User registered successfully'
        ]);
    }
}
