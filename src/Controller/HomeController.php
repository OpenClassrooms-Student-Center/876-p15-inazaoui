<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository; // Me permet d'utiliser le UserRepository pour récupérer les utilisateurs//
use Symfony\Component\HttpFoundation\Response; //Me permet d'utiliser la classe Response pour retourner une réponse HTTP//
use App\Repository\AlbumRepository; // Me permet d'utiliser le AlbumRepository pour récupérer les albums//
use App\Repository\MediaRepository; //Me permet d'utiliser le MediaRepository pour récupérer les médias//


class HomeController extends AbstractController
{
     #[Route("/", name:"home")]
    public function home()
    {
        return $this->render('front/home.html.twig');
    }


    #[Route("/guests", name:"guests")]
    public function guests(UserRepository $userRepository): Response
    {
        $guests = $userRepository->findBy(['admin' => false]);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }


     #[Route("/guest/{id}", name:"guest")]
     public function guest(int $id, UserRepository $userRepository): Response
    {
        $guest = $userRepository->find($id);

        if (!$guest) {
            throw $this->createNotFoundException('Invité introuvable.');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }


     #[Route("/portfolio/{id}", name:"portfolio")]
     public function portfolio(
         AlbumRepository $albumRepository,
         UserRepository $userRepository,
         MediaRepository $mediaRepository,
         ?int $id = null
     ): Response
    {
        $albums = $albumRepository->findAll();
        $album = $id ? $albumRepository->find($id) : null;
        $user = $userRepository->findOneBy([
            'admin' => true,
        ]);

        $medias = $album
            ? $mediaRepository->findByAlbum($album)
            : $mediaRepository->findByUser($user);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }


     #[Route("/about", name:"about")]
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}