<?php

namespace App\DataFixtures;

use App\Factory\BudgetFactory;
use App\Factory\CategoryFactory;
use App\Factory\TransactionFactory;
use App\Factory\TransactionTemplateFactory;
use App\Factory\UserFactory;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        // Create user manually with specific details
        UserFactory::new([
            'email' => 'john@gmail.com',
            'plainPassword' => 'Password#1',
            'fullName' => 'John Doe',
            'birthday' => new DateTime('1990-01-01'),
            'avatarFileName' => null,
            'createdAt' => new DateTime('2020-04-18T20:41:39+00:00')
        ])->create();

        // Create another user manually with specific details
        UserFactory::new([
            'email' => 'jane@gmail.com',
            'plainPassword' => 'Password#1',
            'fullName' => 'Jane Doe',
//            'roles' => ['ROLE_ADMIN']
        ])->create();

//        UserFactory::createMany(3);

        // Default categories
        $defaultCategories = [
            'income' => [
                'Salary' => '#4CAF50',       // Green
                'Scholarship' => '#FFC107',  // Amber
                'Gift' => '#2196F3',         // Blue
                'Other' => '#9C27B0',        // Purple
            ],
            'expense' => [
                'Bills' => '#F44336',          // Red
                'Groceries' => '#FF5722',      // Deep Orange
                'Shopping' => '#795548',       // Brown
                'Transportation' => '#FFEB3B', // Yellow
                'Nights out' => '#607D8B',     // Blue Grey
                'Fun' => '#9E9E9E',            // Grey
                'Trips' => '#673AB7',          // Deep Purple
                'Other' => '#FF9800',          // Orange
            ],
        ];
        // Assign default categories

        foreach ($defaultCategories['income'] as $incomeCategoryName => $color) {
            CategoryFactory::new([
                'categoryName' => $incomeCategoryName,
                'type' => 'income',
                'color' => $color
            ])->create();
        }
        foreach ($defaultCategories['expense'] as $expenseCategoryName => $color) {
            CategoryFactory::new([
                'categoryName' => $expenseCategoryName,
                'type' => 'expense',
                'color' => $color
            ])->create();
        }

        BudgetFactory::createMany(40);

        TransactionFactory::createMany(50);

        TransactionTemplateFactory::createMany(40);

        $manager->flush();
    }
}
