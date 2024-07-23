<?php

namespace App\Service;

use App\Dto\User\RequestPasswordResetDto;
use App\Message\SendEmailMessage;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

readonly class MailerService
{
    public function __construct(
        private UserRepository $userRepository,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MessageBusInterface $bus
    )
    {}

    /**
     * @throws ResetPasswordExceptionInterface
     * @throws ExceptionInterface
     */
    public function forgotPassword(RequestPasswordResetDto $requestPasswordResetDto):void
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
        $this->bus->dispatch(new SendEmailMessage($email, $subject, $template, $context));
    }
}