<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class RequestPasswordResetDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email
    )
    {}
}