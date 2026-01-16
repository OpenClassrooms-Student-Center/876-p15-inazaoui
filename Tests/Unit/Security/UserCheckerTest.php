<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    public function testActiveUserPassesPreAuthCheck(): void
    {
        $user = new User();
        $user->setUserActif(true);

        $checker = new UserChecker();

        // Ne doit lever aucune exception
        $checker->checkPreAuth($user);

        $this->assertTrue(true);
    }

    public function testInactiveUserThrowsException(): void
    {
        $user = new User();
        $user->setUserActif(false);

        $checker = new UserChecker();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage(
            'Votre compte a été désactivé. Veuillez contacter l’administrateur.'
        );

        $checker->checkPreAuth($user);
    }

    public function testNonUserInstanceIsIgnored(): void
    {
        $checker = new UserChecker();

        $mockUser = $this->createMock(UserInterface::class);

        // Ne doit rien faire, ni lever d’exception
        $checker->checkPreAuth($mockUser);

        $this->assertTrue(true);
    }
}
