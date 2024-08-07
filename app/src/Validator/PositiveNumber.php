<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class PositiveNumber extends Constraint
{
    public function __construct(
        public string $message='{{ value }} is not a positive number',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }
}
