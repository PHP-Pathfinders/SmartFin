<?php

namespace App\Dto\Export;

use App\Validator\IntegerType;
use App\Validator\PositiveNumber;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SearchDto
{
    public function __construct(
        #[Assert\Choice([null,'pdf', 'xlsx'], message: 'fileType must be pdf, xlsx or null')]
        public ?string $fileType = null
    )
    {}
}