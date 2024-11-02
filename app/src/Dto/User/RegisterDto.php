<?php

namespace App\Dto\User;

use App\Validator\FieldsMatch;
use App\Validator\IsEmailAvailable;
use Symfony\Component\Validator\Constraints as Assert;

#[FieldsMatch(field:'password',matchingField: 'confirmPassword', message:'Passwords do not match')]
readonly class RegisterDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Full name must be provided and cannot be blank')]
        #[Assert\Length(
            max: 80,
            maxMessage: 'Full name cannot be longer than 50 characters.'
        )]
        public string $fullName='',
        #[Assert\NotBlank(message: 'Email must be provided and cannot be blank')]
        #[Assert\Length(
            max: 80,
            maxMessage: 'Email cannot be longer than 80 characters.'
        )]
        #[Assert\Email]
        #[IsEmailAvailable]
        public string $email='',

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
    ) {}
}