<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur d'administration pour la gestion des médias.
 *
 * Accessible aux administrateurs et aux invités connectés.
 * Les invités ne voient et ne gèrent que leurs propres médias.
 */
class MediaController extends AbstractController
{
    /**
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     */
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Affiche la liste paginée des médias (25 par page).
     * Les administrateurs voient tous les médias ; les invités ne voient que les leurs.
     *
     * @param Request $request La requête HTTP courante (paramètre `page` en query string)
     * @return Response La réponse HTTP avec la liste des médias
     */
    #[Route('/admin/media', name: 'admin_media_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->em->getRepository(Media::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
        $total = $this->em->getRepository(Media::class)->count([]);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * Affiche et traite le formulaire d'ajout d'un média.
     * Génère un nom de fichier unique et déplace l'image dans le dossier uploads/.
     *
     * @param Request $request La requête HTTP courante
     * @return Response La réponse HTTP avec le formulaire ou une redirection
     */
    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                /** @var \App\Entity\User $user */
                $user = $this->getUser();
                $media->setUser($user);
            }

            $file = $media->getFile();
            $webpPath = 'uploads/' . md5(uniqid()) . '.webp';
            $fullPath = $this->getParameter('kernel.project_dir') . '/public/' . $webpPath;

            // Convertit en WebP selon le type
            $tmpPath = $file->getPathname();
            $mimeType = $file->getMimeType();

            $image = match ($mimeType) {
                'image/jpeg' => imagecreatefromjpeg($tmpPath),
                'image/png' => imagecreatefrompng($tmpPath),
                'image/gif' => imagecreatefromgif($tmpPath),
                'image/webp' => imagecreatefromwebp($tmpPath),
                default => null
            };

            if ($image) {
                imagewebp($image, $fullPath, 80);
                $media->setPath($webpPath);
            } else {
                // Fallback sans conversion
                $media->setPath('uploads/' . md5(uniqid()) . '.' . $file->guessExtension());
                $file->move('uploads/', $media->getPath());
            }

            $this->em->persist($media);
            $this->em->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Supprime un média ainsi que son fichier physique.
     * Lève une exception si le média est introuvable ou si l'utilisateur n'en est pas propriétaire.
     *
     * @param int $id Identifiant du média à supprimer
     * @return Response La redirection vers la liste des médias
     */
    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete')]
    public function delete(int $id): Response
    {
        $media = $this->em->getRepository(Media::class)->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Média introuvable.');
        }

        if (!$this->isGranted('ROLE_ADMIN') && $media->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce média.');
        }

        $this->em->remove($media);
        $this->em->flush();
        unlink($media->getPath());

        return $this->redirectToRoute('admin_media_index');
    }
}
