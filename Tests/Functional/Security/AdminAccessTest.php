<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Tests\Support\TestUserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAccessTest extends WebTestCase
{
    public function testUserCanAccessMediaAdmin(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // 🔑 Utilisateur ROLE_USER
        $user = new User();
        $user->setEmail('user_'.uniqid('', true).'@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test');

        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        // ROLE_USER → accès AUTORISÉ à /admin/media
        $client->request('GET', '/admin/media');

        $this->assertResponseIsSuccessful();
    }

    public function testUserIsDeniedForAdminArea(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // 🔑 Utilisateur ROLE_USER
        $user = new User();
        $user->setEmail('user_'.uniqid('', true).'@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test');

        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        // ROLE_USER → accès INTERDIT au reste de l’admin
        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminAccessIsGrantedForAdmin(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // 🔑 Admin (Ina)
        $admin = TestUserFactory::getOrCreateIna($em);

        $client->loginUser($admin);

        // ROLE_ADMIN → accès autorisé
        $client->request('GET', '/admin/media');

        self::assertResponseIsSuccessful();
    }
}
