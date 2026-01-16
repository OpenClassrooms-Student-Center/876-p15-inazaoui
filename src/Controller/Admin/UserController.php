<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users', name: 'admin_users_')]
class UserController extends AbstractController
{
    /* ============================================================
     * LISTE – PAGE
     * ============================================================ */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/users/index.html.twig');
    }

    /* ============================================================
     * LISTE – DATATABLES SERVER-SIDE
     * ============================================================ */
    #[Route('/data', name: 'data', methods: ['POST'])]
    public function data(
        Request $request,
        EntityManagerInterface $em,
        #[\SensitiveParameter] CsrfTokenManagerInterface $csrfTokenManager,
    ): JsonResponse {
        $draw = (int) $request->request->get('draw', 1);
        $start = (int) $request->request->get('start', 0);
        $length = (int) $request->request->get('length', 10);
        $search = $request->request->all('search')['value'] ?? '';

        /* Colonnes autorisées au tri */
        $columns = [
            0 => 'u.id',
            1 => 'u.email',
            2 => 'u.prenom',
            3 => 'u.nom',
            4 => 'u.userActif',
        ];

        $orderColumnIndex = (int) ($request->request->all('order')[0]['column'] ?? 0);
        $orderDir = $request->request->all('order')[0]['dir'] ?? 'asc';

        /* Query principale */
        $qb = $em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        if ('' !== $search) {
            $qb
                ->andWhere('u.email LIKE :search OR u.prenom LIKE :search OR u.nom LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if (isset($columns[$orderColumnIndex])) {
            $qb->orderBy($columns[$orderColumnIndex], $orderDir);
        }

        /* Nombre filtré */
        $recordsFiltered = (int) (clone $qb)
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        /* Pagination */
        $users = $qb
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->getQuery()
            ->getResult();

        /* Nombre total */
        $recordsTotal = (int) $em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->getQuery()
            ->getSingleScalarResult();

        /* Construction des lignes */
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'prenom' => $user->getPrenom(),
                'nom' => $user->getNom(),

                'statut' => $user->isUserActif()
                    ? '<span class="badge bg-success">Actif</span>'
                    : '<span class="badge bg-secondary">Inactif</span>',

                'actions' => sprintf(
                    '<a href="%s" class="text-primary me-3" title="Modifier">
                        <i class="fa-solid fa-pen"></i>
                     </a>

                     <a href="#"
                        class="text-danger"
                        title="Supprimer"
                        data-bs-toggle="modal"
                        data-bs-target="#confirmDeleteModal"
                        data-delete-url="%s"
                        data-delete-token="%s"
                        data-delete-message="Supprimer l’utilisateur « %s » ?">
                        <i class="fa-solid fa-trash"></i>
                     </a>',
                    $this->generateUrl('admin_users_edit', ['id' => $user->getId()]),
                    $this->generateUrl('admin_users_delete', ['id' => $user->getId()]),
                    $csrfTokenManager
                        ->getToken('delete_user_'.$user->getId())
                        ->getValue(),
                    htmlspecialchars($user->getFullName())
                ),
            ];
        }

        return $this->json(compact('draw', 'recordsTotal', 'recordsFiltered', 'data'));
    }

    /* ============================================================
     * CREATION
     * ============================================================ */
    #[Route('/new', name: 'new')]
    public function new(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // OBLIGATOIRE EN CRÉATION
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Valeurs de sécurité par défaut
            $user->setRoles(['ROLE_USER']);
            $user->setUserActif(true);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/form.html.twig', compact('form'));
    }

    /* ============================================================
     * EDITION
     * ============================================================ */
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(
        User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            // UNIQUEMENT SI MODIFIÉ
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $user->setUpdatedAt(new \DateTimeImmutable());

            $em->flush();

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/form.html.twig', compact('form'));
    }

    /* ============================================================
     * SUPPRESSION
     * ============================================================ */
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid(
            'delete_user_'.$user->getId(),
            (string) $request->request->get('_token')
        )) {
            $this->addFlash('danger', 'Token CSRF invalide.');

            return $this->redirectToRoute('admin_users_index');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Invité supprimé.');

        return $this->redirectToRoute('admin_users_index');
    }
}
