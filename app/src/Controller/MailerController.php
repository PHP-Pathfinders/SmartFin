<?php

namespace App\Controller;

use App\Dto\User\RequestPasswordResetDto;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

use OpenApi\Attributes as OA;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/api/mailer')]
class MailerController extends AbstractController
{
    /**
     * @throws ResetPasswordExceptionInterface
     * @throws ExceptionInterface
     */
    #[Route('/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    #[OA\Post(
        description: 'Used for creating new reset password request with current logged user as its owner',
        summary: "Creates a request for resetting password",
        tags: ['Mailer'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful reset password request',
                content: new OA\JsonContent(ref: '#/components/schemas/MailerSuccess')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/MailerInputError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests',
                content: new OA\JsonContent(ref: '#/components/schemas/MailerTooMany')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            )
        ]
    )]
    public function resetPassword(
        #[MapRequestPayload] RequestPasswordResetDto $requestPasswordResetDto,
        MailerService                                $mailerService
    ): JsonResponse
    {
        $mailerService->forgotPassword($requestPasswordResetDto);

        return $this->json([
            'success' => true,
            'message' => 'If your email exists in our system, you will receive a password reset email shortly'
        ]);
    }
}
