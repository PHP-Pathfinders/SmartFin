<?php

namespace App\Scheduler\Message;

use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('#hourly')]
readonly class DeleteUser
{
//    private int $id;
    public function __construct() {}

//    public function getId(): int
//    {
//        return $this->id;
//    }
}