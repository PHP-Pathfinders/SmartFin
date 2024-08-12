<?php

namespace App\Tests\Integration\Service;

use App\Repository\BudgetRepository;
use App\Repository\UserRepository;
use App\Service\BudgetService;
use App\Tests\Mock;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BudgetServiceTest extends KernelTestCase
{
    private BudgetService $budgetService;
    private BudgetRepository $budgetRepository;
    private Mock $mock;
    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->budgetRepository = $container->get(BudgetRepository::class);
        $this->budgetService = $container->get(BudgetService::class);

        $budgetRepository = $container->get(BudgetRepository::class);

        // Instantiate Mock
        $userRepository = $container->get(UserRepository::class);
        $tokenStorage = $container->get(TokenStorageInterface::class);
        $this->mock = new Mock($userRepository, $tokenStorage);
    }

    public function testBudgetSearch(): void
    {
        $user = $this->mock->login();
        //TODO rest of the test
    }


}