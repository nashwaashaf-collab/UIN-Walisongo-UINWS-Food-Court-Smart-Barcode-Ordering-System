<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('pelanggan');
$stands = getStands(true);
$title = 'Dashboard Pelanggan - Food Court';
require_once __DIR__ . '/../layout/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="hero" style="padding: 0; gap: 28px;">
    <div class="hero-copy">
        <div class="tag">Food Court</div>
        <h1>Pesan makanan favorit dari berbagai stand dengan cepat.</h1>
        <p>Scroll untuk memilih stand. Setiap stand punya menu sendiri, dan Anda bisa bayar pakai QRIS atau cash langsung di checkout.</p>
        <a class="btn-primary" href="#stands">Lihat Stand</a>
    </div>
    <div class="card" style="padding: 28px;">
        <h2>Apa yang bisa Anda lakukan?</h2>
        <ul style="color: #4f5a4e; line-height: 1.8; padding-left: 20px;">
            <li>Pilih stand makanan dengan tampilan modern.</li>
            <li>Lihat menu lengkap dan tambahkan ke keranjang.</li>
            <li>Cekout dengan QRIS atau bayar cash.</li>
            <li>Semua transaksi tercatat rapi di sistem.</li>
        </ul>
    </div>
</div>
<div id="stands" class="stand-list" style="margin-top: 36px;">
    <?php foreach ($stands as $stand): ?>
        <div class="card stand-card">
            <div class="stand-info">
                <div class="small-tag">Stand</div>
                <h3><?= htmlspecialchars($stand['name']) ?></h3>
                <p><?= htmlspecialchars($stand['description']) ?></p>
                <a class="btn-primary" href="<?= base_url('pelanggan/menu.php?stand_id=' . $stand['id']) ?>">Lihat Menu</a>
            </div>
            <div class="stand-image-wrapper">
                <img src="<?= base_url('assets/' . $stand['image']) ?>" alt="Stand <?= htmlspecialchars($stand['name']) ?>">
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
<?php require_once __DIR__ . '/../layout/footer.php';
