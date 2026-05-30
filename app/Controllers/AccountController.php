<?php

namespace App\Controllers;

use App\Libraries\OrderStatusService;
use App\Libraries\WishlistService;
use App\Models\AddressModel;
use App\Models\CartItemModel;
use App\Models\CartModel;
use App\Models\ProductModel;

/**
 * Customer-facing account area: order history + detail + wishlist.
 * Mounted under `/account/*` with the `auth` filter.
 */
class AccountController extends BaseController
{
    public function index()
    {
        $userId = (int) session('user_id');
        $db     = db_connect();

        // Recent orders (preview, max 3)
        $recentOrders = (new CartModel())
            ->where('user_id', $userId)
            ->whereNotIn('status', ['active'])
            ->orderBy('created_at', 'DESC')
            ->limit(3)
            ->find();

        // Aggregate counts in a single query each
        $orderTotals = $db->table('cart')
            ->select('COUNT(*) AS orders, COALESCE(SUM(total),0) AS total_spent')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['active', 'cancelled'])
            ->get()->getRowArray();

        $wishlistCount = (new WishlistService())->count();
        $addressCount  = (int) (new AddressModel())->where('user_id', $userId)->countAllResults();

        // Default address card preview
        $defaultAddress = (new AddressModel())
            ->where('user_id', $userId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->first();

        return view('account/index', [
            'title'          => 'Your Account',
            'name'           => session('user_name'),
            'email'          => session('user_email'),
            'recentOrders'   => $recentOrders,
            'totalOrders'    => (int) ($orderTotals['orders'] ?? 0),
            'totalSpent'     => (float) ($orderTotals['total_spent'] ?? 0),
            'wishlistCount'  => $wishlistCount,
            'addressCount'   => $addressCount,
            'defaultAddress' => $defaultAddress,
            'statusMap'      => OrderStatusService::labels(),
        ]);
    }

    public function orders()
    {
        $userId = (int) session('user_id');

        $orders = (new CartModel())
            ->where('user_id', $userId)
            ->whereNotIn('status', ['active'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // Lightweight "first item image" for each order (preview thumb)
        if ($orders !== []) {
            $orderIds = array_map(static fn ($o) => (int) $o['id'], $orders);
            $preview  = (new CartItemModel())
                ->select('cart_id, product_id, quantity')
                ->whereIn('cart_id', $orderIds)
                ->orderBy('id', 'ASC')
                ->find();

            $byCart = [];
            foreach ($preview as $row) {
                $byCart[(int) $row['cart_id']][] = $row;
            }

            $productIds = array_unique(array_map(static fn ($r) => (int) $r['product_id'], $preview));
            $products   = $productIds === [] ? [] : (new ProductModel())->whereIn('id', $productIds)->findAll();
            $productById = [];
            foreach ($products as $p) $productById[(int) $p['id']] = $p;

            foreach ($orders as &$order) {
                $items = $byCart[(int) $order['id']] ?? [];
                $order['item_count']    = array_sum(array_map(static fn ($r) => (int) $r['quantity'], $items));
                $order['unique_count']  = count($items);
                $first = $items[0] ?? null;
                $order['preview_image'] = $first && isset($productById[(int) $first['product_id']])
                    ? $productById[(int) $first['product_id']]['image']
                    : null;
            }
            unset($order);
        }

        return view('account/orders', [
            'title'     => 'Order History',
            'orders'    => $orders,
            'pager'     => (new CartModel())->pager,
            'statusMap' => OrderStatusService::labels(),
        ]);
    }

    public function orderDetail(int $id)
    {
        $userId = (int) session('user_id');

        $order = (new CartModel())->find($id);
        if (! $order || (int) $order['user_id'] !== $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Order not found');
        }

        $items = (new CartItemModel())->where('cart_id', $id)->findAll();
        if ($items !== []) {
            $productIds = array_unique(array_map(static fn ($r) => (int) $r['product_id'], $items));
            $products   = (new ProductModel())->whereIn('id', $productIds)->findAll();
            $byId       = [];
            foreach ($products as $p) $byId[(int) $p['id']] = $p;

            foreach ($items as &$item) {
                $item['product'] = $byId[(int) $item['product_id']] ?? null;
            }
            unset($item);
        }

        return view('account/order_detail', [
            'title'           => 'Order #' . $id,
            'order'           => $order,
            'items'           => $items,
            'statusMap'       => OrderStatusService::labels(),
            'timeline'        => OrderStatusService::timelineFor($order['status']),
            'paymentsEnabled' => (new \App\Libraries\DuitkuService())->isEnabled(),
        ]);
    }

    public function wishlist()
    {
        return view('wishlist/index', [
            'title' => 'Your Wishlist',
            'items' => (new \App\Libraries\WishlistService())->items(),
        ]);
    }
}
