<?php

namespace App\Validator;

use http\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FieldsMatchValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var FieldsMatch $constraint */

        if (null === $value || '' === $value) {
            return;
        }
        $field = $constraint->field;
        $matchingField = $constraint->matchingField;

        // Remember, $value is actually object in this case
        if(!property_exists($value, $field) || !property_exists($value, $matchingField)){
            throw new UnexpectedValueException($value, 'object with properties '.$field.' and '.$matchingField);
        }
        $fieldValue = $value->$field;
        $matchingFieldValue = $value->$matchingField;

//        Check if fields match, and if so return
        if($fieldValue === $matchingFieldValue){
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath($matchingField)
            ->setCode('fields_not_match')
            ->addViolation();
    }
}
