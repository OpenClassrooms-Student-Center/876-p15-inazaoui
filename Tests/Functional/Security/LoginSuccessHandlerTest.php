<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Security\LoginSuccessHandler;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandlerTest extends WebTestCase
{
    public function testHandlerImplementsInterface(): void
    {
        self::bootKernel();

        $urlGenerator = self::getContainer()->get(UrlGeneratorInterface::class);
        $handler = new LoginSuccessHandler($urlGenerator);

        self::assertInstanceOf(AuthenticationSuccessHandlerInterface::class, $handler);
    }

    public function testAdminIsRedirectedToAdminDashboard(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $urlGenerator = $container->get(UrlGeneratorInterface::class);

        $admin = TestUserFactory::getOrCreateIna($em);

        $request = Request::create('/login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $token = new UsernamePasswordToken(
            $admin,
            'main',
            $admin->getRoles()
        );

        $handler = new LoginSuccessHandler($urlGenerator);

        $response = $handler->onAuthenticationSuccess($request, $token);

        self::assertTrue($response->isRedirect());

        // Si ta route admin_dashboard génère "/admin" (comme ton test l’exigeait)
        self::assertSame('/admin', $response->headers->get('Location'));
    }

    public function testGuestIsRedirectedToMediaIndex(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $urlGenerator = $container->get(UrlGeneratorInterface::class);

        $guest = new User();
        $guest->setEmail('guest_'.uniqid('', true).'@test.fr');
        $guest->setPassword('test');
        $guest->setRoles(['ROLE_GUEST']);
        $guest->setUserActif(true);

        $em->persist($guest);
        $em->flush();

        $request = Request::create('/login');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $token = new UsernamePasswordToken(
            $guest,
            'main',
            $guest->getRoles()
        );

        $handler = new LoginSuccessHandler($urlGenerator);

        $response = $handler->onAuthenticationSuccess($request, $token);

        self::assertTrue($response->isRedirect());

        // Le handler renvoie admin_media_index, donc on doit vérifier l’URL générée
        self::assertSame('/admin/media', $response->headers->get('Location'));
    }

    public function testTargetPathIsRespected(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $urlGenerator = $container->get(UrlGeneratorInterface::class);

        $admin = TestUserFactory::getOrCreateIna($em);

        $request = Request::create('/login');
        $session = new Session(new MockArraySessionStorage());
        $session->set('_security.main.target_path', '/protected/page');
        $request->setSession($session);

        $token = new UsernamePasswordToken($admin, 'main', $admin->getRoles());

        $handler = new LoginSuccessHandler($urlGenerator);

        $response = $handler->onAuthenticationSuccess($request, $token);

        self::assertTrue($response->isRedirect());
        self::assertSame('/protected/page', $response->headers->get('Location'));
    }
}
