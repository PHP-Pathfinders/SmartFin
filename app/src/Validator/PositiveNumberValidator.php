<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PositiveNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var PositiveNumber $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_numeric($value)) {
            return;
        }
        if ($value > 0) {
            return;
        }
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setCode('positive_number')
            ->addViolation();
    }
}
