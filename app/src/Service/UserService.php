<?php

namespace App\Service;

use App\Dto\User\UserRegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
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
    }
}