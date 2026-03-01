<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    private static ?string $hashedPassword = null;
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        // Hash the password once and store it for reuse
        if (self::$hashedPassword === null) {
            self::$hashedPassword = $passwordHasher->hashPassword(new User(), 'password123@');
        }
    }

    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        $firstname = self::faker()->firstName();
        return [
            'name' => $firstname ,
            'email' => self::faker()->unique()->safeEmail(),
            'roles' => ['ROLE_GUEST'],
            'description' => self::faker()->optional()->sentence(),
            'isActive' => true,
            'invitationToken'=> null,
            'invitationExpiredAt' => null,
            'password' => self::$hashedPassword,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
