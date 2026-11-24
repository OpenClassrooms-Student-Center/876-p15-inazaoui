<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class MediaController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Affiche la liste des médias avec pagination.
     * Les cas d'erreur comme une valeur du paramètre page <= 0 ou > au nombre max de pages ou une valeur non entière
     * sont gérés en renvoyant la page 1 ou la dernière page.
     */
    #[Route('/admin/media', name: 'admin_media_index')]
    public function index(#[MapQueryParameter(filter: \FILTER_VALIDATE_INT, flags: \FILTER_NULL_ON_FAILURE)]
        ?int $page = null, ): Response
    {
        // Nombre de médias par page
        $limit = max(1, (int) $this->getParameter('app.media_per_page'));

        // Critères de recherche des médias
        $criteria = [];

        // Si l'utilisateur n'est pas admin, l'utilisateur est utilisé comme critère de recherche pour ne récupérer que ses médias
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        // Récupère le nombre total de médias correspondant aux critères (ceux de l'utilisateur ou tous si l'utilisateur est admin)
        $total = $this->em->getRepository(Media::class)->count($criteria);
        $totalPages = max(1, (int) ceil($total / $limit));

        // Borne la page entre 1 et $totalPages
        $page = min(max(1, $page ?? 1), $totalPages);

        // Récupère les médias correspondant aux critères et à la page actuelle
        $medias = $this->em->getRepository(Media::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            $limit * ($page - 1)
        );

        // Rendu de la vue avec les médias, le nombre total, la page actuelle et le nombre total de pages
        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $user = $this->getUser();
                $media->setUser($user instanceof User ? $user : null);
            }
            $media->setPath('uploads/'.md5(uniqid()).'.'.$media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());
            $this->em->persist($media);
            $this->em->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete')]
    public function delete(int $id): Response
    {
        $media = $this->em->getRepository(Media::class)->find($id);
        $this->em->remove($media);
        $this->em->flush();
        unlink($media->getPath());

        return $this->redirectToRoute('admin_media_index');
    }
}
