<?php

namespace App\Scheduler\Handler;

use App\Repository\UserRepository;
use App\Scheduler\Message\DeleteUserMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'monolog.logger.user_cleanup')] private LoggerInterface $logger
    )
    {}
    public function __invoke(DeleteUserMessage $message): void
    {
        $userIds = $this->userRepository->getUsersScheduledForDeletion();
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $user = $this->userRepository->find($userId);
                if ($user) {
                    $this->logger->info('Deleted user:', ['id' => $userId, 'email' => $user->getEmail(), $user->getFullName()]);
                    $this->entityManager->remove($user);
                }
            }
            $this->entityManager->flush();
        }
    }
}