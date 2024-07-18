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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;

#[Route('/api/users')]
class UserController extends AbstractController
{
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
    public function logout(): JsonResponse
    {
        // This endpoint doesn't need to do anything server-side
        return $this->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] RegisterDto $registerDto,
        UserService                      $userService
    ): JsonResponse
    {
        $userService->create($registerDto);
        return $this->json([
            'success' => true,
            'message' => 'User registered successfully'
        ]);
    }

    /**
     * @throws InvalidSignatureException
     */
    #[Route('/verify-email', name: 'api_verify_email')]
    public function verifyEmail(
        #[MapQueryParameter] int $id,
        UserService $userService,
        Request $request
    ): JsonResponse
    {
        $userService->verifyEmail($id,$request);

        return $this->json([
            'success' => true,
            'message' => 'Your email address has been verified'
        ]);
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    #[Route('/reset-password', name: 'api_reset_password', methods: ['PATCH'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordDto $resetPasswordDto,
        UserService $userService
    ): JsonResponse
    {
        $userService->resetPassword($resetPasswordDto);
        return $this->json([
            'success' => true,
            'message' => 'Your password has been reset successfully'
        ]);
    }

    #[Route('', name: 'api_profile', methods: ['GET'] )]
    public function fetchProfile(
        UserService $userService
    ): JsonResponse
    {
        $profileData = $userService->fetchProfile();
        return $this->json([
            'success' => true,
            'data' => $profileData
        ]);
    }

    #[Route('/change-password', name: 'api_change_password', methods: ['PATCH'] )]
    public function changePassword(
        #[MapRequestPayload] ChangePasswordDto $changePasswordDto,
        UserService $userService
    ) :JsonResponse
    {
//        TODO logout this account from all other devices
        $userService->changePassword($changePasswordDto);
        return $this->json([
            'success' => true,
            'message' => 'Your password has been changed successfully'
        ]);
    }

    #[Route('', name: 'api_update_user', methods: ['PATCH'])]
    public function update(
        UserService $userService,
        #[MapRequestPayload] UpdateDataDto $updateDataDto
    ): JsonResponse
    {
        if(!$updateDataDto->fullName && !$updateDataDto->birthday){
            return $this->json([
                'success' => false,
                'message' => 'Nothing to update'
            ]);
        }

        $userService->update($updateDataDto);

        return $this->json([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    }

    #[Route('/image', name:'api_update_image', methods: ["POST"])]
    public function updateProfileImage(
        Request $request,
        UserService $userService,
        Security $security,
    ) :JsonResponse
    {
        /** @var User $user */
        $user = $security->getUser();
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $form = $this->createForm(UserType::class, $user);
        $isUploaded = $userService->updateProfileImage($request,$form,$user);

        if($isUploaded) {
            return $this->json([
                'success' => true,
                'message' => 'Profile image is updated successfully'
            ]);
        }
        return new JsonResponse([
            'success' => false,
            'message' => 'Form is not submitted or not valid. Image can be only jpg, jpeg or png'
        ], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/deactivate', name: 'api_deactivate', methods: ['PATCH'] )]
    public function deactivate(
        #[MapRequestPayload] DeactivateAccountDto $deactivateAccountDto,
        UserService $userService
    ) :JsonResponse
    {
        $password = $deactivateAccountDto->password;
        $userService->deactivate($password);
        return $this->json([
            'success' => true,
            'message' => 'Your account has been deactivated successfully'
        ]);
    }

    /**
     * This is a fake password reset page, and this route probably does not belong in this class
     * @return Response
     */
    #[Route('/reset-password-page', name: 'app_reset_password_page', methods: ['GET'])]
    public function resetPasswordPage():Response
    {
        return $this->render('reset-password/reset_password.html.twig');
    }
}
