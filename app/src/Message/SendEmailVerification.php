<?php

namespace App\Message;

final readonly class SendEmailVerification
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
