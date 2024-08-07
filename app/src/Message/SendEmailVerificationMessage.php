<?php

namespace App\Message;

class SendEmailVerificationMessage
{
    public function __construct(
        private string $email
    )
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}