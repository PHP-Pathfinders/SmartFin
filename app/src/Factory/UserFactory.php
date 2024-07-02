<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'created_at' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-5 years', '2024-06-03')),
            'email' => self::faker()->unique()->email(),
            'full_name' => self::faker()->name(),
            'is_active' => true,
            'is_verified' => true,
            'plainPassword' => self::faker()->password(),
            'roles' => ['ROLE_USER'],
            'birthday' => self::faker()->dateTimeBetween('-60 years', '2010-12-31')
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user): void {
                if($user->getPlainPassword()){
                    $user->setPassword($this->passwordHasher->hashPassword($user,$user->getPlainPassword()));
                }
            });
    }
}
