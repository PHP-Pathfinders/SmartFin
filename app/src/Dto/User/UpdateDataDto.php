<?php

namespace App\Dto\User;

use App\Validator\NotEmptyString;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateDataDto
{
    public function __construct(
        #[NotEmptyString]
        #[Assert\Length(max: 80)]
        public ?string $fullName,
        #[NotEmptyString]
        #[Assert\Date(
            message: 'The birthday must be a valid date in the format YYYY-MM-DD.'
        )]
        public ?string $birthday
    ){}
}