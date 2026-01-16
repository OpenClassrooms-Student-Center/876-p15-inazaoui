<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AlbumFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Groupe "album".
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['album'];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::REF_USER_ADMIN, User::class);

        for ($i = 1; $i <= 50; ++$i) {
            $album = new Album();
            $album->setName('Album '.$i);
            $album->setUser($user);

            $manager->persist($album);
        }

        $manager->flush();
    }

    /**
     * Force l’exécution de UserFixtures AVANT.
     *
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
