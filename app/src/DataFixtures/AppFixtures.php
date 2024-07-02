<?php

namespace App\DataFixtures;

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
        $manager->flush();
    }
}
