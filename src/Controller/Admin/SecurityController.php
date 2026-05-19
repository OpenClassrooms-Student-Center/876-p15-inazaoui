<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant l'authentification des utilisateurs.
 *
 * Gère les routes de connexion et de déconnexion.
 * La déconnexion est interceptée par le pare-feu Symfony avant d'atteindre la méthode.
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion et transmet les éventuelles erreurs d'authentification.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaires d'authentification Symfony
     * @return Response La réponse HTTP avec le formulaire de connexion
     */
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Point d'entrée de la déconnexion, intercepté par le pare-feu Symfony.
     * Cette méthode n'est jamais exécutée directement.
     *
     * @return never
     */
    #[Route('/logout', name: 'admin_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
