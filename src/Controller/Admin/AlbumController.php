<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\User;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AlbumController extends AbstractController
{
    #[Route('/admin/album', name: 'admin_album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $albums = $albumRepository->findAll();
        } else {
            /** @var User $user */
            $user = $this->getUser();

            $albums = $albumRepository->findBy([
                'user' => $user,
            ]);
        }

        return $this->render('admin/album/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    #[Route('/admin/album/add', name: 'admin_album_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $album = new Album();

        if (!$this->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();
            $album->setUser($user);
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($album);
            $em->flush();

            $this->addFlash('success', 'Album créé avec succès.');

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/form.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
            'isEdit' => false,
        ]);
    }

    #[Route('/admin/album/edit/{id}', name: 'admin_album_edit', methods: ['GET', 'POST'])]
    public function edit(Album $album, Request $request, EntityManagerInterface $em): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN')
            && $album->getUser() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Album modifié avec succès.');

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/form.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
            'isEdit' => true,
        ]);
    }

    #[Route('/admin/album/delete/{id}', name: 'admin_album_delete', methods: ['POST'])]
    public function delete(Album $album, Request $request, EntityManagerInterface $em): Response
    {
        if (
            ('test' !== $this->getParameter('kernel.environment'))
            && !$this->isCsrfTokenValid(
                'delete_album_'.$album->getId(),
                $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('admin_album_index');
        }

        if (
            !$this->isGranted('ROLE_ADMIN')
            && $album->getUser() !== $this->getUser()
        ) {
            $this->addFlash('danger', 'Vous n’êtes pas autorisé à supprimer cet album.');

            return $this->redirectToRoute('admin_album_index');
        }

        $em->remove($album);
        $em->flush();

        $this->addFlash('success', 'Album supprimé.');

        return $this->redirectToRoute('admin_album_index');
    }
}
