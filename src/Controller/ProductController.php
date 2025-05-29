<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$product->isIsActive()) {
            throw $this->createNotFoundException('Ce produit n\'est pas disponible.');
        }

        $review = new Review();
        $reviewForm = $this->createForm(ReviewType::class, $review);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid() && $this->getUser()) {
            $review->setProduct($product);
            $review->setUser($this->getUser());

            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a été ajouté avec succès !');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        $relatedProducts = $entityManager->getRepository(Product::class)->findBy([
            'category' => $product->getCategory(),
            'isActive' => true
        ], ['createdAt' => 'DESC'], 4);

        // Remove current product from related products
        $relatedProducts = array_filter($relatedProducts, function($p) use ($product) {
            return $p->getId() !== $product->getId();
        });

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'reviewForm' => $reviewForm->createView(),
            'relatedProducts' => array_slice($relatedProducts, 0, 3),
        ]);
    }

    #[Route('/category/{slug}', name: 'app_product_category')]
    public function category(
        string $slug,
        ProductRepository $productRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 12;

        $products = $productRepository->findByCategory($slug, $page, $limit);
        $totalProducts = $productRepository->countByCategory($slug);
        $totalPages = ceil($totalProducts / $limit);

        return $this->render('product/category.html.twig', [
            'products' => $products,
            'categorySlug' => $slug,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/search', name: 'app_product_search')]
    public function search(
        Request $request,
        ProductRepository $productRepository
    ): Response {
        $query = $request->query->get('q', '');
        $page = $request->query->getInt('page', 1);
        $limit = 12;

        if (empty($query)) {
            return $this->redirectToRoute('app_home');
        }

        $products = $productRepository->search($query, $page, $limit);
        $totalProducts = $productRepository->countSearch($query);
        $totalPages = ceil($totalProducts / $limit);

        return $this->render('product/search.html.twig', [
            'products' => $products,
            'query' => $query,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }
}
