<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('pelanggan');
$standId = intval($_GET['stand_id'] ?? 0);
$stand = getStandById($standId);
if (!$stand) {
    flash('error', 'Stand tidak ditemukan.');
    redirect('pelanggan/dashboard.php');
}
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = intval($_POST['menu_id'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $cart = getCart();
    if ($cart) {
        $standIds = array_unique(array_column($cart, 'stand_id'));
        if (count($standIds) > 1 || $standIds[0] !== $standId) {
            $error = 'Silakan selesaikan pesanan di keranjang terlebih dahulu sebelum memesan dari stand lain.';
        }
    }
    if (!$error && addToCart($menuId, $quantity)) {
        flash('success', 'Menu berhasil ditambahkan ke keranjang.');
        redirect('pelanggan/cart.php');
    } elseif (!$error) {
        $error = 'Menu tidak ditemukan atau gagal ditambahkan.';
    }
}
$menus = getMenusByStand($standId);
$title = 'Menu Stand - Food Court';
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
    <style>
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        justify-items: center;
        max-width: 1220px;
        margin: 0 auto;
    }

    .menu-card {
        border-radius: 20px;
        overflow: hidden;
        min-height: 240px;
        max-width: 320px;
        width: 100%;
    }

    .menu-card img {
        width: 100% !important;
        height: 120px !important;
        object-fit: cover !important;
    }

    .menu-card .card-body {
        padding: 12px !important;
    }

    .menu-card h3 {
        font-size: 1rem;
        margin-bottom: 8px;
    }

    .menu-card p {
        margin-bottom: 10px;
        line-height: 1.4;
        font-size: 0.95rem;
    }

    .menu-card form {
        display: grid;
        gap: 10px;
    }

    .menu-card input[type='number'] {
        width: 100%;
        max-width: 100px;
        padding: 8px 10px;
        border-radius: 12px;
    }

    .menu-card .btn-primary {
        width: fit-content;
        padding: 8px 16px;
        font-size: 0.95rem;
    }
    </style>
    <div class="section-title">
        <h2>Menu Stand <?= htmlspecialchars($stand['name']) ?></h2>
    </div>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="menu-grid">
        <?php foreach ($menus as $menu): ?>
            <div class="card menu-card" style="max-width:320px !important; width:100% !important;">
                <img src="<?= base_url('assets/' . $menu['image']) ?>" alt="<?= htmlspecialchars($menu['name']) ?>" style="height:120px !important; width:100% !important; object-fit:cover !important;">
                <div class="card-body">
                    <h3><?= htmlspecialchars($menu['name']) ?></h3>
                    <p><?= htmlspecialchars($menu['description']) ?></p>
                    <p><strong><?= formatRupiah($menu['price']) ?></strong></p>
                    <form method="post" action="<?= base_url('pelanggan/menu.php?stand_id=' . $stand['id']) ?>">
                        <input type="hidden" name="menu_id" value="<?= $menu['id'] ?>">
                        <label>Jumlah</label>
                        <input type="number" name="quantity" value="1" min="1">
                        <button type="submit" class="btn-primary">Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
<?php require_once __DIR__ . '/../layout/footer.php';
