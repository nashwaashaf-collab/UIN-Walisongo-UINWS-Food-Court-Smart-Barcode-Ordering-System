<?php
require_once __DIR__ . '/helper/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = getUserByEmail($email);
    $isValid = false;
    $shouldRehash = false;

    if ($user) {
        if ($password === $user['password']) {
            $isValid = true;
        } elseif (password_verify($password, $user['password'])) {
            $isValid = true;
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $shouldRehash = true;
            }
        }
    }

    if ($isValid) {
        if ($shouldRehash) {
            global $pdo;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$newHash, $user['id']]);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'stand_id' => $user['stand_id'] ?? null,
        ];
        flash('success', 'Login berhasil. Selamat datang, ' . htmlspecialchars($user['name']) . '!');
        if ($user['role'] === 'admin') {
            redirect('admin/index.php');
        }
        if ($user['role'] === 'stand') {
            redirect('stand/index.php');
        }
        redirect('pelanggan/dashboard.php');
    }

    $error = 'Email atau password salah. Silakan coba lagi.';
}

$title = 'Login - Food Court';
require_once __DIR__ . '/layout/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="form-box">
    <h2>Login</h2>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('login.php') ?>">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn-primary">Masuk</button>
    </form>
    <p>Belum punya akun? <a href="<?= base_url('register.php') ?>">Daftar sebagai pelanggan</a></p>
</div>
</body>
</html>
<?php require_once __DIR__ . '/layout/footer.php';
