<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class NotEmptyString extends Constraint
{
    public function __construct(
        public string $message = 'This field cannot be empty string',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null
    )
    {
        parent::__construct($options, $groups, $payload);
    }
}
