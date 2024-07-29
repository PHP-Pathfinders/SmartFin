<?php

namespace App\Tests\Application\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiTestCase
{
    public function testLogin(): void
    {
        // Define the payload for login
        $payload = [
            'username' => 'john@gmail.com',
            'password' => 'Password#1',
        ];

        // Make the POST request to login
        $response = static::createClient()->request('POST', '/api/users/login', [
            'json' => $payload,
        ]);

        // Assert the response status code
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert the response content
        $responseData = $response->toArray();
        // Token is different every time, so check pattern with regex
        self::assertMatchesRegularExpression('/^[A-Za-z0-9\-._~+\/]+=*$/', $responseData['token']);
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

        static::createClient()->request('POST', '/api/users/register',['json'=> $payload]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonContains([
            'success' => true,
            'message' => 'User registered successfully',
        ]);
    }
    public function testForgotPasswordSystem(): void
    {
        $payload = [
            "email"=>"john@gmail.com"
        ];
        static::createClient()->request('POST', '/api/mailer/forgot-password',['json'=> $payload]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonContains([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset email shortly',
        ]);
    }
}
