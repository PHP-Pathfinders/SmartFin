<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        if (!$user->getIsVerified()) {
            // User must verify email in order to log-in
            throw new CustomUserMessageAccountStatusException('E-mail is not yet verified, please verify e-mail in order to log-in');
        }

        if (!$user->getIsActive()) {
            // Account is deactivated
            throw new CustomUserMessageAccountStatusException('Account is deactivated');
        }
        // user account is expired, the user may be notified
//        if ($user->isExpired()) {
//            throw new AccountExpiredException('...');
//        }
    }
}