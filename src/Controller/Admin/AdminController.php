<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(
        ProductRepository $productRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository
    ): Response {
        $stats = [
            'totalProducts' => $productRepository->count([]),
            'activeProducts' => $productRepository->count(['isActive' => true]),
            'totalUsers' => $userRepository->count([]),
            'totalOrders' => $orderRepository->count([]),
            'pendingOrders' => $orderRepository->count(['status' => 'pending']),
        ];

        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $lowStockProducts = $productRepository->findLowStock(5);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
