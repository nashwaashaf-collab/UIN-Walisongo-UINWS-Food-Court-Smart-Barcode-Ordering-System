<?php
session_start();
require_once __DIR__ . '/db.php';

seedStand4();
seedMenus();
seedStandUsers();
seedOrderItemsTable();

function seedStand4()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT id FROM stands WHERE image = ? LIMIT 1');
        $stmt->execute(['stand4.jpeg']);
        if (!$stmt->fetch()) {
            $insert = $pdo->prepare('INSERT INTO stands (name, description, image, qris_image, status) VALUES (?, ?, ?, ?, 1)');
            $insert->execute([
                'Stand 4',
                'Stand terbaru dengan pilihan menu favorit dan tampilan modern.',
                'stand4.jpeg',
                'qris.jpg'
            ]);
        }
    } catch (Exception $e) {
        // Jika tabel belum dibuat atau koneksi gagal, biarkan proses normal berjalan.
    }
}

function seedMenus()
{
    global $pdo;
    $menus = [
        'menu1.jpeg' => [1, 'Salad Salmon', 'Salad segar dengan salmon bakar.', 38000.00],
        'menu2.jpeg' => [1, 'Nasi Goreng Ayam', 'Nasi goreng spesial dengan ayam premium.', 27000.00],
        'menu3.jpeg' => [1, 'Soto Betawi', 'Soto Betawi hangat dengan empal dan koya.', 29000.00],
        'menu4.jpeg' => [1, 'Ayam Geprek', 'Ayam geprek pedas dengan nasi hangat.', 25000.00],
        'menu5.jpeg' => [2, 'Burger Sayur', 'Burger sehat dengan roti gandum dan sayuran.', 32000.00],
        'menu6.jpeg' => [2, 'Spaghetti Bolognese', 'Pasta klasik dengan saus daging sapi.', 34000.00],
        'menu7.jpeg' => [2, 'Steak Ayam', 'Steak ayam panggang dengan saus jamur.', 36000.00],
        'menu8.jpeg' => [3, 'Nasi Campur', 'Nasi campur khas dengan lauk lengkap.', 30000.00],
        'menu9.jpeg' => [3, 'Ikan Bakar', 'Ikan bakar madu dengan sambal matah.', 38000.00],
        'menu10.jpeg' => [3, 'Sate Ayam', 'Sate ayam bumbu kacang dan lontong.', 28000.00],
        'menu11.jpeg' => [4, 'Burger Daging', 'Burger daging premium dengan keju.', 42000.00],
        'menu12.jpeg' => [4, 'Nasi Lemak', 'Nasi lemak gurih dengan sambal pedas.', 35000.00],
        'menu13.jpeg' => [4, 'Ayam Teriyaki', 'Ayam teriyaki manis pedas dengan nasi.', 33000.00],
    ];

    try {
        foreach ($menus as $image => $data) {
            [$standId, $name, $description, $price] = $data;
            $stmt = $pdo->prepare('SELECT id FROM menus WHERE image = ? LIMIT 1');
            $stmt->execute([$image]);
            $existing = $stmt->fetch();
            // Only insert missing seed menus. Do NOT overwrite existing menus to
            // avoid clobbering user edits in the CRUD UI.
            if (!$existing) {
                $insert = $pdo->prepare('INSERT INTO menus (stand_id, name, description, price, image, available) VALUES (?, ?, ?, ?, ?, 1)');
                $insert->execute([$standId, $name, $description, $price, $image]);
            }
        }
    } catch (Exception $e) {
        // Jika tabel belum dibuat atau koneksi gagal, biarkan proses normal berjalan.
    }
}

