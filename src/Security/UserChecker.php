<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Si le user est désactivé, on bloque la connexion
        if (!$user->isUserActif()) {
            throw new CustomUserMessageAccountStatusException('Votre compte a été désactivé. Veuillez contacter l’administrateur.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien de spécifique après auth pour le moment
    }
}
