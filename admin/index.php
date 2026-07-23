<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');

$standCount = $pdo->query('SELECT COUNT(*) FROM stands')->fetchColumn();
$orderCount = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$adminCount = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn();
$pelangganCount = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "pelanggan"')->fetchColumn();
$penjualCount = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "stand"')->fetchColumn();

$title = 'Admin Dashboard - Food Court';
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
    <div class="section-title">
    <h2>Panel Admin</h2>
    </div>
    <div class="card-grid" style="grid-template-columns: repeat(auto-fit,minmax(240px,1fr));">
        <div class="card">
            <div class="card-body">
                <h3>Stand</h3>
                <p class="tag"><?= $standCount ?> Stand aktif</p>
                <a href="<?= base_url('admin/stands.php') ?>" class="btn-secondary">Kelola Stand</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Pesanan</h3>
                <p class="tag"><?= $orderCount ?> Pesanan masuk</p>
                <a href="<?= base_url('admin/orders.php') ?>" class="btn-secondary">Lihat Pesanan</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Admin</h3>
                <p class="tag"><?= $adminCount ?> Akun admin</p>
                <a href="<?= base_url('admin/admins.php') ?>" class="btn-secondary">Kelola Admin</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Pelanggan</h3>
                <p class="tag"><?= $pelangganCount ?> Akun pelanggan</p>
                <a href="<?= base_url('admin/pelanggan.php') ?>" class="btn-secondary">Kelola Pelanggan</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Penjual</h3>
                <p class="tag"><?= $penjualCount ?> Akun penjual</p>
                <a href="<?= base_url('admin/penjual.php') ?>" class="btn-secondary">Kelola Penjual</a>
            </div>
        </div>
    </div> 
</body>
</html>

<?php require_once __DIR__ . '/../layout/footer.php';
