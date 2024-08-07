<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SendEmailVerificationMessage;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
class SendEmailVerificationMessageHandler
{
    public function __construct(
        private EmailVerifier  $emailVerifier,
        private UserRepository $userRepository
    )
    {
    }

    public function __invoke(SendEmailVerificationMessage $message): void
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $message->getEmail()]);
        // Send verification link to verify email
        $this->emailVerifier->sendEmailConfirmation('api_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('smart-fin@example.com', 'SmartFin'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('email/confirmation_email.html.twig')
                ->context([
                    'expiresAtMessageKey' => 'email.confirmation.expires_at',
                    'expiresAtMessageData' => [
                        '%count%' => 7, // This should match the lifetime in days
                    ],
                ])
        );
    }
}