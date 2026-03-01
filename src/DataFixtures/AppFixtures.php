<?php

namespace App\DataFixtures;

use App\Factory\AlbumFactory;
use App\Factory\MediaFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // admin
        UserFactory::createOne([
            'name' => 'Ina Zaoui',
            'email' => 'ina@zaoui.com',
            'roles' => ['ROLE_ADMIN'],
        ]);

        // blocked guest
        $blockedGuest = UserFactory::createOne([
            'name' => 'Block guest',
            'email' => 'blockedGuest@test.com',
            'roles' => ['ROLE_GUEST'],
            'isActive' => false,
        ]);

        // active guest
        $activeGuest = UserFactory::createOne([
            'name' => 'Active guest',
            'email' => 'activeGuest@test.com',
            'roles' => ['ROLE_GUEST'],
            'isActive' => true,
        ]);

        // random active guests
        $randomActiveGuests = UserFactory::createMany(8, ([
            'roles' => ['ROLE_GUEST'],
            'isActive' => true,
        ]));

        $allGuests = array_merge([$activeGuest], [$blockedGuest], $randomActiveGuests);

        // album 1...5
        $albums = [];
        for ($i = 1; $i <= 5; $i++) {
            $albums[] = AlbumFactory::createOne(['name' => 'Album ' . $i]);
        };

        // Ensure deterministic medias for tests (no randomness)
        $album1 = $albums[0];

        MediaFactory::createOne([
            'user'  => $activeGuest,
            'album' => $album1,
        ]);

        MediaFactory::createOne([
            'user'  => $blockedGuest,
            'album' => $album1,
        ]);

        // each media has a user, but 50% times has an album
        MediaFactory::createMany(100, function () use ($allGuests, $albums) {

            return [
                'user' => $allGuests[array_rand($allGuests)],
                'album' => $albums[array_rand($albums)],
            ];
        });

        $manager->flush();
    }
}
