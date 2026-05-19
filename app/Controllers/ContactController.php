<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;

class ContactController extends BaseController
{
    public function show()
    {
        return view('contact/index', ['title' => 'Contact Us']);
    }

    public function submit()
    {
        $rules = [
            'name'    => 'required|min_length[2]|max_length[120]',
            'email'   => 'required|valid_email|max_length[160]',
            'subject' => 'permit_empty|max_length[160]',
            'message' => 'required|min_length[10]|max_length[2000]',
            // Honeypot — should always be empty for real users
            'website' => 'permit_empty|max_length[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Light bot deflection — honeypot field "website" must remain empty
        if ($this->request->getPost('website') !== null && $this->request->getPost('website') !== '') {
            // Pretend success to confuse bots, but skip storage
            return redirect()->to('/contact')->with('success', 'Thanks for getting in touch.');
        }

        (new ContactMessageModel())->insert([
            'name'       => trim((string) $this->request->getPost('name')),
            'email'      => strtolower(trim((string) $this->request->getPost('email'))),
            'subject'    => trim((string) $this->request->getPost('subject')) ?: null,
            'message'    => trim((string) $this->request->getPost('message')),
            'status'     => 'new',
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return redirect()->to('/contact')->with('success', 'Thanks. We will respond within 1–2 business days.');
    }
}
