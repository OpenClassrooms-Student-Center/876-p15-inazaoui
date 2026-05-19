<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels du contrôleur d'administration des invités.
 *
 * Vérifie la liste, l'ajout, le blocage/déblocage et la redirection pour
 * les utilisateurs non authentifiés.
 */
class AdminGuestControllerTest extends WebTestCase
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
     * Vérifie que la liste des invités est accessible en tant qu'administrateur.
     */
    public function testGuestIndexAsAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/guests');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la page d'ajout d'un invité est accessible en tant qu'administrateur.
     */
    public function testGuestAddPageAsAdmin(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/guests/add');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Vérifie que la liste des invités redirige vers la connexion si l'utilisateur n'est pas authentifié.
     */
    public function testGuestIndexRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/guests');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Vérifie que le basculement du blocage d'un invité redirige correctement.
     * Restaure le statut initial après le test pour ne pas polluer les autres tests.
     */
    public function testBlockGuest(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());

        $em = static::getContainer()->get('doctrine')->getManager();
        $guest = $em->getRepository(User::class)->findOneBy(['email' => 'actif@example.com']);
        $wasBlocked = $guest->isBlocked();

        $client->request('GET', '/admin/guests/block/' . $guest->getId());
        $this->assertResponseRedirects('/admin/guests');

        // Restaure le statut initial
        $em->refresh($guest);
        $guest->setBlocked($wasBlocked);
        $em->flush();
    }

    /**
     * Vérifie qu'un invité peut être créé via le formulaire avec des données valides.
     */
    public function testAddGuest(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getIna());
        $client->request('GET', '/admin/guests/add');

        $uniqueEmail = 'nouveau_' . uniqid() . '@example.com';

        $client->submitForm('Ajouter', [
            'guest[name]' => 'Nouvel Invité',
            'guest[email]' => $uniqueEmail,
            'guest[description]' => 'Description test',
            'guest[plainPassword]' => 'password123',
        ]);

        $this->assertResponseRedirects('/admin/guests');
    }
}
