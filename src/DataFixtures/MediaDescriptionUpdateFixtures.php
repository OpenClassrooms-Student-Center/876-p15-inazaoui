<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class MediaDescriptionUpdateFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        // FixturesBundle fournit bien un EntityManager,
        // mais PHPStan exige une vérification explicite
        if (!$manager instanceof EntityManagerInterface) {
            throw new \RuntimeException('Expected EntityManagerInterface, got '.get_class($manager));
        }

        $faker = Factory::create('fr_FR');

        // Connexion DBAL (pas d’hydratation Doctrine, volontaire)
        $conn = $manager->getConnection();

        // Récupérer uniquement les IDs (optimisé)
        $ids = $conn->fetchFirstColumn('SELECT id FROM media');

        foreach ($ids as $id) {
            $description = $faker->text(200);

            // Met à jour uniquement si description est NULL ou vide
            $conn->executeStatement(
                <<<SQL
                UPDATE media
                SET description = :description
                WHERE id = :id
                  AND (description IS NULL OR description = '')
                SQL,
                compact('description', 'id')
            );
        }
    }

    public static function getGroups(): array
    {
        return ['media_description'];
    }
}
