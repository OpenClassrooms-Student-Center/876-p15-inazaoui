<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

/**
 * Tests unitaires du vérificateur de statut de compte utilisateur.
 *
 * Vérifie que checkPreAuth autorise les utilisateurs actifs, bloque les comptes
 * bloqués et que checkPostAuth ne lève aucune exception.
 */
class UserCheckerTest extends TestCase
{
    /**
     * Vérifie qu'aucune exception n'est levée pour un utilisateur actif (non bloqué).
     */
    public function testCheckPreAuthWithActiveUser(): void
    {
        $user = new User();
        $user->setBlocked(false);

        $checker = new UserChecker();
        $checker->checkPreAuth($user);

        $this->assertTrue(true);
    }

    /**
     * Vérifie qu'une exception est levée pour un utilisateur dont le compte est bloqué.
     */
    public function testCheckPreAuthWithBlockedUser(): void
    {
        $this->expectException(CustomUserMessageAccountStatusException::class);

        $user = new User();
        $user->setBlocked(true);

        $checker = new UserChecker();
        $checker->checkPreAuth($user);
    }

    /**
     * Vérifie que checkPostAuth ne lève aucune exception (vérification post-authentification vide).
     */
    public function testCheckPostAuth(): void
    {
        $user = new User();
        $checker = new UserChecker();
        $checker->checkPostAuth($user);

        $this->assertTrue(true);
    }
}