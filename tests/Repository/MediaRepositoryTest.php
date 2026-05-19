<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests d'intégration du dépôt MediaRepository.
 *
 * Vérifie les requêtes personnalisées sur les médias en base de données de test.
 */
class MediaRepositoryTest extends KernelTestCase
{
    /**
     * Vérifie que findByUserOrderedById retourne uniquement les médias appartenant à l'utilisateur donné.
     */
    public function testFindByUserOrderedById(): void
    {
        self::bootKernel();
        $repo = static::getContainer()->get(MediaRepository::class);
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);
        $medias = $repo->findByUserOrderedById($user);

        $this->assertIsArray($medias);
        foreach ($medias as $media) {
            $this->assertSame($user, $media->getUser());
        }
    }

    /**
     * Vérifie que findByUserOrderedById retourne un tableau vide pour un utilisateur sans médias.
     */
    public function testFindByUserReturnsEmptyForNewUser(): void
    {
        self::bootKernel();
        $repo = static::getContainer()->get(MediaRepository::class);
        $em = static::getContainer()->get('doctrine')->getManager();

        // Utilise un invité qui n'a pas de médias
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'bloque@example.com']);
        $medias = $repo->findByUserOrderedById($user);

        $this->assertIsArray($medias);
        $this->assertEmpty($medias);
    }
}
