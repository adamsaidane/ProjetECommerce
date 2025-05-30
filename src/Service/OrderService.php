<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;
    private CartService $cartService;

    public function __construct(EntityManagerInterface $entityManager, CartService $cartService)
    {
        $this->entityManager = $entityManager;
        $this->cartService = $cartService;
    }

    public function createOrderFromCart(User $user, array $shippingData = []): Order
    {
        $cart = $this->cartService->getCartForUser($user);

        if ($cart->isEmpty()) {
            throw new \InvalidArgumentException('Le panier est vide');
        }

        // Vérifier le stock avant de créer la commande
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getQuantity() > $cartItem->getProduct()->getStock()) {
                throw new \InvalidArgumentException(
                    sprintf('Stock insuffisant pour le produit "%s"', $cartItem->getProduct()->getTitle())
                );
            }
        }

        $order = new Order();
        $order->setUser($user);

        // Calculer les frais de livraison
        $subtotal = $cart->getTotalPrice();
        $shippingCost = $subtotal >= 50 ? 0 : 5.90;
        $order->setShippingCost((string) $shippingCost);

        // Ajouter les articles du panier à la commande
        foreach ($cart->getCartItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setPrice($cartItem->getProduct()->getPrice());
            $order->addOrderItem($orderItem);
        }

        // Calculer le total
        $total = $subtotal + $shippingCost;
        $order->setTotalAmount((string) $total);

        // Ajouter les adresses si fournies
        if (isset($shippingData['shipping_address'])) {
            $order->setShippingAddress($shippingData['shipping_address']);
        }
        if (isset($shippingData['billing_address'])) {
            $order->setBillingAddress($shippingData['billing_address']);
        }

        $this->entityManager->persist($order);

        // Mettre à jour le stock des produits
        foreach ($order->getOrderItems() as $orderItem) {
            $product = $orderItem->getProduct();
            $newStock = $product->getStock() - $orderItem->getQuantity();
            $product->setStock($newStock);
        }

        // Vider le panier
        $this->cartService->clearCart($user);

        $this->entityManager->flush();

        return $order;
    }

    public function updateOrderStatus(Order $order, string $status): void
    {
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Statut de commande invalide');
        }

        $order->setStatus($status);
        $this->entityManager->flush();
    }

    public function cancelOrder(Order $order): void
    {
        if ($order->getStatus() !== 'pending') {
            throw new \InvalidArgumentException('Seules les commandes en attente peuvent être annulées');
        }

        // Remettre le stock
        foreach ($order->getOrderItems() as $orderItem) {
            $product = $orderItem->getProduct();
            $newStock = $product->getStock() + $orderItem->getQuantity();
            $product->setStock($newStock);
        }

        $order->setStatus('cancelled');
        $this->entityManager->flush();
    }
}
