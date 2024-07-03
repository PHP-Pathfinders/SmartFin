<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class UserController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(): void
    {
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        dd('Hello from user controller logout method');
    }
}
