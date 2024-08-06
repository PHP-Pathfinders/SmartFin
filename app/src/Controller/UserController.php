<?php

namespace App\Controller;

use App\Dto\User\ChangePasswordDto;
use App\Dto\User\DeactivateAccountDto;
use App\Dto\User\ResetPasswordDto;
use App\Dto\User\RegisterDto;
use App\Dto\User\UpdateDataDto;
use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Nelmio\ApiDocBundle\Annotation\Security as NSecurity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use OpenApi\Attributes as OA;


#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(private readonly UserService $userService)
    {}
    #[OA\Post(
        description: 'Register new account that will be used on this site',
        summary: 'Used as register entry point to our site',
        tags: ['Entry Points'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful account registration done',
                content: new OA\JsonContent(ref: '#/components/schemas/RegisterSuccess')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request JSON body data given',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/RegisterInputError')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterDto $registerDto): JsonResponse
    {
        $user = $this->userService->register($registerDto);
        return $this->json(
            [
                'success' => true,
                'data' => $user
            ],Response::HTTP_CREATED, context: [
            ObjectNormalizer::GROUPS => ['user']
        ]
        );
    }

    /**
     * @throws InvalidSignatureException
     */
    #[OA\Get(
        description: 'This endpoint is easily tested by going to http://localhost:8025 because all of the required parameters are dynamically generated and are part of the link',
        summary: 'Used for verifying email for your account',
        tags: ['User'],
        parameters: [new OA\Parameter(name: 'token', in: 'query'), new OA\Parameter(name: 'signature', in: 'query'), new OA\Parameter(name: 'expires', in: 'query')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful account email verification done',
                content: new OA\JsonContent(ref: '#/components/schemas/EmailVerifySuccess')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request JSON body data given',
                content: new OA\JsonContent(ref: '#/components/schemas/EmailInvalidSig')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/verify-email', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(
        #[MapQueryParameter] int $id,
        Request                  $request
    ): JsonResponse
    {
        $this->userService->verifyEmail($id, $request);

        return $this->json([
            'success' => true,
            'message' => 'Your email address has been verified'
        ]);
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    #[OA\Patch(
        description: 'Used for resetting password of your account',
        summary: 'Reset password for certain account',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful account registration done',
                content: new OA\JsonContent(ref: '#/components/schemas/ResetSuccess')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request JSON body data given',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResetInputError')
            ),
            new OA\Response(
                response: 403,
                description: 'Invalid token',
                content: new OA\JsonContent(ref: '#/components/schemas/ResetForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/reset-password', name: 'api_reset_password', methods: ['PATCH'])]
    public function resetPassword(#[MapRequestPayload] ResetPasswordDto $resetPasswordDto): JsonResponse
    {
        $user = $this->userService->resetPassword($resetPasswordDto);
        return $this->json(
            [
                'success' => true,
                'data' => $user
            ], context: [
            ObjectNormalizer::GROUPS => ['user']
        ]
        );
    }

    #[OA\Get(
        description: 'Provides all details about any registered user',
        summary: 'Gives data about single user',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/UserNotFound')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/{id<\d+>}', name: 'api_profile', methods: ['GET'])]
    public function fetch(int $id): JsonResponse
    {
        $profileData = $this->userService->fetch($id);
        return $this->json([
            'success' => true,
            'data' => $profileData
        ]);
    }

    #[OA\Patch(
        description: 'Provides a password change functionality for user\'s account',
        summary: 'Used for changing password for user account',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/UserUpdateSuccess')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/UserNotFound')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request sent',
                content: new OA\JsonContent(ref: '#/components/schemas/PassChangeError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/PassChangeInputError')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/{id<\d+>}/change-password', name: 'api_change_password', methods: ['PATCH'])]
    public function changePassword(
        int $id,
        #[MapRequestPayload] ChangePasswordDto $changePasswordDto
    ): JsonResponse
    {
        $user = $this->userService->changePassword($changePasswordDto, $id);
        return $this->json(
            [
                'success' => true,
                'data' => $user
            ], context: [
            ObjectNormalizer::GROUPS => ['user']
        ]
        );
    }

    #[OA\Patch(
        description: 'Enables to change details about single user',
        summary: 'Changes data about single user',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/UserUpdateSuccess')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/UserNotFound')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request JSON body data given',
                content: new OA\JsonContent(ref: '#/components/schemas/InvalidRequest')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/UserPatchError')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/{id<\d+>}', name: 'api_update_user', methods: ['PATCH'])]
    public function update(
        int                                $id,
        #[MapRequestPayload] UpdateDataDto $updateDataDto
    ): JsonResponse
    {
        if (!$updateDataDto->fullName && !$updateDataDto->birthday) {
            return $this->json([
                'success' => false,
                'message' => 'Nothing to update'
            ]);
        }

        $user = $this->userService->update($updateDataDto, $id);
        return $this->json(
            [
                'success' => true,
                'data' => $user
            ], context: [
            ObjectNormalizer::GROUPS => ['user']
        ]
        );
    }

    #[OA\Post(
        description: 'Gives user freedom to change his avatar picture with appropriate and valid one',
        summary: 'Used for changing profile avatar picture for user account',
        requestBody: new OA\RequestBody(
            description: 'Profile image file',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['avatar'],
                    properties: [
                        new OA\Property(
                            property: 'avatar',
                            description: 'Avatar image file',
                            type: 'string',
                            format: 'binary'
                        )
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/UserUpdateSuccess')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/UserNotFound')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request sent',
                content: new OA\JsonContent(ref: '#/components/schemas/ImageError')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/{id<\d+>}/image', name: 'api_update_image', methods: ["POST"])]
    public function updateProfileImage(
        int         $id,
        Request     $request,
        Security    $security,
    ): JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $form = $this->createForm(UserType::class, $user);
        $dataArr = $this->userService->updateProfileImage($request, $form, $user, $id);

        if ($dataArr['success']) {
            return $this->json(
                [
                    'success' => true,
                    'data' => $dataArr['user']
                ], context: [
                ObjectNormalizer::GROUPS => ['user']
            ]
            );
        }
        return new JsonResponse([
            'success' => false,
            'message' => 'Form is not submitted or not valid. Image can be only jpg, jpeg or png'
        ], Response::HTTP_BAD_REQUEST);
    }

    #[OA\Patch(
        description: 'Deactivates user account after which will his access to site be limited and also will have a time period of 7 days to re-activate it',
        summary: 'Used for deactivating the user account',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/DeactivateSuccess')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/UserNotFound')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request sent',
                content: new OA\JsonContent(ref: '#/components/schemas/DeactivateError')
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid input data given',
                content: new OA\JsonContent(ref: '#/components/schemas/DeactivateInputError')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/{id<\d+>}/deactivate', name: 'api_deactivate', methods: ['PATCH'])]
    public function deactivate(
        int                                       $id,
        #[MapRequestPayload] DeactivateAccountDto $deactivateAccountDto
    ): JsonResponse
    {
        $password = $deactivateAccountDto->password;
        $user = $this->userService->deactivate($password, $id);
        return $this->json(
            [
                'success' => true,
                'data' => $user
            ], context: [
            ObjectNormalizer::GROUPS => ['user']
        ]
        );
    }

    #[OA\Patch(
        description: 'Reactivates user account if he decides not to deactivate and delete it and clears scheduled deletion date',
        summary: 'Used for reactivating the user account',
        tags: ['User'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/ActivateSuccess')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/UserNotFound')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized access detected',
                content: new OA\JsonContent(ref: '#/components/schemas/Unauthorized')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request sent',
                content: new OA\JsonContent(ref: '#/components/schemas/DeactivateError')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden access',
                content: new OA\JsonContent(ref: '#/components/schemas/AccessForbidden')
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error(something went really bad)',
            )
        ]
    )]
    #[Route('/{id<\d+>}/activate', name:'api_users_activate', methods: ['PATCH'])]
    public function activate(int $id): JsonResponse
    {
        $user = $this->userService->activate($id);
        return $this->json(
            [
                'success' => true,
                'data' => $user
            ], context: [
            ObjectNormalizer::GROUPS => ['user']
        ]
        );
    }

    /**
     * This is a fake password reset page, and this route probably does not belong in this class
     * @return Response
     */
    #[OA\Tag(name: 'User')]
    #[NSecurity(name: 'Bearer')]
    #[Route('/reset-password-page', name: 'app_reset_password_page', methods: ['GET'])]
    public function resetPasswordPage(): Response
    {
        return $this->render('reset-password/reset_password.html.twig');
    }
}
