# NexGear® Elite Storefront

A premium, high-conversion e-commerce platform for elite gaming hardware. Built with **CodeIgniter 4**, **MySQL**, and a **Tech-Editorial** design system inspired by brutalist aesthetics and precision engineering.

![NexGear Banner](https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?q=80&w=1200&auto=format&fit=crop)

## 💎 Design Philosophy

The NexGear Store follows a **Brutalist Editorial** aesthetic, prioritizing visual impact and high-performance UX:
- **Elite Palette**: Deep Charcoal (`#0D0D0D`) with high-contrast borders and subtle glassmorphism.
- **Typography**: Geometric precision using **Space Grotesk** for high-impact headers and **Inter** for clean, legible body text.
- **Dynamic Interactions**: Leverages **Animate-On-Scroll (AOS)** for smooth component entry and custom CSS marquees for brand movement.
- **Layout**: A disciplined 1px grid system that organizes content with mathematical clarity.

## 🔄 Project Workflows

### 🛍️ Customer Journey
1. **Discovery**: Landing on a high-impact 100vh Hero section that sets the brand tone.
2. **Engagement**: Browsing through interactive marquees and brand story splits that build trust.
3. **Selection**: Exploring the "Curated Store" product grid with clean, informative cards.
4. **Conversion**: Seamless "Add to Bag" interaction leading to a transparent cart and a secure, streamlined checkout process.

### 🛠️ Development Workflow
- **Frontend**: Custom styles located in `public/assets/css/app.css`, utilizing Bootstrap 5 for layout stability.
- **Backend**: CodeIgniter 4 MVC architecture. New features should follow the pattern:
    - Define Model in `app/Models/`
    - Implement Logic in `app/Controllers/`
    - Create View/Component in `app/Views/`
- **Animations**: Use data-aos attributes on HTML elements to trigger entrance animations.

### 💼 Administrative Workflow
- **Inventory Control**: Add or update products via the `/admin` dashboard.
- **Stock Monitoring**: Real-time status badges (In Stock, Low Stock) help maintain supply chain health.
- **Order Management**: Track customer transactions and fulfillment from the dedicated admin interface.

## 🚀 Key Features

### 🛒 Storefront Experience
- **NuPhy-Inspired Hero**: High-impact product showcase with editorial typography.
- **Interactive Marquees**: Dynamic tickers for brand messaging and promotions.
- **Smart Cart**: Persistent session-based cart with real-time updates.
- **Streamlined Checkout**: Integrated delivery data capture and order summary.

### 🛠️ Administrative Suite
- **Elite Dashboard**: Real-time inventory analytics and stock health monitoring.
- **Full CRUD Management**: Comprehensive tools for product media and metadata.
- **Order Tracking**: Detailed records for transaction management.

## 📁 Technical Architecture

```text
nexgear-store/
├── app/
│   ├── Config/              # System & Security configuration
│   ├── Controllers/         # MVC Logic (Storefront, Cart, Admin)
│   ├── Filters/             # Access Control (Admin/User Roles)
│   ├── Models/              # Data persistence (ActiveRecord)
│   └── Views/               # Premium Layouts & AOS-enabled components
├── public/
│   ├── assets/              # Elite CSS, JS, and AOS libraries
│   └── uploads/             # Product Media Storage
├── database/
│   └── nexgear_store.sql   # Schema & Seed Data
└── .env                     # Environment settings
```

## 🛠️ Installation & Comprehensive Setup Guide

### 1. System Prerequisites

| Tool | Minimum | Tested on | Notes |
|---|---|---|---|
| PHP | 8.1 | 8.1, 8.2, 8.3 | Extensions: `intl`, `mbstring`, `gd`, `mysqli`, `curl`, `json` |
| MySQL/MariaDB | MySQL 5.7 / MariaDB 10.4 | MySQL 8.0 | utf8mb4 throughout, FULLTEXT search uses InnoDB |
| Composer | 2.x | 2.7+ | |
| Node.js | not required | — | No frontend build step |

Linux (Debian/Ubuntu) one-liner:

```bash
sudo apt install php8.2-{intl,mbstring,gd,mysql,xml,curl,zip}
```

