<?php
$eyebrow = 'Legal / RFD';
ob_start();
?>
<p>
    Kami ingin Anda puas dengan setiap pembelian. Kebijakan Pengembalian ini menjelaskan kapan, bagaimana,
    dan dalam kondisi apa Anda dapat mengembalikan barang dan mendapatkan pengembalian dana.
</p>

<h2>1. Periode Pengembalian</h2>
<p>Anda dapat mengajukan pengembalian dalam waktu <strong>14 hari kalender</strong> sejak tanggal barang diterima. Setelah periode tersebut, kami tidak dapat menerima pengembalian kecuali ada cacat manufaktur yang tertutup garansi.</p>

<h2>2. Syarat Barang yang Dapat Dikembalikan</h2>
<ul>
    <li>Barang dalam kondisi <strong>belum digunakan</strong> dan masih dalam segel asli</li>
    <li>Kemasan utuh, lengkap dengan aksesoris, kabel, dan dokumen yang disertakan</li>
    <li>Kartu garansi (jika ada) belum diaktifkan atau distempel</li>
    <li>Stiker keamanan tidak rusak atau dilepas</li>
</ul>

<p>Barang berikut <strong>tidak dapat dikembalikan</strong> kecuali ada cacat:</p>
<ul>
    <li>Barang yang sudah dipasang, dioperasikan, atau dikonfigurasi (mis. earcup pad, keycap yang dilepas)</li>
    <li>Barang dengan kerusakan akibat penggunaan tidak wajar atau jatuh</li>
    <li>Barang sale atau clearance dengan label "non-returnable"</li>
</ul>

<h2>3. Alasan Pengembalian</h2>

<h3>3.1 Pengembalian karena perubahan keinginan (change of mind)</h3>
<ul>
    <li>Periode: 14 hari sejak diterima</li>
    <li>Biaya pengiriman pengembalian ditanggung <strong>oleh pembeli</strong></li>
    <li>Refund dilakukan setelah barang diperiksa kondisinya</li>
</ul>

<h3>3.2 Pengembalian karena cacat / barang salah / rusak saat pengiriman</h3>
<ul>
    <li>Periode: 14 hari sejak diterima</li>
    <li>Biaya pengiriman pengembalian <strong>kami tanggung</strong></li>
    <li>Wajib menyertakan foto/video bukti kerusakan saat mengajukan klaim</li>
    <li>Refund penuh atau penggantian barang sesuai pilihan Anda</li>
</ul>

<h2>4. Cara Mengajukan Pengembalian</h2>

<ol>
    <li>Email <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a> dengan subjek <em>"Return — Order #[ID pesanan Anda]"</em></li>
    <li>Sertakan: foto barang, alasan pengembalian, dan rekening tujuan refund</li>
    <li>Kami balas dalam <strong>1 × 24 jam kerja</strong> dengan instruksi pengiriman</li>
    <li>Kemas barang dengan aman (gunakan bubble wrap untuk barang elektronik)</li>
    <li>Kirim ke alamat yang kami berikan; mohon simpan resi sebagai bukti pengiriman</li>
    <li>Setelah barang diterima dan diperiksa, refund diproses dalam <strong>3–5 hari kerja</strong></li>
</ol>

<h2>5. Metode dan Jadwal Refund</h2>

<table>
    <thead><tr><th>Metode Pembayaran Asli</th><th>Refund Ke</th><th>Estimasi</th></tr></thead>
    <tbody>
        <tr><td>Transfer bank</td><td>Rekening tujuan yang Anda berikan</td><td>1–3 hari kerja</td></tr>
        <tr><td>E-wallet (GoPay, OVO, Dana)</td><td>E-wallet yang sama</td><td>1–2 hari kerja</td></tr>
        <tr><td>Kartu kredit/debit</td><td>Kartu yang sama (kembali ke statement)</td><td>5–14 hari kerja (tergantung bank)</td></tr>
        <tr><td>Virtual account</td><td>Rekening tujuan yang Anda berikan</td><td>1–3 hari kerja</td></tr>
    </tbody>
</table>

<p>Anda akan menerima email konfirmasi saat refund diproses. Jika dana belum masuk dalam estimasi waktu di atas, periksa rekening Anda dan hubungi kami.</p>

<h2>6. Pembatalan Pesanan</h2>

<p>Pesanan dapat dibatalkan pada status berikut:</p>

<table>
    <thead><tr><th>Status Pesanan</th><th>Bisa Dibatalkan?</th></tr></thead>
    <tbody>
        <tr><td><strong>Placed</strong> (belum bayar)</td><td>✓ Ya, batal otomatis setelah 24 jam jika tidak dibayar</td></tr>
        <tr><td><strong>Paid</strong></td><td>✓ Ya, hubungi kami sebelum status berubah ke Processing</td></tr>
        <tr><td><strong>Processing</strong></td><td>Sebagian — hubungi kami secepatnya</td></tr>
        <tr><td><strong>Shipped</strong></td><td>✗ Tidak. Tunggu barang tiba lalu ajukan pengembalian</td></tr>
        <tr><td><strong>Delivered</strong></td><td>Hanya via prosedur pengembalian (Bagian 4)</td></tr>
    </tbody>
</table>

<h2>7. Garansi Produk</h2>
<p>Garansi diberikan oleh produsen dan periode bervariasi per produk (biasanya 1–2 tahun). Detail garansi tertera di kemasan atau halaman produk. Klaim garansi setelah periode pengembalian 14 hari kami fasilitasi dengan menghubungkan Anda ke service center resmi.</p>

<h2>8. Pertanyaan</h2>
<p>
    <strong><?= esc($company['name']) ?></strong><br>
    Email: <a href="mailto:<?= esc($company['email']) ?>"><?= esc($company['email']) ?></a><br>
    Lokasi: <?= esc($company['city']) ?>
</p>
<?php
$body = ob_get_clean();
include __DIR__ . '/_shell.php';
?>
