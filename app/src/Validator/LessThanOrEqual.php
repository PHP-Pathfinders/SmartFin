<?php

namespace App\Validator;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class LessThanOrEqual extends Constraint
{
   public function __construct(
       public int $comparedValue,
       public string $message = 'This field must be less than or equal to {{ compared_value }}',
       mixed $options = null,
       ?array $groups = null,
       mixed $payload = null
   )
   {
       parent::__construct($options, $groups, $payload);
   }
}
