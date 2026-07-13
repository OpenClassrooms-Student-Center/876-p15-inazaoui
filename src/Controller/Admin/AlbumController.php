<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Media;
use App\Form\AlbumType;
use App\Form\MediaType;
use ContainerGjqfg9c\EntityManagerGhostEbeb667;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AlbumRepository; // Me permet  d'utiliser le AlbumRepository pour récupérer les albums//
use Doctrine\ORM\EntityManagerInterface; //Me permet d'utiliser l'EntityManagerInterface pour gérer les entités//
use Symfony\Component\HttpFoundation\Response; // Me permet d'utiliser la classe Response pour retourner une réponse HTTP//

class AlbumController extends AbstractController
{

     #[Route("/admin/album", name:"admin_album_index")]

    public function index(AlbumRepository $albumRepository): Response
    {
        $albums = $albumRepository->findAll();

        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }


     #[Route("/admin/album/add", name:"admin_album_add")]

     public function add(
         Request $request,
         EntityManagerInterface $entityManager
     ): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($album);
            $entityManager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form->createView()]);
    }


     #[Route("/admin/album/update/{id}", name:"admin_album_update")]

     public function update(
         Request $request,
         AlbumRepository $albumRepository,
         EntityManagerInterface $entityManager,
         int $id
     ): Response
    {
        $album = $albumRepository->find($id);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }


     #[Route("/admin/album/delete/{id}", name:"admin_album_delete")]

    public function delete(
        AlbumRepository $albumRepository,
         EntityManagerInterface $entityManager,
        int $id
     ): Response
    {
        $album = $albumRepository->find($id);

        $entityManager->remove($album);
        $entityManager->flush();

        return $this->redirectToRoute('admin_album_index');
    }
}