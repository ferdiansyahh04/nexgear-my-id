# Penjelasan Cara Kerja Website NexGear Store

Dokumen ini menjelaskan cara kerja aplikasi e-commerce **NexGear Store** untuk
keperluan laporan proyek. Aplikasi sudah berjalan (live) di
<https://nexgear.my.id>.

---

## 1. Gambaran Umum

NexGear Store adalah toko online (e-commerce) untuk perangkat gaming dan audio:
keyboard, mouse, in-ear monitor (headset), dan mousepad/deskmat. Aplikasi
dibangun dengan framework **CodeIgniter 4** (PHP) dan database **MySQL**,
mengikuti pola arsitektur **MVC (Model–View–Controller)**.

Pengunjung dapat menjelajah katalog, menambahkan barang ke keranjang,
membuat akun, melakukan checkout, dan membayar secara online melalui payment
gateway **Duitku**. Di sisi lain, admin/staf mengelola produk, kategori,
pesanan, dan laporan melalui panel admin.

### Teknologi yang digunakan

| Lapisan | Teknologi |
|---|---|
| Framework backend | CodeIgniter 4 (PHP 8.2+) |
| Database | MySQL / MariaDB (utf8mb4) |
| Tampilan (frontend) | HTML + Bootstrap 5 + CSS kustom, JavaScript (tanpa build step) |
| Pembayaran | Duitku (mode Sandbox untuk pengujian) |
| Invoice PDF | Dompdf |
| Keamanan akun | bcrypt (hash kata sandi) + TOTP 2FA opsional |
| Hosting | VPS (Nginx + PHP-FPM) di belakang Cloudflare |
| Otomatisasi | GitHub Actions (CI/CD: uji otomatis + deploy) |

---

## 2. Arsitektur MVC (Cara Aplikasi Memproses Permintaan)

Setiap kali pengguna membuka sebuah halaman, alurnya seperti ini:

```
Browser  →  Routing  →  Filter  →  Controller  →  Model  →  Database
                                        │
                                        ▼
                                      View (HTML)  →  Browser
```

1. **Routing** (`app/Config/Routes.php`) — menentukan controller mana yang
   menangani sebuah URL. Contoh: `/collection` ditangani oleh
   `ProductController::index`.
2. **Filter** (`app/Filters/`) — "penjaga gerbang" yang berjalan sebelum/sesudah
   controller, misalnya mengecek apakah pengguna sudah login (`auth`), apakah
   admin (`admin`), atau membatasi percobaan login (`throttle`).
3. **Controller** (`app/Controllers/`) — otak yang memproses permintaan,
   memanggil Model, dan memilih View.
4. **Model** (`app/Models/`) — perantara ke tabel database (mengambil/menyimpan
   data) menggunakan Query Builder yang aman dari SQL Injection.
5. **View** (`app/Views/`) — template HTML yang ditampilkan ke pengguna.
6. **Library/Service** (`app/Libraries/`) — logika bisnis yang dipakai ulang,
   misalnya `CartService` (keranjang), `CouponService` (kupon),
   `DuitkuService` (pembayaran).

---

## 3. Alur Pengguna (Customer)

### 3.1 Menjelajah Katalog
- **Beranda** (`/`) menampilkan hero, brand story, dan bagian **"Curated Store"**
  berisi produk unggulan. Lineup ini dikurasi otomatis: mengambil produk
  termahal/flagship dari tiap kategori agar variatif (bukan sekadar produk
  terbaru).
- **Halaman koleksi** (`/collection`) menampilkan semua produk dengan:
  - Filter kategori, urutan (sort), rentang harga, dan ketersediaan stok —
    semuanya bekerja secara **AJAX** (halaman tidak perlu reload penuh).
  - **Paginator editorial** kustom (tombol Prev/Next bergaya, bukan angka biru
    polos bawaan).
- **Halaman detail produk** (`/products/{id}`) menampilkan galeri gambar,
  harga, stok real-time, ulasan, dan tombol tambah ke keranjang.

