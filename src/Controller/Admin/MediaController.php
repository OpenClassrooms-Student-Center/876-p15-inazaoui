<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MediaController extends AbstractController
{
    #[Route('/admin/media', name: 'admin_media_index', methods: ['GET'])]
    public function index(Request $request, MediaRepository $mediaRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $criteria = [];

        // Invité → uniquement ses médias
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $user;
        }

        $medias = $mediaRepository->findVisibleMedias($criteria, $limit, $offset);
        $total = $mediaRepository->countVisibleMedias($criteria);
        $totalPages = (int) ceil($total / $limit);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $media = new Media();

        $form = $this->createForm(MediaType::class, $media, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
            'is_edit' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if (!$uploadedFile) {
                $this->addFlash('danger', 'Aucun fichier envoyé.');

                return $this->redirectToRoute('admin_media_index');
            }

            // ROLE_USER → utilisateur forcé
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($user);
            } elseif (null === $media->getUser()) {
                // ADMIN sans user sélectionné → fallback sécurisé
                $media->setUser($user);
            }

            $em->persist($media);
            $em->flush();

            $extension = $uploadedFile->guessExtension() ?? 'jpg';
            $filename = sprintf('%04d.%s', $media->getId(), $extension);
            $targetPath = $this->getParameter('upload_directory').'/'.$filename;

            try {
                $this->compressImage($uploadedFile->getPathname(), $targetPath);
                $media->setPath('uploads/'.$filename);
                $em->flush();

                $this->addFlash('success', 'Image ajoutée avec succès.');
            } catch (\Throwable $e) {
                $this->addFlash('danger', 'Erreur image : '.$e->getMessage());
            }

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/form.html.twig', [
            'form' => $form->createView(),
            'media' => $media,
            'isEdit' => false,
        ]);
    }

    #[Route('/admin/media/edit/{id}', name: 'admin_media_edit', methods: ['GET', 'POST'])]
    public function edit(Media $media, Request $request, EntityManagerInterface $em): Response
    {
        // Un invité ne peut modifier que ses médias
        if (
            !$this->isGranted('ROLE_ADMIN')
            && $media->getUser() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MediaType::class, $media, [
            'is_admin' => $this->isGranted('ROLE_ADMIN'),
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if ($uploadedFile) {
                if ($media->getPath()) {
                    $oldPath = $this->getParameter('kernel.project_dir').'/public/'.$media->getPath();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $extension = $uploadedFile->guessExtension() ?? 'jpg';
                $filename = sprintf('%04d.%s', $media->getId(), $extension);
                $target = $this->getParameter('upload_directory').'/'.$filename;

                $this->compressImage($uploadedFile->getPathname(), $target);
                $media->setPath('uploads/'.$filename);
            }

            $em->flush();
            $this->addFlash('success', 'Média modifié avec succès.');

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/form.html.twig', [
            'form' => $form->createView(),
            'media' => $media,
            'isEdit' => true,
        ]);
    }

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete', methods: ['POST'])]
    public function delete(Media $media, Request $request, EntityManagerInterface $em): Response
    {
        if (
            ('test' !== $this->getParameter('kernel.environment'))
            && !$this->isCsrfTokenValid(
                'delete_media_'.$media->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('admin_media_index');
        }

        if (
            !$this->isGranted('ROLE_ADMIN')
            && $media->getUser() !== $this->getUser()
        ) {
            $this->addFlash('danger', 'Suppression non autorisée.');

            return $this->redirectToRoute('admin_media_index');
        }

        if ($media->getPath()) {
            $absolutePath = $this->getParameter('kernel.project_dir').'/public/'.$media->getPath();
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }

        $em->remove($media);
        $em->flush();

        $this->addFlash('success', 'Image supprimée.');

        return $this->redirectToRoute('admin_media_index');
    }

    private function compressImage(string $source, string $destination, int $quality = 85): void
    {
        $info = getimagesize($source);
        if (false === $info) {
            throw new \RuntimeException('Fichier image invalide.');
        }

        $mime = $info['mime'];

        match ($mime) {
            'image/jpeg' => imagejpeg(
                imagecreatefromjpeg($source),
                $destination,
                $quality
            ),
            'image/png' => imagepng(
                imagecreatefrompng($source),
                $destination,
                6
            ),
            default => throw new \RuntimeException('Format image non supporté'),
        };
    }
}
