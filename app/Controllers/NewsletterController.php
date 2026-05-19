<?php

namespace App\Controllers;

use App\Models\NewsletterSubscriberModel;

class NewsletterController extends BaseController
{
    /**
     * Quick subscribe from the footer form. Idempotent: a re-subscribe with
     * the same email is treated as success and (re-)issues the confirm token.
     */
    public function subscribe()
    {
        $rules = ['email' => 'required|valid_email|max_length[160]'];
        if (! $this->validate($rules)) {
            $msg = 'Please provide a valid email address.';
            return $this->reply(false, $msg);
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $token = bin2hex(random_bytes(16));

        $model    = new NewsletterSubscriberModel();
        $existing = $model->where('email', $email)->first();
        $shouldEmail = false;

        if ($existing) {
            $update = ['unsubscribed_at' => null];
            if ((int) ($existing['confirmed'] ?? 0) !== 1) {
                $update['token'] = $token;
                $shouldEmail = true;
            }
            $model->update($existing['id'], $update);
            $msg = (int) $existing['confirmed'] === 1
                ? "You're already subscribed."
                : 'Subscription refreshed. Check your inbox to confirm.';
        } else {
            $model->insert([
                'email'     => $email,
                'confirmed' => 0,
                'token'     => $token,
            ]);
            $msg = 'Thanks for subscribing. Check your inbox to confirm.';
            $shouldEmail = true;
        }

        if ($shouldEmail) {
            $confirmUrl = base_url('/newsletter/confirm') . '?email=' . urlencode($email) . '&token=' . urlencode($token);
            (new \App\Libraries\MailerService())->send(
                $email,
                'Confirm your NexGear subscription',
                'emails/newsletter_confirm',
                ['confirmUrl' => $confirmUrl]
            );
        }

        return $this->reply(true, $msg);
    }

    /**
     * Double opt-in confirmation link target.
     */
    public function confirm()
    {
        $token = (string) $this->request->getGet('token');
        $email = strtolower(trim((string) $this->request->getGet('email')));

        if ($token === '' || $email === '') {
            return redirect()->to('/')->with('error', 'Invalid confirmation link.');
        }

        $model = new NewsletterSubscriberModel();
        $row   = $model->where(['email' => $email, 'token' => $token])->first();

        if (! $row) {
            return redirect()->to('/')->with('error', 'Confirmation link is invalid or has expired.');
        }

        $model->update($row['id'], [
            'confirmed' => 1,
            'token'     => null,
        ]);

        return redirect()->to('/')->with('success', 'Thanks. Your subscription is confirmed.');
    }

    public function unsubscribe()
    {
        $email = strtolower(trim((string) $this->request->getGet('email')));
        if ($email !== '') {
            $model = new NewsletterSubscriberModel();
            $row   = $model->where('email', $email)->first();
            if ($row) {
                $model->update($row['id'], ['unsubscribed_at' => date('Y-m-d H:i:s')]);
            }
        }
        return redirect()->to('/')->with('success', 'You have been unsubscribed.');
    }

    private function reply(bool $ok, string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'    => $ok ? 'success' : 'error',
                'message'   => $message,
                'csrfName'  => csrf_token(),
                'csrfToken' => csrf_hash(),
            ]);
        }
        return redirect()->back()->with($ok ? 'success' : 'error', $message);
    }
}
