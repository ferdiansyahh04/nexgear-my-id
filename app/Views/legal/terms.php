<?php
/**
 * Terms of Service — Indonesian e-commerce.
 */
$eyebrow = 'Legal / TOS';
ob_start();
?>
<p>
    Selamat datang di <strong><?= esc($company['name']) ?></strong>. Dengan mengakses atau menggunakan
    <a href="<?= esc($company['website']) ?>"><?= esc($company['website']) ?></a>, Anda setuju untuk
    terikat pada Syarat dan Ketentuan berikut. Mohon dibaca dengan saksama.
</p>

<h2>1. Definisi</h2>
<ul>
    <li><strong>Layanan</strong>: situs web, aplikasi, dan fitur yang dioperasikan oleh <?= esc($company['name']) ?>.</li>
    <li><strong>Pengguna</strong>: setiap orang yang mengakses atau menggunakan Layanan.</li>
    <li><strong>Akun</strong>: identitas terdaftar yang Anda buat untuk berbelanja.</li>
    <li><strong>Produk</strong>: barang fisik yang dijual melalui Layanan.</li>
</ul>

<h2>2. Penggunaan Layanan</h2>

<h3>2.1 Persyaratan</h3>
<p>Untuk menggunakan Layanan, Anda harus:</p>
<ul>
    <li>Berusia minimal <strong>17 tahun</strong> atau memiliki kapasitas hukum penuh sesuai hukum Indonesia</li>
    <li>Memberikan informasi yang akurat, lengkap, dan terkini saat registrasi</li>
    <li>Menjaga kerahasiaan kata sandi akun Anda</li>
    <li>Bertanggung jawab atas seluruh aktivitas yang dilakukan melalui akun Anda</li>
</ul>

<h3>2.2 Larangan</h3>
<p>Anda <strong>dilarang</strong> melakukan:</p>
<ul>
    <li>Penggunaan Layanan untuk tujuan ilegal atau yang melanggar hak pihak ketiga</li>
    <li>Akses tidak sah ke sistem atau data pengguna lain (UU ITE Pasal 30)</li>
    <li>Menyalin, menjual ulang, atau memodifikasi konten Layanan tanpa izin</li>
    <li>Penggunaan automasi (bot, scraping) tanpa persetujuan tertulis</li>
    <li>Pengiriman konten yang menyinggung, palsu, melanggar hukum, atau mengandung malware</li>
    <li>Manipulasi review, harga, atau sistem kupon</li>
</ul>

<h2>3. Akun dan Keamanan</h2>
<p>Anda bertanggung jawab penuh atas keamanan akun Anda. Apabila menduga akun Anda diakses tanpa izin:</p>
<ul>
    <li>Segera ganti kata sandi melalui <a href="<?= base_url('/admin/security') ?>">panel keamanan akun</a></li>
    <li>Aktifkan Two-Factor Authentication (2FA)</li>
    <li>Hubungi kami di <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a></li>
</ul>

<h2>4. Produk dan Harga</h2>

<h3>4.1 Informasi Produk</h3>
<p>Kami berusaha menampilkan deskripsi, foto, dan harga produk seakurat mungkin. Namun:</p>
<ul>
    <li>Warna sebenarnya dapat berbeda akibat kalibrasi monitor</li>
    <li>Spesifikasi teknis bersumber dari produsen dan dapat berubah tanpa pemberitahuan</li>
    <li>Jika terjadi kesalahan harga yang signifikan, kami berhak membatalkan pesanan dan mengembalikan dana penuh</li>
</ul>

<h3>4.2 Stok</h3>
<p>Ketersediaan ditampilkan secara live di halaman produk. Sistem kami memesan stok secara <strong>atomik</strong> saat checkout — jika stok habis sebelum pembayaran selesai, transaksi dibatalkan otomatis tanpa biaya.</p>

<h3>4.3 Harga</h3>
<p>Semua harga dalam <strong>Rupiah (IDR)</strong>, sudah termasuk PPN sesuai peraturan perpajakan Indonesia. Biaya pengiriman dihitung saat checkout atau gratis sesuai promo yang berlaku.</p>

<h2>5. Pemesanan dan Pembayaran</h2>

<p>Pemesanan tunduk pada penerimaan dan ketersediaan produk. Status pesanan akan melewati tahap berikut:</p>
<ol>
    <li><strong>Placed</strong> — pesanan diterima, menunggu konfirmasi pembayaran</li>
    <li><strong>Paid</strong> — pembayaran terverifikasi</li>
    <li><strong>Processing</strong> — pesanan disiapkan untuk pengiriman</li>
    <li><strong>Shipped</strong> — pesanan diserahkan ke kurir</li>
    <li><strong>Delivered</strong> — pesanan diterima Anda</li>
</ol>

<p>Pesanan yang tidak dibayar dalam <strong>24 jam</strong> akan dibatalkan otomatis dan stok dikembalikan.</p>

