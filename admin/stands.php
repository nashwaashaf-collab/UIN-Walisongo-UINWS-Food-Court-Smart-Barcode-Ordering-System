<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('admin');
$error = null;
$standToEdit = null;

if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM stands WHERE id = ? LIMIT 1');
    $stmt->execute([intval($_GET['id'])]);
    $standToEdit = $stmt->fetch();
}

if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM stands WHERE id = ?');
    $stmt->execute([intval($_GET['id'])]);
    if ($stmt->rowCount() > 0) {
        flash('success', 'Stand berhasil dihapus.');
    } else {
        flash('error', 'Stand tidak ditemukan atau tidak dapat dihapus.');
    }
    redirect('admin/stands.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $standId = intval($_POST['stand_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['image'] ?? 'stand1.jpeg');
    $qris_image = trim($_POST['qris_image'] ?? 'qris.jpg');
    $status = isset($_POST['status']) ? 1 : 0;
    if ($name === '' || $description === '') {
        $error = 'Nama dan deskripsi stand wajib diisi.';
    } else {
        if ($standId > 0) {
            $stmt = $pdo->prepare('UPDATE stands SET name = ?, description = ?, image = ?, qris_image = ?, status = ? WHERE id = ?');
            $stmt->execute([$name, $description, $image, $qris_image, $status, $standId]);
            if ($stmt->rowCount() > 0) {
                flash('success', 'Stand berhasil diperbarui.');
            } else {
                flash('error', 'Tidak ada perubahan yang disimpan atau stand tidak ditemukan.');
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO stands (name, description, image, qris_image, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description, $image, $qris_image, $status]);
            flash('success', 'Stand baru berhasil ditambahkan.');
        }
        redirect('admin/stands.php');
    }
}

$stands = getStands(false);
$title = 'Kelola Stand - Admin Food Court';
require_once __DIR__ . '/../layout/header.php';
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
    <h2>Kelola Stand</h2>
</div>
<div class="form-box" style="margin-bottom: 32px;">
    <h3><?= $standToEdit ? 'Edit Stand' : 'Tambah Stand Baru' ?></h3>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('admin/stands.php') ?>">
        <?php if ($standToEdit): ?>
            <input type="hidden" name="stand_id" value="<?= htmlspecialchars($standToEdit['id']) ?>">
        <?php endif; ?>
        <label>Nama Stand</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($standToEdit['name'] ?? '') ?>">
        <label>Deskripsi</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($standToEdit['description'] ?? '') ?></textarea>
        <label>Nama file gambar stand (misal stand1.jpeg)</label>
        <input type="text" name="image" value="<?= htmlspecialchars($standToEdit['image'] ?? 'stand1.jpeg') ?>">
        <label>Nama file QRIS (misal qris.jpg)</label>
        <input type="text" name="qris_image" value="<?= htmlspecialchars($standToEdit['qris_image'] ?? 'qris.jpg') ?>">
        <label><input type="checkbox" name="status" <?= isset($standToEdit['status']) && $standToEdit['status'] ? 'checked' : (!isset($standToEdit['status']) ? 'checked' : '') ?>> Aktif</label>
        <button type="submit" class="btn-primary"><?= $standToEdit ? 'Perbarui Stand' : 'Tambahkan Stand' ?></button>
        <?php if ($standToEdit): ?>
            <a href="<?= base_url('admin/stands.php') ?>" class="btn-secondary" style="margin-left: 12px;">Batal</a>
        <?php endif; ?>
    </form>
</div>
<div class="table-card">
    <h3>Daftar Stand</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Stand</th>
                <th>Deskripsi</th>
                <th>QRIS</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($stands as $stand): ?>
            <tr>
                <td><?= htmlspecialchars($stand['id']) ?></td>
                <td><?= htmlspecialchars($stand['name']) ?></td>
                <td><?= htmlspecialchars($stand['description']) ?></td>
                <td>
                    <?php if (!empty($stand['qris_image'])): ?>
                        <img src="<?= base_url('assets/' . $stand['qris_image']) ?>" alt="QRIS <?= htmlspecialchars($stand['name']) ?>" style="max-width:96px; height:auto; display:block;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><span class="badge <?= $stand['status'] ? 'badge-success' : 'badge-warning' ?>"><?= $stand['status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td>
                    <a class="btn-secondary" href="<?= base_url('admin/stands.php?action=edit&id=' . $stand['id']) ?>">Edit</a>
                    <a class="btn-secondary" href="<?= base_url('admin/stands.php?action=delete&id=' . $stand['id']) ?>" onclick="return confirm('Hapus stand ini?');" style="margin-left:10px;">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php require_once __DIR__ . '/../layout/footer.php';
