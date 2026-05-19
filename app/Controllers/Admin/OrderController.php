<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogService;
use App\Libraries\OrderStatusService;
use App\Models\CartModel;
use App\Models\CartItemModel;
use App\Models\ProductModel;

class OrderController extends BaseController
{
    public function index()
    {
        $filterStatus = (string) $this->request->getGet('status');

        $query = (new CartModel())
            ->whereNotIn('status', ['active'])
            ->orderBy('created_at', 'DESC');

        if ($filterStatus !== '' && array_key_exists($filterStatus, OrderStatusService::labels())) {
            $query->where('status', $filterStatus);
        }

        $orders = $query->findAll();

        return view('admin/orders/index', [
            'title'        => 'Order Management',
            'orders'       => $orders,
            'statusMap'    => OrderStatusService::labels(),
            'filterStatus' => $filterStatus,
        ]);
    }

    public function show(int $id)
    {
        $orderModel   = new CartModel();
        $itemModel    = new CartItemModel();
        $productModel = new ProductModel();

        $order = $orderModel->find($id);
        if (! $order) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Order not found');
        }

        $items = $itemModel->where('cart_id', $id)->findAll();

        // Hydrate products in one round-trip instead of one find() per item.
        if ($items !== []) {
            $productIds = array_values(array_unique(array_filter(
                array_map(static fn ($r) => (int) ($r['product_id'] ?? 0), $items)
            )));
            $byId = [];
            if ($productIds !== []) {
                foreach ($productModel->whereIn('id', $productIds)->findAll() as $row) {
                    $byId[(int) $row['id']] = $row;
                }
            }
            foreach ($items as &$item) {
                $item['product'] = $byId[(int) $item['product_id']] ?? null;
            }
            unset($item);
        }

        return view('admin/orders/show', [
            'title'              => 'Order Details #' . $id,
            'order'              => $order,
            'items'              => $items,
            'statusMap'          => OrderStatusService::labels(),
            'allowedTransitions' => OrderStatusService::allowedTransitions($order['status']),
            'timeline'           => OrderStatusService::timelineFor($order['status']),
        ]);
    }

    public function updateStatus(int $id)
    {
        $orderModel = new CartModel();
        $order      = $orderModel->find($id);
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order not found.');
        }

        $next = (string) $this->request->getPost('status');
        if (! OrderStatusService::canTransition($order['status'], $next)) {
            return redirect()->back()->with('error', "Cannot transition from {$order['status']} to {$next}.");
        }

        // Cancellation should restock items to keep inventory accurate.
        if ($next === 'cancelled') {
            $items = (new CartItemModel())->where('cart_id', $id)->findAll();
            $db    = db_connect();
            $db->transStart();
            foreach ($items as $item) {
                if ($item['product_id'] === null) continue;
                $db->table('products')
                    ->where('id', $item['product_id'])
                    ->set('stock', 'stock + ' . (int) $item['quantity'], false)
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
            }
            $orderModel->update($id, ['status' => $next]);
            $db->transComplete();

            if (! $db->transStatus()) {
                return redirect()->back()->with('error', 'Failed to cancel order.');
            }
        } else {
            $orderModel->update($id, ['status' => $next]);
        }

        (new AuditLogService())->log('order.status_change', [
            'target_type' => 'order',
            'target_id'   => $id,
            'meta'        => ['from' => $order['status'], 'to' => $next],
        ]);

        return redirect()->to('/admin/orders/' . $id)
            ->with('success', "Order status updated to: {$next}");
    }
}
