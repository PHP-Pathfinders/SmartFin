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
        $this->authorizeUserStatus($event);
        $this->trimRequestInputs($event);
    }

    /**
     * For all ^/api routes that require role: IS_AUTHENTICATED_FULLY add also custom voter to check if
     * account is active or not (if not active /api/users/activate will be only route available (See voter setup!))
     * @param RequestEvent $event
     * @return void
     */
    private function authorizeUserStatus(RequestEvent $event): void
    {
        if (
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')
            && !$this->authorizationChecker->isGranted('USER_STATUS')
        )
        {
            throw new AccessDeniedException('Access Denied.');
        }
    }

    /**
     * On each request all inputs from query params and json payload are trimmed
     * @param RequestEvent $event
     * @return void
     */
    private function trimRequestInputs(RequestEvent $event): void
    {
        $request = $event->getRequest();
//         Trim query parameters
        $request->query->replace($this->trimArray($request->query->all()));

//         Trim JSON payload
        $content = $request->getContent();
        if ($content) {
            $jsonArr = json_decode($content, true);
            if (is_array($jsonArr)) {
                $trimmedJsonArr = $this->trimArray($jsonArr);
//                Reinitialize request object with trimmed json and query params
                $request->initialize(
                    $request->query->all(),
                    $request->request->all(),
                    $request->attributes->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    json_encode($trimmedJsonArr)
                );
            }
        }
    }

    private function trimArray(array $data): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            }
            if (is_array($item)) {
                return $this->trimArray($item);
            }
            return $item;
        }, $data);
    }

}
