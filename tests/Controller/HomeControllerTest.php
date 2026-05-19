<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du contrôleur frontend principal.
 *
 * Vérifie l'accès aux pages publiques : accueil, liste des invités, profil d'un invité,
 * portfolio, page à propos, et le comportement de la connexion.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Vérifie que la page d'accueil est accessible et contient un élément body.
     */
    public function testHomePage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    /**
     * Vérifie que la page listant les invités est accessible publiquement.
     */
    public function testGuestsPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/guests');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que le profil d'un invité actif est accessible publiquement.
     */
    public function testGuestPageWithActiveGuest(): void
    {
        $client = static::createClient();

        $em = static::getContainer()->get('doctrine')->getManager();
        $guest = $em->getRepository(User::class)->findOneBy([
            'email' => 'actif@example.com',
            'blocked' => false
        ]);

        if (!$guest) {
            $this->markTestSkipped('Aucun invité actif disponible.');
        }

        $client->request('GET', '/guest/' . $guest->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que l'accès au profil d'un invité bloqué redirige vers la liste des invités.
     */
    public function testGuestPageWithBlockedGuestRedirects(): void
    {
        $client = static::createClient();

        $em = static::getContainer()->get('doctrine')->getManager();
        $guest = $em->getRepository(User::class)->findOneBy([
            'email' => 'bloque@example.com'
        ]);

        $client->request('GET', '/guest/' . $guest->getId());

        $this->assertResponseRedirects('/guests');
    }

    /**
     * Vérifie que la page portfolio est accessible publiquement.
     */
    public function testPortfolioPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la page "À propos" est accessible publiquement.
     */
    public function testAboutPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/about');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la page de connexion est accessible et contient un formulaire.
     */
    public function testLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Vérifie que la soumission du formulaire avec des identifiants valides déclenche une redirection.
     */
    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $client->submitForm('Connexion', [
            '_username' => 'ina@zaoui.com',
            '_password' => 'password',
        ]);

        $this->assertResponseRedirects();
    }

    /**
     * Vérifie qu'un utilisateur non authentifié est redirigé vers la connexion pour accéder à l'admin.
     */
    public function testAdminRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/media');

        $this->assertResponseRedirects('/login');
    }
}
