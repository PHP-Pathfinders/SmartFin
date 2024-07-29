<?php

namespace App\Tests\Integration\Service;

use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserServiceTest extends KernelTestCase
{

    private $userService;
    private $userRepository;
    private $tokenStorage;
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->userRepository = $container->get(UserRepository::class);
        $this->tokenStorage = $container->get(TokenStorageInterface::class);
        $this->userService = $container->get(UserService::class);
    }

    public function testFetchUserSuccess(): void
    {
        $userId = 1;

        // Fetch user from the database
        $user = $this->userRepository->find($userId);

        // Simulate logged-in user
        $token = new UsernamePasswordToken($user, 'password', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);

        $result = $this->userService->fetch($userId);

        $this->assertSame([
            'userId' => 1,
            'fullName' => 'John Doe',
            'birthday' => $user->getBirthday(),
            'avatarFileName' => null,
            'email' => 'john@gmail.com',
            'isActive' => true,
            'createdAt' => $user->getCreatedAt(),
        ], $result);
    }

    public function testFetchUserNotFound(): void
    {
        $userId = 999; // Use an ID that is unlikely to exist in your test database

        // Simulate logged-in user
        $token = new UsernamePasswordToken($this->userRepository->find(1), 'password', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);

        // Expecting NotFoundHttpException to be thrown
        $this->expectException(NotFoundHttpException::class);

        // Call the fetch method with an ID that does not exist
        $this->userService->fetch($userId);
    }
}
