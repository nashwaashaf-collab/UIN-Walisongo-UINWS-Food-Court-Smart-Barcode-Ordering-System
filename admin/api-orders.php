<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');
header('Content-Type: application/json');

$lastCheck = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;
$lastCheckTime = $lastCheck > 0 ? date('Y-m-d H:i:s', $lastCheck) : '2000-01-01 00:00:00';

$stmt = $pdo->prepare('
    SELECT o.id, o.created_at, u.name AS pelanggan_name, s.name AS stand_name, o.total,
           (SELECT GROUP_CONCAT(CONCAT(menu_name, " x", quantity)) FROM order_items WHERE order_id = o.id) AS menu_list
    FROM orders o 
    JOIN users u ON u.id = o.user_id 
    JOIN stands s ON s.id = o.stand_id
    WHERE o.created_at > ?
    ORDER BY o.created_at DESC 
    LIMIT 10
');
$stmt->execute([$lastCheckTime]);
$newOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'orders' => $newOrders,
    'count' => count($newOrders),
    'current_time' => time()
]);
