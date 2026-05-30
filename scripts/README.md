# scripts/

Local-dev helpers. Not used in production runtime — they are convenience
tools for regenerating seed data or smoke-testing the database.

## `build_seeder_data.ps1`

Regenerates `app/Database/Seeds/data/noirgear_kb_mouse.json` from a fresh
`products.json` snapshot of <https://www.noirgear.com/products.json>.

Use when the upstream catalogue changes (price updates, new SKUs, etc.):

```powershell
# 1. Refresh the source feed (one-off)
curl.exe -s "https://www.noirgear.com/products.json?limit=250" -o products.json

# 2. Regenerate the seed JSON
powershell -NoProfile -ExecutionPolicy Bypass -File scripts\build_seeder_data.ps1

# 3. Apply on the running database (or commit + let CI deploy it)
php spark etalase:import-noirgear --refresh
```

## `build_linsoul_data.php`

Regenerates `app/Database/Seeds/data/linsoul_iems.json` from a fresh
`linsoul_all.json` snapshot of <https://www.linsoul.com/products.json>.
Linsoul is a Shopify storefront, so the feed shape matches Noir Gear's.
The script curates a "best of" set of in-ear monitors across price tiers,
strips the HTML bodies, and converts the USD list price to IDR.

Linsoul is a dedicated IEM/audiophile retailer — its catalogue carries no
microphones, so only the headsets (IEM) category is populated.

```powershell
# 1. Refresh the source feed (one-off)
curl.exe -s "https://www.linsoul.com/products.json?limit=250" -o linsoul_all.json

# 2. Regenerate the seed JSON
php scripts\build_linsoul_data.php

# 3. Apply on the running database (or commit + let CI deploy it)
php spark etalase:import-linsoul --refresh
```

## `build_deskmat_data.php`

Regenerates `app/Database/Seeds/data/deskmat_mousepads.json` from fresh
snapshots of two Shopify storefronts:

- <https://pressplayid.com/collections/deskmat/products.json>
- <https://www.noirgear.com/collections/deskmat-mousepad/products.json>

Curates a "best of" set of mousepads/deskmats across both stores. Both price
in IDR, so no currency conversion is applied. Everything maps to the
`mousepads` category.

```powershell
# 1. Refresh the source feeds (one-off)
curl.exe -s "https://pressplayid.com/collections/deskmat/products.json?limit=250" -o pressplay_deskmat.json
curl.exe -s "https://www.noirgear.com/collections/deskmat-mousepad/products.json?limit=250" -o noir_deskmat.json

# 2. Regenerate the seed JSON
php scripts\build_deskmat_data.php

# 3. Apply on the running database (or commit + let CI deploy it)
php spark etalase:import-deskmat --refresh
```

## `verify_products.php`

Quick read-only sanity check for the seeded etalase: lists every keyboard +
mouse with its on-disk image status.

```bash
php scripts/verify_products.php
```
