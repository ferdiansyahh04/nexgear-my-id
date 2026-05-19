# NexGear Storefront — Project Report

A capstone-style write-up of the project that complements the README. Where the README answers *what is this?* and *how do I run it?*, this document answers *why was it built this way?* and *what did we learn?*.

---

## 1. Problem & Scope

The brief asked for a "modern e-commerce web application" implemented in PHP. We chose **gaming hardware** as the vertical because:

- It carries a strong visual identity (clean editorial photography fits the brutalist aesthetic we wanted to explore).
- Real-world parallels exist — NuPhy, Logitech G, Razer — so the UX patterns are familiar.
- Stock-level, multi-image, and reviews features that examiners look for map naturally onto gaming gear.

The MVP scope expanded from a basic catalog into **15 frontend interactions and 21 backend features** (see the README "Feature Matrix"). The dominant theme: build something that feels like a real product, not a course assignment.

## 2. Stack & Why

| Layer | Choice | ADR |
|---|---|---|
| Framework | CodeIgniter 4.7 | [ADR-0001](./adr/0001-codeigniter-4.md) |
| Domain logic | Service classes in `app/Libraries/` | [ADR-0002](./adr/0002-service-layer.md) |
| Frontend | Server-rendered HTML + vanilla JS | [ADR-0003](./adr/0003-progressive-enhancement-frontend.md) |
| Order modelling | `cart` table doubles as `orders` | [ADR-0004](./adr/0004-cart-as-orders.md) |
| Authorization | Three-tier role enum | [ADR-0005](./adr/0005-rbac-three-tier.md) |
| Email | Soft-failing MailerService | [ADR-0006](./adr/0006-soft-mailer.md) |

PHP 8.1 / 8.2 / 8.3 are all supported (verified via the GitHub Actions matrix). MySQL 8 / MariaDB 10.4 are both fine.

## 3. Architecture Overview

The architecture follows a **thin controllers / thick services** approach.

```
[ Browser ]
    │  Bootstrap + vanilla JS
    │
    ▼
[ Controllers ]  ← input validation, redirects, AJAX/HTML branching
    │
    ▼
[ Services ]     ← all domain logic (cart math, status workflow, etc.)
    │
    ▼
[ Models ]       ← Active Record on top of MySQL
    │
    ▼
[ MySQL 8 ]
```

Cross-cutting:

- **Filters** — auth, RBAC (`admin` / `staff`), CSRF, throttle, secureheaders, invalidchars
- **Spark commands** — backups, scheduled tasks, diagnostics
- **PWA** — service worker + manifest for offline shell + installability

## 4. Notable Engineering Bits

### 4.1 Race-condition-safe stock decrement

Checkout runs inside a database transaction with a conditional update:

```sql
UPDATE products
SET stock = stock - :qty,
    updated_at = NOW()
WHERE id = :id AND stock >= :qty;
```

If `affectedRows() === 0`, another customer claimed the last unit between our read and our write. We rollback and refund the user the bad-news message. Tested manually with two browser windows.

### 4.2 Audit log as a side effect, not the main event

Every admin mutation calls `AuditLogService::log(action, details)`. The service is **best-effort**: it wraps the insert in try/catch and degrades to `log_message('warning', ...)` on failure. We never want audit failure to break a user-facing flow.

### 4.3 Two-factor with graceful unwrap

`TotpService` wraps `robthree/twofactorauth` and `bacon/bacon-qr-code`. The QR is rendered inline as an SVG data URI — no temp files, no public uploads. The login challenge has a 5-minute window to bound replay risk.

### 4.4 Performance hot paths

We profile-by-reading: looked at every controller that filters by `created_at >=` or `status IN (...)` and added composite indexes covering both. The migration that adds `idx_cart_status_created` and `idx_cart_user_status_created` lives at `app/Database/Migrations/2026-05-19-600001_AddCartReportingIndexes.php`.

The N+1 trap in `Admin\OrderController::show()` was fixed by hoisting the per-item `find()` calls into a single `whereIn()` and indexing the result by id. Pattern is documented in the [README conventions section](../README.md#-project-conventions).

### 4.5 Soft-failing mailer (ADR-0006)

`MailerService::send()` falls back to `writable/logs/mail.log` when SMTP isn't configured. This means the abandoned-cart cron and order-confirmation flows can be demoed live without an SMTP server.

## 5. Testing

The PHPUnit suite is small but high-signal:

- **Unit tests** exercise the services in isolation (CartService, CouponService, OrderStatusService).
- **Feature tests** spin up the full router for HTTP-level guards (auth flow, route access, RBAC).

```
OK (34 tests, 54 assertions)
```

CSRF and security headers are auto-disabled when `CI_ENVIRONMENT=testing`, which we documented in `app/Config/Filters.php`. Production parity is preserved because the disable only triggers under that env.

## 6. Things We'd Do Differently

- **B20 i18n** — we skipped this for time. With the service layer in place, adding `lang/{id,en}/*.php` files and a session-bound locale switcher is manageable but invasive enough to defer.
- **Real payments** — Midtrans / Stripe Indonesia would replace the manual "set to paid" admin button. The order status workflow already supports it; it's a matter of webhook plumbing.
- **Customer 2FA** — admins and staff can enable TOTP. Customers can't yet. This is a 30-minute wire-up but didn't make the cut.
- **Advanced analytics** — the dashboard chart is hand-rolled. A Metabase / Redash hookup over the same database would give the operator more flexibility.

## 7. Reflection

The biggest lesson: **incremental migrations beat big-bang refactors**. Each migration in `app/Database/Migrations/` makes one focused change. When we extended the order workflow (B2), added categories (B5), or upgraded the role enum (B16), each was a small ALTER that we could apply, rollback, or skip independently.

The service layer paid for itself in tests. By the time we wrote `OrderStatusServiceTest`, the rules were already in pure PHP — testing was just calling the methods with different inputs. Compare that to a controller-bound rule, where you'd need a feature test with HTTP plumbing.

---

*Submitted as the capstone deliverable for the e-commerce assignment. Technical lead: project author. Engineering pair: NexGear team (informal).*
