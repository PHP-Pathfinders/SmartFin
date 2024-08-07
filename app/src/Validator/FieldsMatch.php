<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class FieldsMatch extends Constraint
{
    public function __construct(
        public string $field,
        public string $matchingField,
        public string $message = 'Fields do not match',
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null
    )
    {
        parent::__construct($options, $groups, $payload);
    }

    // This is added so the attribute can be placed on top of the class
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
