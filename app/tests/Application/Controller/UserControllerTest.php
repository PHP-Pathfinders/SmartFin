<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testLogin(): void
    {
        // Define the payload for login
        $payload = [
            'username' => 'john@gmail.com',
            'password' => 'Password#1',
        ];
        $client = static::createClient();

        $client->jsonRequest('POST', '/api/login', $payload);
        self::assertResponseIsSuccessful();
        // Assert the response status code
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $client->getResponse();
        $responseContent = json_decode($response->getContent(), true);
        // Token is different every time, so check pattern with regex
        self::assertMatchesRegularExpression('/^[A-Za-z0-9\-._~+\/]+=*$/', $responseContent['token']);
    }

    public function testRegister(): void
    {
        // Define the payload for registration
        $payload = [
            'fullName' => 'Barry Bar',
            'email' => 'barry@example.com',
            'password' => 'Password#1',
            'confirmPassword' => 'Password#1'
        ];
        $client = static::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $client->jsonRequest('POST', '/api/users/register', $payload);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $jsonResponse = $client->getResponse()->getContent();
        $jsonResponseArr = json_decode($jsonResponse, true);

        self:: assertJson($jsonResponse);
        /** @var User $user */
        $user = $userRepository->findOneBy(['email'=>'barry@example.com']);
        $expectedJsonArr = [
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => 'barry@example.com',
                'roles' => ['ROLE_USER'],
                'fullName' => 'Barry Bar',
                'isVerified' => false,
                'isActive' => true,
                'birthday' => null,
                'avatarFileName' => null,
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'scheduledDeletionDate' => $user->getScheduledDeletionDate()->format('Y-m-d H:i:s'),
            ],
        ];
        self::assertEquals($expectedJsonArr, $jsonResponseArr);
    }
    public function testForgotPasswordSystem(): void
    {
        $payload = [
            "email"=>"john@gmail.com"
        ];
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/mailer/forgot-password', $payload);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $jsonResponse = $client->getResponse()->getContent();
        $jsonResponseArr = json_decode($jsonResponse, true);

        $expectedJsonArr = [
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset email shortly'
        ];
        self::assertSame($expectedJsonArr,$jsonResponseArr);
    }
}
