<?php
require_once __DIR__ . '/../helper/functions.php';
requireRole('stand');
$stand = getStandForUser(currentUser()['id']);
if (!$stand) {
    flash('error', 'Akun stand belum terhubung dengan data stand.');
    redirect('index.php');
}
$error = null;
$menuToEdit = null;

if (!empty($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ? AND stand_id = ? LIMIT 1');
    $stmt->execute([intval($_GET['id']), $stand['id']]);
    $menuToEdit = $stmt->fetch();
}

if (!empty($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM menus WHERE id = ? AND stand_id = ?');
    $stmt->execute([intval($_GET['id']), $stand['id']]);
    if ($stmt->rowCount() > 0) {
        flash('success', 'Menu berhasil dihapus.');
    } else {
        flash('error', 'Menu tidak ditemukan atau tidak dapat dihapus.');
    }
    redirect('stand/menus.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = intval($_POST['menu_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $image = trim($_POST['image'] ?? 'menu1.jpeg');
    $available = isset($_POST['available']) ? 1 : 0;
    if ($name === '' || $description === '' || $price <= 0) {
        $error = 'Nama, deskripsi, dan harga menu wajib diisi dengan benar.';
    } else {
        if ($menuId > 0) {
            $stmt = $pdo->prepare('UPDATE menus SET name = ?, description = ?, price = ?, image = ?, available = ? WHERE id = ? AND stand_id = ?');
            $stmt->execute([$name, $description, $price, $image, $available, $menuId, $stand['id']]);
            if ($stmt->rowCount() > 0) {
                flash('success', 'Menu berhasil diperbarui.');
            } else {
                flash('error', 'Tidak ada perubahan yang disimpan atau menu tidak ditemukan.');
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO menus (stand_id, name, description, price, image, available) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$stand['id'], $name, $description, $price, $image, $available]);
            flash('success', 'Menu berhasil ditambahkan.');
        }
        redirect('stand/menus.php');
    }
}

$menus = getMenusByStandAll($stand['id']);
$title = 'Kelola Menu Stand - Food Court';
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
    <h2>Kelola Menu: <?= htmlspecialchars($stand['name']) ?></h2>
</div>
<div class="form-box" style="margin-bottom: 32px;">
    <h3><?= $menuToEdit ? 'Edit Menu' : 'Tambah Menu Baru' ?></h3>
    <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('stand/menus.php') ?>">
        <?php if ($menuToEdit): ?>
            <input type="hidden" name="menu_id" value="<?= htmlspecialchars($menuToEdit['id']) ?>">
        <?php endif; ?>
        <label>Nama Menu</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($menuToEdit['name'] ?? '') ?>">
        <label>Deskripsi Menu</label>
        <textarea name="description" rows="3" required><?= htmlspecialchars($menuToEdit['description'] ?? '') ?></textarea>
        <label>Harga (angka)</label>
        <input type="text" name="price" required value="<?= htmlspecialchars($menuToEdit['price'] ?? '') ?>">
        <label>Nama file gambar menu (misal menu1.jpeg)</label>
        <input type="text" name="image" value="<?= htmlspecialchars($menuToEdit['image'] ?? 'menu1.jpeg') ?>">
        <label><input type="checkbox" name="available" <?= isset($menuToEdit['available']) ? ($menuToEdit['available'] ? 'checked' : '') : 'checked' ?>> Tersedia</label>
        <button type="submit" class="btn-primary"><?= $menuToEdit ? 'Perbarui Menu' : 'Simpan Menu' ?></button>
        <?php if ($menuToEdit): ?>
            <a href="<?= base_url('stand/menus.php') ?>" class="btn-secondary" style="margin-left: 12px;">Batal</a>
        <?php endif; ?>
    </form>
</div>
<div class="table-card">
    <h3>Daftar Menu</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Menu</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($menus as $index => $menu): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($menu['name']) ?></td>
                <td><?= formatRupiah($menu['price']) ?></td>
                <td><span class="badge <?= $menu['available'] ? 'badge-success' : 'badge-warning' ?>"><?= $menu['available'] ? 'Tersedia' : 'Tidak tersedia' ?></span></td>
                <td>
                    <a class="btn-secondary" href="<?= base_url('stand/menus.php?action=edit&id=' . $menu['id']) ?>">Edit</a>
                    <a class="btn-secondary" href="<?= base_url('stand/menus.php?action=delete&id=' . $menu['id']) ?>" onclick="return confirm('Hapus menu ini?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php require_once __DIR__ . '/../layout/footer.php';