### 2. Fetch the Codebase

```bash
git clone https://github.com/yourusername/nexgear-store.git
cd nexgear-store
composer install
# Production:
# composer install --no-dev --optimize-autoloader
```

### 3. Environment Configuration

```bash
cp .env.example .env
```

Open `.env` and adjust:

```dotenv
CI_ENVIRONMENT  = development            # set to 'production' on the VPS
app.baseURL     = 'http://localhost:8080/'
app.appTimezone = 'Asia/Jakarta'

database.default.hostname = localhost
database.default.database = nexgear_store
database.default.username = root
database.default.password =
```

`CI_ENVIRONMENT=production` automatically tightens defaults: HTTPS-only cookies, database-backed sessions, CSP enforced, `forceGlobalSecureRequests=true`. Don't set production locally unless you have HTTPS.

Generate a fresh encryption key:

```bash
php spark key:generate
```

This writes a 256-bit key to `.env` for session/cookie encryption. Never commit this file.

SMTP is optional. Leave the email block commented and the `MailerService` gracefully writes outbound mail to `writable/logs/mail.log` so dev flows stay green without credentials. See [`docs/adr/0006-soft-mailer.md`](docs/adr/0006-soft-mailer.md).

### 4. Database Setup

**Option A — Canonical SQL import (recommended)**

```bash
mysql -u root -p -e "CREATE DATABASE nexgear_store CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
mysql -u root -p nexgear_store < database/nexgear_store.sql
```

This creates all 17 tables, the FULLTEXT search index on products, and seeds demo accounts + categories + products + coupons.

**Option B — Migrations (after the SQL import)**

```bash
php spark migrate
```

The migration files are incremental updates layered on top of the canonical schema, not a from-empty schema builder. Always import the SQL once first.

Verify the seed accounts hashed correctly:

```bash
php spark check:login
```

If a hash looks like plain text (someone INSERT-ed plaintext into the DB), reset it:

```bash
php spark fix:seed-users
```

### 5. Running the Application

**Local development**

```bash
php spark serve --host 127.0.0.1 --port 8080
```

Open <http://localhost:8080>. The frontend has no build step — just refresh.

**Production (Apache + mod_php)**

Point the vhost `DocumentRoot` to `public/`:

```apache
<VirtualHost *:443>
    ServerName nexgear.example.com
    DocumentRoot /var/www/nexgear-store/public

    <Directory /var/www/nexgear-store/public>
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/nexgear.example.com/fullchain.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/nexgear.example.com/privkey.pem
</VirtualHost>
```

The shipped `.htaccess` files handle pretty URLs, HTTPS forcing, security headers (HSTS, X-Frame-Options, etc.), and block PHP execution inside `public/uploads/`. `AllowOverride All` is required for these to apply.

**Production (Nginx + PHP-FPM)**

```nginx
server {
    listen 443 ssl http2;
    server_name nexgear.example.com;
    root /var/www/nexgear-store/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Belt-and-braces: never serve PHP from inside the upload tree.
    location ~* /uploads/.*\.(?:php|phtml|php\d|pl|py|cgi|sh)$ {
        deny all;
    }
}
```

**File permissions**

```bash
sudo chown -R www-data:www-data writable public/uploads
sudo chmod -R 750 writable public/uploads
```

Use `750`, not `777`. The web server user owns the directory; nobody else needs write access. `chmod 777` is a long-standing footgun — anyone shelled in can overwrite uploaded files and the application's session store.

**Scheduled tasks**

Add to crontab (`crontab -e -u www-data`):

```cron
# Nudge customers about idle carts
*/30 * * * *  cd /var/www/nexgear-store && /usr/bin/php spark remind:abandoned >> writable/logs/cron.log 2>&1

# Notify users when products they wanted return to stock
*/15 * * * *  cd /var/www/nexgear-store && /usr/bin/php spark dispatch:stock-alerts >> writable/logs/cron.log 2>&1
```

### 6. Default Seed Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@nexgear.test` | `password` |
| Customer | `user@nexgear.test` | `password` |

**Change these immediately on any non-local environment.**

