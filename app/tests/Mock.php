<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Mock
{
    private int $userId = 1;
    public function __construct(
        private readonly UserRepository        $userRepository,
        private readonly TokenStorageInterface $tokenStorage
    )
    {}

    public function login(): User
    {
        // Fetch user from the database
        $user = $this->userRepository->find($this->userId);
        // Simulate logged-in user
        $token = new UsernamePasswordToken($user, 'password', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
        return $user;
    }
}