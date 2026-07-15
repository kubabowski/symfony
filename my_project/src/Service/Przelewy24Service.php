<?php

namespace App\Service;

use App\Entity\ShopOrder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Przelewy24Service
{
    private HttpClientInterface $client;

    public function __construct(
        private readonly string $merchantId,
        private readonly string $posId,
        private readonly string $apiKey,
        private readonly string $crcKey,
        private readonly bool $sandbox,
        private readonly LoggerInterface $logger,
    ) {
        $this->client = HttpClient::create([
            'auth_basic' => [$this->posId, $this->apiKey],
            'base_uri' => $this->sandbox
                ? 'https://sandbox.przelewy24.pl/api/v1/'
                : 'https://secure.przelewy24.pl/api/v1/',
        ]);
    }

    public function getPaymentPageBaseUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.przelewy24.pl/trnRequest/'
            : 'https://secure.przelewy24.pl/trnRequest/';
    }

    /**
     * Registers a transaction with Przelewy24 and returns the token used
     * to build the redirect URL to the hosted payment page.
     */
    public function registerTransaction(ShopOrder $order, string $returnUrl, string $statusUrl): string
    {
        $sessionId = $this->buildSessionId($order);
        $amountGrosze = (int) round(((float) $order->getTotalPrice()) * 100);

        $payload = [
            'merchantId' => (int) $this->merchantId,
            'posId' => (int) $this->posId,
            'sessionId' => $sessionId,
            'amount' => $amountGrosze,
            'currency' => 'PLN',
            'description' => sprintf('Order #%d', $order->getId()),
            'email' => $order->getCustomerEmail(),
            'client' => $order->getCustomerName(),
            'address' => $order->getAddressLine1(),
            'city' => $order->getCity(),
            'zip' => $order->getPostalCode(),
            'country' => 'PL',
            'phone' => $order->getCustomerPhone(),
            'urlReturn' => $returnUrl,
            'urlStatus' => $statusUrl,
            'timeLimit' => 0,
            'waitForResult' => false,
            'sign' => $this->sign([
                'sessionId' => $sessionId,
                'merchantId' => (int) $this->merchantId,
                'amount' => $amountGrosze,
                'currency' => 'PLN',
                'crc' => $this->crcKey,
            ]),
        ];

        $response = $this->client->request('POST', 'transaction/register', ['json' => $payload]);
        $data = $response->toArray(false);

        if (!isset($data['data']['token'])) {
            $message = $data['error'] ??
                $data['message'] ??
                json_encode($data);

            throw new \RuntimeException($message);
        }

        $order->setPrzelewy24SessionId($sessionId);
        $order->setAmountGrosze($amountGrosze);

        return $data['data']['token'];
    }

    /**
     * Verifies the signature of an incoming webhook payload and, if valid,
     * confirms the transaction against Przelewy24's verify endpoint.
     *
     * @param array<string, mixed> $payload Decoded JSON body sent by Przelewy24 to urlStatus
     */
    public function verifyWebhook(array $payload): bool
    {
        if (!isset($payload['sessionId'], $payload['orderId'], $payload['amount'], $payload['currency'], $payload['sign'])) {
            $this->logger->warning('Przelewy24 webhook: missing required fields', ['payload' => $payload]);
            return false;
        }

        $expectedSign = $this->sign([
            'sessionId' => $payload['sessionId'],
            'orderId' => $payload['orderId'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'crc' => $this->crcKey,
        ]);

        if (!hash_equals($expectedSign, $payload['sign'])) {
            $this->logger->warning('Przelewy24 webhook: signature mismatch', ['sessionId' => $payload['sessionId']]);
            return false;
        }

        $verifyPayload = [
            'merchantId' => (int) $this->merchantId,
            'posId' => (int) $this->posId,
            'sessionId' => $payload['sessionId'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'],
            'orderId' => $payload['orderId'],
            'sign' => $expectedSign,
        ];

        // Note: check current P24 docs — verify has historically been PUT on /transaction/verify.
        $response = $this->client->request('PUT', 'transaction/verify', ['json' => $verifyPayload]);
        $data = $response->toArray(false);

        return ($data['data']['status'] ?? null) === 'success';
    }

    private function sign(array $data): string
    {
        return hash('sha384', json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    private function buildSessionId(ShopOrder $order): string
    {
        return sprintf('order-%d-%s', $order->getId(), bin2hex(random_bytes(4)));
    }
}