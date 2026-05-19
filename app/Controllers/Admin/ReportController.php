<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\OrderStatusService;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Admin sales reports — interactive HTML view + CSV export + PDF invoice.
 *
 * Routes:
 *   GET  /admin/reports                    → interactive sales report (filters by date)
 *   GET  /admin/reports/export/csv         → download filtered orders as CSV
 *   GET  /admin/orders/{id}/invoice/pdf    → download single-order invoice PDF
 */
class ReportController extends BaseController
{
    public function index()
    {
        [$start, $end] = $this->resolveRange();

        $data = $this->collect($start, $end);

        return view('admin/reports/index', array_merge($data, [
            'title' => 'Sales Report',
            'start' => $start,
            'end'   => $end,
        ]));
    }

    public function exportCsv()
    {
        [$start, $end] = $this->resolveRange();

        $orders = db_connect()->table('cart')
            ->select('id, created_at, status, shipping_name, shipping_phone, shipping_city, total, discount, coupon_code')
            ->whereIn('status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
            ->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        $filename = 'nexgear-sales-' . $start . '_' . $end . '.csv';

        $headers = ['Order ID', 'Date', 'Status', 'Customer', 'Phone', 'City', 'Coupon', 'Discount', 'Total'];

        $output = fopen('php://temp', 'w+');
        // BOM so Excel detects UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);

        foreach ($orders as $row) {
            fputcsv($output, [
                '#' . $row['id'],
                $row['created_at'],
                $row['status'],
                $row['shipping_name'],
                $row['shipping_phone'],
                $row['shipping_city'],
                $row['coupon_code'] ?? '',
                $row['discount'] ?? 0,
                $row['total'],
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    /**
     * Per-order invoice as a printable PDF.
     */
    public function invoicePdf(int $orderId)
    {
        $db = db_connect();
        $order = $db->table('cart')->where('id', $orderId)->get()->getRowArray();
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order not found.');
        }

        $items = $db->table('cart_items')
            ->select('cart_items.*, products.name AS product_name, products.image AS product_image')
            ->join('products', 'products.id = cart_items.product_id', 'left')
            ->where('cart_id', $orderId)
            ->get()
            ->getResultArray();

        $statusInfo = OrderStatusService::labels()[$order['status']] ?? ['label' => ucfirst($order['status'])];

        $html = view('admin/reports/_invoice', [
            'order'      => $order,
            'items'      => $items,
            'statusInfo' => $statusInfo,
            'generatedAt' => date('d M Y, H:i'),
        ]);

        $opts = new Options();
        $opts->set('isRemoteEnabled', true);
        $opts->set('defaultFont', 'DejaVu Sans');

        $pdf = new Dompdf($opts);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $filename = 'invoice-' . $orderId . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdf->output());
    }

    /**
     * @return array{0:string,1:string} [start, end] in Y-m-d
     */
    private function resolveRange(): array
    {
        $start = (string) $this->request->getGet('start');
        $end   = (string) $this->request->getGet('end');

        if ($start === '' || ! strtotime($start)) {
            $start = (new \DateTimeImmutable('-29 days'))->format('Y-m-d');
        }
        if ($end === '' || ! strtotime($end)) {
            $end = (new \DateTimeImmutable('now'))->format('Y-m-d');
        }
        if (strtotime($start) > strtotime($end)) {
            [$start, $end] = [$end, $start];
        }
        return [$start, $end];
    }

    private function collect(string $start, string $end): array
    {
        $db = db_connect();

        $rangeStatuses = ['checked_out', 'paid', 'processing', 'shipped', 'delivered'];

        $totals = $db->table('cart')
            ->select('SUM(total) AS revenue, SUM(discount) AS discounts, COUNT(*) AS orders, AVG(total) AS aov')
            ->whereIn('status', $rangeStatuses)
            ->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59')
            ->get()->getRowArray();

        $byStatus = $db->table('cart')
            ->select('status, COUNT(*) AS c, SUM(total) AS s')
            ->whereIn('status', $rangeStatuses)
            ->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59')
            ->groupBy('status')
            ->get()->getResultArray();

        $topProducts = $db->table('cart_items AS ci')
            ->select('p.id, p.name, SUM(ci.quantity) AS units, SUM(ci.quantity * ci.price) AS revenue')
            ->join('cart', 'cart.id = ci.cart_id')
            ->join('products AS p', 'p.id = ci.product_id', 'left')
            ->whereIn('cart.status', $rangeStatuses)
            ->where('cart.created_at >=', $start . ' 00:00:00')
            ->where('cart.created_at <=', $end . ' 23:59:59')
            ->groupBy('ci.product_id')
            ->orderBy('revenue', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $orders = $db->table('cart')
            ->whereIn('status', $rangeStatuses)
            ->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59')
            ->orderBy('created_at', 'DESC')
            ->limit(100)
            ->get()->getResultArray();

        return [
            'totals'      => [
                'revenue'   => (float) ($totals['revenue'] ?? 0),
                'discounts' => (float) ($totals['discounts'] ?? 0),
                'orders'    => (int) ($totals['orders'] ?? 0),
                'aov'       => (float) ($totals['aov'] ?? 0),
            ],
            'byStatus'    => $byStatus,
            'topProducts' => $topProducts,
            'orders'      => $orders,
            'statusMap'   => OrderStatusService::labels(),
        ];
    }
}
