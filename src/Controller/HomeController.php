<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 12;
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        $criteria = ['isActive' => true];
        if ($category) {
            $categoryEntity = $categoryRepository->findOneBy(['slug' => $category]);
            if ($categoryEntity) {
                $criteria['category'] = $categoryEntity;
            }
        }

        $products = $productRepository->findPaginated($criteria, $page, $limit, $search);
        $totalProducts = $productRepository->countByCriteria($criteria, $search);
        $totalPages = ceil($totalProducts / $limit);

        $categories = $categoryRepository->findAll();
        $featuredProducts = $productRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC'],
            6
        );

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentCategory' => $category,
            'currentSearch' => $search,
        ]);
    }
    #[Route('/products', name: 'app_products')]
    public function showProducts(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 12;
        $category = $request->query->get('category');
        $search = $request->query->get('search');

        $criteria = ['isActive' => true];
        if ($category) {
            $categoryEntity = $categoryRepository->findOneBy(['slug' => $category]);
            if ($categoryEntity) {
                $criteria['category'] = $categoryEntity;
            }
        }

        $products = $productRepository->findPaginated($criteria, $page, $limit, $search);
        $totalProducts = $productRepository->countByCriteria($criteria, $search);
        $totalPages = ceil($totalProducts / $limit);

        $categories = $categoryRepository->findAll();
        $featuredProducts = $productRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC'],
            6
        );

        return $this->render('home/products.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentCategory' => $category,
            'currentSearch' => $search,
        ]);
    }
}
