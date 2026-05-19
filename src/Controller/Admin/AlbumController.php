<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur d'administration pour la gestion des albums.
 *
 * Permet de lister, créer, modifier et supprimer des albums
 * depuis l'interface d'administration.
 */
class AlbumController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     */
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Affiche la liste de tous les albums.
     *
     * @return Response La réponse HTTP avec la liste des albums
     */
    #[Route('/admin/album', name: 'admin_album_index')]
    public function index(): Response
    {
        $albums = $this->em->getRepository(Album::class)->findAll();
        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    /**
     * Affiche et traite le formulaire de création d'un album.
     *
     * @param Request $request La requête HTTP courante
     * @return Response La réponse HTTP avec le formulaire ou une redirection
     */
    #[Route('/admin/album/add', name: 'admin_album_add')]
    public function add(Request $request): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($album);
            $this->em->flush();
            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Affiche et traite le formulaire de modification d'un album existant.
     *
     * @param Request $request La requête HTTP courante
     * @param int     $id      Identifiant de l'album à modifier
     * @return Response La réponse HTTP avec le formulaire ou une redirection
     */
    #[Route('/admin/album/update/{id}', name: 'admin_album_update')]
    public function update(Request $request, int $id): Response
    {
        $album = $this->em->getRepository(Album::class)->find($id);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime un album et redirige vers la liste.
     *
     * @param int $id Identifiant de l'album à supprimer
     * @return Response La redirection vers la liste des albums
     */
    #[Route('/admin/album/delete/{id}', name: 'admin_album_delete')]
    public function delete(int $id): Response
    {
        $album = $this->em->getRepository(Album::class)->find($id);
        $this->em->remove($album);
        $this->em->flush();
        return $this->redirectToRoute('admin_album_index');
    }
}