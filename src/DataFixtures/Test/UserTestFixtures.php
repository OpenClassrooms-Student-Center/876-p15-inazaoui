<?php

declare(strict_types=1);

namespace App\DataFixtures\Test;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTestFixtures extends Fixture
{
    /**
     * Référence utilisée dans les tests.
     */
    public const INA_USER = 'user_test_admin';

    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();

        // EMAIL DIFFÉRENT DE L’ADMIN APPLICATIF
        $user->setEmail('ina.test@free.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setUserActif(true);

        $user->setPassword(
            $this->hasher->hashPassword($user, 'password')
        );

        $manager->persist($user);
        $manager->flush();

        // Référence utilisable dans les tests
        $this->addReference(self::INA_USER, $user);
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['test'];
    }
}
