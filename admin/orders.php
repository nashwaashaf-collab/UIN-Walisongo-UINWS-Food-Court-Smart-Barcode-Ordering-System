<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');

$stmt = $pdo->query('SELECT o.*, u.name AS pelanggan_name, s.name AS stand_name FROM orders o JOIN users u ON u.id = o.user_id JOIN stands s ON s.id = o.stand_id ORDER BY o.created_at DESC');
$orders = $stmt->fetchAll();

$title = 'Pesanan - Admin Food Court';
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
    <h2>Semua Pesanan</h2>
</div>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Stand</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['pelanggan_name']) ?></td>
                    <td><?= htmlspecialchars($order['stand_name']) ?></td>
                    <td><?= formatRupiah($order['total']) ?></td>
                    <td><?= htmlspecialchars($order['payment_method']) ?></td>
                    <td><?= htmlspecialchars($order['payment_status']) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>  
</body>
</html>
<?php require_once __DIR__ . '/../layout/footer.php';
