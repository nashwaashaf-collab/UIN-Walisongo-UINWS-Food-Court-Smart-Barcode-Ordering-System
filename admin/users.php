<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');
$error = null;
$userToEdit = null;
$stands = getStands(false);

if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([intval($_GET['id'])]);
    $userToEdit = $stmt->fetch();
}

if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $userId = intval($_GET['id']);
    if ($userId === currentUser()['id']) {
        flash('error', 'Tidak dapat menghapus akun yang sedang Anda gunakan.');
        redirect('admin/users.php');
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    if ($stmt->rowCount() > 0) {
        flash('success', 'User berhasil dihapus.');
    } else {
        flash('error', 'User tidak ditemukan atau tidak dapat dihapus.');
    }
    redirect('admin/users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'pelanggan';
    $standId = !empty($_POST['stand_id']) ? intval($_POST['stand_id']) : null;

    if ($name === '' || $email === '' || $role === '') {
        $error = 'Nama, email, dan role wajib diisi.';
    } elseif ($role === 'stand' && $standId === null) {
        $error = 'Pilih stand untuk role Stand.';
    } else {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ?' . ($userId > 0 ? ' AND id <> ?' : ''));
        $params = [$email];
        if ($userId > 0) {
            $params[] = $userId;
        }
        $checkStmt->execute($params);
        if ($checkStmt->fetch()) {
            $error = 'Email sudah terdaftar pada user lain.';
        }
    }

    if (!$error) {
        if ($role !== 'stand') {
            $standId = null;
        }

        if ($userId > 0) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ?, role = ?, stand_id = ? WHERE id = ?');
                $stmt->execute([$name, $email, $password, $role, $standId, $userId]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, stand_id = ? WHERE id = ?');
                $stmt->execute([$name, $email, $role, $standId, $userId]);
            }
            if ($stmt->rowCount() > 0) {
                flash('success', 'User berhasil diperbarui.');
            } else {
                flash('success', 'Perubahan tersimpan.');
            }
        } else {
            if ($password === '') {
                $error = 'Password wajib diisi untuk user baru.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, stand_id) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $email, $password, $role, $standId]);
                flash('success', 'User baru berhasil ditambahkan.');
            }
        }
    }

    if (!$error) {
        redirect('admin/users.php');
    }
}

$users = $pdo->query('SELECT u.*, s.name AS stand_name FROM users u LEFT JOIN stands s ON u.stand_id = s.id ORDER BY u.created_at DESC')->fetchAll();
$title = 'Kelola User - Admin Food Court';
require_once __DIR__ . '/../layout/header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="section-title">
        <h2>Kelola User</h2>
    </div>
    <div class="form-box" style="margin-bottom: 32px;">
        <h3><?= $userToEdit ? 'Edit User' : 'Tambah User Baru' ?></h3>
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('admin/users.php') ?>">
            <?php if ($userToEdit): ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userToEdit['id']) ?>">
            <?php endif; ?>
            <label>Nama Lengkap</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $userToEdit['name'] ?? '') ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $userToEdit['email'] ?? '') ?>">
            <label>Password <?= $userToEdit ? '(kosongkan jika tidak ingin mengubah)' : '' ?></label>
            <input type="password" name="password" <?= $userToEdit ? '' : 'required' ?>>
            <label>Role</label>
            <select name="role" required onchange="document.getElementById('stand-select').style.display = this.value === 'stand' ? 'block' : 'none';">
                <?php $currentRole = $_POST['role'] ?? $userToEdit['role'] ?? 'pelanggan'; ?>
                <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="stand" <?= $currentRole === 'stand' ? 'selected' : '' ?>>Stand</option>
                <option value="pelanggan" <?= $currentRole === 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
            </select>
            <div id="stand-select" style="display: <?= ($currentRole === 'stand') ? 'block' : 'none' ?>; margin-top:12px;">
                <label>Pilih Stand (untuk role Stand)</label>
                <select name="stand_id">
                    <option value="">-- Pilih Stand --</option>
                    <?php foreach ($stands as $stand): ?>
                        <?php $selected = (string)($stand['id']) === (string)($_POST['stand_id'] ?? $userToEdit['stand_id'] ?? ''); ?>
                        <option value="<?= htmlspecialchars($stand['id']) ?>" <?= $selected ? 'selected' : '' ?>><?= htmlspecialchars($stand['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary"><?= $userToEdit ? 'Perbarui User' : 'Tambahkan User' ?></button>
            <?php if ($userToEdit): ?>
                <a href="<?= base_url('admin/users.php') ?>" class="btn-secondary" style="margin-left: 12px;">Batal</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-card">
        <h3>Daftar User</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Stand</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr>
                        <td colspan="7">Belum ada user.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['stand_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['created_at'] ?? '-') ?></td>
                            <td>
                                <a class="btn-secondary" href="<?= base_url('admin/users.php?action=edit&id=' . $user['id']) ?>">Edit</a>
                                <a class="btn-secondary" href="<?= base_url('admin/users.php?action=delete&id=' . $user['id']) ?>" onclick="return confirm('Hapus user ini?');" style="margin-left:10px;">Hapus</a>
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
