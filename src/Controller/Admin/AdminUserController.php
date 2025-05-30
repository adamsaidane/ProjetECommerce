<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/user')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $search = $request->query->get('search');
        $role = $request->query->get('role');
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortDir = $request->query->get('dir', 'DESC');

        $criteria = [];
        if ($role && $role !== 'all') {
            $criteria['roles'] = $role;
        }

        $users = $userRepository->findPaginated($criteria, $page, $limit, $search, $sortBy, $sortDir);
        $totalUsers = $userRepository->countByCriteria($criteria, $search);
        $totalPages = ceil($totalUsers / $limit);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentSearch' => $search,
            'currentRole' => $role,
            'currentSort' => $sortBy,
            'currentDir' => $sortDir,
            'totalUsers' => $totalUsers,
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'is_edit' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash new password if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            // Prevent admin from deleting themselves
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte !');
                return $this->redirectToRoute('app_admin_user_index');
            }

            // Check if user has orders
            if (!$user->getOrders()->isEmpty()) {
                $this->addFlash('error', 'Impossible de supprimer un utilisateur qui a des commandes !');
                return $this->redirectToRoute('app_admin_user_index');
            }

            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/{id}/toggle-role', name: 'app_admin_user_toggle_role', methods: ['POST'])]
    public function toggleRole(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->request->get('_token'))) {
            // Prevent admin from removing their own admin role
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas modifier vos propres rôles !');
                return $this->redirectToRoute('app_admin_user_index');
            }

            $roles = $user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                // Remove admin role
                $user->setRoles(['ROLE_USER']);
                $this->addFlash('success', 'Rôle administrateur retiré avec succès !');
            } else {
                // Add admin role
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
                $this->addFlash('success', 'Rôle administrateur accordé avec succès !');
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/bulk-action', name: 'app_admin_user_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('bulk_action');
        $userIds = $request->request->all('users');

        if (empty($userIds)) {
            $this->addFlash('warning', 'Aucun utilisateur sélectionné.');
            return $this->redirectToRoute('app_admin_user_index');
        }

        $users = $userRepository->findBy(['id' => $userIds]);
        $currentUser = $this->getUser();

        // Remove current user from bulk actions to prevent self-modification
        $users = array_filter($users, function($user) use ($currentUser) {
            return $user !== $currentUser;
        });

        if (empty($users)) {
            $this->addFlash('warning', 'Aucune action possible sur les utilisateurs sélectionnés.');
            return $this->redirectToRoute('app_admin_user_index');
        }

        switch ($action) {
            case 'grant_admin':
                foreach ($users as $user) {
                    if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
                    }
                }
                $this->addFlash('success', count($users) . ' utilisateurs promus administrateurs avec succès !');
                break;

            case 'revoke_admin':
                foreach ($users as $user) {
                    $user->setRoles(['ROLE_USER']);
                }
                $this->addFlash('success', 'Rôle administrateur retiré à ' . count($users) . ' utilisateurs avec succès !');
                break;

            case 'delete':
                $deletedCount = 0;
                foreach ($users as $user) {
                    // Only delete users without orders
                    if ($user->getOrders()->isEmpty()) {
                        $entityManager->remove($user);
                        $deletedCount++;
                    }
                }

                if ($deletedCount > 0) {
                    $this->addFlash('success', $deletedCount . ' utilisateurs supprimés avec succès !');
                }

                $skippedCount = count($users) - $deletedCount;
                if ($skippedCount > 0) {
                    $this->addFlash('warning', $skippedCount . ' utilisateurs non supprimés (ont des commandes).');
                }
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('app_admin_user_index');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_user_index');
    }
}
