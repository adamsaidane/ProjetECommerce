<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/order')]
#[IsGranted('ROLE_ADMIN')]
class AdminOrderController extends AbstractController
{
    #[Route('/', name: 'app_admin_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 20;
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortDir = $request->query->get('dir', 'DESC');

        $criteria = [];
        if ($status && $status !== 'all') {
            $criteria['status'] = $status;
        }

        $orders = $orderRepository->findPaginated($criteria, $page, $limit, $search, $sortBy, $sortDir);
        $totalOrders = $orderRepository->countByCriteria($criteria, $search);
        $totalPages = ceil($totalOrders / $limit);

        // Get statistics
        $stats = [
            'total' => $orderRepository->count([]),
            'pending' => $orderRepository->count(['status' => 'pending']),
            'confirmed' => $orderRepository->count(['status' => 'confirmed']),
            'shipped' => $orderRepository->count(['status' => 'shipped']),
            'delivered' => $orderRepository->count(['status' => 'delivered']),
            'cancelled' => $orderRepository->count(['status' => 'cancelled']),
        ];

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentSort' => $sortBy,
            'currentDir' => $sortDir,
            'totalOrders' => $totalOrders,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/update-status', name: 'app_admin_order_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('update_status'.$order->getId(), $request->request->get('_token'))) {
            $newStatus = $request->request->get('status');

            $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
            if (in_array($newStatus, $validStatuses)) {
                $order->setStatus($newStatus);
                $entityManager->flush();

                $this->addFlash('success', 'Statut de la commande mis à jour avec succès !');
            } else {
                $this->addFlash('error', 'Statut invalide !');
            }
        }

        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_admin_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            // Only allow deletion of cancelled orders
            if ($order->getStatus() === 'cancelled') {
                $entityManager->remove($order);
                $entityManager->flush();
                $this->addFlash('success', 'Commande supprimée avec succès !');
            } else {
                $this->addFlash('error', 'Seules les commandes annulées peuvent être supprimées !');
                return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
            }
        }

        return $this->redirectToRoute('app_admin_order_index');
    }

    #[Route('/bulk-action', name: 'app_admin_order_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('bulk_action');
        $orderIds = $request->request->all('orders');

        if (empty($orderIds)) {
            $this->addFlash('warning', 'Aucune commande sélectionnée.');
            return $this->redirectToRoute('app_admin_order_index');
        }

        $orders = $orderRepository->findBy(['id' => $orderIds]);

        switch ($action) {
            case 'confirm':
                foreach ($orders as $order) {
                    if ($order->getStatus() === 'pending') {
                        $order->setStatus('confirmed');
                    }
                }
                $this->addFlash('success', count($orders) . ' commandes confirmées avec succès !');
                break;

            case 'ship':
                foreach ($orders as $order) {
                    if (in_array($order->getStatus(), ['pending', 'confirmed'])) {
                        $order->setStatus('shipped');
                    }
                }
                $this->addFlash('success', count($orders) . ' commandes marquées comme expédiées !');
                break;

            case 'deliver':
                foreach ($orders as $order) {
                    if ($order->getStatus() === 'shipped') {
                        $order->setStatus('delivered');
                    }
                }
                $this->addFlash('success', count($orders) . ' commandes marquées comme livrées !');
                break;

            case 'cancel':
                foreach ($orders as $order) {
                    if (in_array($order->getStatus(), ['pending', 'confirmed'])) {
                        $order->setStatus('cancelled');
                    }
                }
                $this->addFlash('success', count($orders) . ' commandes annulées !');
                break;

            case 'delete':
                $deletedCount = 0;
                foreach ($orders as $order) {
                    if ($order->getStatus() === 'cancelled') {
                        $entityManager->remove($order);
                        $deletedCount++;
                    }
                }

                if ($deletedCount > 0) {
                    $this->addFlash('success', $deletedCount . ' commandes supprimées avec succès !');
                }

                $skippedCount = count($orders) - $deletedCount;
                if ($skippedCount > 0) {
                    $this->addFlash('warning', $skippedCount . ' commandes non supprimées (seules les commandes annulées peuvent être supprimées).');
                }
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('app_admin_order_index');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_order_index');
    }
}
