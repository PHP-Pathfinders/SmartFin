<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEmptyStringValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var NotEmptyString $constraint */

        if (null === $value) {
            return;
        }

        if (!is_string($value)) {
            return;
        }
        if ($value !== ''){
            return;
        }
        $this->context->buildViolation($constraint->message)
            ->setCode('empty_string')
            ->addViolation();
    }
}
