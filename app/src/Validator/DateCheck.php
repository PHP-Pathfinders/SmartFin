<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DateCheck extends Constraint
{
    public function __construct(
        public string $dateStart,
        public string $dateEnd,
        public string $message = 'Starting date cannot be after ending one',
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