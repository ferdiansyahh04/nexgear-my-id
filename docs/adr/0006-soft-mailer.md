# ADR 0006 — Soft-failing MailerService

- **Status:** Accepted
- **Date:** 2026-05-19

## Context

Three flows need to send email: order confirmation (B1), abandoned cart reminder (B19), back-in-stock notification (B8), and newsletter double-opt-in (B11). The naïve approach is to call `service('email')` directly inside controllers and commands. The problems with that:

1. **Local dev** rarely has SMTP configured. Cron jobs would fail every time the dev runs them.
2. **CI / PHPUnit** definitely doesn't have SMTP. Tests would either skip mail-touching paths or hit a real inbox.
3. **Examiner experience.** They run `php spark cart:remind-abandoned` to see the cron flow, and a stack trace because PHP `mail()` isn't configured would be a poor demo.

## Decision

A single `App\Libraries\MailerService` wraps CodeIgniter's email service with **graceful degradation**:

```php
private function shouldShortCircuit(): bool
{
    if (ENVIRONMENT === 'testing') return true;

    $config = config('Email');
    if (trim($config->SMTPHost) === '') return true;

    return false;
}
```

- **No SMTP host configured** → render the template, append to `writable/logs/mail.log`, return `true`.
- **`CI_ENVIRONMENT === 'testing'`** → same: log, never spam a real inbox.
- **SMTP configured** → use CI4's email service normally.

All callers treat the boolean return as **dispatch attempt result, not delivery confirmation**. Critical state (order placed, alert dispatched, audit entry) is written in the database BEFORE the mail call, so a failed send doesn't break correctness.

## Consequences

- **Examiner-friendly.** They can demo the abandoned cart reminder, see the cron-style log output, and read the would-be email body in `writable/logs/mail.log` without setting up SMTP.
- **CI-friendly.** Tests can exercise paths that send email without flaking on transport.
- **Production-ready.** Add SMTP env vars and the same code paths now actually send. No behaviour switch needed.
- **Templates are real HTML.** They live in `app/Views/emails/` with a shared `_layout.php` shell. We didn't degrade them to plain text just because dev logs them.
- **Trade-off:** users won't receive their welcome email until SMTP is wired. We accept this for an academic project. A real product would block the merge until a transactional mail provider is configured.
