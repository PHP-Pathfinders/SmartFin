<?php

namespace App\Controller;

use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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

    /**
     * @throws InvalidSignatureException
     */
    #[Route('/verify/email', name: 'api_verify_email')]
    public function verifyUserEmail(
        #[MapQueryParameter] int $id,
        MailerService $mailerService,
        Request $request
    ): JsonResponse
    {
        $mailerService->verifyUserEmail($id,$request);

        return $this->json([
            'success' => true,
            'message' => 'Your email address has been verified'
        ]);
    }
}
