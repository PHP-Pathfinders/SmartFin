<?php

namespace App\Dto\User;

use App\Validator\NotEmptyString;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDataDto
{
    public function __construct(
        #[NotEmptyString]
        public ?string $avatarPath,
        #[NotEmptyString]
        #[Assert\Length(
            max: 80,
            maxMessage: 'Full name cannot be longer than 50 characters.'
        )]
        public ?string $fullName,
        #[NotEmptyString]
        #[Assert\Length(
            max: 80,
            maxMessage: 'Email cannot be longer than 80 characters.'
        )]
        #[Assert\Email]
        public ?string $email,
        #[NotEmptyString]
        #[Assert\Date(
            message: 'The birthday must be a valid date in the format YYYY-MM-DD.'
        )]
        public ?string $birthday
    ){}
}