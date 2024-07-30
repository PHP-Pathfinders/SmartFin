<?php

namespace App\Tests\Integration\Service;

use App\Dto\User\RegisterDto;
use App\Dto\User\UpdateDataDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserServiceTest extends KernelTestCase
{

    private UserService $userService;
    private UserRepository $userRepository;
    private TokenStorageInterface $tokenStorage;
    private int $userId = 1;
    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->userRepository = $container->get(UserRepository::class);
        $this->tokenStorage = $container->get(TokenStorageInterface::class);
        $this->userService = $container->get(UserService::class);
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
//        TODO Finish off this test
        $user = $this->mockLogin();
        // Mock a file upload
        $tempFile = tmpfile();
        $metaData = stream_get_meta_data($tempFile);
        $tempFilePath = $metaData['uri'];
        fwrite($tempFile, 'dummy image content');
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'avatar.jpg',
            'image/jpeg',
            null,
            true
        );

        // Create a mock request
        $request = new Request([], [], [], [], ['avatar' => $uploadedFile]);

        // Mock form handling
        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($request);

        $this->form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->form->expects($this->once())
            ->method('get')
            ->with('avatar')
            ->willReturn($this->createConfiguredMock(FormInterface::class, ['getData' => $uploadedFile]));

        // Call the method to test
        $result = $this->userService->updateProfileImage($request, $this->form, $user, $user->getId());

        // Assert that the profile image was updated
        $this->assertTrue($result);

        // Clean up temporary file
        fclose($tempFile);
    }

}
