<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du contrôleur de sécurité (connexion/déconnexion).
 *
 * Vérifie l'accessibilité du formulaire de connexion, le comportement avec
 * des identifiants invalides et la déconnexion d'un utilisateur connecté.
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * Vérifie que la page de connexion est accessible et contient un formulaire.
     */
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Vérifie que la soumission du formulaire avec des identifiants invalides redirige vers /login.
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $client->submitForm('Connexion', [
            '_username' => 'wrong@example.com',
            '_password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie que la déconnexion d'un utilisateur connecté déclenche une redirection.
     */
    public function testLogout(): void
    {
        $client = static::createClient();

        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'ina@zaoui.com']);

        $client->loginUser($user);
        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }
}