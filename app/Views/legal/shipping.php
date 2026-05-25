<?php
$eyebrow = 'Legal / SHP';
ob_start();
?>
<p>
    Kebijakan Pengiriman ini menjelaskan bagaimana <strong><?= esc($company['name']) ?></strong> mengirim
    pesanan Anda — dari proses penyiapan hingga pengiriman ke alamat tujuan.
</p>

<h2>1. Wilayah Pengiriman</h2>
<p>Saat ini kami melayani pengiriman ke <strong>seluruh wilayah Indonesia</strong>. Pengiriman internasional belum tersedia.</p>

<h2>2. Estimasi Waktu Pengiriman</h2>

<table>
    <thead><tr><th>Wilayah</th><th>Estimasi (Hari Kerja)</th></tr></thead>
    <tbody>
        <tr><td>Jabodetabek</td><td>1–2 hari</td></tr>
        <tr><td>Pulau Jawa (luar Jabodetabek)</td><td>2–4 hari</td></tr>
        <tr><td>Bali, Sumatera (kota besar)</td><td>3–5 hari</td></tr>
        <tr><td>Kalimantan, Sulawesi (kota besar)</td><td>4–7 hari</td></tr>
        <tr><td>Maluku, Papua, daerah pedalaman</td><td>5–14 hari</td></tr>
    </tbody>
</table>

<p>Estimasi di atas <strong>belum termasuk</strong> 1 hari kerja untuk processing dan packing. Hari libur nasional tidak terhitung sebagai hari kerja.</p>

<h2>3. Biaya Pengiriman</h2>
<p>Saat ini kami memberikan <strong>pengiriman gratis</strong> untuk semua pesanan tanpa minimum. Biaya pengiriman kami tanggung sepenuhnya.</p>
<p>Kebijakan ini dapat berubah dengan pemberitahuan minimal 30 hari sebelumnya.</p>

<h2>4. Mitra Kurir</h2>
<p>Kami bekerja sama dengan kurir terpercaya:</p>
<ul>
    <li><strong>JNE</strong> (REG, YES, OKE)</li>
    <li><strong>J&T Express</strong></li>
    <li><strong>SiCepat</strong> (REG, BEST)</li>
    <li><strong>AnterAja</strong></li>
    <li><strong>Pos Indonesia</strong> (untuk daerah remote)</li>
</ul>

<p>Pemilihan kurir berdasarkan tujuan, ukuran/berat paket, dan SLA terbaik.</p>

<h2>5. Pelacakan Pesanan</h2>
<p>Setelah pesanan dikirim, Anda akan menerima email berisi <strong>nomor resi</strong>. Cara melacak:</p>
<ul>
    <li>Buka <a href="<?= base_url('/account/orders') ?>">halaman pesanan</a> di akun Anda — status terupdate otomatis</li>
    <li>Atau gunakan nomor resi di situs kurir untuk detail real-time</li>
</ul>

<h2>6. Alur Status Pesanan</h2>
<ol>
    <li><strong>Placed</strong> — pesanan diterima sistem, menunggu pembayaran</li>
    <li><strong>Paid</strong> — pembayaran berhasil, pesanan masuk antrian</li>
    <li><strong>Processing</strong> — pesanan disiapkan dan dikemas (biasanya hari yang sama)</li>
    <li><strong>Shipped</strong> — paket diserahkan ke kurir, nomor resi diterbitkan</li>
    <li><strong>Delivered</strong> — paket diterima Anda</li>
</ol>

<h2>7. Pengemasan</h2>
<p>Setiap pesanan dikemas dengan:</p>
<ul>
    <li>Bubble wrap dan buble pad untuk barang elektronik</li>
    <li>Kotak karton tebal dengan label "Fragile" untuk barang sensitif</li>
    <li>Tas anti-air untuk dokumen dan kabel</li>
    <li>Plastik bening untuk barang yang sudah dalam kemasan retail</li>
</ul>

<h2>8. Asuransi dan Risiko</h2>
<ul>
    <li>Risiko kehilangan/kerusakan saat pengiriman <strong>kami tanggung</strong> sampai paket diterima Anda</li>
    <li>Pesanan bernilai &gt;Rp 5.000.000 otomatis kami asuransikan</li>
    <li>Jika paket diterima dalam keadaan rusak/segel terbuka:
        <ul>
            <li><strong>Tolak penerimaan</strong> dan minta kurir mencatatnya</li>
            <li>Atau terima dan dokumentasikan dengan foto/video</li>
            <li>Hubungi kami dalam 24 jam ke <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a></li>
        </ul>
    </li>
</ul>

<h2>9. Alamat Salah atau Tidak Lengkap</h2>
<p>Pastikan alamat yang Anda input lengkap dan benar:</p>
<ul>
    <li>Jika paket gagal terkirim karena alamat salah, kami akan menghubungi Anda untuk konfirmasi alamat baru. Biaya pengiriman ulang <strong>ditanggung pembeli</strong>.</li>
    <li>Jika paket sudah dikembalikan ke kami (RTS — Return To Sender), opsinya: kirim ulang (biaya kurir baru) atau refund dikurangi biaya kurir asli.</li>
</ul>

<h2>10. Hari Libur dan Force Majeure</h2>
<p>Pengiriman tidak diproses pada hari libur nasional Indonesia. Estimasi dapat tertunda akibat:</p>
<ul>
    <li>Cuaca ekstrem, bencana alam</li>
    <li>Kondisi pandemic / pembatasan pemerintah</li>
    <li>Backlog kurir di musim Lebaran, Natal, atau periode 11.11/12.12</li>
</ul>
<p>Dalam kasus ini, kami akan memberitahu Anda dan tidak ada kompensasi keterlambatan.</p>

<h2>11. Hubungi Kami</h2>
<p>
    <strong><?= esc($company['name']) ?></strong><br>
    Email: <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a><br>
    Lokasi: <?= esc($company['city']) ?>
</p>
<?php
$body = ob_get_clean();
include __DIR__ . '/_shell.php';
?>
