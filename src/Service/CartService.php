<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    private EntityManagerInterface $entityManager;
    private CartRepository $cartRepository;

    public function __construct(EntityManagerInterface $entityManager, CartRepository $cartRepository)
    {
        $this->entityManager = $entityManager;
        $this->cartRepository = $cartRepository;
    }

    public function getCartForUser(User $user): Cart
    {
        return $this->cartRepository->findOrCreateByUser($user);
    }

    public function addProductToCart(User $user, Product $product, int $quantity = 1): void
    {
        if (!$product->isInStock() || $quantity > $product->getStock()) {
            throw new \InvalidArgumentException('Produit non disponible en stock suffisant.');
        }

        $cart = $this->getCartForUser($user);

        // Vérifier si le produit est déjà dans le panier
        $existingItem = $cart->getCartItemByProduct($product);

        if ($existingItem) {
            $newQuantity = $existingItem->getQuantity() + $quantity;
            if ($newQuantity > $product->getStock()) {
                throw new \InvalidArgumentException('Quantité demandée supérieure au stock disponible.');
            }
            $existingItem->setQuantity($newQuantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addCartItem($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function removeProductFromCart(User $user, Product $product): void
    {
        $cart = $this->getCartForUser($user);
        $cartItem = $cart->getCartItemByProduct($product);

        if ($cartItem) {
            $cart->removeCartItem($cartItem);
            $this->entityManager->remove($cartItem);
            $cart->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    }

    public function updateCartItemQuantity(User $user, Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeProductFromCart($user, $product);
            return;
        }

        if ($quantity > $product->getStock()) {
            throw new \InvalidArgumentException('Quantité demandée supérieure au stock disponible.');
        }

        $cart = $this->getCartForUser($user);
        $cartItem = $cart->getCartItemByProduct($product);

        if ($cartItem) {
            $cartItem->setQuantity($quantity);
            $cart->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    }

    public function clearCart(User $user): void
    {
        $cart = $this->getCartForUser($user);

        foreach ($cart->getCartItems() as $cartItem) {
            $this->entityManager->remove($cartItem);
        }

        $cart->clear();
        $this->entityManager->flush();
    }

    public function getCartItemCount(User $user): int
    {
        $cart = $this->getCartForUser($user);
        return $cart->getTotalItems();
    }

    public function getCartTotal(User $user): float
    {
        $cart = $this->getCartForUser($user);
        return $cart->getTotalPrice();
    }
}
