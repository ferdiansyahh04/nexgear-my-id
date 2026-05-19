<?php

namespace App\Controllers;

use App\Libraries\AuditLogService;
use App\Libraries\StockAlertService;

class StockAlertController extends BaseController
{
    public function subscribe(int $productId)
    {
        $service = new StockAlertService();

        $email = (string) $this->request->getPost('email');
        if ($email === '' && session('is_logged_in')) {
            $email = (string) session('user_email');
        }

        $result = $service->subscribe($email, $productId, session('user_id') ? (int) session('user_id') : null);

        if ($result['ok']) {
            (new AuditLogService())->log('stock.alert_subscribe', [
                'target_type' => 'product',
                'target_id'   => $productId,
                'meta'        => ['email' => $email],
            ]);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'    => $result['ok'] ? 'success' : 'error',
                'message'   => $result['message'],
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
