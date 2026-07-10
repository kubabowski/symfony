<?php

namespace App\Controller;

use App\Entity\ShopOrder;
use App\Form\CheckoutType;
use App\Repository\ProductRepository;
use App\Repository\ShopOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ShopOrderRepository $shopOrderRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/product/{slug}', name: 'shop_product')]
    public function product(string $slug): Response
    {
        $product = $this->productRepository->findOneBy(['slug' => $slug, 'isActive' => true]);

        if (!$product) {
            throw new NotFoundHttpException('Product not found.');
        }

        return $this->render('shop/product.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/checkout/{slug}', name: 'shop_checkout')]
    public function checkout(string $slug, Request $request): Response
    {
        $product = $this->productRepository->findOneBy(['slug' => $slug, 'isActive' => true]);

        if (!$product) {
            throw new NotFoundHttpException('Product not found.');
        }

        $order = new ShopOrder();
        $order->setProduct($product);

        $form = $this->createForm(CheckoutType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setQuantity(1);
            $order->setTotalPrice($product->getPrice());
            $order->setStatus(ShopOrder::STATUS_PENDING);

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            // TODO: hook up real payment gateway here.
            // For now we redirect straight to the success page.
            return $this->redirectToRoute('shop_success', ['id' => $order->getId()]);
        }

        return $this->render('shop/checkout.html.twig', [
            'product' => $product,
            'checkout_form' => $form->createView(),
        ]);
    }

    #[Route('/order/{id}/success', name: 'shop_success')]
    public function success(int $id): Response
    {
        $order = $this->shopOrderRepository->find($id);

        if (!$order) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $this->render('shop/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/{id}/fail', name: 'shop_fail')]
    public function fail(int $id): Response
    {
        $order = $this->shopOrderRepository->find($id);

        if (!$order) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $this->render('shop/fail.html.twig', [
            'order' => $order,
        ]);
    }
}