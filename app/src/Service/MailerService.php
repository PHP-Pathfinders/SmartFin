<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;

readonly class MailerService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailVerifier  $emailVerifier
    )
    {}

    /**
     * @throws InvalidSignatureException
     */
    public function verifyUserEmail(int $id,Request $request):void
    {
        $user = $this->userRepository->find($id);
        // Ensure the user exists
        if (null === $user) {
            throw new InvalidSignatureException('Invalid signature');
        }
        // validate email confirmation link, sets User isVerified=true
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }
}