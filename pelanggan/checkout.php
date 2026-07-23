<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('pelanggan');
$cart = getCart();
if (empty($cart)) {
    flash('error', 'Keranjang kosong.');
    redirect('pelanggan/dashboard.php');
}
$standId = array_values($cart)[0]['stand_id'];
$stand = getStandById($standId);
if (!$stand) {
    flash('error', 'Stand tidak ditemukan.');
    redirect('pelanggan/dashboard.php');
}
$paymentMethod = $_POST['payment_method'] ?? null;
$step = 'form';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle quantity update actions from +/- buttons or direct set
    if (!empty($_POST['action']) && $_POST['action'] === 'update_qty') {
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
        }
        redirect('pelanggan/checkout.php');
    }

    if ($paymentMethod === 'cash') {
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, stand_id, total, payment_method, payment_status, items, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([currentUser()['id'], $standId, cartTotal(), 'Cash', 'Bayar tunai diterima', json_encode($cart)]);
        $orderId = $pdo->lastInsertId();
        saveOrderItems($orderId, $cart);
        unset($_SESSION['cart']);
        flash('success', 'Pesanan berhasil dibuat. Silakan bayar tunai saat pengambilan.');
        redirect('pelanggan/dashboard.php');
    }
    if ($paymentMethod === 'qris') {
        $step = 'qris';
    }
    if ($paymentMethod === 'confirm_qris') {
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, stand_id, total, payment_method, payment_status, items, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([currentUser()['id'], $standId, cartTotal(), 'QRIS', 'Pembayaran QRIS dikonfirmasi', json_encode($cart)]);
        $orderId = $pdo->lastInsertId();
        saveOrderItems($orderId, $cart);
        unset($_SESSION['cart']);
        flash('success', 'Pembayaran QRIS berhasil dikonfirmasi. Pesanan Anda telah diproses.');
        redirect('pelanggan/dashboard.php');
    }
}
$title = 'Checkout - Food Court';
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
    <h2>Checkout</h2>
</div>
<div class="card-grid" style="grid-template-columns: 1fr;">
    <div class="card">
        <div class="card-body">
            <h3>Detail Pesanan</h3>
            <p><strong>Stand:</strong> <?= htmlspecialchars($stand['name']) ?></p>
            <div class="table-card" style="margin:12px 0 18px 0;">
                <table>
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= formatRupiah($item['price']) ?></td>
                                <td style="white-space:nowrap;">
                                    <form method="post" action="<?= base_url('pelanggan/checkout.php') ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                        <input type="hidden" name="op" value="dec">
                                        <button type="submit" class="btn-secondary">-</button>
                                    </form>
                                    <span style="margin:0 8px; display:inline-block; min-width:36px; text-align:center;"><?= htmlspecialchars($item['quantity']) ?></span>
                                    <form method="post" action="<?= base_url('pelanggan/checkout.php') ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id']) ?>">
                                        <input type="hidden" name="op" value="inc">
                                        <button type="submit" class="btn-secondary">+</button>
                                    </form>
                                    <!-- removed Set input for cleaner UI -->
                                </td>
                                <td><?= formatRupiah($item['price'] * $item['quantity']) ?></td>
                                <td><a class="btn-secondary" href="<?= base_url('pelanggan/cart.php?remove=' . $item['id']) ?>">Hapus</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p><strong>Total:</strong> <?= formatRupiah(cartTotal()) ?></p>
            <?php if ($step === 'form'): ?>
                <form method="post" action="<?= base_url('pelanggan/checkout.php') ?>">
                    <label>Pilih metode pembayaran</label>
                    <select name="payment_method" required>
                        <option value="">-- Pilih --</option>
                        <option value="qris">QRIS</option>
                        <option value="cash">Cash</option>
                    </select>
                    <button type="submit" class="btn-primary">Lanjutkan</button>
                </form>
            <?php elseif ($step === 'qris'): ?>
                <div class="card-body">
                    <p>Silakan scan QRIS berikut untuk menyelesaikan pembayaran.</p>
                    <img src="<?= base_url('assets/' . $stand['qris_image']) ?>" alt="QRIS <?= htmlspecialchars($stand['name']) ?>" style="max-width: 280px; display:block; margin: 18px 0;">
                    <form method="post" action="<?= base_url('pelanggan/checkout.php') ?>">
                        <input type="hidden" name="payment_method" value="confirm_qris">
                        <button type="submit" class="btn-primary">Konfirmasi Pembayaran QRIS</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
<?php require_once __DIR__ . '/../layout/footer.php';
