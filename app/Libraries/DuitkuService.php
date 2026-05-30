<?php

namespace App\Libraries;

use Config\Duitku as DuitkuConfig;

/**
 * Thin Duitku Pop client.
 *
 * Talks to the Duitku Create-Invoice REST API over cURL (no SDK dependency).
 * Responsible for:
 *   - creating an invoice (returns a Duitku `reference` + paymentUrl)
 *   - verifying the HMAC-SHA256 signature on incoming callbacks
 *   - mapping Duitku resultCode → our internal payment_status
 *
 * Keys are read from Config\Duitku, which sources them from .env. When keys
 * are absent the service reports itself disabled and callers fall back to the
 * legacy non-payment checkout, so a missing key never breaks the store.
 *
 * Signature formulas (Duitku, current HMAC scheme):
 *   create-invoice header: HMAC_SHA256(merchantCode + timestamp, apiKey)
 *   callback validation:   HMAC_SHA256(merchantCode + amount + merchantOrderId, apiKey)
 */
class DuitkuService
{
    protected DuitkuConfig $config;

    public function __construct(?DuitkuConfig $config = null)
    {
        $this->config = $config ?? config(DuitkuConfig::class);
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function popJsUrl(): string
    {
        return $this->config->popJsUrl();
    }

    /**
     * Create a Duitku invoice and return its reference + payment URL.
     *
     * @param array{merchantOrderId:string, paymentAmount:int, productDetails:string} $order
     * @param array<string, mixed>                                                    $customer
     * @param array<int, array<string, mixed>>                                        $items
     * @param array{callbackUrl:string, returnUrl:string}                             $urls
     *
     * @return array{reference:string, paymentUrl:string}
     *
     * @throws \RuntimeException on transport or API error
     */
    public function createInvoice(array $order, array $customer, array $items, array $urls): array
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('Duitku is not configured.');
        }

        // UNIX timestamp in milliseconds (Jakarta time, but epoch ms is TZ-agnostic).
        $timestamp = (string) (int) round(microtime(true) * 1000);
        $signature = hash_hmac('sha256', $this->config->merchantCode . $timestamp, $this->config->apiKey);

        $payload = [
            'paymentAmount'   => (int) $order['paymentAmount'],
            'merchantOrderId' => (string) $order['merchantOrderId'],
            'productDetails'  => mb_substr((string) $order['productDetails'], 0, 255),
            'email'           => (string) ($customer['email'] ?? ''),
            'customerVaName'  => mb_substr((string) ($customer['name'] ?? 'Customer'), 0, 20),
            'phoneNumber'     => (string) ($customer['phone'] ?? ''),
            'itemDetails'     => $items,
            'callbackUrl'     => $urls['callbackUrl'],
            'returnUrl'       => $urls['returnUrl'],
            'expiryPeriod'    => $this->config->expiryPeriod,
        ];

        $response = $this->request('/api/merchant/createInvoice', $payload, $timestamp, $signature);

        if (($response['statusCode'] ?? null) !== '00' || empty($response['reference'])) {
            $msg = (string) ($response['statusMessage'] ?? 'Unknown Duitku error');
            throw new \RuntimeException('Duitku create-invoice: ' . $msg);
        }

        return [
            'reference'  => (string) $response['reference'],
            'paymentUrl' => (string) ($response['paymentUrl'] ?? ''),
        ];
    }

    /**
     * Verify the signature on a Duitku callback (x-www-form-urlencoded POST).
     *
     * Duitku computes: HMAC_SHA256(merchantCode + amount + merchantOrderId, apiKey).
     *
     * @param array<string, mixed> $body
     */
    public function verifyCallback(array $body): bool
    {
        $merchantCode    = (string) ($body['merchantCode'] ?? '');
        $amount          = (string) ($body['amount'] ?? '');
        $merchantOrderId = (string) ($body['merchantOrderId'] ?? '');
        $signature       = (string) ($body['signature'] ?? '');

        if ($merchantCode === '' || $merchantOrderId === '' || $signature === '') {
            return false;
        }

        // The callback must belong to our merchant.
        if (! hash_equals($this->config->merchantCode, $merchantCode)) {
            return false;
        }

        $expected = hash_hmac('sha256', $merchantCode . $amount . $merchantOrderId, $this->config->apiKey);

        return hash_equals($expected, $signature);
    }

    /**
     * Map a Duitku resultCode to our internal payment_status.
     *
     * Callback result codes: 00 = success, 01 = failed (some docs use 02).
     *
     * @return string one of: paid | failed | unpaid
     */
    public function mapResultCode(string $resultCode): string
    {
        return match ($resultCode) {
            '00'         => 'paid',
            '01', '02'   => 'failed',
            default      => 'unpaid',
        };
    }

    /**
     * POST a JSON payload to the Duitku API with the auth headers.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    private function request(string $path, array $payload, string $timestamp, string $signature): array
    {
        $url = $this->config->apiBase() . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'x-duitku-signature: ' . $signature,
                'x-duitku-timestamp: ' . $timestamp,
                'x-duitku-merchantcode: ' . $this->config->merchantCode,
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
            throw new \RuntimeException('Duitku transport error: ' . $err);
        }

        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException("Duitku returned non-JSON (HTTP {$code}).");
        }

        return $decoded;
    }
}
