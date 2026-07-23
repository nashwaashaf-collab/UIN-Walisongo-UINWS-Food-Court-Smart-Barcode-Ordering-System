<?php
require_once __DIR__ . '/helper/db.php';

$users = [
    ['stand2@foodhunt.test', 'Penjaga Stand 2', 'stand2pass', 2],
    ['stand3@foodhunt.test', 'Penjaga Stand 3', 'stand3pass', 3],
    ['stand4@foodhunt.test', 'Penjaga Stand 4', 'stand4pass', 4],
];

header('Content-Type: text/plain; charset=utf-8');
try {
    foreach ($users as $u) {
        list($email, $name, $password, $standId) = $u;
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row) {
            echo "User exists: {$email} (id={$row['id']}).\n";
            // ensure stand_id is set
            $upd = $pdo->prepare('UPDATE users SET stand_id = ? WHERE id = ?');
            $upd->execute([$standId, $row['id']]);
            echo " - Updated stand_id={$standId}\n";
        } else {
            $ins = $pdo->prepare('INSERT INTO users (name, email, password, role, stand_id) VALUES (?, ?, ?, ?, ?)');
            $ins->execute([$name, $email, $password, 'stand', $standId]);
            echo "Inserted user: {$email} (stand_id={$standId})\n";
        }
    }
    echo "\nDone. Try logging in at /fd/login.php with the credentials above.\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
