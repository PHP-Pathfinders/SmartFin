<?php

namespace App\Service;

use App\Dto\User\RequestPasswordResetDto;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

readonly class MailerService
{
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer
    )
    {}

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function resetPassword(RequestPasswordResetDto $requestPasswordResetDto):void
    {
        $email = $requestPasswordResetDto->email;
        $user = $this->userRepository->findOneBy(['email'=>$email]);
        if (!$user) {
            return;
        }
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $subject = 'Reset password';
        $template = 'email/reset_password.html.twig';
        $context = [
            'resetToken' => $resetToken->getToken(),
            'expirationMessageKey' => 'reset_password.expiration',
            'expirationMessageData' => ['%count%' => ($resetToken->getExpiresAt()->getTimestamp() - time()) / 60]
        ];
        $this->sendMail($email,$subject,$template,$context);
    }
    private function sendMail(string $to, string $subject, string $template, ?array $context):void
    {
        $email = (new TemplatedEmail())
                ->from(new Address('smart-fin@example.com', 'SmartFin'))
                ->to($to)
                ->subject($subject)
                ->htmlTemplate($template)
                ->context($context);

        $this->mailer->send($email);
    }
}