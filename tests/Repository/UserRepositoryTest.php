<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests d'intégration du dépôt UserRepository.
 *
 * Vérifie les requêtes personnalisées sur les utilisateurs en base de données de test.
 */
class UserRepositoryTest extends KernelTestCase
{
    /**
     * Vérifie que findActiveGuests retourne uniquement des invités non bloqués et non administrateurs.
     */
    public function testFindActiveGuests(): void
    {
        self::bootKernel();
        $repo = static::getContainer()->get(UserRepository::class);

        $guests = $repo->findActiveGuests();

        $this->assertIsArray($guests);
        foreach ($guests as $guest) {
            $this->assertFalse($guest->isAdmin());
            $this->assertFalse($guest->isBlocked());
        }
    }

    /**
     * Vérifie que findAllGuests retourne au moins deux invités (actif et bloqué),
     * aucun n'étant administrateur.
     */
    public function testFindAllGuests(): void
    {
        self::bootKernel();
        $repo = static::getContainer()->get(UserRepository::class);

        $guests = $repo->findAllGuests();

        $this->assertIsArray($guests);
        $this->assertGreaterThanOrEqual(2, count($guests));
        foreach ($guests as $guest) {
            $this->assertFalse($guest->isAdmin());
        }
    }

    /**
     * Vérifie que upgradePassword met bien à jour le mot de passe haché en base de données.
     * Restaure l'ancien mot de passe après le test.
     */
    public function testUpgradePassword(): void
    {
        self::bootKernel();
        $repo = static::getContainer()->get(UserRepository::class);
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);
        $oldPassword = $user->getPassword();

        $repo->upgradePassword($user, 'newhashedpassword');

        $this->assertEquals('newhashedpassword', $user->getPassword());

        // Restaure l'ancien mot de passe
        $repo->upgradePassword($user, $oldPassword);
    }
}
