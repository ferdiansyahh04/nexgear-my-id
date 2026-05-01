<?php

namespace App\Controllers;

use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

class CheckoutController extends CartController
{
    public function index()
    {
        return view('checkout/index', [
            'title' => 'Checkout',
            'items' => $this->cartItems(),
            'total' => $this->cartTotal(),
        ]);
    }

    public function place()
    {
        $items = $this->cartItems();
        if ($items === []) {
            return redirect()->to('/cart')->with('error', 'Cart is empty.');
        }

        $cartModel = new CartModel();
        $itemModel = new CartItemModel();
        $products = new ProductModel();
        $db = db_connect();

        $db->transStart();

        $cartId = $cartModel->insert([
            'user_id' => session('user_id'),
            'status' => 'checked_out',
            'total' => $this->cartTotal(),
            'shipping_name' => $this->request->getPost('shipping_name'),
            'shipping_phone' => $this->request->getPost('shipping_phone'),
            'shipping_address' => $this->request->getPost('shipping_address'),
            'shipping_city' => $this->request->getPost('shipping_city'),
            'shipping_postal_code' => $this->request->getPost('shipping_postal_code'),
        ], true);

        foreach ($items as $item) {
            $product = $item['product'];
            $qty = (int) $item['qty'];

            $itemModel->insert([
                'cart_id' => $cartId,
                'product_id' => $product['id'],
                'quantity' => $qty,
                'price' => $product['price'],
            ]);

            $products->update($product['id'], [
                'stock' => max(0, (int) $product['stock'] - $qty),
            ]);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Checkout failed. Try again.');
        }

        session()->remove('cart');

        return redirect()->to('/products')->with('success', 'Checkout complete. Order #' . $cartId . ' saved.');
    }
}
