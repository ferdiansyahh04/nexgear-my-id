# ADR 0005 — Three-tier RBAC: `user`, `staff`, `admin`

- **Status:** Accepted
- **Date:** 2026-05-15

## Context

We started with two roles: `user` (can shop) and `admin` (can do everything backend). Once order processing matured (B2 status workflow + B14 reports + B12 messages), a single backend tier became too coarse:

- The store owner (the project examiner, in this case) is the `admin` and the only one who should manage products, categories, and view audit logs.
- Day-to-day operations (process orders, reply to messages, generate reports) doesn't need the catalog write power.
- Granting day-staff full admin would violate the principle of least privilege.

A full RBAC system with `permissions` and `role_permissions` tables felt over-engineered for the scope. We needed a middle tier, not a permissions matrix.

## Decision

We extend the role enum to **three tiers**:

```sql
ALTER TABLE users MODIFY COLUMN role
ENUM('admin','staff','user') NOT NULL DEFAULT 'user';
```

### Capability matrix

| Surface | `user` | `staff` | `admin` |
|---|---|---|---|
| Storefront (browse, cart, checkout) | ✓ | ✓ | ✓ |
| `/account/*` (own data) | ✓ | ✓ | ✓ |
| `/admin` (dashboard) | – | ✓ | ✓ |
| `/admin/orders` (read + status update) | – | ✓ | ✓ |
| `/admin/messages` (inbox + reply) | – | ✓ | ✓ |
| `/admin/reports` (read + CSV/PDF export) | – | ✓ | ✓ |
| `/admin/security` (own 2FA) | – | ✓ | ✓ |
| `/admin/products` (catalog write) | – | – | ✓ |
| `/admin/categories` (taxonomy write) | – | – | ✓ |
| `/admin/audit` (audit trail) | – | – | ✓ |

### Implementation

We split the `/admin` route group in `app/Config/Routes.php` into two filter-bound segments:

```php
// staff + admin
$routes->group('admin', ['filter' => 'staff'], function ($routes) { ... });

// admin only
$routes->group('admin', ['filter' => 'admin'], function ($routes) { ... });
```

The filters live in `app/Filters/`:
- `StaffOrAdminFilter` — accepts `admin` OR `staff`.
- `AdminFilter` — strict `admin` only.

Sidebar in `app/Views/layouts/admin.php` reads `session('role')` and conditionally renders catalog/audit links so staff don't see dead-end menu items.

## Consequences

- **Zero migration risk.** Existing users keep their `admin` or `user` role. New role is opt-in.
- **No permissions table needed.** A future request like "let staff edit one specific category" would push us toward fine-grained permissions. We accept that re-architecture is on the table when that day comes.
- **Filter ordering matters.** A route registered under `filter: admin` after a `filter: staff` registration replaces the latter for that exact path. We rely on this — staff group registers all readable routes first, then the admin group registers writes. It's documented in the routes file.
- **Audit log scope.** Only `admin` can view `/admin/audit`, but the audit log records actions by both staff and admin (and the system, e.g. cron jobs). Staff who change order status will see their email in the audit log when admin reviews it.
