<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures de l'application pour les tests et le développement.
 *
 * Crée les données initiales : l'administrateur (Ina Zaoui), un album de test,
 * un invité actif, un invité bloqué et des médias associés.
 */
class AppFixtures extends Fixture
{
    /**
     * @param UserPasswordHasherInterface $hasher Service de hachage de mot de passe
     */
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    /**
     * Charge les données de test en base de données.
     *
     * @param ObjectManager $manager Le gestionnaire d'objets Doctrine
     */
    public function load(ObjectManager $manager): void
    {
        // Création d'Ina (admin)
        $ina = new User();
        $ina->setName('Ina Zaoui');
        $ina->setEmail('ina@zaoui.com');
        $ina->setAdmin(true);
        $ina->setBlocked(false);
        $ina->setPassword($this->hasher->hashPassword($ina, 'password'));
        $manager->persist($ina);

        // Création d'un album
        $album = new Album();
        $album->setName('Album Test');
        $manager->persist($album);

        // Création d'un invité actif
        $guestActive = new User();
        $guestActive->setName('Invité Actif');
        $guestActive->setEmail('actif@example.com');
        $guestActive->setAdmin(false);
        $guestActive->setBlocked(false);
        $guestActive->setDescription('Photographe invité actif');
        $guestActive->setPassword($this->hasher->hashPassword($guestActive, 'password'));
        $manager->persist($guestActive);

        // Création d'un invité bloqué
        $guestBlocked = new User();
        $guestBlocked->setName('Invité Bloqué');
        $guestBlocked->setEmail('bloque@example.com');
        $guestBlocked->setAdmin(false);
        $guestBlocked->setBlocked(true);
        $guestBlocked->setDescription('Photographe invité bloqué');
        $guestBlocked->setPassword($this->hasher->hashPassword($guestBlocked, 'password'));
        $manager->persist($guestBlocked);

        // Création de médias pour Ina
        for ($i = 1; $i <= 3; $i++) {
            $media = new Media();
            $media->setTitle('Photo Ina ' . $i);
            $media->setPath('uploads/test' . $i . '.jpg');
            $media->setUser($ina);
            $media->setAlbum($album);
            $manager->persist($media);
        }

        // Création de médias pour l'invité actif
        for ($i = 1; $i <= 2; $i++) {
            $media = new Media();
            $media->setTitle('Photo Invité ' . $i);
            $media->setPath('uploads/guest' . $i . '.jpg');
            $media->setUser($guestActive);
            $media->setAlbum($album);
            $manager->persist($media);
        }

        $manager->flush();
    }
}