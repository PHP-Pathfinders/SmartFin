<?php

namespace App\Controller;

use App\Dto\User\RequestPasswordResetDto;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/api/mailer')]
class MailerController extends AbstractController
{
    /**
     * @throws ResetPasswordExceptionInterface
     */
    #[Route('/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] RequestPasswordResetDto $requestPasswordResetDto,
        MailerService                                $mailerService
    ): JsonResponse
    {
        $mailerService->forgotPassword($requestPasswordResetDto);

        return $this->json([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset email shortly'
        ]);
    }
}
