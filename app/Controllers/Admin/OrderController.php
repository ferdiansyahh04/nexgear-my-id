<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CartModel;
use App\Models\CartItemModel;
use App\Models\ProductModel;

class OrderController extends BaseController
{
    public function index()
    {
        $orderModel = new CartModel();
        // Get checked out carts with user info
        $orders = $orderModel->where('status', 'checked_out')
                             ->orderBy('created_at', 'DESC')
                             ->findAll();

        return view('admin/orders/index', [
            'title' => 'Order Management',
            'orders' => $orders
        ]);
    }

    public function show(int $id)
    {
        $orderModel = new CartModel();
        $itemModel = new CartItemModel();
        $productModel = new ProductModel();

        $order = $orderModel->find($id);
        if (!$order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Order not found');
        }

        $items = $itemModel->where('cart_id', $id)->findAll();
        
        // Enhance items with product data
        foreach ($items as &$item) {
            $item['product'] = $productModel->find($item['product_id']);
        }

        return view('admin/orders/show', [
            'title' => 'Order Details #' . $id,
            'order' => $order,
            'items' => $items
        ]);
    }
}
