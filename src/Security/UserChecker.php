<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vérificateur de statut du compte utilisateur avant et après l'authentification.
 *
 * Empêche la connexion des utilisateurs dont le compte a été bloqué par l'administrateur.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Vérifie le statut du compte avant l'authentification.
     * Lève une exception si l'utilisateur est bloqué.
     *
     * @param UserInterface $user L'utilisateur tentant de se connecter
     * @throws CustomUserMessageAccountStatusException Si le compte est bloqué
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été bloqué. Veuillez contacter l\'administrateur.'
            );
        }
    }

    /**
     * Vérifie le statut du compte après l'authentification.
     * Aucune vérification supplémentaire requise.
     *
     * @param UserInterface $user L'utilisateur authentifié
     */
    public function checkPostAuth(UserInterface $user): void {}
}