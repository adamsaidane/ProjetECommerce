<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\CheckoutType;
use App\Service\CartService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    private OrderService $orderService;
    private CartService $cartService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        OrderService $orderService,
        CartService $cartService,
        EntityManagerInterface $entityManager
    ) {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->entityManager = $entityManager;
    }

    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(Request $request): Response
    {
        $cart = $this->cartService->getCartForUser($this->getUser());

        if ($cart->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        // Vérifier le stock avant de procéder
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getQuantity() > $cartItem->getProduct()->getStock()) {
                $this->addFlash('error', sprintf(
                    'Stock insuffisant pour "%s". Stock disponible: %d',
                    $cartItem->getProduct()->getTitle(),
                    $cartItem->getProduct()->getStock()
                ));
                return $this->redirectToRoute('app_cart_index');
            }
        }

        $form = $this->createForm(CheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formData = $form->getData();

                $shippingData = [
                    'shipping_address' => $this->formatAddress($formData, 'shipping'),
                    'billing_address' => $this->formatAddress($formData, 'billing'),
                ];

                $order = $this->orderService->createOrderFromCart($this->getUser(), $shippingData);

                $this->addFlash('success', 'Votre commande a été créée avec succès !');
                return $this->redirectToRoute('app_order_confirmation', ['orderNumber' => $order->getOrderNumber()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création de la commande: ' . $e->getMessage());
            }
        }

        return $this->render('order/checkout.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation/{orderNumber}', name: 'app_order_confirmation')]
    public function confirmation(string $orderNumber): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy([
            'orderNumber' => $orderNumber,
            'user' => $this->getUser()
        ]);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/history', name: 'app_order_history')]
    public function history(): Response
    {
        $orders = $this->entityManager->getRepository(Order::class)->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('order/history.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', requirements: ['id' => '\d+'])]
    public function show(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette commande.');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_order_cancel', methods: ['POST'])]
    public function cancel(Order $order, Request $request): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette commande.');
        }

        if (!$this->isCsrfTokenValid('cancel_order_' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        try {
            $this->orderService->cancelOrder($order);
            $this->addFlash('success', 'Votre commande a été annulée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/reorder', name: 'app_order_reorder', methods: ['POST'])]
    public function reorder(Order $order, Request $request): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas recommander cette commande.');
        }

        if (!$this->isCsrfTokenValid('reorder_' . $order->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $addedItems = 0;
        $unavailableItems = [];

        foreach ($order->getOrderItems() as $orderItem) {
            $product = $orderItem->getProduct();

            if ($product->isIsActive() && $product->isInStock()) {
                try {
                    $quantity = min($orderItem->getQuantity(), $product->getStock());
                    $this->cartService->addProductToCart($this->getUser(), $product, $quantity);
                    $addedItems++;
                } catch (\Exception $e) {
                    $unavailableItems[] = $product->getTitle();
                }
            } else {
                $unavailableItems[] = $product->getTitle();
            }
        }

        if ($addedItems > 0) {
            $this->addFlash('success', sprintf('%d article(s) ajouté(s) à votre panier.', $addedItems));
        }

        if (!empty($unavailableItems)) {
            $this->addFlash('warning', sprintf(
                'Les articles suivants ne sont plus disponibles: %s',
                implode(', ', $unavailableItems)
            ));
        }

        return $this->redirectToRoute('app_cart_index');
    }

    private function formatAddress(array $data, string $type): string
    {
        $prefix = $type . '_';

        return sprintf(
            "%s %s\n%s\n%s %s\n%s",
            $data[$prefix . 'first_name'] ?? '',
            $data[$prefix . 'last_name'] ?? '',
            $data[$prefix . 'address'] ?? '',
            $data[$prefix . 'postal_code'] ?? '',
            $data[$prefix . 'city'] ?? '',
            $data[$prefix . 'country'] ?? 'Tunisia'
        );
    }
}
