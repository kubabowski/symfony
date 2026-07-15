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
use App\Service\Przelewy24Service;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

#[Route('/sklep')]
final class ShopController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ShopOrderRepository $shopOrderRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Przelewy24Service $przelewy24Service,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('', name: 'shop_index')]
    public function index(): Response
    {
        $product = $this->productRepository->findOneBy(['isActive' => true], ['id' => 'ASC']);

        if (!$product) {
            throw new NotFoundHttpException('No products available.');
        }

        return $this->redirectToRoute('shop_product', ['slug' => $product->getSlug()]);
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

            $returnUrl = $this->generateUrl('shop_payment_return', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            $statusUrl = $this->generateUrl('shop_payment_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL);

            try {
                $token = $this->przelewy24Service->registerTransaction($order, $returnUrl, $statusUrl);
            } catch (\Throwable $e) {
                $this->logger->error('Przelewy24 transaction registration failed', [
                    'orderId' => $order->getId(),
                    'exception' => $e,
                ]);

                $order->setStatus(ShopOrder::STATUS_FAILED);
                $order->setErrorMessage(
                    $e->getMessage() . "\n\n" . $e->getTraceAsString()
                );

                $this->entityManager->flush();

                return $this->redirectToRoute('shop_fail', ['id' => $order->getId()]);
            }

            $this->entityManager->flush(); // persists sessionId/amountGrosze set by registerTransaction()

            return $this->redirect($this->przelewy24Service->getPaymentPageBaseUrl() . $token);
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

        $this->logger->warning('Order marked as failed', [
            'orderId' => $order->getId(),
            'status' => $order->getStatus(),
        ]);

        return $this->render('shop/fail.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/{id}/payment-return', name: 'shop_payment_return')]
    public function paymentReturn(int $id): Response
    {
        $order = $this->shopOrderRepository->find($id);

        if (!$order) {
            throw new NotFoundHttpException('Order not found.');
        }

        if ($order->getStatus() === ShopOrder::STATUS_PAID) {
            return $this->redirectToRoute('shop_success', ['id' => $order->getId()]);
        }

        return $this->redirectToRoute('shop_fail', ['id' => $order->getId()]);
    }

    #[Route('/payment/przelewy24/webhook', name: 'shop_payment_webhook', methods: ['POST'])]
    public function przelewy24Webhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $order = $this->shopOrderRepository->findOneBy(['przelewy24SessionId' => $payload['sessionId'] ?? null]);

        if (!$order) {
            $this->logger->error('Przelewy24 webhook: order not found', ['sessionId' => $payload['sessionId'] ?? null]);
            return new Response('Order not found', 404);
        }

        if (!$this->przelewy24Service->verifyWebhook($payload)) {
            $this->logger->error('Przelewy24 webhook: verification failed', ['orderId' => $order->getId()]);

            $order->setStatus(ShopOrder::STATUS_FAILED);
            $order->setErrorMessage('Webhook verification failed.');
            $this->entityManager->flush();

            return new Response('Verification failed', 400);
        }

        $order->setPrzelewy24OrderId((int) $payload['orderId']);
        $order->setStatus(ShopOrder::STATUS_PAID);
        $this->entityManager->flush();

        return new Response('OK', 200);
    }
}