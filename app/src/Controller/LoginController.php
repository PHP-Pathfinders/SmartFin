<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('/api')]
class LoginController extends AbstractController
{
    #[OA\Post(
        description: 'Used as login entry point to our site, given example should work as a test user account. To use this test user here you will have to try it out, get the token copy and paste it in the Authorize section at the top of this page.',
        summary: 'Login with existing account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'user@gmail.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Password#1')
                ],
                type: 'object'
            )
        ),
        tags: ['Entry Points'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful login',
                content: new OA\JsonContent(ref: '#/components/schemas/LoginSuccess')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access attempt detected',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedLogin')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request JSON body data given',
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        //In case of client didn't send json payload at all
        return $this->json([
            'success' => false,
            'message' => 'Json payload not found'
        ], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    #[OA\Post(
        description: 'This is supposed to be a logout but it is not really utilized',
        summary: 'Logout of account',
        tags: ['User'], responses: [
        new OA\Response(response: 200, description: 'Logged out', content: new OA\JsonContent(ref: '#/components/schemas/Logout')),
        new OA\Response(response: 401, description: 'Unauthorized access detected', content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')),])]
    public function logout(): JsonResponse
    {
        // This endpoint doesn't need to do anything server-side
        return $this->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
