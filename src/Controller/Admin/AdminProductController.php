<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/product')]
#[IsGranted('ROLE_ADMIN')]
class AdminProductController extends AbstractController
{
    #[Route('/', name: 'app_admin_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository,CategoryRepository $categoryRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $categories= $categoryRepository->findAll();
        $search = $request->query->get('search');
        $category = $request->query->get('category');
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortDir = $request->query->get('dir', 'DESC');

        $criteria = [];
        if ($category) {
            $criteria['category'] = $category;
        }

        $products = $productRepository->findPaginated($criteria, $page, $limit, $search);
        $totalProducts = $productRepository->countByCriteria($criteria, $search);
        $totalPages = ceil($totalProducts / $limit);

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentSearch' => $search,
            'categories' => $categories,
            'currentCategory' => $category,
            'currentSort' => $sortBy,
            'currentDir' => $sortDir,
            'totalProducts' => $totalProducts,
        ]);
    }

    #[Route('/new', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = null;

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }

                $product->setImage($newFilename);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('app_admin_product_index');
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = null;

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );

                    // Supprimer l'ancienne image si elle existe
                    $oldImage = $product->getImage();
                    if ($oldImage) {
                        $oldImagePath = $this->getParameter('images_directory').'/'.$oldImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $product->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('app_admin_product_index');
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            // Supprimer l'image si elle existe
            $image = $product->getImage();
            if ($image) {
                $imagePath = $this->getParameter('images_directory').'/'.$image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès !');
        }

        return $this->redirectToRoute('app_admin_product_index');
    }

    #[Route('/{id}/toggle-active', name: 'app_admin_product_toggle_active', methods: ['POST'])]
    public function toggleActive(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$product->getId(), $request->request->get('_token'))) {
            $product->setIsActive(!$product->isIsActive());
            $product->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $status = $product->isIsActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', 'Produit ' . $status . ' avec succès !');
        }

        return $this->redirectToRoute('app_admin_product_index');
    }

    #[Route('/bulk-action', name: 'app_admin_product_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('bulk_action');
        $productIds = $request->request->all('products');

        if (empty($productIds)) {
            $this->addFlash('warning', 'Aucun produit sélectionné.');
            return $this->redirectToRoute('app_admin_product_index');
        }

        $products = $productRepository->findBy(['id' => $productIds]);

        switch ($action) {
            case 'activate':
                foreach ($products as $product) {
                    $product->setIsActive(true);
                    $product->setUpdatedAt(new \DateTimeImmutable());
                }
                $this->addFlash('success', count($products) . ' produits activés avec succès !');
                break;

            case 'deactivate':
                foreach ($products as $product) {
                    $product->setIsActive(false);
                    $product->setUpdatedAt(new \DateTimeImmutable());
                }
                $this->addFlash('success', count($products) . ' produits désactivés avec succès !');
                break;

            case 'delete':
                foreach ($products as $product) {
                    // Supprimer l'image si elle existe
                    $image = $product->getImage();
                    if ($image) {
                        $imagePath = $this->getParameter('images_directory').'/'.$image;
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
                    }

                    $entityManager->remove($product);
                }
                $this->addFlash('success', count($products) . ' produits supprimés avec succès !');
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('app_admin_product_index');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_product_index');
    }
}
