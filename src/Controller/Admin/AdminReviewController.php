<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/review')]
#[IsGranted('ROLE_ADMIN')]
class AdminReviewController extends AbstractController
{
    #[Route('/', name: 'app_admin_review_index', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository, ProductRepository $productRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $search = $request->query->get('search');
        $product = $request->query->get('product');
        $rating = $request->query->get('rating');
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortDir = $request->query->get('dir', 'DESC');

        $criteria = [];
        if ($product) {
            $criteria['product'] = $product;
        }
        if ($rating) {
            $criteria['rating'] = $rating;
        }

        $reviews = $reviewRepository->findPaginated($criteria, $page, $limit, $search, $sortBy, $sortDir);
        $totalReviews = $reviewRepository->countByCriteria($criteria, $search);
        $totalPages = ceil($totalReviews / $limit);

        $products = $productRepository->findAll();

        // Get statistics
        $stats = [
            'total' => $reviewRepository->count([]),
            'average' => $reviewRepository->getAverageRating(),
            'fiveStars' => $reviewRepository->count(['rating' => 5]),
            'fourStars' => $reviewRepository->count(['rating' => 4]),
            'threeStars' => $reviewRepository->count(['rating' => 3]),
            'twoStars' => $reviewRepository->count(['rating' => 2]),
            'oneStar' => $reviewRepository->count(['rating' => 1]),
        ];

        return $this->render('admin/review/index.html.twig', [
            'reviews' => $reviews,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentSearch' => $search,
            'currentProduct' => $product,
            'currentRating' => $rating,
            'currentSort' => $sortBy,
            'currentDir' => $sortDir,
            'totalReviews' => $totalReviews,
            'products' => $products,
            'stats' => $stats,
            'active' => 'reviews',
        ]);
    }

    #[Route('/new', name: 'app_admin_review_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        UserRepository $userRepository
    ): Response {
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Avis créé avec succès !');
            return $this->redirectToRoute('app_admin_review_index');
        }

        return $this->render('admin/review/new.html.twig', [
            'review' => $review,
            'form' => $form->createView(),
            'active' => 'reviews',
        ]);
    }

    #[Route('/{id}', name: 'app_admin_review_show', methods: ['GET'])]
    public function show(Review $review): Response
    {
        return $this->render('admin/review/show.html.twig', [
            'review' => $review,
            'active' => 'reviews',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_review_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Review $review,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Avis modifié avec succès !');
            return $this->redirectToRoute('app_admin_review_index');
        }

        return $this->render('admin/review/edit.html.twig', [
            'review' => $review,
            'form' => $form->createView(),
            'active' => 'reviews',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_review_delete', methods: ['POST'])]
    public function delete(Request $request, Review $review, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $review->getId(), $request->request->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
            $this->addFlash('success', 'Avis supprimé avec succès !');
        }

        return $this->redirectToRoute('app_admin_review_index');
    }

    #[Route('/bulk-action', name: 'app_admin_review_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, ReviewRepository $reviewRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('bulk_action');
        $reviewIds = $request->request->all('reviews');

        if (empty($reviewIds)) {
            $this->addFlash('warning', 'Aucun avis sélectionné.');
            return $this->redirectToRoute('app_admin_review_index');
        }

        $reviews = $reviewRepository->findBy(['id' => $reviewIds]);

        switch ($action) {
            case 'delete':
                foreach ($reviews as $review) {
                    $entityManager->remove($review);
                }
                $this->addFlash('success', count($reviews) . ' avis supprimés avec succès !');
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('app_admin_review_index');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_review_index');
    }
}
