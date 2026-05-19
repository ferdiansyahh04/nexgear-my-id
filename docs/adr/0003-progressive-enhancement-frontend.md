# ADR 0003 — Progressive-enhancement frontend (no SPA, no build step)

- **Status:** Accepted
- **Date:** 2026-04-29

## Context

The brief required a PHP backend, but the frontend stack was open. A typical 2026 e-commerce app reaches for React/Next or Vue/Nuxt with an API layer. We considered:

- **SPA (React/Vue) with PHP API** — modern but doubles the project surface (two apps to test, two deploys), and the SEO/PWA story gets harder.
- **Inertia.js** — bridges PHP server with Vue client; nice DX but introduces a build step the examiner has to run.
- **Server-rendered HTML + vanilla JS islands** — PHP renders every page, JS enhances individual interactions where it earns its keep.

## Decision

**Server-rendered HTML with progressive-enhancement vanilla JS.** No bundler, no transpiler, no `node_modules` for the frontend. Bootstrap 5 + Bootstrap Icons + AOS via CDN. All custom code lives in two files:

- `public/assets/css/app.css` — the brutalist design system (≈ 3,000 LoC)
- `public/assets/js/app.js` — vanilla ES2020 modules wired via event delegation

### Hard rules we follow

1. **Every page renders without JS.** Forms work via standard POST. JS only upgrades them with AJAX, optimistic UI, and inline validation.
2. **Event delegation by default.** `document.addEventListener('click', handler)` so we don't have to re-bind after AJAX swaps content.
3. **AJAX contract is uniform.** Every JSON response carries `{status, message, csrfName, csrfToken}`. Client calls `refreshCsrf(data)` after every mutation.
4. **CSS variables for theming.** Dark mode is a `data-theme="dark"` attribute swap; the early-loaded inline script in the layout sets it before paint to avoid FOUC.
5. **No client-side routing.** Page transitions are real navigations with a soft fade overlay (A9). Browser back/forward Just Works.

## Consequences

- **Zero build step.** `composer install` + `php spark serve` and you're running. No `npm install`, no `vite build`. CI doesn't need a Node toolchain.
- **Real PWA.** The service worker caches the shell, and a real service worker file is reachable at `/service-worker.js`. No bundler complications.
- **Accessibility wins by default.** Forms have correct semantics; JS just enhances them. Screen readers and keyboard navigation work out of the box.
- **Trade-off:** the JS file grows monolithically. We mitigate with clear "module" comment dividers (`// ─── A1 — Quick View ───`) and IIFEs for scoping. If it crosses ~2,000 LoC we'll re-evaluate, but at ~1,500 it's still scannable.
- **Trade-off:** no TypeScript. We compensate with disciplined JSDoc comments and small functions.
