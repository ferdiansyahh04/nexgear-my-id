<?php

namespace App\Controllers;

use App\Libraries\MidtransService;
use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

/**
 * Midtrans Snap payment flow.
 *
 *   POST /payment/snap/(:num)   — (auth) create/return a Snap token for an order
 *   POST /payment/notification  — Midtrans server-to-server webhook (no CSRF)
 *   GET  /payment/finish        — redirect target after a successful pay
 *   GET  /payment/unfinish      — redirect target when the user backs out
 *   GET  /payment/error         — redirect target on a failed pay
 *
 * The webhook is the source of truth for marking an order paid; the browser
 * redirects are cosmetic (the user can close the tab before they fire).
 */
class PaymentController extends BaseController
{
    /**
     * Create (or reuse) a Snap token for an order the current user owns.
     * Returns JSON { token, clientKey } for the front-end snap.pay() call.
     */
    public function snap(int $orderId)
    {
        $midtrans = new MidtransService();
        if (! $midtrans->isEnabled()) {
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
            return $this->response->setJSON([
                'status' => 'already_paid',
            ]);
        }

        // A fresh, unique order_id is required by Midtrans for each new Snap
        // token. We namespace by cart id + timestamp so retries never collide.
        $paymentRef = 'NEXGEAR-' . $orderId . '-' . time();

        $items = (new CartItemModel())->where('cart_id', $orderId)->findAll();
        $productModel = new ProductModel();
        $itemDetails  = [];
        foreach ($items as $line) {
            $product = $productModel->find((int) $line['product_id']);
            $itemDetails[] = [
                'id'       => (string) $line['product_id'],
                'price'    => (int) round((float) $line['price']),
                'quantity' => (int) $line['quantity'],
                'name'     => mb_substr((string) ($product['name'] ?? 'Item'), 0, 50),
            ];
        }

        // Represent the coupon discount as a negative line so the item total
        // reconciles with gross_amount (Midtrans rejects mismatches).
        $discount = (int) round((float) ($order['discount'] ?? 0));
        if ($discount > 0) {
            $itemDetails[] = [
                'id'       => 'DISCOUNT',
                'price'    => -$discount,
                'quantity' => 1,
                'name'     => 'Discount' . ($order['coupon_code'] ? ' (' . $order['coupon_code'] . ')' : ''),
            ];
        }

        $grossAmount = (int) round((float) $order['total']);

        try {
            $snap = $midtrans->createTransaction(
                ['order_id' => $paymentRef, 'gross_amount' => $grossAmount],
                [
                    'first_name' => (string) ($order['shipping_name'] ?: session('user_name')),
                    'email'      => (string) (session('user_email') ?? ''),
                    'phone'      => (string) ($order['shipping_phone'] ?? ''),
                ],
                $itemDetails,
                ['finish' => base_url('/payment/finish?order=' . $orderId)]
            );
        } catch (\Throwable $e) {
            log_message('error', 'Snap token creation failed: {msg}', ['msg' => $e->getMessage()]);
            return $this->response->setStatusCode(502)->setJSON([
                'status'  => 'error',
                'message' => 'Could not start the payment. Please try again.',
            ]);
        }

        $orderModel->update($orderId, [
            'payment_ref'    => $paymentRef,
            'snap_token'     => $snap['token'],
            'payment_status' => 'pending',
        ]);

        return $this->response->setJSON([
            'status'    => 'success',
            'token'     => $snap['token'],
            'clientKey' => $midtrans->clientKey(),
        ]);
    }

    /**
     * Midtrans server-to-server webhook. Verifies the signature, then updates
     * the order's payment + lifecycle status idempotently.
     */
    public function notification()
    {
        $midtrans = new MidtransService();
        $body     = $this->request->getJSON(true);
        if (! is_array($body)) {
            $body = $this->request->getPost();
        }

        if (! $midtrans->isEnabled() || ! $midtrans->verifySignature($body)) {
            log_message('warning', 'Rejected Midtrans webhook (bad signature or disabled).');
            return $this->response->setStatusCode(403)->setJSON(['status' => 'forbidden']);
        }

        $paymentRef = (string) ($body['order_id'] ?? '');
        $orderModel = new CartModel();
        $order      = $orderModel->where('payment_ref', $paymentRef)->first();
        if (! $order) {
            // Unknown ref — ack with 200 so Midtrans stops retrying.
            return $this->response->setJSON(['status' => 'ignored']);
        }

        $newPaymentStatus = $midtrans->mapStatus($body);
        $update = [
            'payment_status' => $newPaymentStatus,
            'payment_method' => (string) ($body['payment_type'] ?? $order['payment_method']),
        ];

        // Promote the order lifecycle to 'paid' once (and only once).
        if ($newPaymentStatus === 'paid' && ($order['payment_status'] ?? '') !== 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
            if (in_array($order['status'], ['checked_out'], true)) {
                $update['status'] = 'paid';
            }
        }

        $orderModel->update((int) $order['id'], $update);

        return $this->response->setJSON(['status' => 'ok']);
    }

    /**
     * Browser redirect after Snap closes on success. The webhook does the real
     * state change; here we just route the user to their order page.
     */
    public function finish()
    {
        $orderId = (int) $this->request->getGet('order');
        if ($orderId > 0) {
            return redirect()->to('/account/orders/' . $orderId)
                ->with('success', 'Thanks! We are confirming your payment — this updates automatically.');
        }
        return redirect()->to('/account/orders');
    }

    public function unfinish()
    {
        $orderId = (int) $this->request->getGet('order');
        $to = $orderId > 0 ? '/account/orders/' . $orderId : '/account/orders';
        return redirect()->to($to)->with('error', 'Payment not completed. You can resume it from your order.');
    }

    public function error()
    {
        $orderId = (int) $this->request->getGet('order');
        $to = $orderId > 0 ? '/account/orders/' . $orderId : '/account/orders';
        return redirect()->to($to)->with('error', 'Payment failed. Please try again or use another method.');
    }
}
