<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');
$error = null;
$userToEdit = null;

if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role = ? LIMIT 1');
    $stmt->execute([intval($_GET['id']), 'pelanggan']);
    $userToEdit = $stmt->fetch();
}

if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $userId = intval($_GET['id']);
    if ($userId === currentUser()['id']) {
        flash('error', 'Tidak dapat menghapus akun yang sedang Anda gunakan.');
        redirect('admin/pelanggan.php');
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = ?');
    $stmt->execute([$userId, 'pelanggan']);
    if ($stmt->rowCount() > 0) {
        flash('success', 'Pelanggan berhasil dihapus.');
    } else {
        flash('error', 'Pelanggan tidak ditemukan atau tidak dapat dihapus.');
    }
    redirect('admin/pelanggan.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '') {
        $error = 'Nama dan email wajib diisi.';
    } else {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND role = ?' . ($userId > 0 ? ' AND id <> ?' : ''));
        $params = [$email, 'pelanggan'];
        if ($userId > 0) {
            $params[] = $userId;
        }
        $checkStmt->execute($params);
        if ($checkStmt->fetch()) {
            $error = 'Email sudah terdaftar pada pelanggan lain.';
        }
    }

    if (!$error) {
        if ($userId > 0) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ? AND role = ?');
                $stmt->execute([$name, $email, $password, $userId, 'pelanggan']);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ? AND role = ?');
                $stmt->execute([$name, $email, $userId, 'pelanggan']);
            }
            flash('success', 'Pelanggan berhasil diperbarui.');
        } else {
            if ($password === '') {
                $error = 'Password wajib diisi untuk pelanggan baru.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $email, $password, 'pelanggan']);
                flash('success', 'Pelanggan baru berhasil ditambahkan.');
            }
        }
    }

    if (!$error) {
        redirect('admin/pelanggan.php');
    }
}

$users = $pdo->prepare('SELECT * FROM users WHERE role = ? ORDER BY created_at DESC');
$users->execute(['pelanggan']);
$users = $users->fetchAll();
$title = 'Kelola Pelanggan - Admin Food Court';
require_once __DIR__ . '/../layout/header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan</title>
</head>
<body>
    <div class="section-title">
        <h2>Kelola Pelanggan</h2>
    </div>
    <div class="form-box" style="margin-bottom: 32px;">
        <h3><?= $userToEdit ? 'Edit Pelanggan' : 'Tambah Pelanggan Baru' ?></h3>
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('admin/pelanggan.php') ?>">
            <?php if ($userToEdit): ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userToEdit['id']) ?>">
            <?php endif; ?>
            <label>Nama Lengkap</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $userToEdit['name'] ?? '') ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $userToEdit['email'] ?? '') ?>">
            <label>Password <?= $userToEdit ? '(kosongkan jika tidak ingin mengubah)' : '' ?></label>
            <input type="password" name="password" <?= $userToEdit ? '' : 'required' ?>>
            <button type="submit" class="btn-primary"><?= $userToEdit ? 'Perbarui Pelanggan' : 'Tambahkan Pelanggan' ?></button>
            <?php if ($userToEdit): ?>
                <a href="<?= base_url('admin/pelanggan.php') ?>" class="btn-secondary" style="margin-left: 12px;">Batal</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-card">
        <h3>Daftar Pelanggan</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr>
                        <td colspan="5">Belum ada pelanggan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['created_at'] ?? '-') ?></td>
                            <td>
                                <a class="btn-secondary" href="<?= base_url('admin/pelanggan.php?action=edit&id=' . $user['id']) ?>">Edit</a>
                                <?php if ($user['id'] !== currentUser()['id']): ?>
                                    <a class="btn-secondary" href="<?= base_url('admin/pelanggan.php?action=delete&id=' . $user['id']) ?>" onclick="return confirm('Hapus pelanggan ini?');" style="margin-left:10px;">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php require_once __DIR__ . '/../layout/footer.php';
