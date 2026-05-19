# ADR 0002 — Service-first domain logic

- **Status:** Accepted
- **Date:** 2026-04-22

## Context

Early prototypes of NexGear had cart math, coupon validation, and order state machines living inside controllers. As features piled up (B1–B22), controllers grew to 200+ LoC and started duplicating logic between the storefront and admin sides.

Symptoms:
- `CartController::add` duplicated price math used by `CheckoutController::place`.
- The order status workflow lived inline in `Admin\OrderController` and the user-facing order detail.
- Tests had to spin up full HTTP requests just to verify a price calculation.

## Decision

Domain logic lives in **`app/Libraries/*Service.php`** classes. Controllers stay thin — validate input, call a service, return a response.

Rules of thumb:

- **Controllers are I/O.** They handle request/response, redirects, AJAX vs HTML branching, and CSRF token plumbing.
- **Services are logic.** They take primitives + IDs, return domain results. They never know about HTTP.
- **Models are data.** They are CodeIgniter Active Record subclasses with `allowedFields`. They don't carry domain semantics.

Concrete services we've stood up:

| Service | Responsibility |
|---|---|
| `CartService` | Hydrate session cart into items, totals, subtotals, discount-aware final totals |
| `CouponService` | Validate code against subtotal, compute discount, increment usage |
| `OrderStatusService` | Define enum, allowed transitions, customer-facing timeline |
| `WishlistService` | Persist for users, session-buffer for guests, merge on login |
| `RecentlyViewedService` | Session-bound product trail with capped size |
| `RecommendationService` | 3-stage recommender (bought-together → similar → fallback) |
| `AbandonedCartService` | Snapshot session cart on every mutation, cron picks it up |
| `StockAlertService` | Subscribe + dispatch back-in-stock notifications |
| `AuditLogService` | Write structured audit entries from any admin mutation |
| `MailerService` | Render templated emails, fall back to log file in dev |
| `TotpService` | Wrap RobThree TOTP + bacon-qr-code into a one-line API |
| `RecentlyViewedService`, `RecommendationService` | Browsing aids |

## Consequences

- **Tests are fast and unit-shaped.** `OrderStatusServiceTest` and `CouponServiceTest` exercise the actual rules without HTTP. The full suite runs in ~250ms on PHP 8.2.
- **Controllers are diff-friendly.** Most are < 100 LoC; logic changes happen inside services and rarely produce noisy controller diffs.
- **Service ↔ service coupling is allowed.** `CartService::finalTotal()` calls `CouponService::currentDiscount()`. We treat composition over inheritance — no shared base class.
- **No DI container.** We `new` services where we need them. CI4 doesn't ship a container by default, and these objects are stateless enough that constructor injection wouldn't earn its keep here.
