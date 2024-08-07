<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LessThanOrEqualValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var LessThanOrEqual $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if(!is_numeric($value)){
            return;
        }
        // Check if input value is less than or equal as defined value
        if($value <= $constraint->comparedValue ){
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setParameter('{{ compared_value }}', $constraint->comparedValue)
            ->setCode('less_than_or_equal')
            ->addViolation();
    }
}
