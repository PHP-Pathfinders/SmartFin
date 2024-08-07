<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsEmailAvailableValidator extends ConstraintValidator
{
    public function __construct(private UserRepository $userRepository)
    {}
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IsEmailAvailable $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        $isEmailAvailable = $this->userRepository->isEmailAvailable($value);
        if ($isEmailAvailable) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setCode('email_taken')
            ->addViolation();
    }
}
