<?php

namespace App\Dto\User;

use App\Validator\FieldsMatch;
use Symfony\Component\Validator\Constraints as Assert;

#[FieldsMatch(field:'password',matchingField: 'confirmPassword', message:'Passwords do not match')]
readonly class ResetPasswordDto
{
    public function __construct(
        #[Assert\NotBlank(message:'Token must be provided and cannot be blank')]
        public string $token='',
        #[Assert\NotBlank(message: 'Password must be provided and cannot be blank.')]
        #[Assert\Regex(
            pattern: '/^(?=.*[a-z])/',
            message: 'Password must contain at least one lowercase letter.'
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*[A-Z])/',
            message: 'Password must contain at least one uppercase letter.'
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*\d)/',
            message: 'Password must contain at least one number.'
        )]
        #[Assert\Regex(
            pattern: '/^(?=.*[\W_])/',
            message: 'Password must contain at least one special character.'
        )]
        #[Assert\Length(
            min: 6,
            minMessage: 'Password must be at least {{ limit }} characters long.'
        )]
        public string $password='',
        #[Assert\NotBlank(message: 'Password must be provided and cannot be blank.')]
        public string $confirmPassword=''
    )
    {}
}