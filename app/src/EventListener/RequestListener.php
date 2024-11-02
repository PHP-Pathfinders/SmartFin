<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class RequestListener
{

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $this->authorizeUserStatus();
    }

    /**
     * For all ^/api routes that require role: IS_AUTHENTICATED_FULLY add also custom voter to check if
     * account is active or not (if not active /api/users/activate will be only route available (See voter setup!))
     * @return void
     */
    private function authorizeUserStatus(): void
    {
        if (
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')
            && !$this->authorizationChecker->isGranted('USER_STATUS')
        )
        {
            throw new AccessDeniedException('Access Denied.');
        }
    }

}
