<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class IntegerType extends Constraint
{
    public function __construct(
        public string $message = '\'{{ value }}\' is not an integer.',
        ?array $groups = null,
        mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
    }
}
