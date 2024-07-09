<?php

namespace App\Controller;

use App\Dto\User\UserRegisterDto;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        //In case of client didn't send json payload at all
        return $this->json([
            'success' => false,
            'message' => 'Json payload not found'
        ], Response::HTTP_NOT_FOUND);
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
    public function createUser(
        #[MapRequestPayload] UserRegisterDto $userRegisterDto,
        UserService $userService
    ): JsonResponse
    {
        $userService->create($userRegisterDto);
        return $this->json([
            'success' => true,
            'message' => 'User registered successfully'
        ]);
    }
}
