<?php

namespace App\Controllers;

use App\Models\ReviewModel;

class ReviewController extends BaseController
{
    /**
     * Submit a review. Requires the user to have purchased the product.
     */
    public function store(int $productId)
    {
        $userId = (int) session('user_id');
        if ($userId < 1) {
            return redirect()->to('/login')->with('error', 'Please sign in to review.');
        }

        $rules = [
            'rating' => 'required|integer|greater_than[0]|less_than[6]',
            'title'  => 'permit_empty|max_length[160]',
            'body'   => 'permit_empty|max_length[1500]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $reviewModel = new ReviewModel();

        // Already reviewed? Update instead of insert (one row per (user, product)).
        $existing = $reviewModel
            ->where(['user_id' => $userId, 'product_id' => $productId])
            ->first();

        // Verified purchase guard: at minimum the user needs an order containing this product
        $verified = (bool) db_connect()
            ->table('cart_items')
            ->join('cart', 'cart.id = cart_items.cart_id')
            ->where('cart.user_id', $userId)
            ->where('cart_items.product_id', $productId)
            ->whereIn('cart.status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->countAllResults();

        if (! $verified) {
            return redirect()->back()->with('error', 'You can only review products you have purchased.');
        }

        $payload = [
            'user_id'           => $userId,
            'product_id'        => $productId,
            'rating'            => (int) $this->request->getPost('rating'),
            'title'             => trim((string) $this->request->getPost('title')) ?: null,
            'body'              => trim((string) $this->request->getPost('body')) ?: null,
            'verified_purchase' => 1,
        ];

        if ($existing) {
            $reviewModel->update($existing['id'], $payload);
            $msg = 'Your review has been updated.';
        } else {
            $reviewModel->insert($payload);
            $msg = 'Thanks for your review.';
        }

        return redirect()->to('/products/' . $productId . '#reviews')->with('success', $msg);
    }

    public function delete(int $reviewId)
    {
        $userId = (int) session('user_id');
        if ($userId < 1) return redirect()->to('/login');

        $reviewModel = new ReviewModel();
        $row = $reviewModel->find($reviewId);
        if (! $row) return redirect()->back()->with('error', 'Review not found.');

        // Owner or admin can delete
        if ((int) $row['user_id'] !== $userId && session('role') !== 'admin') {
            return redirect()->back()->with('error', 'Not allowed.');
        }

        $reviewModel->delete($reviewId);
        return redirect()->to('/products/' . $row['product_id'] . '#reviews')->with('success', 'Review removed.');
    }
}
