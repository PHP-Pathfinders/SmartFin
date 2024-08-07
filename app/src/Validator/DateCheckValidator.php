<?php

namespace App\Validator;

use http\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateCheckValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var DateCheck $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        $dateStart = $constraint->dateStart;
        $dateEnd = $constraint->dateEnd;

        if (!property_exists($value, $dateStart) || !property_exists($value, $dateEnd)){
            throw new UnexpectedValueException($value, 'object with properties ' .$dateStart . ' and ' . $dateEnd);
        }
        $dateStartValue = $value->$dateStart;
        $dateEndValue = $value->$dateEnd;
        if($dateStartValue < $dateEndValue){
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath($dateStart)
            ->setCode('dates_not_valid')
            ->addViolation();

    }


}