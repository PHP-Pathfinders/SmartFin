<?php

namespace App\Controller;

use App\Dto\User\UserResetPasswordDto;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;

#[Route('/api/mailer')]
class MailerController extends AbstractController
{
    /**
     * For testing purposes, it will be deleted later
     */
    #[Route('', name: 'api_send_email', methods: ['GET'])]
    public function sendEmail(MailerInterface $mailer): JsonResponse
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return $this->json([
            'success' => true,
            'message' => 'Email is sent successfully'
        ]);
    }

    #[Route('/reset-password', name: 'api_mailer_reset_password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] UserResetPasswordDto $userResetPasswordDto,
        MailerService $mailerService
    ): JsonResponse
    {
        $mailerService->resetPassword($userResetPasswordDto);

        return $this->json([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset email shortly'
        ]);
    }
}
