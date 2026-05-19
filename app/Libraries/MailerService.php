<?php

namespace App\Libraries;

/**
 * Centralised mailer with graceful no-op fallback.
 *
 * Behaviour:
 *  - If Email config has no SMTPHost set (and protocol is "smtp"), or if the
 *    runtime is "testing", we skip actually sending and write the message to
 *    `writable/logs/mail.log` instead. This keeps developer flows + CI green
 *    without requiring SMTP credentials.
 *  - Real environments simply set the env vars and the service uses CI4's
 *    Email class transparently.
 *
 * All public methods return bool — true on success/dispatched, false on any
 * failure. Callers should NOT rely on mail delivery for correctness; they
 * should record their own audit / dispatch state separately.
 */
class MailerService
{
    /**
     * Send a templated HTML email. The template is rendered via CI4 view().
     *
     * @param string|array<int, string> $to
     */
    public function send(
        string|array $to,
        string $subject,
        string $template,
        array $data = [],
        ?string $textFallback = null
    ): bool {
        $html = view($template, $data);
        $text = $textFallback ?? trim(strip_tags($html));

        if ($this->shouldShortCircuit()) {
            $this->writeToLog($to, $subject, $html, $text);
            return true;
        }

        try {
            $email = service('email');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($html);
            $email->setAltMessage($text);
            $sent = (bool) $email->send(false);

            if (! $sent) {
                log_message('error', 'Mailer: send failed — ' . $email->printDebugger(['headers']));
            }
            return $sent;
        } catch (\Throwable $e) {
            log_message('error', 'Mailer: exception — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a plain-text email (no template). Useful for short notifications.
     *
     * @param string|array<int, string> $to
     */
    public function sendText(string|array $to, string $subject, string $body): bool
    {
        if ($this->shouldShortCircuit()) {
            $this->writeToLog($to, $subject, $body, $body);
            return true;
        }

        try {
            $email = service('email');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($body);
            return (bool) $email->send(false);
        } catch (\Throwable $e) {
            log_message('error', 'Mailer: text send failed — ' . $e->getMessage());
            return false;
        }
    }

    private function shouldShortCircuit(): bool
    {
        if (ENVIRONMENT === 'testing') return true;

        $config = config('Email');
        $smtpHost = trim((string) $config->SMTPHost);

        // No SMTP host configured → fall back to log file. This guards against
        // PHP's built-in mail() being unconfigured on local dev machines.
        if ($smtpHost === '') {
            return true;
        }
        return false;
    }

    /**
     * Write the would-be email into a dedicated log file so dev users can
     * inspect what got "sent" without setting up SMTP.
     *
     * @param string|array<int, string> $to
     */
    private function writeToLog(string|array $to, string $subject, string $html, string $text): void
    {
        $path = WRITEPATH . 'logs/mail.log';
        $rcpt = is_array($to) ? implode(', ', $to) : $to;
        $line = sprintf(
            "[%s] To: %s\nSubject: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $rcpt,
            $subject,
            str_repeat('-', 60),
            $text
        );
        @file_put_contents($path, $line, FILE_APPEND);
    }
}
