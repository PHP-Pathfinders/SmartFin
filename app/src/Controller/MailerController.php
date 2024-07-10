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
    #[Route('/reset-password', name: 'api_mailer_reset_password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] RequestPasswordResetDto $requestPasswordResetDto,
        MailerService                                $mailerService
    ): JsonResponse
    {
        $mailerService->resetPassword($requestPasswordResetDto);

        return $this->json([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset email shortly'
        ]);
    }
}
