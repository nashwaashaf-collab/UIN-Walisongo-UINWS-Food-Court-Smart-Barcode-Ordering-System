<?php
require_once __DIR__ . '/helper/functions.php';
if (isLoggedIn()) {
    redirect('index.php');
}
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($name === '' || $email === '' || $password === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (getUserByEmail($email)) {
        $error = 'Email sudah terdaftar.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?);');
        $stmt->execute([$name, $email, $password, 'pelanggan']);
        flash('success', 'Akun pelanggan berhasil dibuat. Silakan login.');
        redirect('login.php');
    }
}

$title = 'Daftar - Food Court';
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
    <h2>Daftar Pelanggan</h2>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('register.php') ?>">
        <label>Nama Lengkap</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn-primary">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="<?= base_url('login.php') ?>">Login</a></p>
</div>
</body>
</html>
<?php require_once __DIR__ . '/layout/footer.php';
