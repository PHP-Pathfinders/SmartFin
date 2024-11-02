<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
//use Symfony\Component\HttpFoundation\RequestStack;

readonly class JWTCreatedListener
{
//    public function __construct(
//        private RequestStack $requestStack
//    )
//    {}

    /**
     * @param JWTCreatedEvent $event
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();

        /** @var User $user */
        $user = $event->getUser();
        $payload['token_version'] = $user->getJwtVersion();
        $payload['user_id'] = $user->getId();

        $payload['is_active'] = $user->getIsActive();

        $event->setData($payload);

        $header = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}