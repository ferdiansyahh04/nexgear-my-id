<?php

namespace App\Libraries;

use Config\Duitku as DuitkuConfig;

/**
 * Thin Duitku client.
 *
 * Talks to the Duitku REST API over cURL (no SDK dependency). Responsible for:
 *   - creating an invoice (returns a Duitku `reference` + paymentUrl)
 *   - checking a transaction's status (server-side reconciliation)
 *   - verifying the signature on incoming callbacks
 *   - mapping Duitku result/status codes → our internal payment_status
 *
 * Keys are read from Config\Duitku, which sources them from .env. When keys
 * are absent the service reports itself disabled and callers fall back to the
 * legacy non-payment checkout, so a missing key never breaks the store.
 *
 * Signature formulas (matching the official duitku-php SDK):
 *   create-invoice header : sha256(merchantCode + timestamp + apiKey)
 *   check transaction     : md5(merchantCode + merchantOrderId + apiKey)
 *   callback validation   : md5(merchantCode + amount + merchantOrderId + apiKey)
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
        // Signature per the official duitku-php library (Pop::createInvoice):
        // sha256(merchantCode + timestamp + apiKey). Duitku's newer HMAC scheme
        // is documented but the SHA256 concat form is what the reference SDK
        // sends and remains accepted.
        $signature = hash('sha256', $this->config->merchantCode . $timestamp . $this->config->apiKey);

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
     * The official duitku-php library computes:
     *   md5(merchantCode + amount + merchantOrderId + apiKey)
     * Duitku also documents a newer HMAC-SHA256 scheme. We accept either so the
     * integration keeps working regardless of which the account is configured
     * to send.
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

        $stringToSign = $merchantCode . $amount . $merchantOrderId;

        // Legacy MD5 (what the reference SDK validates against).
        $md5 = md5($stringToSign . $this->config->apiKey);
        if (hash_equals($md5, $signature)) {
            return true;
        }

        // Newer HMAC-SHA256 scheme.
        $hmac = hash_hmac('sha256', $stringToSign, $this->config->apiKey);

        return hash_equals($hmac, $signature);
    }

    /**
     * Check a transaction's current status directly with Duitku (server-side
     * reconciliation, independent of the callback).
     *
     * Signature: md5(merchantCode + merchantOrderId + apiKey).
     *
     * @return array{statusCode:string, statusMessage:string, reference:string, amount:string}
     *
     * @throws \RuntimeException on transport / API error
     */
    public function checkTransaction(string $merchantOrderId): array
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('Duitku is not configured.');
        }

        $signature = md5($this->config->merchantCode . $merchantOrderId . $this->config->apiKey);

        $payload = [
            'merchantCode'    => $this->config->merchantCode,
            'merchantOrderId' => $merchantOrderId,
            'signature'       => $signature,
        ];

        $url = $this->config->webApiBase() . '/webapi/api/merchant/transactionStatus';
        $response = $this->requestJson($url, $payload);

        return [
            'statusCode'    => (string) ($response['statusCode'] ?? ''),
            'statusMessage' => (string) ($response['statusMessage'] ?? ''),
            'reference'     => (string) ($response['reference'] ?? ''),
            'amount'        => (string) ($response['amount'] ?? ''),
        ];
    }

    /**
     * Map a Duitku transaction-status statusCode to our internal payment_status.
     * (Cek Transaksi: 00 = success, 01 = pending, 02 = canceled/failed.)
     */
    public function mapStatusCode(string $statusCode): string
    {
        return match ($statusCode) {
            '00'    => 'paid',
            '01'    => 'pending',
            '02'    => 'failed',
            default => 'unpaid',
        };
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
     * POST a JSON payload to the Duitku Pop API with the create-invoice auth
     * headers (signature / timestamp / merchantcode).
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    private function request(string $path, array $payload, string $timestamp, string $signature): array
    {
        return $this->curlJson(
            $this->config->apiBase() . $path,
            json_encode($payload),
            [
                'x-duitku-signature: ' . $signature,
                'x-duitku-timestamp: ' . $timestamp,
                'x-duitku-merchantcode: ' . $this->config->merchantCode,
            ]
        );
    }

    /**
     * POST a JSON payload to an absolute Duitku URL (no special headers — the
     * signature travels inside the body). Used by transactionStatus.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    private function requestJson(string $url, array $payload): array
    {
        return $this->curlJson($url, json_encode($payload), []);
    }

    /**
     * Shared cURL JSON POST.
     *
     * @param list<string> $extraHeaders
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    private function curlJson(string $url, string $body, array $extraHeaders): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => array_merge([
                'Accept: application/json',
                'Content-Type: application/json',
            ], $extraHeaders),
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
