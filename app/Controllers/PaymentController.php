<?php

namespace App\Controllers;

use App\Libraries\DuitkuService;
use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

/**
 * Duitku Pop payment flow.
 *
 *   POST /payment/invoice/(:num) — (auth) create/return a Duitku reference for an order
 *   POST /payment/callback       — Duitku server-to-server callback (no CSRF)
 *   GET  /payment/return         — browser redirect target after Pop closes
 *
 * The callback is the source of truth for marking an order paid; the browser
 * return is cosmetic (the user can close the tab before it fires).
 */
class PaymentController extends BaseController
{
    /**
     * Create (or refresh) a Duitku invoice for an order the current user owns.
     * Returns JSON { reference } for the front-end checkout.process() call.
     */
    public function invoice(int $orderId)
    {
        $duitku = new DuitkuService();
        if (! $duitku->isEnabled()) {
            return $this->response->setStatusCode(503)->setJSON([
                'status'  => 'error',
                'message' => 'Online payment is not available right now.',
            ]);
        }

        $orderModel = new CartModel();
        $order      = $orderModel->find($orderId);

        // Ownership + state guards.
        if (! $order || (int) $order['user_id'] !== (int) session('user_id')) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error', 'message' => 'Order not found.',
            ]);
        }
        if (($order['payment_status'] ?? 'unpaid') === 'paid') {
            return $this->response->setJSON(['status' => 'already_paid']);
        }

        // A fresh, unique merchantOrderId is required for each new invoice.
        // We namespace by cart id + timestamp so retries never collide, and
        // store it on the order so the callback can find us.
        $merchantOrderId = 'NEXGEAR-' . $orderId . '-' . time();

        $items        = (new CartItemModel())->where('cart_id', $orderId)->findAll();
        $productModel = new ProductModel();
        $itemDetails  = [];
        foreach ($items as $line) {
            $product = $productModel->find((int) $line['product_id']);
            $itemDetails[] = [
                'name'     => mb_substr((string) ($product['name'] ?? 'Item'), 0, 50),
                'price'    => (int) round((float) $line['price']),
                'quantity' => (int) $line['quantity'],
            ];
        }

        // Represent the coupon discount as a negative line so the item totals
        // reconcile with paymentAmount (Duitku requires the sum to match).
        $discount = (int) round((float) ($order['discount'] ?? 0));
        if ($discount > 0) {
            $itemDetails[] = [
                'name'     => mb_substr('Discount' . ($order['coupon_code'] ? ' (' . $order['coupon_code'] . ')' : ''), 0, 50),
                'price'    => -$discount,
                'quantity' => 1,
            ];
        }

        $paymentAmount = (int) round((float) $order['total']);

        try {
            $invoice = $duitku->createInvoice(
                [
                    'merchantOrderId' => $merchantOrderId,
                    'paymentAmount'   => $paymentAmount,
                    'productDetails'  => 'NexGear Order #' . $orderId,
                ],
                [
                    'name'  => (string) ($order['shipping_name'] ?: session('user_name')),
                    'email' => (string) (session('user_email') ?? ''),
                    'phone' => (string) ($order['shipping_phone'] ?? ''),
                ],
                $itemDetails,
                [
                    'callbackUrl' => base_url('/payment/callback'),
                    'returnUrl'   => base_url('/payment/return?order=' . $orderId),
                ]
            );
        } catch (\Throwable $e) {
            log_message('error', 'Duitku invoice creation failed: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(502)->setJSON([
                'status'  => 'error',
                'message' => 'Could not start the payment. Please try again.',
            ]);
        }

        $orderModel->update($orderId, [
            'payment_ref'    => $merchantOrderId,
            'payment_token'  => $invoice['reference'], // stores the Duitku reference
            'payment_status' => 'pending',
        ]);

        return $this->response->setJSON([
            'status'     => 'success',
            'reference'  => $invoice['reference'],
            'paymentUrl' => $invoice['paymentUrl'],
        ]);
    }

    /**
     * Duitku server-to-server callback. Verifies the HMAC signature, then
     * updates the order's payment + lifecycle status idempotently.
     */
    public function callback()
    {
        $duitku = new DuitkuService();
        $body   = $this->request->getPost();

        if (! $duitku->isEnabled() || ! $duitku->verifyCallback($body)) {
            log_message('warning', 'Rejected Duitku callback (bad signature or disabled).');
            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        }

        $merchantOrderId = (string) ($body['merchantOrderId'] ?? '');
        $orderModel = new CartModel();
        $order      = $orderModel->where('payment_ref', $merchantOrderId)->first();
        if (! $order) {
            // Unknown ref — ack with 200 so Duitku stops retrying.
            return $this->response->setBody('OK');
        }

        $resultCode       = (string) ($body['resultCode'] ?? '');
        $newPaymentStatus = $duitku->mapResultCode($resultCode);

        $update = [
            'payment_status' => $newPaymentStatus,
            'payment_method' => (string) ($body['paymentCode'] ?? $order['payment_method']),
        ];

        // Promote the order lifecycle to 'paid' once (and only once).
        if ($newPaymentStatus === 'paid' && ($order['payment_status'] ?? '') !== 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
            if (in_array($order['status'], ['checked_out'], true)) {
                $update['status'] = 'paid';
            }
        }

        $orderModel->update((int) $order['id'], $update);

        // Duitku expects a 200 OK body to consider the callback delivered.
        return $this->response->setBody('OK');
    }

    /**
     * Browser redirect after the Pop closes / the user returns. The callback
     * does the real state change; here we just route the user to their order.
     */
    public function return()
    {
        $orderId = (int) $this->request->getGet('order');
        if ($orderId <= 0) {
            return redirect()->to('/account/orders');
        }

        $resultCode = (string) $this->request->getGet('resultCode');
        $flash = match ($resultCode) {
            '00'    => ['success', 'Thanks! We are confirming your payment — this updates automatically.'],
            '01'    => ['error', 'Payment not completed yet. You can resume it from your order.'],
            default => ['error', 'Payment was cancelled or failed. You can try again from your order.'],
        };

        return redirect()->to('/account/orders/' . $orderId)->with($flash[0], $flash[1]);
    }
}
