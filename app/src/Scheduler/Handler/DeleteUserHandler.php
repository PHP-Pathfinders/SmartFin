<?php

namespace App\Scheduler\Handler;

use App\Repository\UserRepository;
use App\Scheduler\Message\DeleteUser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteUserHandler
{
    public function __construct(private UserRepository $userRepository)
    {}
    public function __invoke(DeleteUser $message): void
    {
        $this->userRepository->deleteUsers();
    }
}