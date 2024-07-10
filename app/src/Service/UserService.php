<?php

namespace App\Service;

use App\Dto\User\UserRegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserService
{
    public function __construct(
        private UserRepository              $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailVerifier               $emailVerifier
    ){}

    public function create(UserRegisterDto $userRegisterDto):void
    {
        $fullName = $userRegisterDto->fullName;
        $email = $userRegisterDto->email;
        $plainPassword = $userRegisterDto->password;

//        Make new instance of user and hash password
        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($user,$plainPassword);
        $this->userRepository->create($fullName,$email,$hashedPassword,$user);

        // Send verification link to verify email
        $this->emailVerifier->sendEmailConfirmation('api_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('smart-fin@example.com', 'SmartFin'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}