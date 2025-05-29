<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/', name: 'app_cart_index')]
    public function index(): Response
    {
        $cart = $this->cartService->getCartForUser($this->getUser());

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): Response
    {
        $quantity = $request->request->getInt('quantity', 1);

        try {
            $this->cartService->addProductToCart($this->getUser(), $product, $quantity);
            $this->addFlash('success', 'Produit ajouté au panier avec succès !');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        // Si c'est une requête AJAX, retourner du JSON
        if ($request->isXmlHttpRequest()) {
            $cartCount = $this->cartService->getCartItemCount($this->getUser());
            return new JsonResponse([
                'success' => true,
                'cartCount' => $cartCount,
                'message' => 'Produit ajouté au panier'
            ]);
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Product $product, Request $request): Response
    {
        $quantity = $request->request->getInt('quantity');

        try {
            $this->cartService->updateCartItemQuantity($this->getUser(), $product, $quantity);
            $this->addFlash('success', 'Quantité mise à jour !');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        if ($request->isXmlHttpRequest()) {
            $cart = $this->cartService->getCartForUser($this->getUser());
            return new JsonResponse([
                'success' => true,
                'cartTotal' => $cart->getTotalPrice(),
                'cartCount' => $cart->getTotalItems()
            ]);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Product $product, Request $request): Response
    {
        $this->cartService->removeProductFromCart($this->getUser(), $product);
        $this->addFlash('success', 'Produit retiré du panier !');

        if ($request->isXmlHttpRequest()) {
            $cart = $this->cartService->getCartForUser($this->getUser());
            return new JsonResponse([
                'success' => true,
                'cartTotal' => $cart->getTotalPrice(),
                'cartCount' => $cart->getTotalItems()
            ]);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        $this->cartService->clearCart($this->getUser());
        $this->addFlash('success', 'Panier vidé !');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        $count = $this->cartService->getCartItemCount($this->getUser());

        return new JsonResponse(['count' => $count]);
    }
}
