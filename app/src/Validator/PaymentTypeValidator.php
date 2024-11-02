<?php

namespace App\Validator;

use http\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PaymentTypeValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var PaymentType $constraint */

        if (null === $value || '' === $value) {
            return;
        }


        $categoryType = $constraint->categoryType;
        $paymentType = $constraint->paymentType;

        if (!property_exists($value, $categoryType) || !property_exists($value, $paymentType)) {
            throw new UnexpectedValueException($value, 'object with properties ' . $paymentType . ' and ' . $categoryType);
        }
        $paymentType = $value->$paymentType;
        $categoryType = $value->$categoryType;

        if ($categoryType === 'income') {
            return;
        }
        if ($categoryType === 'expense') {
            if (!($paymentType === 'card' || $paymentType === 'cash')) {
                $this->context->buildViolation('Payment type must be either \'cash\' or \'card\'')
                    ->atPath($constraint->paymentType)
                    ->setCode('invalid_payment_type')
                    ->addViolation();
            }
            return;

        }

        $this->context->buildViolation('Category type must be either \'income\' or \'expense\'')
            ->atPath($constraint->categoryType)
            ->setCode('invalid_category_type')
            ->addViolation();
    }
}
