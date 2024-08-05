<?php

namespace App\Scheduler\Handler;

use App\Repository\UserRepository;
use App\Scheduler\Message\DeleteUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    )
    {}
    public function __invoke(DeleteUserMessage $message): void
    {
        $userIds = $this->userRepository->getUsersScheduledForDeletion();
        $this->logger->info('Starting user deletion process.', ['userIds' => $userIds]);
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $user = $this->userRepository->find($userId['id']);
                if ($user) {
                    $this->entityManager->remove($user);
                    $this->logger->info('Deleted user.', ['userId' => $userId['id']]);
                }
                else {
                    $this->logger->warning('User not found for deletion.', ['userId' => $userId['id']]);
                }
            }
            $this->entityManager->flush();
            $this->logger->info('Completed user deletion process.');
        }
    }
}