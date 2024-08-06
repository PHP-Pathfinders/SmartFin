<?php

namespace App\Scheduler;

use App\Scheduler\Message\DeleteUserMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('DeleteUserMessage')]
readonly class DeleteUserScheduler implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                 RecurringMessage::every('1 minute', new DeleteUserMessage()),
            )
            ->stateful($this->cache)
            ;
    }
}