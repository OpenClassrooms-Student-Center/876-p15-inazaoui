<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Respecter un target_path si présent (ex: accès à une page protégée avant login)
        if ($request->hasSession()) {
            $session = $request->getSession();

            if ($session->has('_security.main.target_path')) {
                $targetPath = (string) $session->get('_security.main.target_path');
                $session->remove('_security.main.target_path');

                return new RedirectResponse($targetPath);
            }
        }

        // Redirection selon le rôle
        if (in_array('ROLE_ADMIN', $token->getRoleNames(), true)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        // ROLE_USER / invité -> espace médias
        return new RedirectResponse($this->urlGenerator->generate('admin_media_index'));
    }
}
