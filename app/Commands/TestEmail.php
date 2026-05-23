<?php

namespace App\Commands;

use App\Libraries\MailerService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Smoke-test the configured mailer by sending a one-off message.
 *
 * Usage:
 *   php spark email:test someone@example.com
 *   php spark email:test someone@example.com "Optional subject"
 *
 * Honours the same MailerService used by the rest of the app so we exercise
 * the real send pipeline (SMTP fallback to log file when SMTP not configured).
 */
class TestEmail extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'email:test';
    protected $description = 'Send a smoke-test email through the configured mailer.';
    protected $usage       = 'email:test <recipient> [subject]';
    protected $arguments   = [
        'recipient' => 'Email address to send the test message to.',
        'subject'   => 'Optional subject (defaults to "NexGear SMTP test").',
    ];

    public function run(array $params)
    {
        $to      = $params[0] ?? CLI::prompt('Recipient email');
        $subject = $params[1] ?? 'NexGear SMTP test';

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            CLI::error("Invalid email: {$to}");
            return;
        }

        $body = '<h2 style="font-family:sans-serif;">It works.</h2>'
            . '<p style="font-family:sans-serif;">This is a NexGear SMTP smoke test sent at <strong>'
            . date('Y-m-d H:i:s')
            . '</strong>.</p>'
            . '<p style="font-family:sans-serif;color:#666;font-size:13px;">'
            . 'If this landed in your inbox, the production mailer is wired up correctly.'
            . '</p>';

        // Use MailerService::sendText so we exercise the real config/short-
        // circuit logic without needing a dedicated view template.
        $ok = (new MailerService())->sendText($to, $subject, strip_tags($body) . "\n\n(plain-text fallback)");

        if ($ok) {
            CLI::write("[OK] Test email dispatched to {$to}", 'green');
        } else {
            CLI::error('[FAIL] MailerService returned false. Check writable/logs/log-*.log for the SMTP debug trace.');
        }
    }
}
