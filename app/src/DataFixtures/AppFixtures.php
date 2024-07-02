<?php

namespace App\DataFixtures;

use App\Factory\CategoryFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create admin manually with specific details
        UserFactory::new([
            'email' => 'admin@gmail.com',
            'plainPassword' => 'password',
            'full_name' => 'Kristijan Dulic',
            'roles' => ['ROLE_ADMIN']
        ])->create();

        // Create user manually with specific details
        UserFactory::new([
            'email' => 'user@gmail.com',
            'plainPassword' => 'password',
            'full_name' => 'Andrej Dvornic',
        ])->create();

        UserFactory::createMany(5);

        // Default categories
        $defaultCategories = [
            'income' => ['Salary', 'Scholarship', 'Gift', 'Other'],
            'expense' => ['Bills', 'Groceries', 'Shopping', 'Transportation', 'Nights out', 'Fun', 'Trips', 'Other'],
        ];

        // Iterate over each user and assign default categories
        foreach (UserFactory::repository()->findAll() as $user) {
            foreach ($defaultCategories['income'] as $incomeCategoryName) {
                CategoryFactory::new([
                    'category_name' => $incomeCategoryName,
                    'income_or_expense' => 'income',
                    'is_custom' => false,
                    'user' => $user,
                ])->create();
            }
            foreach ($defaultCategories['expense'] as $expenseCategoryName) {
                CategoryFactory::new([
                    'category_name' => $expenseCategoryName,
                    'income_or_expense' => 'expense',
                    'is_custom' => false,
                    'user' => $user,
                ])->create();
            }
        }

        $manager->flush();
    }
}
