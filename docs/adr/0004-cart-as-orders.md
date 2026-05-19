# ADR 0004 — `cart` table doubles as `orders`

- **Status:** Accepted
- **Date:** 2026-05-04

## Context

We had a choice in modelling the order lifecycle:

1. **Two tables, `cart` + `orders`.** The cart row gets copied into orders at checkout, then deleted. Cleaner mental model: a cart has only the items in it, an order has shipping data and a final total.
2. **One table, `cart` with a `status` enum.** The same row that started life as `'active'` transitions through `'checked_out' → 'paid' → 'processing' → 'shipped' → 'delivered'`.

## Decision

We use **option 2: a single `cart` table** with a status enum.

```sql
status ENUM(
    'active',         -- in-progress cart, no checkout yet
    'checked_out',    -- user submitted checkout form
    'paid',           -- payment confirmed (manual flag for now)
    'processing',     -- preparing for shipment
    'shipped',        -- on the way
    'delivered',      -- final
    'cancelled'       -- cancelled (early stages only)
)
```

The same table holds shipping fields (`shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `shipping_postal_code`, `coupon_code`, `discount`, `total`).

Rationale:

- **One ID for the customer to remember.** The order they see in `/account/orders/{id}` is the same numeric ID as the row in `cart`.
- **Lifecycle clarity.** A single row tells the whole story. No ambiguity about whether a cart "became" an order — it just transitioned status.
- **Foreign keys remain stable.** `cart_items.cart_id` keeps pointing at the same row whether it's a cart or a shipped order.
- **Audit trail aligns naturally.** `audit_logs` entries reference `target_type='order'` and `target_id=cart.id`.

## Consequences

- **Querying "active carts" needs `WHERE status = 'active'` everywhere.** We accept this — it's explicit, and the index `idx_cart_user_status_created` makes it fast.
- **A user can have multiple `'active'` rows in theory.** In practice we keep one row per user, but the schema doesn't enforce it. We rely on `CartService` (session-driven) so this is a non-issue.
- **The "cart" terminology bleeds into post-checkout views.** We hide it from customers (the UI says "Order #123"), but it leaks into admin code (`Admin\OrderController` queries `CartModel`). We tolerate this for the simplicity gain.
- **Stock decrement is transactional and tied to status change.** `CheckoutController::place` does an atomic `UPDATE products SET stock = stock - qty WHERE stock >= qty` inside the same transaction that sets `status = 'checked_out'`. Cancellation (`status = 'cancelled'`) re-stocks.
