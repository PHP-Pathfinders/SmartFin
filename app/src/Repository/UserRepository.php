<?php

namespace App\Repository;

use App\Dto\User\UpdateDataDto;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Reset password
     * @param string $password
     * @param User $user
     * @return void
     */
    public function resetPassword(string $password, User $user): void
    {
        $user->setPassword($password);
        // Log out user from all devices
        $user->incrementJwtVersion();
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }


    public function fetchUser(int $id): ?User
    {
        return $this->find($id);
    }

    /**
     * Creates a new user if email is not taken
     * @param string $fullName
     * @param string $email
     * @param string $password
     * @param User $user
     * @return void
     */
    public function register(string $fullName, string $email, string $password,User $user):void
    {
        $isEmailAvailable = $this->isEmailAvailable($email);
        if (!$isEmailAvailable) {
            throw new ConflictHttpException('E-mail is already taken');
        }
        $user->setFullName($fullName);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setScheduledDeletionDate();
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function update(UpdateDataDto $updateDataDto, User $user):void
    {
        $fullName = $updateDataDto->fullName;
        $birthdayStr = $updateDataDto->birthday;

        if ($fullName) {
            $user->setFullName($fullName);
        }
        if ($birthdayStr) {
            $birthday = \DateTime::createFromFormat('Y-m-d', $birthdayStr);
            $user->setBirthday($birthday);
        }
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function updateProfileImage(?string $newFileName, User $user):void
    {
        $user->setAvatarFileName($newFileName);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Change password
     * @param string $password
     * @param User $user
     * @return void
     */
    public function changePassword(string $password, User $user):void
    {
        $user->setPassword($password);
        // Increment token version in order to invalidate jwt token (Log out from all devices)
        $user->incrementJwtVersion();
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Deactivates the user and sets scheduled deletion date
     * @param User $user
     * @return void
     */
    public function deactivate(User $user): void
    {
        $user->setIsActive(false);
        $user->setScheduledDeletionDate();
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Activates the user and clears scheduled deletion date
     * @param User $user
     * @return void
     */
    public function activate(User $user): void
    {
        $user->setIsActive(true);
        $user->clearScheduledDeletionDate();
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function deleteUsers(): void
    {
        $userIds = $this->getUsersScheduledForDeletion();

        if (!empty($userIds)) {
            $entityManager = $this->getEntityManager();
            foreach ($userIds as $userId) {
                $user = $this->find($userId['id']);
                if ($user) {
                    $entityManager->remove($user);
                }
            }
            $entityManager->flush();
        }
    }

    private function getUsersScheduledForDeletion(): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.scheduledDeletionDate < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Checks whether email is available or not
     * @param string $email
     * @return bool
     */
    private function isEmailAvailable(string $email): bool
    {
//        Checking if email exists in db
        $isEmailTaken = (bool) $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult();
        return !$isEmailTaken;
    }
}
