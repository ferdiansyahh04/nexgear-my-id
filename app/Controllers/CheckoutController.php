<?php

namespace App\Controllers;

use App\Libraries\CartService;
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
        $items = $this->cart->items();

        return view('checkout/index', [
            'title' => 'Checkout',
            'items' => $items,
            'total' => $this->cart->total($items),
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
        $cartModel = new CartModel();
        $itemModel = new CartItemModel();
        $db        = db_connect();

        $db->transStart();

        $cartId = $cartModel->insert([
            'user_id'              => session('user_id'),
            'status'               => 'checked_out',
            'total'                => $this->cart->total($items),
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

        session()->remove('cart');

        return redirect()->to('/products')->with('success', 'Checkout complete. Order #' . $cartId . ' saved.');
    }
}
