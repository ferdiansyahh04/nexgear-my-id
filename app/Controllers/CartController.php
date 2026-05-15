<?php

namespace App\Controllers;

use App\Libraries\CartService;
use App\Models\ProductModel;

class CartController extends BaseController
{
    protected CartService $cart;

    public function __construct()
    {
        $this->cart = new CartService();
    }

    public function index()
    {
        $items = $this->cart->items();

        return view('cart/index', [
            'title' => 'Cart',
            'items' => $items,
            'total' => $this->cart->total($items),
        ]);
    }

    public function add(int $productId)
    {
        $product = (new ProductModel())->find($productId);

        if (! $product || (int) $product['stock'] < 1) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Product unavailable.', 'csrfName' => csrf_token(), 'csrfToken' => csrf_hash()]);
            }
            return redirect()->back()->with('error', 'Product unavailable.');
        }

        $cart = session('cart') ?? [];
        $current = (int) ($cart[$productId] ?? 0);
        $cart[$productId] = min($current + 1, (int) $product['stock']);
        session()->set('cart', $cart);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Added to selection.',
                'cartCount' => array_sum($cart),
                'html' => view('partials/offcanvas_cart', ['cartData' => (new CartService())->items()]),
                'csrfName' => csrf_token(),
                'csrfToken' => csrf_hash(),
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

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Removed from selection.',
                'cartCount' => array_sum($cart),
                'html' => view('partials/offcanvas_cart', ['cartData' => (new CartService())->items()]),
                'csrfName' => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        return redirect()->to('/cart')->with('success', 'Item removed.');
    }

    public function clear()
    {
        session()->remove('cart');

        return redirect()->to('/cart')->with('success', 'Cart cleared.');
    }
}
