<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du contrôleur d'administration des médias.
 *
 * Vérifie l'accès à la liste et au formulaire d'ajout pour l'administrateur
 * et les invités, ainsi que la redirection pour les utilisateurs non authentifiés.
 */
class AdminMediaControllerTest extends WebTestCase
{
    /**
     * Récupère l'utilisateur administrateur (Ina Zaoui) depuis la base de données de test.
     *
     * @return User L'utilisateur administrateur
     */
    private function getIna(): User
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        return $em->getRepository(User::class)->findOneBy(['email' => 'ina@zaoui.com']);
    }

    /**
     * Récupère un invité actif depuis la base de données de test.
     *
     * @return User Un utilisateur invité non bloqué
     */
    private function getGuest(): User
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        return $em->getRepository(User::class)->findOneBy(['email' => 'actif@example.com']);
    }

    /**
     * Vérifie que la liste des médias est accessible en tant qu'administrateur.
     */
    public function testMediaIndexAsAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/media');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la liste des médias est accessible en tant qu'invité.
     */
    public function testMediaIndexAsGuest(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getGuest());
        $client->request('GET', '/admin/media');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la liste des médias redirige vers la connexion si l'utilisateur n'est pas authentifié.
     */
    public function testMediaIndexRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/media');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie que la page d'ajout d'un média est accessible en tant qu'administrateur.
     */
    public function testMediaAddPageAsAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/media/add');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la page d'ajout d'un média est accessible en tant qu'invité.
     */
    public function testMediaAddPageAsGuest(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getGuest());
        $client->request('GET', '/admin/media/add');

        $this->assertResponseIsSuccessful();
    }
}