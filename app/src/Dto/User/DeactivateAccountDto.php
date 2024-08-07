<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class DeactivateAccountDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Password must be provided and cannot be blank')]
        public string $password=''
    )
    {}
}