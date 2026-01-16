<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const REF_USER_ADMIN = 'user_admin';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /**
         * =========================
         * UTILISATEUR ADMIN OBLIGATOIRE
         * =========================.
         */
        $admin = new User();
        $admin->setEmail('ina@free.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Zaoui');
        $admin->setPrenom('Ina');
        $admin->setUserActif(true);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // Référence utilisée par AlbumFixtures
        $this->addReference(self::REF_USER_ADMIN, $admin);

        /*
         * =========================
         * UTILISATEURS STANDARD (ID 2 → 101)
         * =========================
         */
        for ($i = 2; $i <= 101; ++$i) {
            $user = new User();

            $prenom = $faker->firstName;
            $nom = $faker->lastName;

            $email = strtolower($prenom.'.'.$nom.$i.'@example.com');

            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setUserActif(true);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['app'];
    }
}
