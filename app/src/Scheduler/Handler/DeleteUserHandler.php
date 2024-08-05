<?php

namespace App\Scheduler\Handler;

use App\Repository\UserRepository;
use App\Scheduler\Message\DeleteUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    )
    {}
    public function __invoke(DeleteUserMessage $message): void
    {
        $userIds = $this->userRepository->getUsersScheduledForDeletion();
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $user = $this->userRepository->find($userId['id']);
                if ($user) {
                    $this->entityManager->remove($user);
                }
            }
            $this->entityManager->flush();
        }
    }
}