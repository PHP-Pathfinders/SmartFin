<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserResetPasswordDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email
    )
    {}
}