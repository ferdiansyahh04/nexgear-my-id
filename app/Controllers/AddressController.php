<?php

namespace App\Controllers;

use App\Models\AddressModel;

class AddressController extends BaseController
{
    public function index()
    {
        $userId = (int) session('user_id');
        $addresses = (new AddressModel())
            ->where('user_id', $userId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->findAll();

        return view('account/addresses', [
            'title'     => 'Address Book',
            'addresses' => $addresses,
        ]);
    }

    public function store()
    {
        $userId = (int) session('user_id');
        $data   = $this->validatedData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data['user_id'] = $userId;

        $model = new AddressModel();

        if (! empty($data['is_default'])) {
            $model->where('user_id', $userId)->set(['is_default' => 0])->update();
        } elseif ($model->where('user_id', $userId)->countAllResults() === 0) {
            // First address — promote to default automatically
            $data['is_default'] = 1;
        }

        $model->insert($data);
        return redirect()->to('/account/addresses')->with('success', 'Address saved.');
    }

    public function update(int $id)
    {
        $userId = (int) session('user_id');
        $model  = new AddressModel();
        $row    = $model->find($id);
        if (! $row || (int) $row['user_id'] !== $userId) {
            return redirect()->to('/account/addresses')->with('error', 'Address not found.');
        }

        $data = $this->validatedData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! empty($data['is_default'])) {
            $model->where('user_id', $userId)->set(['is_default' => 0])->update();
        }

        $model->update($id, $data);
        return redirect()->to('/account/addresses')->with('success', 'Address updated.');
    }

    public function delete(int $id)
    {
        $userId = (int) session('user_id');
        $model  = new AddressModel();
        $row    = $model->find($id);
        if ($row && (int) $row['user_id'] === $userId) {
            $model->delete($id);
        }
        return redirect()->to('/account/addresses')->with('success', 'Address removed.');
    }

    /**
     * AJAX hydrate one saved address into the checkout form.
     */
    public function fetch(int $id)
    {
        $userId = (int) session('user_id');
        $row = (new AddressModel())->find($id);
        if (! $row || (int) $row['user_id'] !== $userId) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error']);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'address' => [
                'name'        => $row['name'],
                'phone'       => $row['phone'],
                'address'     => $row['address'],
                'city'        => $row['city'],
                'postal_code' => $row['postal_code'],
            ],
        ]);
    }

    private function validatedData(): ?array
    {
        $rules = [
            'label'       => 'permit_empty|max_length[60]',
            'name'        => 'required|min_length[3]|max_length[120]',
            'phone'       => 'required|min_length[8]|max_length[20]|regex_match[/^[\d\+\-\s]+$/]',
            'address'     => 'required|min_length[10]|max_length[500]',
            'city'        => 'required|min_length[2]|max_length[100]',
            'postal_code' => 'required|min_length[3]|max_length[10]|regex_match[/^[\d\-]+$/]',
        ];

        if (! $this->validate($rules)) return null;

        return [
            'label'       => trim((string) $this->request->getPost('label')) ?: null,
            'name'        => trim((string) $this->request->getPost('name')),
            'phone'       => trim((string) $this->request->getPost('phone')),
            'address'     => trim((string) $this->request->getPost('address')),
            'city'        => trim((string) $this->request->getPost('city')),
            'postal_code' => trim((string) $this->request->getPost('postal_code')),
            'is_default'  => $this->request->getPost('is_default') ? 1 : 0,
        ];
    }
}