### 3.2 Keranjang & Kupon
- Keranjang disimpan di **session**, jadi tetap ada selama sesi browser.
  Penambahan/perubahan dilakukan via AJAX (`CartController`).
- Kode kupon (mis. `WELCOME10`) divalidasi oleh `CouponService`: cek minimal
  belanja, batas pemakaian, dan masa berlaku. Diskon dihitung terhadap subtotal.
- Untuk pengguna yang sudah login, keranjang yang ditinggalkan disimpan
  (`abandoned_carts`) dan ada pengingat terjadwal.

### 3.3 Akun Pengguna
- **Registrasi & Login** (`AuthController`) — kata sandi di-hash dengan
  **bcrypt**. Endpoint login/registrasi dibatasi (throttle) 5 percobaan per menit
  per IP untuk mencegah brute force.
- **2FA opsional** berbasis TOTP (aplikasi authenticator) untuk keamanan tambahan.
- Area akun (`/account`) berisi riwayat pesanan, detail pesanan, wishlist, dan
  buku alamat.

### 3.4 Checkout & Pembayaran (Inti Transaksi)

Alur lengkap dari keranjang sampai lunas:

```
1. Pelanggan klik Checkout (/checkout) — wajib sudah login.
2. Mengisi data pengiriman (atau pilih alamat tersimpan), lalu klik "Place Order".
3. CheckoutController::place membuat pesanan:
   - Membuat baris pesanan di tabel `cart` dengan status "checked_out".
   - Mengurangi stok secara ATOMIK + transaksional (mencegah dua pembeli
     berebut unit terakhir).
   - Mencatat pemakaian kupon dan mengirim email konfirmasi (best-effort).
4. Pelanggan diarahkan ke halaman pembayaran (/checkout/pay/{id}).
5. Klik "Pay Now" → PaymentController::start membuat invoice ke Duitku,
   lalu mengarahkan (redirect) ke halaman pembayaran Duitku.
6. Pelanggan memilih metode (VA bank / e-wallet / QRIS / kartu) dan membayar.
7. Duitku mengirim CALLBACK (server-to-server) ke /payment/callback →
   status pesanan otomatis berubah menjadi "paid".
8. Saat pelanggan kembali ke situs (/payment/return), aplikasi JUGA menanyakan
   status langsung ke Duitku (rekonsiliasi) sebagai cadangan bila callback telat.
```

Catatan penting tentang keamanan pembayaran:
- **Callback adalah sumber kebenaran** status lunas, bukan tampilan di browser
  (yang bisa dimanipulasi pengguna).
- Callback diverifikasi dengan **signature (HMAC/MD5)** agar dipastikan benar
  berasal dari server Duitku.
- Saat ini menggunakan **Duitku Sandbox** (mode uji, tanpa uang sungguhan).
  Untuk go-live cukup mengganti API key produksi dan satu flag.

---

## 4. Alur Admin / Staf

Panel admin (`/admin`) dilindungi filter peran. Ada dua tingkat:

- **Staf** (`staff`): membaca dashboard, mengelola status pesanan, membalas
  pesan kontak, melihat & mengekspor laporan, mencetak invoice PDF.
- **Admin** (`admin`): semua kemampuan staf, ditambah mengelola produk,
  kategori, dan melihat audit log.

Fitur utama panel admin:
- **Dashboard** — ringkasan pendapatan, jumlah pesanan, produk terlaris, dan
  status stok (In Stock / Low Stock).
- **Manajemen Produk & Kategori** — operasi CRUD (tambah/ubah/hapus) lengkap
  dengan unggah gambar.
- **Manajemen Pesanan** — mengubah status pesanan sesuai siklus hidup:
  `Placed → Paid → Processing → Shipped → Delivered` (atau `Cancelled`).
  Pembatalan otomatis mengembalikan stok.
- **Audit Log** — mencatat setiap perubahan penting oleh admin untuk
  akuntabilitas.

---

## 5. Struktur Data (Tabel Utama)

