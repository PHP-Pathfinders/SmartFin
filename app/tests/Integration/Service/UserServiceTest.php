<?php

namespace App\Tests\Integration\Service;

use App\Dto\User\ChangePasswordDto;
use App\Dto\User\RegisterDto;
use App\Dto\User\ResetPasswordDto;
use App\Dto\User\UpdateDataDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
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
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->userRepository = $container->get(UserRepository::class);
        $this->tokenStorage = $container->get(TokenStorageInterface::class);
        $this->userService = $container->get(UserService::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->resetPasswordHelper = $container->get(ResetPasswordHelperInterface::class);
    }

    private function mockLogin(): User
    {
        // Fetch user from the database
        $user = $this->userRepository->find($this->userId);
        // Simulate logged-in user
        $token = new UsernamePasswordToken($user, 'password', ['ROLE_USER']);
        $this->tokenStorage->setToken($token);
        return $user;
    }

    public function testFetchUserSuccess(): void
    {

        $user = $this->mockLogin();
        $result = $this->userService->fetch($this->userId);

        $this->assertSame([
            'userId' => 1,
            'fullName' => 'John Doe',
            'birthday' => $user->getBirthday(),
            'avatarFileName' => null,
            'email' => 'john@gmail.com',
            'isActive' => true,
            'createdAt' => $user->getCreatedAt(),
        ], $result);
    }

    public function testFetchUserNotFound(): void
    {
        $userId = 999; // ID that does not exist in a test database

        // Simulate logged-in user
        $this->mockLogin();

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
        $this->userService->create($registerDto);

        // Assert that the user was created
        $createdUser = $this->userRepository->findOneBy(['email' => $registerDto->email]);
        $this->assertNotNull($createdUser);
        $this->assertEquals($registerDto->fullName, $createdUser->getFullName());
        $this->assertEquals($registerDto->email, $createdUser->getEmail());
        $this->assertFalse($createdUser->getIsVerified());
    }

    public function testCreateUserWithExistingEmail(): void
    {
        // Prepare the RegisterDto with an existing email
        $registerDto = new RegisterDto('John Doe','john@gmail.com','Password#1');

        // Expect an exception or handle the logic that should occur with duplicate emails
        $this->expectException(ConflictHttpException::class);

        // Call the create method, expecting it to fail due to duplicate email
        $this->userService->create($registerDto);
    }

    public function testUpdateSuccess(): void
    {
        $user = $this->mockLogin();
        $updateDataDto = new UpdateDataDto('Gary Doe','2000-01-01');
        $this->userRepository->update($updateDataDto,$user);
        $updatedUser = $this->userRepository->find($user->getId());
        // Assert that the user's data has been updated
        $this->assertEquals('Gary Doe', $updatedUser->getFullName());
        $this->assertEquals('2000-01-01', $updatedUser->getBirthday()->format('Y-m-d'));
    }
    public function testUpdateJustBirthDay(): void
    {
        $user = $this->mockLogin();
        $updateDataDto = new UpdateDataDto(null,'2000-01-01');
        $this->userRepository->update($updateDataDto,$user);
        $updatedUser = $this->userRepository->find($user->getId());
        // Assert that the user's data has been updated
        $this->assertEquals('John Doe', $updatedUser->getFullName());
        $this->assertEquals('2000-01-01', $updatedUser->getBirthday()->format('Y-m-d'));
    }

    public function testUpdateProfileImage(): void
    {
        $user = $this->mockLogin();
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
        $this->assertTrue($result);
    }
    public function testUpdateProfileImageInvalidForm(): void
    {
        $user = $this->mockLogin();
        $form = $this->createMock(FormInterface::class);
        $request = new Request();

        // Simulate form not being submitted
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(false);

        $result = $this->userService->updateProfileImage($request, $form, $user, $user->getId());
        $this->assertFalse($result);
    }
    public function testChangePasswordSuccess(): void
    {
        $user = $this->mockLogin();
        $changePasswordDto = new ChangePasswordDto('Password#1','NewPassword#1','NewPassword#1');
        $this->userService->changePassword($changePasswordDto,1);
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, 'NewPassword#1'));
    }
    public function testChangePasswordIncorrectOldPassword(): void
    {
        $this->mockLogin();
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
        $user = $this->mockLogin();
        $this->userService->deactivate('Password#1',1);
        $this->assertFalse($user->getIsActive());
        $this->userService->activate(1);
        // Reload user
        $user = $this->userRepository->find(1);
        $this->assertTrue($user->getIsActive());
    }
    public function testDeactivateWrongPassword(): void
    {
        $user = $this->mockLogin();
        $this->expectException(BadRequestException::class);
        $this->userService->deactivate('WrongPassword',1);
    }
}
