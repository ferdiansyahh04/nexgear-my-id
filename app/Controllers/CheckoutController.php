<?php

namespace App\Controllers;

use App\Libraries\CartService;
use App\Libraries\CouponService;
use App\Models\AddressModel;
use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

class CheckoutController extends BaseController
{
    protected CartService $cart;

    public function __construct()
    {
        $this->cart = new CartService();
    }

    public function index()
    {
        $items     = $this->cart->items();
        $subtotal  = $this->cart->subtotal($items);
        $discount  = $this->cart->discount($items);
        $coupon    = (new CouponService())->applied();
        $addresses = (new AddressModel())
            ->where('user_id', (int) session('user_id'))
            ->orderBy('is_default', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->findAll();

        return view('checkout/index', [
            'title'     => 'Checkout',
            'items'     => $items,
            'subtotal'  => $subtotal,
            'discount'  => $discount,
            'total'     => $subtotal - $discount,
            'coupon'    => $coupon,
            'addresses' => $addresses,
        ]);
    }

    public function place()
    {
        // ── 1. Validate shipping input ──────────────────────────
        $rules = [
            'shipping_name'        => 'required|min_length[3]|max_length[120]',
            'shipping_phone'       => 'required|min_length[8]|max_length[20]|regex_match[/^[\d\+\-\s]+$/]',
            'shipping_address'     => 'required|min_length[10]|max_length[500]',
            'shipping_city'        => 'required|min_length[2]|max_length[100]',
            'shipping_postal_code' => 'required|min_length[3]|max_length[10]|regex_match[/^[\d\-]+$/]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // ── 2. Check cart is not empty ──────────────────────────
        $items = $this->cart->items();
        if ($items === []) {
            return redirect()->to('/cart')->with('error', 'Cart is empty.');
        }

        // ── 3. Process checkout with stock locking ──────────────
        $cartModel  = new CartModel();
        $itemModel  = new CartItemModel();
        $couponSvc  = new CouponService();
        $db         = db_connect();

        $subtotal     = $this->cart->subtotal($items);
        $discount     = $couponSvc->currentDiscount($subtotal);
        $appliedCode  = $couponSvc->applied();
        $finalTotal   = max(0, $subtotal - $discount);

        $db->transStart();

        $cartId = $cartModel->insert([
            'user_id'              => session('user_id'),
            'status'               => 'checked_out',
            'total'                => $finalTotal,
            'coupon_code'          => $discount > 0 ? $appliedCode : null,
            'discount'             => $discount,
            'shipping_name'        => trim((string) $this->request->getPost('shipping_name')),
            'shipping_phone'       => trim((string) $this->request->getPost('shipping_phone')),
            'shipping_address'     => trim((string) $this->request->getPost('shipping_address')),
            'shipping_city'        => trim((string) $this->request->getPost('shipping_city')),
            'shipping_postal_code' => trim((string) $this->request->getPost('shipping_postal_code')),
        ], true);

        foreach ($items as $item) {
            $product = $item['product'];
            $qty     = (int) $item['qty'];

            // Atomic stock decrement with availability check (prevents race condition)
            $updated = $db->table('products')
                ->where('id', $product['id'])
                ->where('stock >=', $qty)
                ->set('stock', 'stock - ' . $qty, false)
                ->set('updated_at', date('Y-m-d H:i:s'))
                ->update();

            // If stock was insufficient (another user bought it first), abort
            if (! $updated || $db->affectedRows() === 0) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Sorry, "' . esc($product['name']) . '" is no longer available in the requested quantity. Please update your cart.');
            }

            $itemModel->insert([
                'cart_id'    => $cartId,
                'product_id' => $product['id'],
                'quantity'   => $qty,
                'price'      => $product['price'],
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Checkout failed. Please try again.');
        }

        // Increment coupon usage outside the transaction (idempotent enough)
        if ($discount > 0 && $appliedCode !== null) {
            $couponSvc->recordUsage($appliedCode);
        }

        // Order confirmation email (best-effort)
        $userEmail = (string) (session('user_email') ?? '');
        if ($userEmail !== '') {
            $hydratedItems = [];
            foreach ($items as $line) {
                $hydratedItems[] = [
                    'price'    => (float) $line['product']['price'],
                    'quantity' => (int) $line['qty'],
                    'product'  => ['name' => $line['product']['name']],
                ];
            }
            (new \App\Libraries\MailerService())->send(
                $userEmail,
                'Order #' . $cartId . ' confirmed',
                'emails/order_placed',
                [
                    'order' => [
                        'id'                => $cartId,
                        'status'            => 'checked_out',
                        'total'             => $finalTotal,
                        'discount'          => $discount,
                        'coupon_code'       => $appliedCode,
                        'shipping_name'     => trim((string) $this->request->getPost('shipping_name')),
                        'shipping_city'     => trim((string) $this->request->getPost('shipping_city')),
                    ],
                    'items'       => $hydratedItems,
                    'statusLabel' => 'Placed',
                ]
            );
        }

        session()->remove('cart');
        $couponSvc->clear();
        (new \App\Libraries\AbandonedCartService())->clearForUser((int) session('user_id'));

        // When Midtrans is configured, send the customer to the payment step.
        // Otherwise fall back to the legacy "order saved, pay offline" flow so
        // the store keeps working without gateway keys.
        if ((new \App\Libraries\MidtransService())->isEnabled()) {
            return redirect()->to('/checkout/pay/' . $cartId);
        }

        return redirect()->to('/account/orders/' . $cartId)
            ->with('success', 'Checkout complete. Order #' . $cartId . ' saved.');
    }

    /**
     * Payment step — shows the Snap "Pay Now" page for an unpaid order the
     * current user owns. Only reachable when Midtrans is configured.
     */
    public function pay(int $orderId)
    {
        $midtrans = new \App\Libraries\MidtransService();
        if (! $midtrans->isEnabled()) {
            return redirect()->to('/account/orders/' . $orderId);
        }

        $order = (new CartModel())->find($orderId);
        if (! $order || (int) $order['user_id'] !== (int) session('user_id')) {
            return redirect()->to('/account/orders')->with('error', 'Order not found.');
        }

        if (($order['payment_status'] ?? 'unpaid') === 'paid') {
            return redirect()->to('/account/orders/' . $orderId)
                ->with('success', 'This order is already paid.');
        }

        return view('checkout/pay', [
            'title'     => 'Complete Payment',
            'order'     => $order,
            'snapJsUrl' => $midtrans->snapJsUrl(),
            'clientKey' => $midtrans->clientKey(),
        ]);
    }
}
