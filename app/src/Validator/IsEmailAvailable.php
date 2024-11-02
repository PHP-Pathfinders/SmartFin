<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class IsEmailAvailable extends Constraint
{
    public function __construct(
        public string $message = 'Email: \'{{ value }}\' is already taken',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }
}
