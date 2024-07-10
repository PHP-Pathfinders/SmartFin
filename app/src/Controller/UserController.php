<?php

namespace App\Controller;

use App\Dto\User\UserRegisterDto;
use App\Service\MailerService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;

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
        ], Response::HTTP_BAD_REQUEST);
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
    public function create(
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

    /**
     * @throws InvalidSignatureException
     */
    #[Route('/verify-email', name: 'api_verify_email')]
    public function verifyEmail(
        #[MapQueryParameter] int $id,
        UserService $userService,
        Request $request
    ): JsonResponse
    {
        $userService->verifyEmail($id,$request);

        return $this->json([
            'success' => true,
            'message' => 'Your email address has been verified'
        ]);
    }
    #[Route('/reset-password-page', name: 'app_reset_password_page', methods: ['GET'])]
    public function resetPassword():Response
    {
        return $this->render('reset-password/reset_password.html.twig');
    }
    //TODO make new endpoint for password-reset and validate token that is sent from frontend!
}
