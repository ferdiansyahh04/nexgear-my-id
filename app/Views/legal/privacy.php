<?php
/**
 * Privacy Policy — Indonesia (UU PDP No. 27/2022) aligned.
 *
 * Variables come from LegalController::privacy().
 */
$eyebrow = 'Legal / PRV';
ob_start();
?>
<p>
    Kebijakan Privasi ini menjelaskan bagaimana <strong><?= esc($company['name']) ?></strong> ("kami", "Perusahaan")
    mengumpulkan, menggunakan, melindungi, dan membagikan data pribadi pengguna ("Anda", "Pengguna") yang
    mengakses atau bertransaksi melalui <a href="<?= esc($company['website']) ?>"><?= esc($company['website']) ?></a>.
</p>

<p>
    Kebijakan ini disusun mengacu pada <strong>UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)</strong>,
    serta PP No. 71/2019 dan UU ITE.
</p>

<h2>1. Data yang Kami Kumpulkan</h2>

<h3>1.1 Data yang Anda berikan secara langsung</h3>
<ul>
    <li><strong>Identitas:</strong> nama lengkap, email, nomor telepon (saat registrasi atau checkout)</li>
    <li><strong>Alamat pengiriman:</strong> alamat lengkap, kota, kode pos</li>
    <li><strong>Kredensial akun:</strong> kata sandi (disimpan dalam bentuk hash bcrypt, tidak pernah dalam bentuk teks polos)</li>
    <li><strong>Konten yang Anda kirim:</strong> ulasan produk, pesan kontak, review, jawaban formulir</li>
</ul>

<h3>1.2 Data yang dikumpulkan otomatis</h3>
<ul>
    <li><strong>Data sesi:</strong> session cookie untuk menjaga status login Anda</li>
    <li><strong>Log akses:</strong> alamat IP, user agent, halaman yang dikunjungi, timestamp (untuk keamanan dan diagnostik)</li>
    <li><strong>Riwayat keranjang:</strong> produk yang Anda lihat dan masukkan ke keranjang</li>
    <li><strong>Pencarian:</strong> kata kunci yang Anda gunakan di pencarian internal</li>
</ul>

<h3>1.3 Data yang TIDAK kami kumpulkan</h3>
<ul>
    <li>Kami <strong>tidak menyimpan data kartu kredit/debit</strong>. Pembayaran ditangani oleh penyedia gateway pihak ketiga; kami hanya menerima konfirmasi transaksi.</li>
    <li>Kami tidak menggunakan tracking cookie pihak ketiga, fingerprinting browser, atau pixel pelacakan untuk iklan.</li>
</ul>

<h2>2. Dasar Hukum dan Tujuan Pemrosesan</h2>

<table>
    <thead>
        <tr><th>Tujuan</th><th>Dasar Hukum (UU PDP)</th></tr>
    </thead>
    <tbody>
        <tr><td>Memproses pesanan dan pengiriman</td><td>Pelaksanaan kontrak (Pasal 20(2)(b))</td></tr>
        <tr><td>Mengirim konfirmasi order, tracking, dan layanan pelanggan</td><td>Pelaksanaan kontrak (Pasal 20(2)(b))</td></tr>
        <tr><td>Mengirim newsletter (jika berlangganan)</td><td>Persetujuan eksplisit (Pasal 20(2)(a))</td></tr>
        <tr><td>Mencegah kecurangan, debug, dan keamanan sistem</td><td>Kepentingan sah Perusahaan (Pasal 20(2)(f))</td></tr>
        <tr><td>Memenuhi kewajiban perpajakan dan akuntansi</td><td>Kewajiban hukum (Pasal 20(2)(c))</td></tr>
    </tbody>
</table>

<h2>3. Berbagi Data dengan Pihak Ketiga</h2>

<p>Data Anda dapat diteruskan kepada <strong>Penerima Data</strong> berikut, terbatas pada apa yang diperlukan:</p>

<ul>
    <li><strong>Layanan pengiriman</strong> (kurir): nama, alamat, nomor telepon penerima, daftar item</li>
    <li><strong>Penyedia email transaksional (Resend.com)</strong>: alamat email tujuan dan isi pesan email</li>
    <li><strong>Penyedia hosting/CDN (Cloudflare, VPS provider)</strong>: lalu lintas terenkripsi yang melewati infrastruktur mereka</li>
    <li><strong>Aparat penegak hukum:</strong> hanya jika ada permintaan resmi yang sah secara hukum</li>
</ul>

<p>Kami <strong>tidak menjual</strong> data pribadi Anda kepada siapa pun.</p>

<h2>4. Hak-Hak Anda sebagai Subjek Data</h2>

<p>Sesuai Pasal 5–13 UU PDP, Anda berhak untuk:</p>

