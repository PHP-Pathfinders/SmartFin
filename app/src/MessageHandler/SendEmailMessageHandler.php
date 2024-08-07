<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
final readonly class SendEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
    )
    {}
    public function __invoke(SendEmailMessage $message): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('smart-fin@example.com', 'SmartFin'))
            ->to($message->getTo())
            ->subject($message->getSubject())
            ->htmlTemplate($message->getTemplate())
            ->context($message->getContext());
        $this->mailer->send($email);
    }

}
