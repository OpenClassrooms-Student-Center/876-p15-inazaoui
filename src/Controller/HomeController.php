<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * Liste des invités (utilisateurs actifs uniquement).
     */
    #[Route('/guests', name: 'guests')]
    public function guests(UserRepository $userRepository): Response
    {
        $guests = $userRepository->findAllGuests(); // déjà filtré userActif = true

        return $this->render('front/guests.html.twig', compact('guests'));
    }

    /**
     * Détail d’un invité actif.
     */
    #[Route('/guest/{id}', name: 'guest')]
    public function guest(
        int $id,
        UserRepository $userRepository,
    ): Response {
        $guest = $userRepository->find($id);

        // Sécurité : invité inexistant ou inactif
        if (!$guest || !$guest->isUserActif()) {
            throw $this->createNotFoundException();
        }

        return $this->render('front/guest.html.twig', compact('guest'));
    }

    /**
     * Portfolio / Albums
     * médias d’utilisateurs actifs uniquement.
     */
    #[Route('/portfolio/{id?}', name: 'portfolio')]
    public function portfolio(
        AlbumRepository $albumRepository,
        MediaRepository $mediaRepository,
        UserRepository $userRepository,
        ?int $id = null,
    ): Response {
        // Albums avec médias visibles uniquement
        $albums = $albumRepository->findAlbumsWithVisibleMedias();

        // Album sélectionné
        $album = $id ? $albumRepository->find($id) : null;

        // Si l’album n’existe pas
        if ($id && !$album) {
            throw $this->createNotFoundException();
        }

        // Administratrice (Ina, active)
        $admin = $userRepository->findAdmin();

        // Médias visibles selon le contexte
        if ($album) {
            $medias = $mediaRepository->findVisibleMedias(compact('album'));
        } else {
            $medias = $mediaRepository->findVisibleMedias([
                'user' => $admin,
            ]);
        }

        return $this->render('front/portfolio.html.twig', compact('albums', 'album', 'medias'));
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
