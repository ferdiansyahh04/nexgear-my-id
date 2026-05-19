<?php

namespace App\Controllers;

use App\Libraries\AbandonedCartService;
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
        $items    = $this->cart->items();
        $subtotal = $this->cart->subtotal($items);
        $discount = $this->cart->discount($items);
        $coupon   = (new \App\Libraries\CouponService())->applied();

        return view('cart/index', [
            'title'    => 'Cart',
            'items'    => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => $subtotal - $discount,
            'coupon'   => $coupon,
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

        (new AbandonedCartService())->snapshot();

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

        (new AbandonedCartService())->snapshot();

        return redirect()->to('/cart')->with('success', 'Cart updated.');
    }

    /**
     * AJAX-only — adjust a single line item's quantity.
     * Accepts POST { delta: -1|+1 } OR { qty: <int> }.
     */
    public function updateQty(int $productId)
    {
        $product = (new ProductModel())->find($productId);

        if (! $product) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'    => 'error',
                'message'   => 'Product not found.',
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        $cart    = session('cart') ?? [];
        $current = (int) ($cart[$productId] ?? 0);
        $stock   = (int) $product['stock'];

        $rawQty   = $this->request->getPost('qty');
        $rawDelta = $this->request->getPost('delta');

        if ($rawQty !== null && $rawQty !== '') {
            $next = max(0, (int) $rawQty);
        } elseif ($rawDelta !== null && $rawDelta !== '') {
            $next = max(0, $current + (int) $rawDelta);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'status'    => 'error',
                'message'   => 'Missing qty or delta.',
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        $clamped = min($next, $stock);

        if ($clamped < 1) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $clamped;
        }

        session()->set('cart', $cart);

        $service = new CartService();
        $items   = $service->items();

        (new AbandonedCartService())->snapshot();

        $line = null;
        foreach ($items as $item) {
            if ((int) $item['product']['id'] === $productId) {
                $line = [
                    'qty'      => (int) $item['qty'],
                    'subtotal' => (float) $item['subtotal'],
                ];
                break;
            }
        }

        return $this->response->setJSON([
            'status'    => 'success',
            'message'   => $clamped < 1 ? 'Item removed.' : 'Quantity updated.',
            'productId' => $productId,
            'qty'       => $clamped,
            'stock'     => $stock,
            'capped'    => $next > $stock,
            'line'      => $line,
            'cartCount' => array_sum($cart),
            'cartTotal' => $service->total($items),
            'html'      => view('partials/offcanvas_cart', ['cartData' => $items]),
            'csrfName'  => csrf_token(),
            'csrfToken' => csrf_hash(),
        ]);
    }

    public function remove(int $productId)
    {
        $cart = session('cart') ?? [];
        unset($cart[$productId]);
        session()->set('cart', $cart);

        (new AbandonedCartService())->snapshot();

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

        (new AbandonedCartService())->clearForUser((int) (session('user_id') ?? 0));

        return redirect()->to('/cart')->with('success', 'Cart cleared.');
    }
}
