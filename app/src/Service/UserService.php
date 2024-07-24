<?php

namespace App\Service;

use App\Dto\User\ChangePasswordDto;
use App\Dto\User\ResetPasswordDto;
use App\Dto\User\RegisterDto;
use App\Dto\User\UpdateDataDto;
use App\Entity\User;
use App\Form\UserType;
use App\Message\SendEmailVerification;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
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
        private Security                     $security,
        private string                       $avatarDirectory,
        private SluggerInterface             $slugger,
        private MessageBusInterface          $bus
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
        $this->userRepository->resetPassword($hashedPassword,$user);
    }

    /**
     * @throws ExceptionInterface
     */
    public function create(RegisterDto $registerDto):void
    {
        $fullName = $registerDto->fullName;
        $email = $registerDto->email;
        $plainPassword = $registerDto->password;

//        Make new instance of user and hash password
        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($user,$plainPassword);
        $this->userRepository->create($fullName,$email,$hashedPassword,$user);

        $this->bus->dispatch(new SendEmailVerification($email));
    }

    public function fetchUser(int $userId) :array
    {
//        TODO RETURN ID OF USER ENTITY
        $this->checkUser($userId);
        /** @var User $user */
        // Search by user id
        $user = $this->userRepository->fetchUser($userId);
        return [
            'fullName' => $user->getFullName(),
            'birthday' => $user->getBirthday(),
            'avatarFileName' => $user->getAvatarFileName(),
            'email' => $user->getEmail()
        ];
    }

    public function update(UpdateDataDto $updateDataDto, int $userId): void
    {
        $user = $this->checkUser($userId);
        $this->userRepository->update($updateDataDto,$user);
    }

    public function updateProfileImage(
        Request $request,
        FormInterface $form,
        User $user,
        int $userId
    ):bool
    {
        $this->checkUser($userId);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $this->slugger->slug($originalFilename);
                $newFileName = $safeFileName.'-'.uniqid('', true).'.'.$avatarFile->guessExtension();

                // Delete if profile image already exists
                $this->deleteAvatarIfExists($user);
                // Move the new image to the directory where avatars are stored
                $avatarFile->move($this->avatarDirectory, $newFileName);
                $this->userRepository->updateProfileImage($newFileName,$user);
                return true;
            }
            // If avatarFile is null, delete profile image and set avatarFileName to null
            $this->deleteAvatarIfExists($user);
            $this->userRepository->updateProfileImage(null,$user);
            return true;

        }
        return false;
    }

    private function deleteAvatarIfExists(User $user):void
    {
        $oldAvatar = $user->getAvatarFileName();
        if ($oldAvatar) {
            $oldAvatarPath = $this->avatarDirectory . '/' . $oldAvatar;
            if (file_exists($oldAvatarPath)) {
                // If profile image exists, delete it
                unlink($oldAvatarPath);
            }
        }
    }
    public function changePassword(ChangePasswordDto $changePasswordDto, int $userId) :void
    {
        $user = $this->checkUser($userId);
        $oldPassword = $changePasswordDto->oldPassword;
        $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $oldPassword);
        if(!$isPasswordValid){
            throw new BadRequestException('Incorrect old password');
        }

        $newPassword = $changePasswordDto->newPassword;
        $hashedPassword = $this->passwordHasher->hashPassword($user,$newPassword);

        $this->userRepository->changePassword($hashedPassword, $user);
    }

    public function deactivate(string $password, int $userId) :void
    {
        $user = $this->checkUser($userId);

        $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $password);
        if(!$isPasswordValid){
            throw new BadRequestException('Incorrect password');
        }
        $this->userRepository->deactivate($user);
    }

    private function checkUser(int $userId): User
    {
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        if(!$user || $user->getId() !== $userId){
            throw new NotFoundHttpException('User not found');
        }
        return $user;
    }
}