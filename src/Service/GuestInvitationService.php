<?php

namespace App\Service;

use App\Entity\User;
use App\Security\InvitationTokenGeneratorInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class GuestInvitationService
{

    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private InvitationTokenGeneratorInterface $tokenGenerator
    ) {}
    public function invite(User $guest, int $expiredDays = 2): string
    {

        // prepare the guest
        $guest->setRoles(['ROLE_USER']);
        $guest->setIsActive(false);

        // generate token + expiration 
        $invitationToken = $this->tokenGenerator->generate();
        $guest->setInvitationToken($invitationToken);

        $guest->setInvitationExpiredAt(new DateTimeImmutable('+' . $expiredDays . ' days'));

        $this->em->persist($guest);
        $this->em->flush();

        // prepare the link
        $invitationUrl = $this->urlGenerator->generate(
            'guest_set_password', // route name
            ['invitationToken' => $invitationToken], // parameter in the route
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $siteUrl = $this->urlGenerator->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // prepare email
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@zaoui.com', 'Ina Zaoui'))
            ->to(new Address($guest->getEmail(), $guest->getName()))
            ->subject('Invitation Ina Zaoui - Activer ton compte')
            ->htmlTemplate('/email/invitation.html.twig')
            ->context([
                'guestName' => $guest->getName(),
                'invitationUrl' => $invitationUrl,
                'siteUrl' => $siteUrl,
                'expiredAt' => $expiredDays * 24,
            ]);

        $this->mailer->send($email);

        return $invitationToken;
    }
}
