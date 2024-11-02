<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IntegerTypeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var IntegerType $constraint */


        if (null === $value || '' === $value) {
            return;
        }

        if(is_numeric($value) && is_int($value+0)){
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setCode('integer_type')
            ->addViolation();
    }
}
