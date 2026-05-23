# NexGear — Local Setup Guide

This is the deeper companion to the README's **Installation** section. Use it when you want to know the *why* behind each step and the troubleshooting paths when something goes wrong.

## Prerequisites

| Tool | Version | Notes |
|---|---|---|
| PHP | 8.1.0 or higher | Tested on 8.1, 8.2, 8.3 |
| MySQL or MariaDB | MySQL 8 / MariaDB 10.4+ | utf8mb4 throughout |
| Composer | 2.x | We don't need 1.x compat |
| Node.js | **not required** | Frontend has no build step |

PHP extensions required: `intl`, `mbstring`, `gd`, `mysqli`. On Windows + XAMPP these are usually on by default. On Linux:

```bash
sudo apt install php8.2-{intl,mbstring,gd,mysql,xml,curl,zip}
```

## Step-by-step

### 1. Clone & install

```bash
git clone <repository-url> nexgear
cd nexgear
composer install
```

`composer install` pulls CodeIgniter 4, Dompdf (B14 PDF invoices), bacon-qr-code + robthree/twofactorauth (B17), faker (dev).

### 2. Environment

```bash
cp .env.example .env
```

Open `.env` and fill in:

```dotenv
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = nexgear_store
database.default.username = root
database.default.password =
```

Optional: SMTP block at the bottom of `.env.example`. Leave commented for dev — the [soft-failing mailer](./adr/0006-soft-mailer.md) writes to `writable/logs/mail.log` instead.

### 3. Encryption key

```bash
php spark key:generate
```

This generates a 256-bit key into `.env` for session/cookie encryption. **Never commit this.**

### 4. Database

Two ways:

**Option A — Import the canonical SQL** (fastest):

```bash
mysql -u root nexgear_store < database/nexgear_store.sql
```

The schema file creates the database, all 17 tables, and seeds the demo accounts + categories + products + coupons.

**About migrations**

For a brand-new local database, use the canonical SQL import above. The current
migration files are incremental changes on top of the base schema, not a full
from-empty schema builder, so `php spark migrate` is intended for future
updates after the base database already exists and the migration history is in
sync.

### 5. Verify accounts

```bash
php spark check:login
```

Expected output:

```
Email: admin@nexgear.test
  password_verify('password', hash): TRUE
Email: user@nexgear.test
  password_verify('password', hash): TRUE
```

If a hash doesn't verify (e.g. someone INSERT-ed plaintext into the DB), reset the seeds:

```bash
php spark fix:seed-users
```

### 6. Run the dev server

```bash
php spark serve
```

Open <http://localhost:8080>. Default credentials:

- **Admin** — `admin@nexgear.test` / `password`
- **User** — `user@nexgear.test` / `password`

## Test setup

```bash
php spark test:setup    # creates nexgear_test DB and applies the schema
composer test           # runs PHPUnit
```

Expected output:

```
OK (34 tests, 54 assertions)
```

## Common gotchas

### "Cache key contains reserved characters"

You hit the throttle filter on IPv6 localhost (`::1`). The fix is in [`ThrottleFilter`](../app/Filters/ThrottleFilter.php) — we sanitize IPs to alphanumeric. If you see this, you're on an old branch.

### Login fails even with correct password

Check `php spark check:login`. If the hash field looks like plain text, run `php spark fix:seed-users` to re-hash.

### Admin error pages show "could not be converted to string"

You're on a CodeIgniter version where the 404 override returns must be a string, not a Response object. Our routes file uses the string-returning form (`return view(...)`). Pull the latest.

### Empty trending searches

`SearchLogModel::trending()` only counts queries from the last 7 days. Try a few searches, then re-open the search overlay.

### PWA install prompt missing

Chrome only offers install on HTTPS or `localhost`. Make sure you're hitting `http://localhost:8080`, not your machine's LAN IP.

## Production deploy

The repo ships a `.github/workflows/ci.yml` that runs the test suite on push, then deploys to a VPS over SSH if the run was on `main`. Required secrets:

- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY` (a private key paired with an authorized_keys entry on the server)

The deploy script does:

```bash
cd /var/www/nexgear-store
git pull origin main
composer install --no-dev --optimize-autoloader
php spark migrate
php spark cache:clear
sudo systemctl reload php8.1-fpm
```

Pair this with the cron block from the README **Scheduled Tasks** section for the abandoned cart and stock alert dispatchers.
