<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class AuditController extends BaseController
{
    public function index()
    {
        $model = new AuditLogModel();

        $action = trim((string) $this->request->getGet('action'));
        $actor  = trim((string) $this->request->getGet('actor'));

        if ($action !== '') $model->like('action', $action);
        if ($actor !== '')  $model->like('actor_label', $actor);

        $logs = $model->orderBy('created_at', 'DESC')->paginate(30);

        // Distinct action keys for the filter dropdown
        $actions = array_map(
            static fn ($r) => $r['action'],
            db_connect()->table('audit_logs')->select('action')->distinct()->get()->getResultArray()
        );

        return view('admin/audit/index', [
            'title'   => 'Audit Log',
            'logs'    => $logs,
            'pager'   => $model->pager,
            'action'  => $action,
            'actor'   => $actor,
            'actions' => $actions,
        ]);
    }
}
