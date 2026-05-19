<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur d'administration pour la gestion des invités.
 *
 * Réservé aux administrateurs. Permet de lister, ajouter,
 * bloquer/débloquer et supprimer des comptes invités.
 */
#[IsGranted('ROLE_ADMIN')]
class GuestController extends AbstractController
{
    /**
     * @param EntityManagerInterface      $em     Gestionnaire d'entités Doctrine
     * @param UserPasswordHasherInterface $hasher Service de hachage de mot de passe
     */
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {}

    /**
     * Affiche la liste paginée des invités (25 par page).
     *
     * @param Request $request La requête HTTP courante (paramètre `page` en query string)
     * @return Response La réponse HTTP avec la liste paginée des invités
     */
    #[Route('/admin/guests', name: 'admin_guest_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 25;

        $guests = $this->em->getRepository(User::class)->findBy(
            ['admin' => false],
            ['name' => 'ASC'],
            $limit,
            $limit * ($page - 1)
        );

        $total = $this->em->getRepository(User::class)->count(['admin' => false]);


        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * Affiche et traite le formulaire de création d'un invité.
     * Hache le mot de passe avant la persistance en base de données.
     *
     * @param Request $request La requête HTTP courante
     * @return Response La réponse HTTP avec le formulaire ou une redirection
     */
    #[Route('/admin/guests/add', name: 'admin_guest_add')]
    public function add(Request $request): Response
    {
        $guest = new User();
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $guest->setPassword(
                $this->hasher->hashPassword($guest, $form->get('plainPassword')->getData())
            );
            $guest->setAdmin(false);
            $this->em->persist($guest);
            $this->em->flush();

            $this->addFlash('success', 'Invité ajouté avec succès.');
            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Bascule le statut de blocage d'un invité (bloqué ↔ actif).
     *
     * @param int $id Identifiant de l'invité à bloquer ou débloquer
     * @return Response La redirection vers la liste des invités
     */
    #[Route('/admin/guests/block/{id}', name: 'admin_guest_block')]
    public function block(int $id): Response
    {
        $guest = $this->em->getRepository(User::class)->find($id);
        $guest->setBlocked(!$guest->isBlocked());
        $this->em->flush();

        $status = $guest->isBlocked() ? 'bloqué' : 'débloqué';
        $this->addFlash('success', "Invité {$status} avec succès.");

        return $this->redirectToRoute('admin_guest_index');
    }

    /**
     * Supprime un invité ainsi que tous ses médias associés (fichiers et entrées BDD).
     *
     * @param int $id Identifiant de l'invité à supprimer
     * @return Response La redirection vers la liste des invités
     */
    #[Route('/admin/guests/delete/{id}', name: 'admin_guest_delete')]
    public function delete(int $id): Response
    {
        $guest = $this->em->getRepository(User::class)->find($id);

        foreach ($guest->getMedias() as $media) {
            if (file_exists($media->getPath())) {
                unlink($media->getPath());
            }
            $this->em->remove($media);
        }

        $this->em->remove($guest);
        $this->em->flush();

        $this->addFlash('success', 'Invité supprimé avec succès.');
        return $this->redirectToRoute('admin_guest_index');
    }
}