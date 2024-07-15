<?php

namespace App\Service;

use App\Dto\User\ChangePasswordDto;
use App\Dto\User\ResetPasswordDto;
use App\Dto\User\RegisterDto;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
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
        private Security $security,
        private string $avatarDirectory,
        private SluggerInterface $slugger,
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

    public function create(RegisterDto $registerDto):void
    {
        $fullName = $registerDto->fullName;
        $email = $registerDto->email;
        $plainPassword = $registerDto->password;

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
            'avatarFileName' => $user->getAvatarFileName(),
            'email' => $user->getEmail()
        ];
    }

    public function updateProfileImage(
        Request $request,
        FormInterface $form,
        User $user
    ):bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $this->slugger->slug($originalFilename);
                $newFileName = $safeFileName.'-'.uniqid('', true).'.'.$avatarFile->guessExtension();

                // Check if user already has a profile image
                $oldAvatar = $user->getAvatarFileName();
                if ($oldAvatar) {
                    $oldAvatarPath = $this->avatarDirectory . '/' . $oldAvatar;
                    if (file_exists($oldAvatarPath)) {
                        // If profile image exists, delete it
                        unlink($oldAvatarPath);
                    }
                }

                // Move the new image to the directory where avatars are stored
                $avatarFile->move($this->avatarDirectory, $newFileName);
                $this->userRepository->updateProfileImage($newFileName,$user);
                return true;
            }
        }
        return false;
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