<?php

namespace App\Scheduler;

use App\Scheduler\Message\DeleteUser;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('DeleteUser')]
class DeleteUserProvider implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }
    public function getSchedule(): Schedule
    {
//        RecurringMessage::cron('@daily', new DeleteUser() );
//        // ...
        return (new Schedule())
            ->add(
                // @TODO - Create a Message to schedule
                 RecurringMessage::every('50 seconds', new DeleteUser()),
            )
            ->stateful($this->cache)
            ;
    }
}