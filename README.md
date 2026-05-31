# NexGear® Elite Storefront

Platform e-commerce premium berkonversi tinggi untuk perangkat gaming dan audio kelas atas. Dibangun dengan **CodeIgniter 4**, **MySQL**, dan sistem desain **Tech-Editorial** yang terinspirasi estetika brutalis dan presisi teknik.

Demo langsung: <https://nexgear.my.id>

> **Catatan proyek akademik.** Pembayaran online berjalan di **Duitku Sandbox**
> (mode uji — tanpa uang sungguhan). Untuk go-live cukup mengganti key Duitku
> produksi dan membalik satu flag; lihat [Pembayaran](#-5-pembayaran-duitku).

## 💎 Filosofi Desain

NexGear Store mengusung estetika **Brutalist Editorial**, mengutamakan dampak visual dan UX berperforma tinggi:
- **Palet Elite**: Charcoal pekat (`#0D0D0D`) dengan border kontras tinggi dan glassmorphism halus.
- **Tipografi**: Presisi geometris memakai **Space Grotesk** untuk header berdampak dan **Inter** untuk teks isi yang bersih dan mudah dibaca.
- **Interaksi Dinamis**: Memanfaatkan **Animate-On-Scroll (AOS)** untuk transisi komponen yang mulus dan marquee CSS kustom untuk gerakan brand.
- **Tata Letak**: Sistem grid 1px yang disiplin sehingga konten tertata dengan kejelasan matematis.

## 🔄 Alur Kerja Proyek

### 🛍️ Perjalanan Pelanggan
1. **Penemuan**: Mendarat di Hero section 100vh berdampak tinggi yang menetapkan nuansa brand.
2. **Keterlibatan**: Menjelajah marquee interaktif dan brand story split yang membangun kepercayaan.
3. **Pemilihan**: Mengeksplorasi grid produk "Curated Store" dengan kartu yang bersih dan informatif.
4. **Konversi**: Interaksi "Add to Bag" yang mulus menuju keranjang transparan dan proses checkout yang aman dan ringkas.

### 🛠️ Alur Kerja Pengembangan
- **Frontend**: Gaya kustom ada di `public/assets/css/app.css`, memanfaatkan Bootstrap 5 untuk kestabilan tata letak.
- **Backend**: Arsitektur MVC CodeIgniter 4. Fitur baru sebaiknya mengikuti pola:
    - Definisikan Model di `app/Models/`
    - Implementasikan Logika di `app/Controllers/`
    - Buat View/Komponen di `app/Views/`
- **Animasi**: Gunakan atribut data-aos pada elemen HTML untuk memicu animasi masuk.

### 💼 Alur Kerja Administratif
- **Kontrol Inventaris**: Tambah atau perbarui produk lewat dashboard `/admin`.
- **Pemantauan Stok**: Badge status real-time (In Stock, Low Stock) membantu menjaga kesehatan rantai pasok.
- **Manajemen Pesanan**: Lacak transaksi pelanggan dan pemenuhan dari antarmuka admin khusus.

## 🚀 Fitur Utama

### 🛒 Pengalaman Storefront
- **Hero Bergaya NuPhy**: Showcase produk berdampak tinggi dengan tipografi editorial.
- **Katalog Terkurasi**: Keyboard, mouse, in-ear monitor (headset), dan deskmat/mousepad — dari tier hemat hingga flagship.
- **Lineup Home Terkurasi**: Bagian "Curated Store" di home menampilkan "best of" premium yang bervariasi (satu flagship per kategori), bukan sekadar produk terbaru.
- **Filter & Pencarian**: Chip kategori AJAX, sort, rentang harga, dan filter stok dengan paginator editorial (template pager CI4 kustom — bukan nomor halaman biru polos).
- **Marquee Interaktif**: Ticker dinamis untuk pesan brand dan promosi.
- **Smart Cart**: Keranjang berbasis sesi yang persisten dengan pembaruan real-time, kupon, dan wishlist.
- **Checkout Ringkas**: Alamat tersimpan, ringkasan pesanan, lalu pembayaran online.

### 💳 Pembayaran (Duitku)
- **Alur hosted redirect**: checkout membuat invoice Duitku dan mengarahkan ke halaman pembayaran Duitku (transfer bank/VA, e-wallet, QRIS, retail, kartu).
- **Callback terverifikasi signature** menandai pesanan lunas secara server-to-server; handler return **juga merekonsiliasi** lewat API `transactionStatus` Duitku sehingga pesanan yang sudah dibayar tetap terselesaikan walau callback tertunda.
- **Fallback aman**: tanpa key Duitku yang dikonfigurasi, checkout kembali ke alur "buat pesanan, bayar offline" sehingga toko tidak pernah rusak.

### 🛠️ Suite Administratif
- **Dashboard Elite**: Analitik inventaris real-time dan pemantauan kesehatan stok.
- **Manajemen CRUD Lengkap**: Perkakas komprehensif untuk media dan metadata produk.
- **Pelacakan Pesanan**: Status siklus hidup (`Placed → Paid → Processing → Shipped → Delivered`) dengan audit logging.

### 🤖 Importer Katalog (perintah Spark)
Importer idempotent menarik produk terkurasi (beserta gambar) dari feed Shopify publik ke etalase. Mereka melakukan upsert berdasarkan nama dan berjalan otomatis saat deploy.

```bash
php spark etalase:import-noirgear   # keyboard & mouse (noirgear.com)
php spark etalase:import-linsoul    # in-ear monitor (linsoul.com) → headset
php spark etalase:import-deskmat    # deskmat/mousepad (Press Play + Noir Gear)
```

## 📁 Arsitektur Teknis

**Stack:** CodeIgniter 4 (PHP 8.2+) · MySQL/MariaDB (utf8mb4) · Bootstrap 5 + CSS editorial kustom · Pembayaran Duitku · Dompdf (invoice) · RobThree/BaconQR (TOTP 2FA). Tanpa langkah build frontend.

```text
nexgear-store/
├── app/
│   ├── Commands/            # Spark CLI: importer katalog, payment:status, backup, cron
│   ├── Config/              # Konfigurasi sistem & keamanan (termasuk Duitku, CSP, Pager)
│   ├── Controllers/         # Logika MVC (Storefront, Cart, Checkout, Payment, Admin)
│   ├── Database/            # Migrasi + data seed (JSON katalog)
│   ├── Filters/             # Kontrol akses (auth / admin / staff / throttle)
│   ├── Libraries/           # Service (Cart, Coupon, Duitku, Mailer, Audit, TOTP…)
│   ├── Models/              # Persistensi data (ActiveRecord)
│   └── Views/               # Layout premium & komponen ber-AOS (termasuk pagers/)
├── public/
│   ├── assets/              # CSS editorial, JS, ikon, AOS
│   └── uploads/             # Penyimpanan media produk
├── scripts/                 # Helper dev-only untuk (regenerasi) JSON seed katalog
├── database/
│   └── nexgear_store.sql    # Skema & data seed
└── .env                     # Pengaturan environment (key disimpan di sini — jangan commit)
```

## 🛠️ Panduan Instalasi & Setup Lengkap

### 1. Prasyarat Sistem

| Tool | Minimum | Diuji pada | Catatan |
|---|---|---|---|
| PHP | 8.2 | 8.2, 8.3 | Ekstensi: `intl`, `mbstring`, `gd`, `mysqli`, `curl`, `json` |
| MySQL/MariaDB | MySQL 5.7 / MariaDB 10.4 | MySQL 8.0 | utf8mb4 menyeluruh, pencarian FULLTEXT memakai InnoDB |
| Composer | 2.x | 2.7+ | |
| Node.js | tidak diperlukan | — | Tanpa langkah build frontend |

Perintah satu baris Linux (Debian/Ubuntu):

```bash
sudo apt install php8.2-{intl,mbstring,gd,mysql,xml,curl,zip}
```

### 2. Ambil Kode Sumber

```bash
git clone https://github.com/yourusername/nexgear-store.git
cd nexgear-store
composer install
# Produksi:
# composer install --no-dev --optimize-autoloader
```

### 3. Konfigurasi Environment

```bash
cp .env.example .env
```

Buka `.env` dan sesuaikan:

```dotenv
CI_ENVIRONMENT  = development            # set ke 'production' di VPS
app.baseURL     = 'http://localhost:8080/'
app.appTimezone = 'Asia/Jakarta'

database.default.hostname = localhost
database.default.database = nexgear_store
database.default.username = root
database.default.password =
```

`CI_ENVIRONMENT=production` otomatis memperketat default: cookie khusus HTTPS, sesi berbasis database, CSP ditegakkan, `forceGlobalSecureRequests=true`. Jangan set production di lokal kecuali Anda punya HTTPS.

Buat encryption key baru:

```bash
php spark key:generate
```

Ini menulis key 256-bit ke `.env` untuk enkripsi sesi/cookie. Jangan pernah commit file ini.

SMTP bersifat opsional. Biarkan blok email tetap dikomentari dan `MailerService` akan dengan rapi menulis email keluar ke `writable/logs/mail.log` sehingga alur dev tetap lancar tanpa kredensial. Lihat [`docs/adr/0006-soft-mailer.md`](docs/adr/0006-soft-mailer.md).

### 4. Setup Database

Impor SQL kanonik:

```bash
mysql -u root -p -e "CREATE DATABASE nexgear_store CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"
mysql -u root -p nexgear_store < database/nexgear_store.sql
```

Ini membuat seluruh tabel **dengan skema lengkap terkini** (termasuk kolom
pembayaran Duitku pada `cart`), index pencarian FULLTEXT pada produk, dan
menyemai akun demo + kategori + produk + kupon. Setelah impor ini, skema sudah
mutakhir — **Anda tidak perlu menjalankan `php spark migrate`**.

> ⚠️ **Untuk setup baru: cukup impor SQL ini, lalu berhenti.** Jangan jalankan
> `php spark migrate` di atas impor yang segar. Berkas di
> `app/Database/Migrations/` adalah **patch inkremental yang mengasumsikan dump
> ini sudah diimpor** (beberapa migrasi membuat tabel turunan dan menambah
> foreign key yang mereferensikan `users`/`products`) — bukan pembangun skema
> dari database kosong. Karena dump ini tidak mengisi tabel pelacakan
> `migrations` milik CodeIgniter, menjalankan `migrate` setelah impor bisa error
> ("table already exists"). Migrasi hanya relevan untuk **menerapkan perubahan
> skema baru** pada database yang sudah ada.

Verifikasi akun seed ter-hash dengan benar:

```bash
php spark check:login
```

Jika sebuah hash terlihat seperti teks polos (seseorang meng-INSERT plaintext ke DB), reset:

```bash
php spark fix:seed-users
```

### 5. Pembayaran (Duitku)

Pembayaran online memakai **Duitku** (payment gateway Indonesia). Bersifat opsional
untuk pengembangan lokal — biarkan key kosong dan checkout akan kembali ke alur
"pesanan tersimpan" offline.

1. Buat proyek di [merchant portal Duitku](https://passport.duitku.com/merchant/Project)
   lalu salin **Merchant Code** dan **API Key (Merchant Key)**-nya. Gunakan
   proyek **Sandbox** saat pengujian.
2. Tambahkan key ke `.env` (jangan pernah di-commit):

   ```dotenv
   duitku.merchantCode = DSxxxx
   duitku.apiKey       = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   duitku.production   = false        # true hanya saat go-live
   ```

3. Di pengaturan proyek Duitku, atur:
   - **Callback URL**: `https://your-domain/payment/callback`
   - **Return URL**: `https://your-domain/payment/return`
4. Jika server punya firewall ketat, whitelist IP callback Duitku
   (Sandbox: `182.23.85.11`, `182.23.85.12`, `103.177.101.187`, `103.177.101.188`).

Verifikasi integrasi dari server (menampilkan konfigurasi tanpa membocorkan rahasia,
memeriksa kolom pembayaran pada `cart`, dan dapat ping ke gateway):

```bash
php spark payment:status            # tampilkan konfigurasi + status skema
php spark payment:status --repair   # tambahkan kolom pembayaran cart yang hilang
php spark payment:status --ping     # kirim invoice uji langsung ke Duitku
```

**Go-live:** ganti ke key proyek produksi, set `duitku.production = true`,
arahkan Callback/Return URL ke proyek produksi, lalu reload PHP-FPM.

### 6. Menjalankan Aplikasi

**Pengembangan lokal**

```bash
php spark serve --host 127.0.0.1 --port 8080
```

Buka <http://localhost:8080>. Frontend tanpa langkah build — cukup refresh.

**Produksi (Apache + mod_php)**

Arahkan `DocumentRoot` vhost ke `public/`:

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

Berkas `.htaccess` yang disertakan menangani pretty URL, pemaksaan HTTPS, header keamanan (HSTS, X-Frame-Options, dll.), dan memblokir eksekusi PHP di dalam `public/uploads/`. `AllowOverride All` diperlukan agar semua itu berlaku.

**Produksi (Nginx + PHP-FPM)**

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

    # Pengaman ganda: jangan pernah menyajikan PHP dari dalam pohon upload.
    location ~* /uploads/.*\.(?:php|phtml|php\d|pl|py|cgi|sh)$ {
        deny all;
    }
}
```

**Izin berkas**

```bash
sudo chown -R www-data:www-data writable public/uploads
sudo chmod -R 750 writable public/uploads
```

Gunakan `750`, bukan `777`. User web server yang memiliki direktori; tidak ada pihak lain yang butuh akses tulis. `chmod 777` adalah jebakan klasik — siapa pun yang masuk shell bisa menimpa berkas yang diunggah dan penyimpanan sesi aplikasi.

**Tugas terjadwal**

Tambahkan ke crontab (`crontab -e -u www-data`):

```cron
# Ingatkan pelanggan tentang keranjang yang menganggur
*/30 * * * *  cd /var/www/nexgear-store && /usr/bin/php spark cart:remind-abandoned >> writable/logs/cron.log 2>&1

# Beri tahu pengguna saat produk yang mereka inginkan kembali tersedia
*/15 * * * *  cd /var/www/nexgear-store && /usr/bin/php spark stock:dispatch-alerts >> writable/logs/cron.log 2>&1

# Backup DB mingguan (writable/backups/)
0 3 * * 0     cd /var/www/nexgear-store && /usr/bin/php spark db:backup >> writable/logs/cron.log 2>&1
```

### 7. Akun Seed Bawaan

| Peran | Email | Kata Sandi |
|---|---|---|
| Admin | `admin@nexgear.my.id` | `password` |
| Pelanggan | `user@nexgear.my.id` | `password` |

**Segera ganti akun-akun ini di environment apa pun selain lokal.**

### 8. Pengujian

```bash
php spark test:setup    # membuat DB nexgear_test dan menerapkan skema
composer test           # menjalankan seluruh suite PHPUnit (butuh DB uji)
```

Suite **UnitFast** yang bebas-DB mencakup logika bisnis murni (cart, order-status,
signature Duitku) dan inilah yang dijadikan gate oleh CI — berjalan tanpa database:

```bash
vendor/bin/phpunit --testsuite UnitFast
```

CI menjalankan suite cepat ini plus langkah deploy via SSH saat push ke `main`. Lihat
[`.github/workflows/ci.yml`](.github/workflows/ci.yml).

## 🔒 Postur Keamanan

Toko ini hadir dengan default keamanan yang sudah terpasang. Ringkasan dalam urutan [OWASP Top 10](https://owasp.org/www-project-top-ten/):

| Risiko | Mitigasi | Lokasi |
|---|---|---|
| **A01 Broken Access Control** | Filter sadar-peran: `auth`, `admin`, `staff`. Rute dikelompokkan via `Routes.php`. | [`app/Filters/`](app/Filters/), [`app/Config/Routes.php`](app/Config/Routes.php) |
| **A02 Cryptographic Failures** | bcrypt via `password_hash(PASSWORD_DEFAULT)`. HTTPS dipaksa di produksi. HSTS disertakan. | [`AuthController`](app/Controllers/AuthController.php), [`public/.htaccess`](public/.htaccess) |
| **A03 Injection (SQL)** | Semua akses DB lewat Query Builder atau parameter `?`. Tanpa SQL hasil konkatenasi string dengan input pengguna. | Semua model + controller |
| **A03 Injection (XSS)** | Semua output view memakai `esc()` / `<?= ?>` dengan auto-escape. CSP berbasis nonce — tanpa `'unsafe-inline'` untuk tag `<script>`. | [`Views/**/*.php`](app/Views/), [`app/Config/ContentSecurityPolicy.php`](app/Config/ContentSecurityPolicy.php) |
| **A04 Insecure Design** | Pengurangan stok bersifat atomik + transaksional (mencegah race pada unit terakhir). Tantangan 2FA dibatasi 5 menit. | [`CheckoutController::place`](app/Controllers/CheckoutController.php), [`AuthController::twoFactorVerify`](app/Controllers/AuthController.php) |
| **A05 Security Misconfiguration** | `.htaccess` memblokir `.env`, `.sql`, berkas lock, dll. Direktori upload menonaktifkan eksekusi PHP. Listing index dinonaktifkan. | [`public/.htaccess`](public/.htaccess), [`public/uploads/.htaccess`](public/uploads/.htaccess) |
| **A06 Vulnerable Components** | Dependensi Composer dipin dan minimal (CodeIgniter 4, Dompdf, Bacon QR, TwoFactorAuth). Jalankan `composer audit` secara rutin. | [`composer.json`](composer.json) |
| **A07 Identification & Auth Failures** | Filter throttle pada register/login/2FA/contact (5 req/menit/IP). Sesi diregenerasi saat login. TOTP 2FA opsional. | [`app/Filters/ThrottleFilter.php`](app/Filters/ThrottleFilter.php), [`app/Libraries/TotpService.php`](app/Libraries/TotpService.php) |
| **A08 Software & Data Integrity** | Audit log untuk mutasi admin (`AuditLogService`). Best-effort: kegagalan log tidak pernah mematahkan alur pengguna. | [`app/Libraries/AuditLogService.php`](app/Libraries/AuditLogService.php) |
| **A09 Logging & Monitoring** | `writable/logs/` dirotasi per-hari. Login gagal muncul via flash sesi; pola massal terlihat lewat audit log. | [`app/Config/Logger.php`](app/Config/Logger.php) |
| **A10 SSRF** | Tidak ada URL keluar yang dibangun dari input pengguna. Host SMTP dikonfigurasi admin. Tidak ada pengambilan gambar dari URL milik pengguna (hanya unggahan). | — |

### Checklist hardening sebelum go-live

- [ ] `CI_ENVIRONMENT=production` di `.env`
- [ ] `php spark key:generate` untuk menyegarkan `encryption.key`
- [ ] Ganti kedua kata sandi akun seed (atau jalankan `php spark fix:seed-users` untuk re-hash)
- [ ] Pastikan HTTPS bekerja dan `Strict-Transport-Security` terkirim (`curl -I https://your-domain`)
- [ ] Pastikan `https://your-domain/.env` mengembalikan 403/404 (bukan isi berkasnya)
- [ ] Pastikan `https://your-domain/uploads/test.php` mengembalikan 403 (tidak dieksekusi)
- [ ] Set izin user DB hanya ke database aplikasi — jangan pernah `GRANT ALL` pada `*.*`
- [ ] Jadwalkan backup offsite (`php spark db:backup` menulis ke `writable/backups/`)
- [ ] Pembayaran: ganti ke key Duitku produksi, set `duitku.production = true`, perbarui Callback/Return URL di proyek produksi, dan verifikasi dengan `php spark payment:status --ping`

### Melaporkan masalah keamanan

Email ke security@your-domain (atau buka GitHub Security Advisory privat). Jangan membuat issue publik untuk kerentanan.
