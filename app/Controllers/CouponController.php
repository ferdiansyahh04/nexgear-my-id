<?php

namespace App\Controllers;

use App\Libraries\CartService;
use App\Libraries\CouponService;

class CouponController extends BaseController
{
    public function apply()
    {
        $code     = (string) $this->request->getPost('code');
        $cart     = new CartService();
        $coupon   = new CouponService();
        $subtotal = $cart->total();

        $result = $coupon->apply($code, $subtotal);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'    => $result['valid'] ? 'success' : 'error',
                'message'   => $result['message'],
                'discount'  => $result['discount'] ?? 0,
                'subtotal'  => $subtotal,
                'total'     => $subtotal - ($result['discount'] ?? 0),
                'code'      => $coupon->applied(),
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with($result['valid'] ? 'success' : 'error', $result['message']);
    }

    public function remove()
    {
        (new CouponService())->clear();

        if ($this->request->isAJAX()) {
            $cart = new CartService();
            $subtotal = $cart->total();
            return $this->response->setJSON([
                'status'    => 'success',
                'message'   => 'Coupon removed.',
                'subtotal'  => $subtotal,
                'total'     => $subtotal,
                'discount'  => 0,
                'code'      => null,
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with('success', 'Coupon removed.');
    }
}
