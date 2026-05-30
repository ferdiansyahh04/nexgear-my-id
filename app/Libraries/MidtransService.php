<?php

namespace App\Libraries;

use Config\Midtrans as MidtransConfig;

/**
 * Thin Midtrans Snap client.
 *
 * Talks to the Snap REST API over cURL (no SDK dependency). Responsible for:
 *   - creating a Snap transaction (returns a token + redirect_url)
 *   - verifying the SHA-512 signature on incoming webhook notifications
 *   - mapping Midtrans transaction_status → our internal payment_status
 *
 * Keys are read from Config\Midtrans, which sources them from .env. When keys
 * are absent the service reports itself disabled and callers fall back to the
 * legacy non-payment checkout, so a missing key never breaks the store.
 */
class MidtransService
{
    protected MidtransConfig $config;

    public function __construct(?MidtransConfig $config = null)
    {
        $this->config = $config ?? config(MidtransConfig::class);
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function clientKey(): string
    {
        return $this->config->clientKey;
    }

    public function snapJsUrl(): string
    {
        return $this->config->snapJsUrl();
    }

    /**
     * Create a Snap transaction and return its token + redirect URL.
     *
     * @param array{order_id:string, gross_amount:int} $transaction
     * @param array<string, mixed>                      $customer
     * @param array<int, array<string, mixed>>          $items
     * @param array<string, string>                     $callbacks
     *
     * @return array{token:string, redirect_url:string}
     *
     * @throws \RuntimeException on transport or API error
     */
    public function createTransaction(array $transaction, array $customer = [], array $items = [], array $callbacks = []): array
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('Midtrans is not configured.');
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $transaction['order_id'],
                'gross_amount' => (int) $transaction['gross_amount'],
            ],
            'credit_card' => ['secure' => true],
        ];

        if ($customer !== []) {
            $payload['customer_details'] = $customer;
        }
        if ($items !== []) {
            $payload['item_details'] = $items;
        }
        if ($callbacks !== []) {
            $payload['callbacks'] = $callbacks;
        }

        $response = $this->request('/transactions', $payload);

        if (! isset($response['token'])) {
            $msg = isset($response['error_messages'])
                ? implode('; ', (array) $response['error_messages'])
                : 'Unknown Snap API error';
            throw new \RuntimeException('Midtrans Snap: ' . $msg);
        }

        return [
            'token'        => (string) $response['token'],
            'redirect_url' => (string) ($response['redirect_url'] ?? ''),
        ];
    }

    /**
     * Verify the signature_key on a webhook notification body.
     *
     * Midtrans computes: sha512(order_id + status_code + gross_amount + serverKey).
     *
     * @param array<string, mixed> $body
     */
    public function verifySignature(array $body): bool
    {
        $orderId     = (string) ($body['order_id'] ?? '');
        $statusCode  = (string) ($body['status_code'] ?? '');
        $grossAmount = (string) ($body['gross_amount'] ?? '');
        $signature   = (string) ($body['signature_key'] ?? '');

        if ($orderId === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->config->serverKey);

        return hash_equals($expected, $signature);
    }

    /**
     * Map a Midtrans notification to our internal payment_status.
     *
     * @param array<string, mixed> $body
     *
     * @return string one of: paid | pending | failed | expired | refunded | unpaid
     */
    public function mapStatus(array $body): string
    {
        $txn   = (string) ($body['transaction_status'] ?? '');
        $fraud = (string) ($body['fraud_status'] ?? 'accept');

        return match ($txn) {
            'capture'    => $fraud === 'challenge' ? 'pending' : 'paid',
            'settlement' => 'paid',
            'pending'    => 'pending',
            'deny', 'cancel', 'failure' => 'failed',
            'expire'     => 'expired',
            'refund', 'partial_refund', 'chargeback' => 'refunded',
            default      => 'unpaid',
        };
    }

    /**
     * POST a JSON payload to the Snap API with HTTP Basic auth (serverKey as
     * username, empty password — the Midtrans convention).
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    private function request(string $path, array $payload): array
    {
        $url  = $this->config->apiBase() . $path;
        $auth = base64_encode($this->config->serverKey . ':');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $auth,
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new \RuntimeException('Midtrans transport error: ' . $err);
        }

        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException("Midtrans returned non-JSON (HTTP {$code}).");
        }

        return $decoded;
    }
}