function seedStandUsers()
{
    global $pdo;
    $users = [
        ['stand2@foodhunt.test', 'Penjaga Stand 2', 'stand2pass', 2],
        ['stand3@foodhunt.test', 'Penjaga Stand 3', 'stand3pass', 3],
        ['stand4@foodhunt.test', 'Penjaga Stand 4', 'stand4pass', 4],
    ];

    try {
        foreach ($users as $u) {
            [$email, $name, $password, $standId] = $u;
            $stmt = $pdo->prepare('SELECT id, stand_id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $existing = $stmt->fetch();
            if ($existing) {
                // ensure stand_id is set correctly for the user without changing password
                if (empty($existing['stand_id']) || intval($existing['stand_id']) !== intval($standId)) {
                    $upd = $pdo->prepare('UPDATE users SET stand_id = ? WHERE id = ?');
                    $upd->execute([$standId, $existing['id']]);
                }
            } else {
                $ins = $pdo->prepare('INSERT INTO users (name, email, password, role, stand_id) VALUES (?, ?, ?, ?, ?)');
                $ins->execute([$name, $email, $password, 'stand', $standId]);
            }
        }
    } catch (Exception $e) {
        // jika koneksi belum siap atau tabel belum ada, abaikan dan lanjut
    }
}

$rootDir = str_replace('\\', '/', realpath(__DIR__ . '/../'));
$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$baseUrl = str_replace($documentRoot, '', $rootDir);
$baseUrl = $baseUrl === '' ? '' : '/' . trim($baseUrl, '/');

function base_url($path = '')
{
    global $baseUrl;
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . base_url($path));
    exit;
}

function flash($key, $message = null)
{
    if ($message === null) {
        if (!empty($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $msg;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
    return null;
}

function showFlash($key)
{
    $message = flash($key);
    if ($message) {
        return '<div class="alert">' . htmlspecialchars($message) . '</div>';
    }
    return '';
}

function isLoggedIn()
{
    return !empty($_SESSION['user']);
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

function requireRole($roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    if (!isLoggedIn() || !in_array(currentUser()['role'], $roles, true)) {
        flash('error', 'Akses ditolak.');
        redirect('index.php');
    }
}

function getUserByEmail($email)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function getStandById($id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM stands WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getStandForUser($userId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT s.* FROM stands s JOIN users u ON u.stand_id = s.id WHERE u.id = ? LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function getStands($activeOnly = true)
{
    global $pdo;
    if ($activeOnly) {
        $stmt = $pdo->query('SELECT * FROM stands WHERE status = 1 ORDER BY id');
    } else {
        $stmt = $pdo->query('SELECT * FROM stands ORDER BY id');
    }
    return $stmt->fetchAll();
}

function getMenusByStand($standId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE stand_id = ? AND available = 1 ORDER BY id');
    $stmt->execute([$standId]);
    return $stmt->fetchAll();
}

function getMenusByStandAll($standId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE stand_id = ? ORDER BY id');
    $stmt->execute([$standId]);
    return $stmt->fetchAll();
}

function seedOrderItemsTable()
{
    global $pdo;
    try {
        $pdo->exec('CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            menu_id INT NOT NULL,
            menu_name VARCHAR(150) NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(12,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
    } catch (Exception $e) {
        // If the tables are not yet ready, ignore and retry on later requests.
    }
}

function saveOrderItems($orderId, $cart)
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, menu_id, menu_name, quantity, price, total_price) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($cart as $item) {
        $stmt->execute([
            $orderId,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity'],
        ]);
    }
}

function getOrderItemsByOrder($orderId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function getOrderCountByStand($standId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE stand_id = ?');
    $stmt->execute([$standId]);
    return $stmt->fetchColumn();
}

function formatRupiah($value)
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}

function addToCart($menuId, $quantity = 1)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT m.*, s.name AS stand_name, s.image AS stand_image, s.qris_image FROM menus m JOIN stands s ON s.id = m.stand_id WHERE m.id = ? LIMIT 1');
    $stmt->execute([$menuId]);
    $item = $stmt->fetch();
    if (!$item) {
        return false;
    }
    $cart = $_SESSION['cart'] ?? [];
    if (!empty($cart[$menuId])) {
        $cart[$menuId]['quantity'] += $quantity;
    } else {
        $cart[$menuId] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'stand_id' => $item['stand_id'],
            'stand_name' => $item['stand_name'],
            'image' => $item['image'],
            'qris_image' => $item['qris_image'],
        ];
    }
    $_SESSION['cart'] = $cart;
    return true;
}

function getCart()
{
    return $_SESSION['cart'] ?? [];
}

function cartTotal()
{
    $total = 0;
    foreach (getCart() as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
