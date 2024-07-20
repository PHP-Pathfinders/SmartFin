<?php

namespace App\Dto\Export;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SearchDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'userId must be provided and cannot be blank')]
        #[IntegerType]
        #[PositiveNumber]
        public ?string $userId,
//        #[Assert\NotBlank(message: 'fileName must be provided and cannot be blank')]
        #[Assert\Choice([null,'pdf', 'xlsx'], message: 'fileType must be pdf, xls or null')]
        public ?string $fileType = null
    )
    {}
}