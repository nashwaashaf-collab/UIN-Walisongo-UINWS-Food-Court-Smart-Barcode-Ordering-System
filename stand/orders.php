<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('stand');
$stand = getStandForUser(currentUser()['id']);
if (!$stand) {
    flash('error', 'Akun stand belum terhubung dengan data stand.');
    redirect('index.php');
}
$stmt = $pdo->prepare('SELECT o.*, u.name AS pelanggan_name FROM orders o JOIN users u ON u.id = o.user_id WHERE o.stand_id = ? ORDER BY o.created_at DESC');
$stmt->execute([$stand['id']]);
$orders = $stmt->fetchAll();

$title = 'Pesanan Stand - Food Court';
require_once __DIR__ . '/../layout/header.php';
function formatOrderItems($itemsJson)
{
    $items = json_decode($itemsJson, true);
    if (!is_array($items)) {
        return '-';
    }
    $list = [];
    foreach ($items as $item) {
        if (empty($item['name']) || empty($item['quantity'])) {
            continue;
        }
        $list[] = htmlspecialchars($item['name']) . ' x' . intval($item['quantity']);
    }
    return empty($list) ? '-' : implode(', ', $list);
}

function formatOrderItemsForOrder($orderId, $itemsJson)
{
    $items = getOrderItemsByOrder($orderId);
    if (!empty($items)) {
        $list = [];
        foreach ($items as $item) {
            $list[] = htmlspecialchars($item['menu_name']) . ' x' . intval($item['quantity']);
        }
        return implode(', ', $list);
    }
    return formatOrderItems($itemsJson);
}
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
    <h2>Pesanan untuk <?= htmlspecialchars($stand['name']) ?></h2>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Menu</th>
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
                <td><?= formatOrderItemsForOrder($order['id'], $order['items']) ?></td>
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
