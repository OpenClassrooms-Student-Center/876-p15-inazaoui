<?php

namespace App\Tests\Unit;

use App\Entity\User;
use App\Security\InvitationTokenGeneratorInterface;
use App\Service\GuestInvitationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GuestInvitationServiceTest extends TestCase
{
    public function testGuestInvitationPrepareUsersPersistsAndSendEmail(): void
    {

        $em = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $invitationTokenGenerator = $this->createMock(InvitationTokenGeneratorInterface::class);

        $guest = new User();
        $guest->setEmail('test@test.com');
        $guest->setName('Test Guest');

        $invitationTokenGenerator->expects($this->once())->method('generate')->willReturn('Password123@');

        $urlGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnCallback(
                function (string $route, array $params = []) {
                    if ($route === 'guest_set_password') {
                        return 'https://sitename.com/set-password/' . $params['invitationToken'];
                    }

                    if ($route === 'home') {
                        return 'https://sitename.com/';
                    }

                    throw new \RuntimeException('Unexpected route: ' . $route);
                }
            );

        $em->expects($this->once())->method('persist')->with($guest);
        $em->expects($this->once())->method('flush');

        // capture the email 
        $mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($email) {
                $this->assertInstanceOf(TemplatedEmail::class, $email);
                return true;
            }));

        $service = new GuestInvitationService($em, $mailer, $urlGenerator, $invitationTokenGenerator);

        $returnedToken = $service->invite($guest, expiredDays: 2);

        //Returned token
        $this->assertSame('Password123@', $returnedToken);

        // User prepared
        $this->assertSame(['ROLE_USER'], $guest->getRoles());
        $this->assertFalse($guest->isActive());
        $this->assertSame('Password123@', $guest->getInvitationToken());

        $this->assertNotNull($guest->getInvitationExpiredAt());
    }
}
