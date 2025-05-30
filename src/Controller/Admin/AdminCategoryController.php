<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/category')]
#[IsGranted('ROLE_ADMIN')]
class AdminCategoryController extends AbstractController
{
    #[Route('/', name: 'app_admin_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $search = $request->query->get('search');
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortDir = $request->query->get('dir', 'DESC');

        $categories = $categoryRepository->findPaginated($page, $limit, $search, $sortBy, $sortDir);
        $totalCategories = $categoryRepository->countBySearch($search);
        $totalPages = ceil($totalCategories / $limit);

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentSearch' => $search,
            'currentSort' => $sortBy,
            'currentDir' => $sortDir,
            'totalCategories' => $totalCategories,
        ]);
    }

    #[Route('/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name if not provided
            if (!$category->getSlug()) {
                $slug = $slugger->slug(strtolower($category->getName()));
                $category->setSlug($slug);
            }

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name if not provided
            if (!$category->getSlug()) {
                $slug = $slugger->slug(strtolower($category->getName()));
                $category->setSlug($slug);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès !');
            return $this->redirectToRoute('app_admin_category_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            // Check if category has products
            if (!$category->getProducts()->isEmpty()) {
                $this->addFlash('error', 'Impossible de supprimer une catégorie qui contient des produits !');
                return $this->redirectToRoute('app_admin_category_index');
            }

            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('success', 'Catégorie supprimée avec succès !');
        }

        return $this->redirectToRoute('app_admin_category_index');
    }

    #[Route('/bulk-action', name: 'app_admin_category_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('bulk_action');
        $categoryIds = $request->request->all('categories');

        if (empty($categoryIds)) {
            $this->addFlash('warning', 'Aucune catégorie sélectionnée.');
            return $this->redirectToRoute('app_admin_category_index');
        }

        $categories = $categoryRepository->findBy(['id' => $categoryIds]);

        switch ($action) {
            case 'delete':
                $deletedCount = 0;
                foreach ($categories as $category) {
                    // Only delete categories without products
                    if ($category->getProducts()->isEmpty()) {
                        $entityManager->remove($category);
                        $deletedCount++;
                    }
                }

                if ($deletedCount > 0) {
                    $this->addFlash('success', $deletedCount . ' catégories supprimées avec succès !');
                }

                $skippedCount = count($categories) - $deletedCount;
                if ($skippedCount > 0) {
                    $this->addFlash('warning', $skippedCount . ' catégories non supprimées (contiennent des produits).');
                }
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('app_admin_category_index');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_category_index');
    }
}
