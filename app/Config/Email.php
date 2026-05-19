<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Email config — driven from `.env` so SMTP credentials never live in code.
 *
 * Set in your .env (example for Mailtrap dev sandbox):
 *
 *   email.protocol      = smtp
 *   email.fromEmail     = no-reply@nexgear.test
 *   email.fromName      = NexGear Store
 *   email.SMTPHost      = sandbox.smtp.mailtrap.io
 *   email.SMTPUser      = xxxxxxxxxxxx
 *   email.SMTPPass      = xxxxxxxxxxxx
 *   email.SMTPPort      = 2525
 *   email.SMTPCrypto    = tls
 *   email.mailType      = html
 *
 * Leave SMTPHost empty to fall back to PHP's built-in `mail()`.
 */
class Email extends BaseConfig
{
    public string $fromEmail  = 'no-reply@nexgear.test';
    public string $fromName   = 'NexGear Store';
    public string $recipients = '';

    public string $userAgent = 'NexGear/Mailer';

    public string $protocol = 'mail';
    public string $mailPath = '/usr/sbin/sendmail';

    public string $SMTPHost       = '';
    public string $SMTPAuthMethod = 'login';
    public string $SMTPUser       = '';
    public string $SMTPPass       = '';
    public int    $SMTPPort       = 587;
    public int    $SMTPTimeout    = 8;
    public bool   $SMTPKeepAlive  = false;
    public string $SMTPCrypto     = 'tls';

    public bool   $wordWrap = true;
    public int    $wrapChars = 96;
    public string $mailType = 'html';
    public string $charset  = 'UTF-8';
    public bool   $validate = true;
    public int    $priority = 3;

    public string $CRLF    = "\r\n";
    public string $newline = "\r\n";

    public bool $BCCBatchMode = false;
    public int  $BCCBatchSize = 200;

    public bool $DSN = false;
}
