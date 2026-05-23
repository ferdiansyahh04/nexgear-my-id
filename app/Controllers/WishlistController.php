<?php

namespace App\Controllers;

use App\Libraries\WishlistService;

class WishlistController extends BaseController
{
    protected WishlistService $wishlist;

    public function __construct()
    {
        $this->wishlist = new WishlistService();
    }

    /**
     * Customer-facing wishlist page.
     */
    public function index()
    {
        return view('wishlist/index', [
            'title' => 'Your Wishlist',
            'items' => $this->wishlist->items(),
        ]);
    }

    /**
     * Toggle a product in/out of the wishlist.
     * Returns JSON for AJAX, redirect otherwise.
     */
    public function toggle(int $productId)
    {
        $state = $this->wishlist->toggle($productId);
        if ($state === 'invalid') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status'    => 'error',
                    'state'     => $state,
                    'count'     => $this->wishlist->count(),
                    'productId' => $productId,
                    'message'   => 'Product not found.',
                    'csrfName'  => csrf_token(),
                    'csrfToken' => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('error', 'Product not found.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'    => 'success',
                'state'     => $state,
                'count'     => $this->wishlist->count(),
                'productId' => $productId,
                'message'   => $state === 'added' ? 'Saved to your wishlist.' : 'Removed from wishlist.',
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with(
            'success',
            $state === 'added' ? 'Saved to your wishlist.' : 'Removed from wishlist.'
        );
    }
}
