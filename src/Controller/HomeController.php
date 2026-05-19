<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur principal gérant les pages publiques du site.
 *
 * Gère l'affichage de la page d'accueil, la liste des invités,
 * le profil d'un invité, le portfolio et la page à propos.
 */
class HomeController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     */
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Affiche la page d'accueil du site.
     *
     * @return Response La réponse HTTP avec la vue d'accueil
     */
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * Affiche la liste des invités non bloqués, triés par nom.
     *
     * @return Response La réponse HTTP avec la liste des invités actifs
     */
    #[Route('/guests', name: 'guests')]
    public function guests(): Response
    {
        $guests = $this->em->getRepository(User::class)->findActiveGuestsWithMediaCount();
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * Affiche le profil d'un invité spécifique.
     * Redirige vers la liste si l'invité est introuvable ou bloqué.
     *
     * @param int $id Identifiant de l'invité
     * @return Response La réponse HTTP avec le profil, ou une redirection
     */
    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id): Response
    {
        $guest = $this->em->getRepository(User::class)->find($id);

        if (!$guest || $guest->isBlocked()) {
            return $this->redirectToRoute('guests');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    /**
     * Affiche le portfolio avec les médias d'un album ou de l'administrateur.
     *
     * @param int|null $id Identifiant optionnel de l'album à afficher
     * @return Response La réponse HTTP avec le portfolio
     */
    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(?int $id = null): Response
    {
        $albums = $this->em->getRepository(Album::class)->findAll();
        $album = $id ? $this->em->getRepository(Album::class)->find($id) : null;
        $user = $this->em->getRepository(User::class)->findOneBy(['admin' => true]);
        $medias = $album
            ? $this->em->getRepository(Media::class)->findBy(['album' => $album])
            : $this->em->getRepository(Media::class)->findBy(['user' => $user]);
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * Affiche la page "À propos".
     *
     * @return Response La réponse HTTP avec la page à propos
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
