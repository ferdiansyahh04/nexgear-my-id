<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\OrderStatusService;
use App\Models\CartModel;
use App\Models\ContactMessageModel;
use App\Models\NewsletterSubscriberModel;
use App\Models\ProductModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        // ── Time windows ─────────────────────────────────────
        $now     = new \DateTimeImmutable('now');
        $today   = $now->format('Y-m-d');
        $monthAgo = $now->modify('-30 days')->format('Y-m-d 00:00:00');

        // ── Revenue (excluding cancelled / active) ───────────
        $revenueRow = $db->table('cart')
            ->select('SUM(total) AS total, COUNT(*) AS orders')
            ->whereIn('status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->get()->getRowArray();
        $totalRevenue = (float) ($revenueRow['total'] ?? 0);
        $totalOrders  = (int) ($revenueRow['orders'] ?? 0);

        $monthRevenueRow = $db->table('cart')
            ->select('SUM(total) AS total, COUNT(*) AS orders')
            ->whereIn('status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->where('created_at >=', $monthAgo)
            ->get()->getRowArray();
        $monthRevenue = (float) ($monthRevenueRow['total'] ?? 0);
        $monthOrders  = (int) ($monthRevenueRow['orders'] ?? 0);

        // ── Inventory snapshot ───────────────────────────────
        $productModel = new ProductModel();
        $products     = $productModel->findAll();
        $lowStock     = array_filter($products, static fn ($p) => (int) $p['stock'] > 0 && (int) $p['stock'] <= 5);
        $outOfStock   = array_filter($products, static fn ($p) => (int) $p['stock'] <= 0);
        $inventoryValue = array_reduce(
            $products,
            static fn ($carry, $p) => $carry + ((float) $p['price'] * (int) $p['stock']),
            0.0
        );

        // ── Customer counts ──────────────────────────────────
        $customerCount = (int) $db->table('users')->where('role', 'user')->countAllResults();

        // ── Pending tasks (status = checked_out / paid waiting processing) ──
        $pendingByStatus = [];
        $rows = $db->table('cart')
            ->select('status, COUNT(*) AS c')
            ->whereIn('status', ['checked_out', 'paid', 'processing', 'shipped'])
            ->groupBy('status')
            ->get()->getResultArray();
        foreach ($rows as $row) $pendingByStatus[$row['status']] = (int) $row['c'];

        // ── Daily revenue chart (last 30 days) ───────────────
        $dailyRows = $db->table('cart')
            ->select("DATE(created_at) AS day, SUM(total) AS revenue, COUNT(*) AS orders")
            ->whereIn('status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->where('created_at >=', $monthAgo)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get()->getResultArray();

        // Build complete 30-day series even when there are gap days
        $chartLabels  = [];
        $chartRevenue = [];
        $chartOrders  = [];
        $byDay = [];
        foreach ($dailyRows as $r) $byDay[$r['day']] = $r;
        for ($i = 29; $i >= 0; $i--) {
            $d = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
            $chartLabels[]  = (new \DateTimeImmutable($d))->format('d M');
            $chartRevenue[] = (float) ($byDay[$d]['revenue'] ?? 0);
            $chartOrders[]  = (int)   ($byDay[$d]['orders']  ?? 0);
        }

        // ── Top products (by units sold all-time, post-checkout) ──
        $topProducts = $db->table('cart_items AS ci')
            ->select('ci.product_id, p.name, p.price, p.image, SUM(ci.quantity) AS units, SUM(ci.quantity * ci.price) AS revenue')
            ->join('cart', 'cart.id = ci.cart_id')
            ->join('products AS p', 'p.id = ci.product_id', 'left')
            ->whereIn('cart.status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->groupBy('ci.product_id')
            ->orderBy('units', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // ── Recent orders ────────────────────────────────────
        $recentOrders = (new CartModel())
            ->whereNotIn('status', ['active'])
            ->orderBy('created_at', 'DESC')
            ->limit(8)
            ->find();

        // ── Engagement ───────────────────────────────────────
        $newsletterCount = (int) (new NewsletterSubscriberModel())
            ->where('confirmed', 1)
            ->whereNull('unsubscribed_at')
            ->countAllResults();
        $newMessages = (int) (new ContactMessageModel())
            ->where('status', 'new')
            ->countAllResults();

        return view('admin/dashboard/index', [
            'title'           => 'Dashboard',
            'totalRevenue'    => $totalRevenue,
            'totalOrders'     => $totalOrders,
            'monthRevenue'    => $monthRevenue,
            'monthOrders'     => $monthOrders,
            'productCount'    => count($products),
            'lowStock'        => array_values($lowStock),
            'outOfStockCount' => count($outOfStock),
            'inventoryValue'  => $inventoryValue,
            'customerCount'   => $customerCount,
            'pendingByStatus' => $pendingByStatus,
            'chartLabels'     => $chartLabels,
            'chartRevenue'    => $chartRevenue,
            'chartOrders'     => $chartOrders,
            'topProducts'     => $topProducts,
            'recentOrders'    => $recentOrders,
            'statusMap'       => OrderStatusService::labels(),
            'newsletterCount' => $newsletterCount,
            'newMessages'     => $newMessages,
        ]);
    }
}
