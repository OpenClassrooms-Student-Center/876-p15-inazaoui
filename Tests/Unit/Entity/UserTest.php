<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $user = new User();

        // User actif par défaut
        $this->assertTrue($user->isUserActif());

        // Roles par défaut
        $this->assertContains('ROLE_USER', $user->getRoles());

        // Dates initialisées
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testEmailGetterSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testPasswordGetterSetter(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');

        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testNomPrenom(): void
    {
        $user = new User();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');

        $this->assertSame('Dupont', $user->getNom());
        $this->assertSame('Jean', $user->getPrenom());
    }

    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUserActif(): void
    {
        $user = new User();
        $user->setUserActif(false);

        $this->assertFalse($user->isUserActif());
    }

    public function testDates(): void
    {
        $user = new User();
        $date = new \DateTimeImmutable('2025-01-01');

        $user->setCreatedAt($date);
        $user->setUpdatedAt($date);

        $this->assertSame($date, $user->getCreatedAt());
        $this->assertSame($date, $user->getUpdatedAt());
    }

    public function testEraseCredentialsDoesNotFail(): void
    {
        $user = new User();
        $user->eraseCredentials();

        $this->assertTrue(true);
    }

    public function testGetFullNameReturnsFormattedName(): void
    {
        $user = new User();

        $user->setPrenom('Ina');
        $user->setNom('Zaoui');

        $this->assertSame('Ina Zaoui', $user->getFullName());
    }
}
