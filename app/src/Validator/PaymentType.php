<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PaymentType extends Constraint
{
    public function __construct(
        public string $categoryType,
        public string $paymentType,
        public string $message = 'Only expenses can have payment type.',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

}