| Tabel | Fungsi |
|---|---|
| `users` | Akun pengguna (admin/staff/user), kata sandi ter-hash, info 2FA |
| `categories` | Kategori produk (keyboards, mice, headsets, mousepads, dll.) |
| `products` | Data produk (nama, harga, stok, gambar) + index pencarian FULLTEXT |
| `cart` | Pesanan (status, total, data pengiriman, status pembayaran) |
| `cart_items` | Item dalam tiap pesanan |
| `coupons` | Kode kupon dan aturannya |
| `addresses` | Buku alamat pengiriman pengguna |
| `reviews` | Ulasan produk (khusus pembeli terverifikasi) |
| `wishlists` | Daftar keinginan |
| `audit_logs` | Catatan aktivitas admin |
| `newsletter_subscribers`, `contact_messages`, `stock_alerts`, `abandoned_carts`, `search_logs` | Fitur pendukung |

---

## 6. Pengisian Katalog Otomatis (Importer)

Agar etalase berisi produk nyata, dibuat perintah **Spark command** yang menarik
data produk (beserta gambar) dari sumber publik dan memasukkannya ke database.
Perintah bersifat *idempotent* (aman dijalankan berulang — memperbarui data yang
sama, bukan menduplikasi):

```bash
php spark etalase:import-noirgear   # keyboard & mouse
php spark etalase:import-linsoul    # in-ear monitor → kategori headset
php spark etalase:import-deskmat    # mousepad/deskmat
```

Perintah ini juga dijalankan otomatis saat proses deploy, sehingga katalog di
server selalu terisi.

---

## 7. Keamanan

Aplikasi menerapkan praktik keamanan standar (mengacu OWASP Top 10):

- **Kontrol akses berbasis peran** (filter `auth`, `admin`, `staff`).
- **Kata sandi di-hash bcrypt**, tidak pernah disimpan polos.
- **Anti SQL Injection** — semua akses database lewat Query Builder berparameter.
- **Anti XSS** — output ditampilkan dengan auto-escape; **CSP berbasis nonce**.
- **CSRF protection** pada semua form (kecuali callback pembayaran yang
  diamankan dengan signature).
- **Rate limiting** pada login/registrasi/2FA/kontak.
- **HTTPS dipaksa** + **HSTS** aktif (mencegah downgrade ke HTTP).
- **Header keamanan**: X-Frame-Options, X-Content-Type-Options, Referrer-Policy.
- **2FA (TOTP)** opsional untuk akun staf/admin.
- File sensitif (`.env`, `.sql`, dll.) diblokir dari akses publik.

---

## 8. Otomatisasi (CI/CD)

Proyek menggunakan **GitHub Actions** untuk integrasi dan deployment otomatis.
Setiap kali kode di-push ke branch `main`:

1. **Tahap Uji (Lint & Test)** — kode diuji otomatis dengan **PHPUnit** (47 uji)
   terhadap database MySQL sungguhan, dijalankan di **dua versi PHP (8.2 & 8.3)**.
   Mencakup uji logika keranjang, status pesanan, kupon, signature pembayaran,
   autentikasi, akses admin, dan routing.
2. **Tahap Deploy** — jika semua uji lulus, kode otomatis di-deploy ke VPS via
   SSH: tarik kode terbaru, install dependency, jalankan migrasi database,
   import katalog, bersihkan cache, dan reload PHP-FPM.

Dengan ini, perubahan yang merusak akan tertangkap uji otomatis sebelum sampai
ke server produksi.

---

## 9. Ringkasan Alur Singkat

**Sisi Pelanggan:**
> Buka toko → jelajah/filter produk → tambah ke keranjang → (login) → checkout →
> isi alamat → bayar via Duitku → pesanan berstatus *Paid* → lacak di akun.

**Sisi Admin:**
> Login admin → kelola produk/kategori → pantau pesanan masuk → ubah status
> (Processing → Shipped → Delivered) → lihat laporan & audit.

**Sisi Sistem:**
> Push kode → GitHub Actions menguji → deploy otomatis ke VPS → website
> ter-update tanpa downtime.

---

*Dokumen ini dibuat untuk menjelaskan cara kerja sistem pada laporan proyek.
Detail teknis lengkap (instalasi, konfigurasi, keamanan) tersedia di `README.md`.*
