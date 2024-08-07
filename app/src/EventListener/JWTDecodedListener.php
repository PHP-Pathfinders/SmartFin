<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;


readonly class JWTDecodedListener
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
//        Grab request data
//        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getPayload();
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $payload['username']]);

        if ($payload['token_version'] !== $user->getJwtVersion()) {
            $event->markAsInvalid();
        }
//        Check for IP
//        if (!isset($payload['ip']) || $payload['ip'] !== $request->getClientIp()) {
//            $event->markAsInvalid();
//        }
//
    }
}