<h2>6. Pengiriman</h2>
<p>Detail lengkap pengiriman tersedia di <a href="<?= base_url('/shipping-policy') ?>">Kebijakan Pengiriman</a>. Ringkasan:</p>
<ul>
    <li>Saat ini hanya melayani pengiriman dalam <strong>wilayah Indonesia</strong></li>
    <li>Estimasi 3–5 hari kerja untuk kota besar; 5–10 hari untuk daerah lain</li>
    <li>Risiko hilang/rusak selama pengiriman ditanggung kami sampai pesanan diterima Anda</li>
</ul>

<h2>7. Pengembalian dan Refund</h2>
<p>Detail lengkap di <a href="<?= base_url('/refund-policy') ?>">Kebijakan Pengembalian</a>. Ringkasan: barang segel utuh, tidak terpakai, dapat dikembalikan dalam <strong>14 hari</strong> setelah diterima.</p>

<h2>8. Hak Kekayaan Intelektual</h2>
<p>Seluruh konten Layanan — termasuk logo, desain, foto, teks, dan kode — adalah milik <strong><?= esc($company['name']) ?></strong> atau pemberi lisensi kami. Dilindungi oleh UU Hak Cipta dan perjanjian lisensi terkait.</p>
<p>Anda boleh:</p>
<ul>
    <li>Menggunakan Layanan untuk keperluan pribadi non-komersial</li>
    <li>Membagikan tautan ke halaman publik kami di media sosial</li>
</ul>
<p>Anda <strong>tidak</strong> boleh:</p>
<ul>
    <li>Mengunduh massal konten produk untuk tujuan komersial</li>
    <li>Menggunakan logo atau nama dagang kami tanpa izin tertulis</li>
    <li>Memodifikasi atau membuat karya turunan tanpa persetujuan</li>
</ul>

<h2>9. Konten Pengguna (User-Generated Content)</h2>
<p>Saat Anda mengirim ulasan, komentar, atau pesan kontak, Anda memberikan kepada kami <strong>lisensi non-eksklusif, bebas royalti, di seluruh dunia</strong> untuk menampilkan, menyimpan, dan memodifikasi konten tersebut sepanjang berkaitan dengan operasional Layanan.</p>

<p>Anda menjamin bahwa konten yang Anda kirim:</p>
<ul>
    <li>Adalah karya asli atau Anda memiliki izin untuk membagikannya</li>
    <li>Tidak melanggar hak pihak ketiga (privasi, hak cipta, merek dagang)</li>
    <li>Tidak mengandung ujaran kebencian, fitnah, atau materi ilegal</li>
</ul>

<p>Kami berhak menghapus konten yang melanggar tanpa pemberitahuan terlebih dahulu.</p>

<h2>10. Pembatasan Tanggung Jawab</h2>
<p>Sebatas yang diizinkan oleh hukum:</p>
<ul>
    <li>Layanan disediakan <strong>"sebagaimana adanya"</strong> dan "sebagaimana tersedia"</li>
    <li>Kami tidak menjamin Layanan akan bebas error, tanpa gangguan, atau memenuhi ekspektasi spesifik Anda</li>
    <li>Kami tidak bertanggung jawab atas kerugian tidak langsung, kerugian usaha, atau kehilangan data yang timbul dari penggunaan Layanan</li>
    <li>Total tanggung jawab kami terhadap satu pesanan terbatas pada <strong>nilai pesanan tersebut</strong></li>
</ul>

<p>Pembatasan ini tidak berlaku untuk kerugian yang ditimbulkan akibat <strong>kesengajaan</strong> atau <strong>kelalaian berat</strong> dari pihak kami.</p>

<h2>11. Penghentian Layanan</h2>
<p>Kami berhak menangguhkan atau menutup akun Anda jika:</p>
<ul>
    <li>Anda melanggar Syarat ini secara material</li>
    <li>Akun Anda dipakai untuk aktivitas curang atau ilegal</li>
    <li>Permintaan resmi dari aparat hukum</li>
</ul>

<p>Anda dapat menghapus akun Anda kapan saja dengan menghubungi <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a>.</p>

<h2>12. Hukum yang Berlaku dan Penyelesaian Sengketa</h2>
<p>Syarat ini diatur oleh <strong>hukum Republik Indonesia</strong>. Setiap sengketa yang timbul akan diselesaikan secara musyawarah terlebih dahulu. Jika tidak tercapai, sengketa diserahkan kepada <strong>Pengadilan Negeri Jakarta Selatan</strong>.</p>

<h2>13. Perubahan Syarat</h2>
<p>Kami dapat mengubah Syarat ini dari waktu ke waktu. Versi terbaru selalu tersedia di halaman ini dengan tanggal "Terakhir diperbarui". Perubahan material akan diberitahukan via email atau notifikasi pada Layanan setidaknya 14 hari sebelum berlaku.</p>

<h2>14. Hubungi Kami</h2>
<p>
    <strong><?= esc($company['name']) ?></strong><br>
    Email: <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a><br>
    Lokasi: <?= esc($company['city']) ?>
</p>
<?php
$body = ob_get_clean();
include __DIR__ . '/_shell.php';
?>
