<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité User.
 *
 * Vérifie les accesseurs, mutateurs, la gestion des rôles, l'identifiant Symfony Security
 * et le comportement de la méthode eraseCredentials.
 */
class UserTest extends TestCase
{
    /**
     * Vérifie que tous les champs de l'utilisateur peuvent être définis et récupérés correctement.
     */
    public function testGettersAndSetters(): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');
        $user->setAdmin(false);
        $user->setBlocked(false);
        $user->setDescription('Une description');

        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('hashedpassword', $user->getPassword());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isBlocked());
        $this->assertEquals('Une description', $user->getDescription());
    }

    /**
     * Vérifie que l'identifiant Symfony Security correspond bien à l'adresse e-mail.
     */
    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    /**
     * Vérifie qu'un administrateur possède les rôles ROLE_ADMIN et ROLE_USER.
     */
    public function testGetRolesForAdmin(): void
    {
        $user = new User();
        $user->setAdmin(true);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    /**
     * Vérifie qu'un invité possède uniquement le rôle ROLE_USER.
     */
    public function testGetRolesForGuest(): void
    {
        $user = new User();
        $user->setAdmin(false);

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Vérifie que eraseCredentials ne supprime pas le mot de passe haché
     * (aucune donnée sensible temporaire à effacer dans cette entité).
     */
    public function testEraseCredentials(): void
    {
        $user = new User();
        $user->setPassword('hashedpassword');
        $user->eraseCredentials();

        $this->assertEquals('hashedpassword', $user->getPassword());
    }

    /**
     * Vérifie que le statut bloqué peut être défini à true et lu correctement.
     */
    public function testIsBlocked(): void
    {
        $user = new User();
        $user->setBlocked(true);

        $this->assertTrue($user->isBlocked());
    }
}