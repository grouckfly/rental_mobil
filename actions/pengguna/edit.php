<?php
// File: actions/pengguna/edit.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Admin');
$id_user = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if ($id_user === 0) { redirect_with_message('../../admin/user.php', 'ID tidak valid.', 'error'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Logika validasi dan UPDATE sama seperti sebelumnya) ...
    // ... Cek duplikat (kecuali diri sendiri) ...
    // ... Logika update password jika diisi ...
    // ... Query UPDATE ...
    // ... Redirect on success or error ...
}

try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch();
    if (!$user) { redirect_with_message('../../admin/user.php', 'Pengguna tidak ditemukan.', 'error'); }
} catch (PDOException $e) { redirect_with_message('../../admin/user.php', 'Error database.', 'error'); }

$page_title = 'Edit Pengguna';
require_once '../../includes/header.php';
?>
<div class="page-header"><h1>Edit Pengguna: <?= htmlspecialchars($user['username']) ?></h1></div>
<?php display_flash_message(); ?>
<div class="form-container admin-form">
    <div class="form-box">
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?= $user['id_pengguna'] ?>">
            <div class="form-grid">
                 <div class="form-group"><label>Username</label><input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>"></div>
                 <div class="form-group"><label>Password Baru</label><input type="password" name="password"><small>Kosongkan jika tidak diubah</small></div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="../../admin/user.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>