<ul>
    <li><strong>Mengakses</strong> data pribadi Anda yang kami simpan</li>
    <li><strong>Memperbaiki</strong> data yang tidak akurat (lewat halaman <a href="<?= base_url('/account') ?>">akun</a> atau menghubungi kami)</li>
    <li><strong>Menghapus</strong> data Anda (right to be forgotten), kecuali yang wajib disimpan untuk keperluan hukum</li>
    <li><strong>Membatasi</strong> atau menolak pemrosesan tertentu (mis. berhenti newsletter)</li>
    <li><strong>Memindahkan data</strong> Anda dalam format yang dapat dibaca mesin (data portability)</li>
    <li><strong>Menarik persetujuan</strong> kapan saja tanpa konsekuensi pada transaksi sebelumnya</li>
    <li><strong>Mengajukan keluhan</strong> kepada <a href="https://www.kominfo.go.id" target="_blank" rel="noopener">Kementerian Komunikasi dan Informatika RI</a> jika hak Anda dilanggar</li>
</ul>

<p>Untuk menggunakan hak-hak ini, kirim email ke <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a> dengan subjek "Permintaan Data Pribadi". Kami akan merespons dalam <strong>3 × 24 jam kerja</strong>.</p>

<h2>5. Periode Penyimpanan Data</h2>

<table>
    <thead><tr><th>Jenis Data</th><th>Disimpan Selama</th></tr></thead>
    <tbody>
        <tr><td>Akun aktif</td><td>Selama akun aktif</td></tr>
        <tr><td>Akun yang tidak digunakan</td><td>Dihapus 24 bulan setelah login terakhir</td></tr>
        <tr><td>Catatan transaksi (faktur)</td><td>10 tahun (kewajiban perpajakan UU KUP)</td></tr>
        <tr><td>Log akses dan keamanan</td><td>30 hari</td></tr>
        <tr><td>Pesan kontak / dukungan</td><td>2 tahun</td></tr>
        <tr><td>Daftar email newsletter</td><td>Sampai Anda berhenti berlangganan</td></tr>
    </tbody>
</table>

<h2>6. Keamanan Data</h2>

<p>Kami menerapkan langkah-langkah teknis dan organisasional yang wajar:</p>
<ul>
    <li>Komunikasi terenkripsi <strong>HTTPS/TLS 1.2+</strong> di seluruh situs (HSTS aktif)</li>
    <li>Kata sandi di-hash dengan <strong>bcrypt</strong>, tidak pernah disimpan dalam bentuk teks polos</li>
    <li>Database hanya dapat diakses dari aplikasi yang berjalan di server yang sama (loopback only)</li>
    <li>Proteksi <strong>CSRF</strong> dan <strong>Content Security Policy</strong> nonce-based pada semua formulir</li>
    <li>Rate limiting pada endpoint login dan registrasi untuk mencegah brute force</li>
    <li>Backup database harian dengan retensi 30 hari</li>
    <li>Log audit untuk seluruh perubahan administratif</li>
    <li>Two-Factor Authentication (TOTP) tersedia untuk akun staff dan admin</li>
</ul>

<p>Walaupun demikian, tidak ada sistem yang 100% kebal. Apabila terjadi pelanggaran data yang berdampak terhadap Anda, kami akan memberitahu Anda dan otoritas terkait dalam <strong>3 × 24 jam</strong> sesuai Pasal 46 UU PDP.</p>

<h2>7. Cookies dan Teknologi Serupa</h2>

<p>Kami menggunakan cookies hanya untuk fungsi yang penting (essential cookies):</p>
<ul>
    <li><code>ci_session</code> — menjaga status login dan keranjang belanja Anda</li>
    <li><code>csrf_cookie_name</code> — token anti-CSRF untuk keamanan formulir</li>
    <li><code>nexgear_theme</code> — preferensi tema (light/dark) yang Anda pilih</li>
</ul>

<p>Kami <strong>tidak</strong> menggunakan cookie analitik pihak ketiga, cookie iklan, atau cookie pelacak lintas-situs.</p>

<h2>8. Privasi Anak</h2>

<p>Layanan ini ditujukan untuk pengguna berusia <strong>17 tahun ke atas</strong>. Kami tidak secara sengaja mengumpulkan data anak di bawah umur. Jika Anda mengetahui ada anak di bawah umur yang mendaftar, mohon laporkan ke <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a> dan kami akan menghapus data tersebut.</p>

<h2>9. Transfer Data Lintas-Negara</h2>

<p>Beberapa penyedia layanan kami (mis. Cloudflare, Resend) berlokasi di luar Indonesia. Transfer data dilakukan sesuai Pasal 56 UU PDP, dengan negara tujuan yang memiliki tingkat pelindungan data yang setara atau lebih tinggi, atau dengan klausul kontrak yang mengikat.</p>

<h2>10. Perubahan Kebijakan</h2>

<p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Tanggal "Terakhir diperbarui" di bagian atas dokumen ini menunjukkan revisi terakhir. Perubahan material akan diberitahukan via email kepada pengguna terdaftar setidaknya <strong>30 hari sebelum berlaku</strong>.</p>

<h2>11. Hubungi Kami</h2>

<p>Untuk pertanyaan, permintaan akses data, keluhan, atau hal lain terkait privasi:</p>
<p>
    <strong><?= esc($company['name']) ?></strong><br>
    Email: <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a><br>
    Lokasi: <?= esc($company['city']) ?>
</p>
<?php
$body = ob_get_clean();
include __DIR__ . '/_shell.php';
?>
