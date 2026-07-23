<?php
require_once __DIR__ . '/helper/functions.php';
if (isLoggedIn()) {
    $role = currentUser()['role'];
    if ($role === 'admin') {
        redirect('admin/index.php');
    }
    if ($role === 'stand') {
        redirect('stand/index.php');
    }
    if ($role === 'pelanggan') {
        redirect('pelanggan/dashboard.php');
    }
}
$title = 'Food Court - Dashboard Pemesanan Makanan';
require_once __DIR__ . '/layout/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Utama</title>
</head>
<body>
    <div class="hero">
    <div class="hero-copy">
        <h1>Nikmati Kemudahan Memesan Makanan Langsung dari Meja Anda</h1>
        <p>Scan barcode, pilih menu favorit, dan pesan makanan langsung dari meja Anda. Nikmati pengalaman pemesanan yang cepat, praktis, dan nyaman tanpa perlu mengantri.</p>
        <a class="btn-primary" href="<?= base_url('login.php') ?>">Mulai Pesan</a>
    </div>
    <div class="card">
        <div class="card-body">
            <h2>Cara Menggunakan Aplikasi</h2>
            <ul style="padding-left: 20px; color:#4f5a4e; line-height:1.8;">
                <li>Scan barcode yang tersedia di meja</li>
                <li>Pilih stand dan menu yang diinginkan</li>
                <li>Masukkan menu ke keranjang pesanan</li>
                <li>Lakukan checkout dan pilih metode pembayaran</li>
                <li>Tunggu pesanan diproses oleh stand</li>
                <li>Ambil atau nikmati pesanan saat siap</li>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
<?php require_once __DIR__ . '/layout/footer.php';
