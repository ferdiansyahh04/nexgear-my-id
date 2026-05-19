# ADR 0001 — Choose CodeIgniter 4 as the framework

- **Status:** Accepted
- **Date:** 2026-04-15

## Context

This is an academic project graded on code quality, breadth of features, and engineering discipline. The brief mandated **PHP only** for the backend. Within that constraint, the practical choices were:

- **Plain PHP / no framework** — tempting for "from scratch" purity, but every project at this size eventually rebuilds routing, validation, sessions, query building, CSRF, and migrations.
- **Laravel 11** — most popular PHP framework, generous ecosystem, but the boot footprint, queue/horizon expectations, and Eloquent's magic add cognitive load that's hard to justify on a single-class deliverable.
- **CodeIgniter 4** — ships routing, validation, query builder, migrations, sessions, throttling, and a CLI (`spark`) out of the box. Lower mental overhead than Laravel and an explicit, documented filesystem layout.
- **Symfony** — Powerful but verbose; configuring a from-scratch storefront feels disproportionate.

## Decision

We adopt **CodeIgniter 4.7** as the application framework.

The deciding factors:

1. **Predictable layout.** `app/Controllers`, `app/Models`, `app/Views`, `app/Filters`, `app/Libraries`, `app/Commands` are all discoverable folders. Nothing is hidden behind service container resolution.
2. **No runtime magic.** Routes are explicit in `app/Config/Routes.php`. Filters are wired by alias. The Active Record models are simple PHP classes — no listener events, no observers.
3. **Built-in tooling.** `spark` covers migrations, seeding, route inspection, and we can add custom commands trivially. Production cron uses the same entrypoint.
4. **Smaller surface area for an examiner.** Reading the framework documentation cover-to-cover is feasible (~150 pages). Laravel's docs are 3–4× longer.

## Consequences

- We accept that CodeIgniter 4's ecosystem is smaller. Auth, payments, and queues are not first-party — we build the small slices we need.
- Our service-layer (`app/Libraries/`) takes on more responsibility than it would under Laravel. We treat that as a feature: domain logic stays out of controllers and out of the framework.
- Migrating to Laravel later would mean rewriting models and migrations but most of `app/Libraries/` would port unchanged because it's plain PHP.