### 7. Tests

```bash
php spark test:setup    # creates nexgear_test DB and applies the schema
composer test           # runs PHPUnit (38 tests)
```

CI runs the same suite plus a deploy step on push to `main`. See [`.github/workflows/ci.yml`](.github/workflows/ci.yml).

## 🔒 Security Posture

The store ships with security defaults wired in. Summary in [OWASP Top 10](https://owasp.org/www-project-top-ten/) order:

| Risk | Mitigation | Where |
|---|---|---|
| **A01 Broken Access Control** | Role-aware filters: `auth`, `admin`, `staff`. Routes grouped via `Routes.php`. | [`app/Filters/`](app/Filters/), [`app/Config/Routes.php`](app/Config/Routes.php) |
| **A02 Cryptographic Failures** | bcrypt via `password_hash(PASSWORD_DEFAULT)`. HTTPS forced in production. HSTS shipped. | [`AuthController`](app/Controllers/AuthController.php), [`public/.htaccess`](public/.htaccess) |
| **A03 Injection (SQL)** | All DB access via Query Builder or `?` parameters. No string-concatenated SQL with user input. | All models + controllers |
| **A03 Injection (XSS)** | All view output uses `esc()` / `<?= ?>` with auto-escape. CSP nonce-based — no `'unsafe-inline'` for `<script>` tags. | [`Views/**/*.php`](app/Views/), [`app/Config/ContentSecurityPolicy.php`](app/Config/ContentSecurityPolicy.php) |
| **A04 Insecure Design** | Stock decrement is atomic + transactional (prevents race on the last unit). 2FA challenge bounded to 5 minutes. | [`CheckoutController::place`](app/Controllers/CheckoutController.php), [`AuthController::twoFactorVerify`](app/Controllers/AuthController.php) |
| **A05 Security Misconfiguration** | `.htaccess` blocks `.env`, `.sql`, lock files, etc. Uploads directory disables PHP execution. Index listing disabled. | [`public/.htaccess`](public/.htaccess), [`public/uploads/.htaccess`](public/uploads/.htaccess) |
| **A06 Vulnerable Components** | Composer dependencies pinned and minimal (CodeIgniter 4, Dompdf, Bacon QR, TwoFactorAuth). Run `composer audit` regularly. | [`composer.json`](composer.json) |
| **A07 Identification & Auth Failures** | Throttle filter on register/login/2FA/contact (5 req/min/IP). Session regenerated on login. Optional TOTP 2FA. | [`app/Filters/ThrottleFilter.php`](app/Filters/ThrottleFilter.php), [`app/Libraries/TotpService.php`](app/Libraries/TotpService.php) |
| **A08 Software & Data Integrity** | Audit log of admin mutations (`AuditLogService`). Best-effort: log failure never breaks user flow. | [`app/Libraries/AuditLogService.php`](app/Libraries/AuditLogService.php) |
| **A09 Logging & Monitoring** | `writable/logs/` per-day rolled. Failed logins surface via session flash; bulk patterns visible through audit log. | [`app/Config/Logger.php`](app/Config/Logger.php) |
| **A10 SSRF** | No outbound URLs constructed from user input. SMTP host is admin-configured. No image fetches from user-supplied URLs (uploads only). | — |

### Hardening checklist before going live

- [ ] `CI_ENVIRONMENT=production` in `.env`
- [ ] `php spark key:generate` to refresh `encryption.key`
- [ ] Change both seed account passwords (or run `php spark fix:seed-users` to re-hash)
- [ ] Confirm HTTPS works and `Strict-Transport-Security` ships (`curl -I https://your-domain`)
- [ ] Confirm `https://your-domain/.env` returns 403/404 (not the file)
- [ ] Confirm `https://your-domain/uploads/test.php` returns 403 (not executes)
- [ ] Set DB user permissions to the application database only — never `GRANT ALL` on `*.*`
- [ ] Schedule offsite backups (`php spark db:backup` writes to `writable/backups/`)

### Reporting a security issue

Email security@your-domain (or open a private GitHub Security Advisory). Do not file public issues for vulnerabilities.
