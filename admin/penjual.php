<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');
$error = null;
$userToEdit = null;
$stands = getStands(false);

if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role = ? LIMIT 1');
    $stmt->execute([intval($_GET['id']), 'stand']);
    $userToEdit = $stmt->fetch();
}

if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $userId = intval($_GET['id']);
    if ($userId === currentUser()['id']) {
        flash('error', 'Tidak dapat menghapus akun yang sedang Anda gunakan.');
        redirect('admin/penjual.php');
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = ?');
    $stmt->execute([$userId, 'stand']);
    if ($stmt->rowCount() > 0) {
        flash('success', 'Penjual berhasil dihapus.');
    } else {
        flash('error', 'Penjual tidak ditemukan atau tidak dapat dihapus.');
    }
    redirect('admin/penjual.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $standId = !empty($_POST['stand_id']) ? intval($_POST['stand_id']) : null;

    if ($name === '' || $email === '' || $standId === null) {
        $error = 'Nama, email, dan stand wajib diisi.';
    } else {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND role = ?' . ($userId > 0 ? ' AND id <> ?' : ''));
        $params = [$email, 'stand'];
        if ($userId > 0) {
            $params[] = $userId;
        }
        $checkStmt->execute($params);
        if ($checkStmt->fetch()) {
            $error = 'Email sudah terdaftar pada penjual lain.';
        }
    }

    if (!$error) {
        if ($userId > 0) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ?, stand_id = ? WHERE id = ? AND role = ?');
                $stmt->execute([$name, $email, $password, $standId, $userId, 'stand']);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, stand_id = ? WHERE id = ? AND role = ?');
                $stmt->execute([$name, $email, $standId, $userId, 'stand']);
            }
            flash('success', 'Penjual berhasil diperbarui.');
        } else {
            if ($password === '') {
                $error = 'Password wajib diisi untuk penjual baru.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, stand_id) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $email, $password, 'stand', $standId]);
                flash('success', 'Penjual baru berhasil ditambahkan.');
            }
        }
    }

    if (!$error) {
        redirect('admin/penjual.php');
    }
}

$users = $pdo->prepare('SELECT u.*, s.name AS stand_name FROM users u LEFT JOIN stands s ON u.stand_id = s.id WHERE u.role = ? ORDER BY u.created_at DESC');
$users->execute(['stand']);
$users = $users->fetchAll();
$title = 'Kelola Penjual - Admin Food Court';
require_once __DIR__ . '/../layout/header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penjual</title>
</head>
<body>
    <div class="section-title">
        <h2>Kelola Penjual</h2>
    </div>
    <div class="form-box" style="margin-bottom: 32px;">
        <h3><?= $userToEdit ? 'Edit Penjual' : 'Tambah Penjual Baru' ?></h3>
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('admin/penjual.php') ?>">
            <?php if ($userToEdit): ?>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userToEdit['id']) ?>">
            <?php endif; ?>
            <label>Nama Lengkap</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? $userToEdit['name'] ?? '') ?>">
            <label>Email</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $userToEdit['email'] ?? '') ?>">
            <label>Password <?= $userToEdit ? '(kosongkan jika tidak ingin mengubah)' : '' ?></label>
            <input type="password" name="password" <?= $userToEdit ? '' : 'required' ?>>
            <label>Pilih Stand</label>
            <select name="stand_id" required>
                <option value="">-- Pilih Stand --</option>
                <?php $selectedStand = $_POST['stand_id'] ?? $userToEdit['stand_id'] ?? ''; ?>
                <?php foreach ($stands as $stand): ?>
                    <option value="<?= htmlspecialchars($stand['id']) ?>" <?= ((string)$selectedStand === (string)$stand['id']) ? 'selected' : '' ?>><?= htmlspecialchars($stand['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary"><?= $userToEdit ? 'Perbarui Penjual' : 'Tambahkan Penjual' ?></button>
            <?php if ($userToEdit): ?>
                <a href="<?= base_url('admin/penjual.php') ?>" class="btn-secondary" style="margin-left: 12px;">Batal</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="table-card">
        <h3>Daftar Penjual</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Stand</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr>
                        <td colspan="6">Belum ada penjual.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['stand_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['created_at'] ?? '-') ?></td>
                            <td>
                                <a class="btn-secondary" href="<?= base_url('admin/penjual.php?action=edit&id=' . $user['id']) ?>">Edit</a>
                                <?php if ($user['id'] !== currentUser()['id']): ?>
                                    <a class="btn-secondary" href="<?= base_url('admin/penjual.php?action=delete&id=' . $user['id']) ?>" onclick="return confirm('Hapus penjual ini?');" style="margin-left:10px;">Hapus</a>
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
