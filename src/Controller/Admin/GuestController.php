<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use App\Form\SetPasswordType;
use App\Repository\UserRepository;
use App\Service\GuestInvitationService;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class GuestController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/guests', name: 'admin_guest_index')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        $limit = 9;
        $offset = $limit * ($page - 1);

        $guests = $userRepository->findGuests($limit, $offset);

        $total = $userRepository->count($criteria);

        return $this->render(
            '/admin/guest/index.html.twig',
            [
                'guests' => $guests,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(
        Request $request,
        GuestInvitationService $guestInvitationService
    ) {
        $guest = new User();
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $guestInvitationService->invite($guest);
            $this->addFlash('success', 'Invité ajouté avec succès. L\'email d’activation est envoyé.');
            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', ['form' => $form->createView()]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/guest/disable/{id}', name: 'admin_guest_disable', methods: ['POST'])]
    public function disable(User $guest, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('guest_disable_' . $guest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $guest->setIsActive(false);

        $em->flush();
        return $this->redirectToRoute('admin_guest_index');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/guest/enable/{id}', name: 'admin_guest_enable', methods: ['POST'])]
    public function enable(User $guest, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('guest_enable_' . $guest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $guest->setIsActive(true);

        $em->flush();
        return $this->redirectToRoute('admin_guest_index');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/guest/delete/{id}', name: 'admin_guest_delete', methods: ['POST'])]
    public function delete(User $guest, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('guest_delete_' . $guest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $em->remove($guest);

        $em->flush();
        return $this->redirectToRoute('admin_guest_index');
    }

    #[Route('/set-password/{invitationToken}', 
        name: 'guest_set_password', 
        methods:['GET', 'POST'],
        requirements:['invitationToken' => '[A-Fa-f0-9]{64}']
    )]
    public function setGuestPassword(
        string $invitationToken,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $guestRepo,
        UserPasswordHasherInterface $guestPasswordHasher,
        ClockInterface $clock
    ) {
        
        $now = $clock->now();
        $guest = $guestRepo->findValidInvitation($invitationToken, $now);

        if (!$guest) {
            $this->addFlash('danger', 'Invitation invalide ou expirée.');
            return $this->redirectToRoute('home'); 
        }

        $form = $this->createForm(SetPasswordType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            $guest->setPassword($guestPasswordHasher->hashPassword($guest, $plainPassword));

            $guest->setIsActive(true);
            $guest->setInvitationExpiredAt(null);
            $guest->setInvitationToken(null);
            
            $em->flush();

            $this->addFlash('success', 'Activation réussie ! Tu peux te connecter sur le site.');
            
            return $this->redirectToRoute('home');
        }

        return $this->render('security/set-password.html.twig', [
            'setPasswordForm' => $form,
        ]);
    }
}
