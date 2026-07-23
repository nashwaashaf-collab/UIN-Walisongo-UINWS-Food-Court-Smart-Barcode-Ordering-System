<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('pelanggan');
$cart = getCart();
// Handle remove via GET
if (isset($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    if (isset($cart[$removeId])) {
        unset($cart[$removeId]);
        $_SESSION['cart'] = $cart;
        flash('success', 'Item berhasil dihapus dari keranjang.');
        redirect('pelanggan/cart.php');
    }
}

// Handle quantity updates (inc/dec/set)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'update_qty') {
    $itemId = intval($_POST['item_id'] ?? 0);
    $op = $_POST['op'] ?? '';
    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : null;
    $cart = getCart();
    if (isset($cart[$itemId])) {
        if ($op === 'inc') {
            $cart[$itemId]['quantity'] = intval($cart[$itemId]['quantity']) + 1;
        } elseif ($op === 'dec') {
            $cart[$itemId]['quantity'] = max(1, intval($cart[$itemId]['quantity']) - 1);
        } elseif ($op === 'set' && $qty !== null) {
            $cart[$itemId]['quantity'] = max(1, $qty);
        }
        $_SESSION['cart'] = $cart;
        flash('success', 'Jumlah pesanan berhasil diperbarui.');
    }
    redirect('pelanggan/cart.php');
}
$title = 'Keranjang - Food Court';
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
    <h2>Keranjang Saya</h2>
</div>
<?php if (empty($cart)): ?>
    <div class="card">
        <div class="card-body">
            <p>Keranjang Anda kosong. Silakan pilih menu pada <a href="<?= base_url('pelanggan/dashboard.php') ?>">dashboard</a>.</p>
        </div>
    </div>
<?php else: ?>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Stand</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['stand_name']) ?></td>
                        <td style="white-space:nowrap;">
                            <form method="post" action="<?= base_url('pelanggan/cart.php') ?>" style="display:inline;">
                                <input type="hidden" name="action" value="update_qty">
                                <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <input type="hidden" name="op" value="dec">
                                <button type="submit" class="btn-secondary">-</button>
                            </form>
                            <span style="margin:0 8px; display:inline-block; min-width:36px; text-align:center;"><?= htmlspecialchars($item['quantity']) ?></span>
                            <form method="post" action="<?= base_url('pelanggan/cart.php') ?>" style="display:inline;">
                                <input type="hidden" name="action" value="update_qty">
                                <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <input type="hidden" name="op" value="inc">
                                <button type="submit" class="btn-secondary">+</button>
                            </form>
                            <!-- removed Set input for cleaner UI -->
                        </td>
                        <td><?= formatRupiah($item['price']) ?></td>
                        <td><?= formatRupiah($item['price'] * $item['quantity']) ?></td>
                        <td><a class="btn-secondary" href="<?= base_url('pelanggan/cart.php?remove=' . $item['id']) ?>">Hapus</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
            <strong>Total: <?= formatRupiah(cartTotal()) ?></strong>
            <a class="btn-primary" href="<?= base_url('pelanggan/checkout.php') ?>">Lanjutkan ke Checkout</a>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
<?php require_once __DIR__ . '/../layout/footer.php';
