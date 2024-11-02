<?php

namespace App\Tests\Integration\Service;

use App\Dto\User\ChangePasswordDto;
use App\Dto\User\RegisterDto;
use App\Dto\User\ResetPasswordDto;
use App\Dto\User\UpdateDataDto;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Tests\Mock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserServiceTest extends KernelTestCase
{

    private UserService $userService;
    private UserRepository $userRepository;
    private TokenStorageInterface $tokenStorage;
    private int $userId = 1;
    private UserPasswordHasherInterface  $passwordHasher;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private Mock $mock;
    private ValidatorInterface $validator;
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->userRepository = $container->get(UserRepository::class);
        $this->tokenStorage = $container->get(TokenStorageInterface::class);
        $this->userService = $container->get(UserService::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->resetPasswordHelper = $container->get(ResetPasswordHelperInterface::class);
        $this->validator = $container->get(ValidatorInterface::class);

        // Instantiate Mock
        $userRepository = $container->get(UserRepository::class);
        $tokenStorage = $container->get(TokenStorageInterface::class);
        $this->mock = new Mock($userRepository, $tokenStorage);
    }

    public function testFetchUserSuccess(): void
    {

        $user = $this->mock->login();
        $result = $this->userService->fetch($this->userId);

        $this->assertSame([
            'id' => 1,
            'fullName' => 'John Doe',
            'birthday' => $user->getBirthday(),
            'avatarPath' => null,
            'email' => 'john@gmail.com',
            'isActive' => true,
            'createdAt' => $user->getCreatedAt(),
        ], $result);
    }

    public function testFetchUserNotFound(): void
    {
        $userId = 999; // ID that does not exist in a test database

        // Simulate logged-in user
        $this->mock->login();

        // Expecting NotFoundHttpException to be thrown
        $this->expectException(NotFoundHttpException::class);

        // Call the fetch method with an ID that does not exist
        $this->userService->fetch($userId);
    }

    public function testCreateUserSuccess(): void
    {
        // Prepare the RegisterDto
        $registerDto = new RegisterDto('Jane Doe','jane@example.com','Password#1');
        // Call the create method
        $this->userService->register($registerDto);

        // Assert that the user was created
        $createdUser = $this->userRepository->findOneBy(['email' => $registerDto->email]);
        $this->assertNotNull($createdUser);
        $this->assertEquals($registerDto->fullName, $createdUser->getFullName());
        $this->assertEquals($registerDto->email, $createdUser->getEmail());
        $this->assertFalse($createdUser->getIsVerified());
    }

    public function testRegisterDtoValidation(): void
    {
        // Prepare the RegisterDto with an existing email
        $registerDto = new RegisterDto('John Doe','john@gmail.com','Password#1','NotMatching');

        // Validate the DTO
        $violations = $this->validator->validate($registerDto);
        // Assert that there is one violation
        $this->assertCount(2, $violations);
        $this->assertEquals('confirmPassword', $violations[0]->getPropertyPath());
        $this->assertEquals('email', $violations[1]->getPropertyPath());
    }

    public function testUpdateSuccess(): void
    {
        $user = $this->mock->login();
        $updateDataDto = new UpdateDataDto('Gary Doe','2000-01-01');
        $this->userRepository->update($updateDataDto,$user);
        $updatedUser = $this->userRepository->find($user->getId());
        // Assert that the user's data has been updated
        $this->assertEquals('Gary Doe', $updatedUser->getFullName());
        $this->assertEquals('2000-01-01', $updatedUser->getBirthday()->format('Y-m-d'));
    }
    public function testUpdateJustBirthDay(): void
    {
        $user = $this->mock->login();
        $updateDataDto = new UpdateDataDto(null,'2000-01-01');
        $this->userRepository->update($updateDataDto,$user);
        $updatedUser = $this->userRepository->find($user->getId());
        // Assert that the user's data has been updated
        $this->assertEquals('John Doe', $updatedUser->getFullName());
        $this->assertEquals('2000-01-01', $updatedUser->getBirthday()->format('Y-m-d'));
    }

    public function testUpdateProfileImage(): void
    {
        $user = $this->mock->login();
        $form = $this->createMock(FormInterface::class);
        $slugger = $this->createMock(SluggerInterface::class);
        // Mock the SluggerInterface to return a safe filename

        $slugger->method('slug')->willReturn(new UnicodeString('safe-filename'));
        // Mock the form submission and validation
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $avatarFile = $this->createMock(UploadedFile::class);
        $avatarFile->method('getClientOriginalName')->willReturn('avatar.png');
        $avatarFile->method('guessExtension')->willReturn('png');
        // Create a mock File object to return from the move method
        $mockFile = $this->createMock(File::class);
        $avatarFile->method('move')->willReturn($mockFile);

        // Mock the form's getData to return the UploadedFile
        $form->method('get')->willReturnSelf();
        $form->method('getData')->willReturn($avatarFile);

        // Create a mock request
        $request = new Request();

        // Call the method and assert the result
        $result = $this->userService->updateProfileImage($request, $form, $user, $user->getId());
        $this->assertTrue($result['success']);
    }
    public function testUpdateProfileImageInvalidForm(): void
    {
        $user = $this->mock->login();
        $form = $this->createMock(FormInterface::class);
        $request = new Request();

        // Simulate form not being submitted
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(false);

        $result = $this->userService->updateProfileImage($request, $form, $user, $user->getId());
        $this->assertFalse($result['success']);
    }
    public function testChangePasswordSuccess(): void
    {
        $user = $this->mock->login();
        $changePasswordDto = new ChangePasswordDto('Password#1','NewPassword#1','NewPassword#1');
        $this->userService->changePassword($changePasswordDto,1);
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, 'NewPassword#1'));
    }
    public function testChangePasswordIncorrectOldPassword(): void
    {
        $user = $this->mock->login();
        $changePasswordDto = new ChangePasswordDto('IncorrectPass','NewPassword#1','NewPassword#1');
        $this->expectException(BadRequestException::class);
        $this->userService->changePassword($changePasswordDto,1);
    }
    public function testResetPasswordSuccess(): void
    {
        // Generate a real reset token
        $resetToken = $this->resetPasswordHelper->generateResetToken($this->userRepository->find(1));
        $resetPasswordDto = new ResetPasswordDto($resetToken->getToken(),'NewPassword#1','NewPassword#1');
        $this->userService->resetPassword($resetPasswordDto);
        $this->assertTrue($this->passwordHasher->isPasswordValid($this->userRepository->find(1), 'NewPassword#1'));
    }
    public function testResetPasswordBadToken(): void
    {
        $resetToken = 'BadToken';
        $resetPasswordDto = new ResetPasswordDto($resetToken,'NewPassword#1','NewPassword#1');
        $this->expectException(InvalidResetPasswordTokenException::class);
        $this->userService->resetPassword($resetPasswordDto);
        $this->assertTrue($this->passwordHasher->isPasswordValid($this->userRepository->find(1), 'NewPassword#1'));
    }
    public function testDeactivateAndActivate(): void
    {
        $user = $this->mock->login();
        $this->userService->deactivate('Password#1',1);
        $this->assertFalse($user->getIsActive());
        $this->userService->activate(1);
        // Reload user
        $user = $this->userRepository->find(1);
        $this->assertTrue($user->getIsActive());
    }
    public function testDeactivateWrongPassword(): void
    {
        $this->mock->login();
        $this->expectException(BadRequestException::class);
        $this->userService->deactivate('WrongPassword',1);
    }
}
