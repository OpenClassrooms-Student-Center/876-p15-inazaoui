<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du contrôleur d'administration des albums.
 *
 * Vérifie l'accès aux pages de liste, de création et de modification,
 * ainsi que la redirection vers la connexion pour les utilisateurs non authentifiés.
 */
class AdminAlbumControllerTest extends WebTestCase
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
     * Vérifie que la liste des albums est accessible en tant qu'administrateur.
     */
    public function testAlbumIndexAsAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/album');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la page d'ajout d'un album est accessible en tant qu'administrateur.
     */
    public function testAlbumAddPageAsAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/album/add');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la liste des albums redirige vers la connexion si l'utilisateur n'est pas authentifié.
     */
    public function testAlbumIndexRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/album');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie que la page de modification d'un album est accessible en tant qu'administrateur.
     */
    public function testAlbumUpdatePage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());

        $em = static::getContainer()->get('doctrine')->getManager();
        $album = $em->getRepository(\App\Entity\Album::class)->findOneBy([]);

        $client->request('GET', '/admin/album/update/' . $album->getId());

        $this->assertResponseIsSuccessful();
    }
}
