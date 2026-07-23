<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('stand');
header('Content-Type: application/json');

$stand = getStandForUser(currentUser()['id']);
if (!$stand) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$lastCheck = isset($_GET['last_check']) ? intval($_GET['last_check']) : 0;
$lastCheckTime = $lastCheck > 0 ? date('Y-m-d H:i:s', $lastCheck) : '2000-01-01 00:00:00';

$stmt = $pdo->prepare('
    SELECT o.id, o.created_at, u.name AS pelanggan_name, o.total, 
           (SELECT GROUP_CONCAT(CONCAT(menu_name, " x", quantity)) FROM order_items WHERE order_id = o.id) AS menu_list
    FROM orders o 
    JOIN users u ON u.id = o.user_id 
    WHERE o.stand_id = ? AND o.created_at > ?
    ORDER BY o.created_at DESC 
    LIMIT 10
');
$stmt->execute([$stand['id'], $lastCheckTime]);
$newOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'orders' => $newOrders,
    'count' => count($newOrders),
    'current_time' => time()
]);
