<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserStatusVoter extends Voter
{
    private const USER_STATUS = 'USER_STATUS';
    private const ALLOWED_ROUTE_IF_INACTIVE = 'api_users_activate';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        return $attribute === self::USER_STATUS;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Get the current route
        $request = $this->requestStack->getCurrentRequest();
        $route = $request->attributes->get('_route');

        //If user is not active and route is meant for inactive users then allow access
        if($route === self::ALLOWED_ROUTE_IF_INACTIVE && !$user->getIsActive()){
            return true;
        }
        // If route is meant for active users and user is active then allow access
        if ($route !== self::ALLOWED_ROUTE_IF_INACTIVE && $user->getIsActive()) {
            return true;
        }

        return false;
    }
}

