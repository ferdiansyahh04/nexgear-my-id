<?php

namespace App\Controllers;

use App\Models\ProductModel;

class CartController extends BaseController
{
    public function index()
    {
        return view('cart/index', [
            'title' => 'Cart',
            'items' => $this->cartItems(),
            'total' => $this->cartTotal(),
        ]);
    }

    public function add(int $productId)
    {
        $product = (new ProductModel())->find($productId);

        if (! $product || (int) $product['stock'] < 1) {
            return redirect()->back()->with('error', 'Product unavailable.');
        }

        $cart = session('cart') ?? [];
        $current = (int) ($cart[$productId] ?? 0);
        $cart[$productId] = min($current + 1, (int) $product['stock']);
        session()->set('cart', $cart);
        $totalItems = array_sum($cart);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Added to cart.',
                'cartCount' => $totalItems
            ]);
        }

        return redirect()->back()->with('success', 'Added to cart.');
    }

    public function update()
    {
        $quantities = (array) $this->request->getPost('qty');
        $products = new ProductModel();
        $cart = [];

        foreach ($quantities as $productId => $qty) {
            $product = $products->find((int) $productId);
            $amount = max(0, (int) $qty);

            if ($product && $amount > 0) {
                $cart[(int) $productId] = min($amount, (int) $product['stock']);
            }
        }

        session()->set('cart', $cart);

        return redirect()->to('/cart')->with('success', 'Cart updated.');
    }

    public function remove(int $productId)
    {
        $cart = session('cart') ?? [];
        unset($cart[$productId]);
        session()->set('cart', $cart);

        return redirect()->to('/cart')->with('success', 'Item removed.');
    }

    public function clear()
    {
        session()->remove('cart');

        return redirect()->to('/cart')->with('success', 'Cart cleared.');
    }

    protected function cartItems(): array
    {
        $cart = session('cart') ?? [];
        if ($cart === []) {
            return [];
        }

        $products = (new ProductModel())->whereIn('id', array_keys($cart))->findAll();
        $items = [];

        foreach ($products as $product) {
            $qty = (int) ($cart[$product['id']] ?? 0);
            if ($qty < 1) {
                continue;
            }

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'subtotal' => $qty * (float) $product['price'],
            ];
        }

        return $items;
    }

    protected function cartTotal(): float
    {
        return array_sum(array_column($this->cartItems(), 'subtotal'));
    }
}
