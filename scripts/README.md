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

## `verify_products.php`

Quick read-only sanity check for the seeded etalase: lists every keyboard +
mouse with its on-disk image status.

```bash
php scripts/verify_products.php
```
