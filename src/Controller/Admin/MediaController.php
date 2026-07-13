<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MediaRepository; //Me permet de utiliser le MediaRepository pour récupérer les médias//
use Doctrine\ORM\EntityManagerInterface; // Me permet d'utiliser l'EntityManagerInterface pour gérer les entités//
use Symfony\Component\HttpFoundation\Response; //Me permet d'utiliser la classe Response pour retourner une réponse HTTP//

class MediaController extends AbstractController
{

     #[Route("/admin/media", name:"admin_media_index")]

     public function index(
         Request $request,
         MediaRepository $mediaRepository
     ): Response
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $mediaRepository->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
        $total = $mediaRepository->count([]);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }


     #[Route("/admin/media/add", name:"admin_media_add")]

     public function add(
         Request $request,
         EntityManagerInterface $entityManager
     ): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }
            $media->setPath('uploads/' . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());
            $entityManager->persist($media);
            $entityManager->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }


     #[Route("/admin/media/delete/{id}", name:"admin_media_delete")]

     public function delete(
         MediaRepository $mediaRepository,
         EntityManagerInterface $entityManager,
         int $id
     ): Response
    {
        $media = $mediaRepository->find($id);

        $entityManager->remove($media);
        $entityManager->flush();

        unlink($media->getPath());

        return $this->redirectToRoute('admin_media_index');
    }
}