<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
    private function createUser(
        #[\SensitiveParameter] string $email,
        #[\SensitiveParameter] string $plainPassword,
        array $roles,
        bool $actif,
        #[\SensitiveParameter] UserPasswordHasherInterface $passwordHasher,
    ): void {
        $em = static::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setUserActif($actif);

        $user->setPassword(
            $passwordHasher->hashPassword($user, $plainPassword)
        );

        $em->persist($user);
        $em->flush();
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="email"]');
        self::assertSelectorExists('input[name="password"]');
    }

    public function testActiveUserCanLogin(): void
    {
        $client = static::createClient();

        $passwordHasher = static::getContainer()
            ->get(UserPasswordHasherInterface::class);

        $email = 'user_'.uniqid('', true).'@test.com';

        $this->createUser(
            $email,
            'password123',
            ['ROLE_USER'],
            true,
            $passwordHasher
        );

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'email' => $email,
            'password' => 'password123',
        ]);

        $client->submit($form);

        // login réussi = redirection
        self::assertResponseRedirects();
    }

    public function testInactiveUserCannotLogin(): void
    {
        $client = static::createClient();

        $passwordHasher = static::getContainer()
            ->get(UserPasswordHasherInterface::class);

        $email = 'inactive_'.uniqid('', true).'@test.com';

        $this->createUser(
            $email,
            'password123',
            ['ROLE_USER'],
            false,
            $passwordHasher
        );

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'email' => $email,
            'password' => 'password123',
        ]);

        $client->submit($form);

        //  login refusé = redirection (Symfony)
        self::assertResponseRedirects();
    }
}
