<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class TestUserFactory
{
    public static function getOrCreateIna(EntityManagerInterface $em): User
    {
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'ina@free.fr']);
        if ($user instanceof User) {
            return $user;
        }

        $user = new User();
        $user->setEmail('ina@free.fr');
        $user->setRoles(['ROLE_ADMIN']);

        // Le password doit être non-null en DB, on met une valeur factice (pas besoin de hash ici)
        $user->setPassword('test');

        $em->persist($user);
        $em->flush();

        return $user;
    }
}
