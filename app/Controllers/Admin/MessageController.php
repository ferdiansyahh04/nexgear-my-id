<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContactMessageModel;

class MessageController extends BaseController
{
    public function index()
    {
        $filter = (string) $this->request->getGet('status');
        $model  = new ContactMessageModel();

        if (in_array($filter, ['new', 'read', 'archived'], true)) {
            $model->where('status', $filter);
        }

        $messages = $model->orderBy('created_at', 'DESC')->paginate(15);

        return view('admin/messages/index', [
            'title'    => 'Messages',
            'messages' => $messages,
            'pager'    => $model->pager,
            'filter'   => $filter,
        ]);
    }

    public function show(int $id)
    {
        $model = new ContactMessageModel();
        $msg   = $model->find($id);
        if (! $msg) {
            return redirect()->to('/admin/messages')->with('error', 'Message not found.');
        }

        if ($msg['status'] === 'new') {
            $model->update($id, ['status' => 'read']);
            $msg['status'] = 'read';
        }

        return view('admin/messages/show', [
            'title'   => 'Message #' . $id,
            'message' => $msg,
        ]);
    }

    public function status(int $id)
    {
        $next = (string) $this->request->getPost('status');
        if (! in_array($next, ['new', 'read', 'archived'], true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        (new ContactMessageModel())->update($id, ['status' => $next]);
        return redirect()->back()->with('success', 'Status updated.');
    }
}
