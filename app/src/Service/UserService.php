<?php

namespace App\Service;

use App\Dto\User\ChangePasswordDto;
use App\Dto\User\ResetPasswordDto;
use App\Dto\User\UserRegisterDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;

readonly class UserService
{
    public function __construct(
        private UserRepository               $userRepository,
        private UserPasswordHasherInterface  $passwordHasher,
        private EmailVerifier                $emailVerifier,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private Security $security
    ){}

    /**
     * @throws InvalidSignatureException
     */
    public function verifyEmail(int $id,Request $request):void
    {
        $user = $this->userRepository->find($id);
        // Ensure the user exists
        if (null === $user) {
            throw new InvalidSignatureException('Invalid signature');
        }
        // validate an email confirmation link, sets User isVerified=true
        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }

    /**
     * @throws ResetPasswordExceptionInterface
     */
    public function resetPassword(ResetPasswordDto $resetPasswordDto):void
    {
        $token = $resetPasswordDto->token;
        // This will throw an error if the token is invalid
        /** @var  User $user */

        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $resetPasswordDto->password);
//        TODO add token versioning: if user change password log out from all devices
        $this->userRepository->resetPassword($hashedPassword,$user);
    }

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
                ->htmlTemplate('email/confirmation_email.html.twig')
        );
    }

    public function fetchProfile() :array
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        return [
            'fullName' => $user->getFullName(),
            'birthday' => $user->getBirthday(),
            'avatarPath' => $user->getAvatarPath(),
            'email' => $user->getEmail()
        ];
    }
    public function changePassword(ChangePasswordDto $changePasswordDto) :void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        $oldPassword = $changePasswordDto->oldPassword;
        $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $oldPassword);
        if(!$isPasswordValid){
            throw new BadRequestException('Incorrect old password');
        }

        $newPassword = $changePasswordDto->newPassword;
        $hashedPassword = $this->passwordHasher->hashPassword($user,$newPassword);

        $this->userRepository->changePassword($hashedPassword, $user);
    }

    public function deactivate(string $password) :void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if(!$user){
            throw new NotFoundHttpException('User not found');
        }
        $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $password);
        if(!$isPasswordValid){
            throw new BadRequestException('Incorrect password');
        }
        $this->userRepository->deactivate($user);
    }
}