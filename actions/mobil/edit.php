<?php
// File: actions/mobil/edit.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../admin/mobil.php', 'ID tidak valid.', 'error');
}

// ===================================================================
// BAGIAN 1: PROSES FORM JIKA DISUBMIT (METHOD POST)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_mobil = (int)$_POST['id_mobil'];
    // ... logika validasi dan update ...
    // ... logika update gambar jika ada yang baru ...
    try {
        // ... Query UPDATE ...
        redirect_with_message('../../admin/mobil.php', 'Data mobil berhasil diperbarui!');
    } catch (PDOException $e) {
        redirect_with_message("edit.php?id=$id_mobil", 'Gagal memperbarui: ' . $e->getMessage(), 'error');
    }
}

// ===================================================================
// BAGIAN 2: AMBIL DATA & TAMPILKAN FORM (METHOD GET)
// ===================================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt->execute([$id_mobil]);
    $mobil = $stmt->fetch();
    if (!$mobil) {
        redirect_with_message('../../admin/mobil.php', 'Mobil tidak ditemukan.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('../../admin/mobil.php', 'Error database.', 'error');
}

$page_title = 'Edit Mobil';
require_once '../../includes/header.php';
?>
<div class="page-header"><h1>Edit Mobil: <?= htmlspecialchars($mobil['merk'].' '.$mobil['model']) ?></h1></div>
<?php display_flash_message(); ?>
<div class="form-container admin-form">
    <div class="form-box">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
            <div class="form-grid">
                <div class="form-group"><label>Merk</label><input type="text" name="merk" required value="<?= htmlspecialchars($mobil['merk']) ?>"></div>
                <div class="form-group full-width"><label>Ganti Gambar (Opsional)</label><input type="file" name="gambar_mobil"></div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="../../admin/mobil.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php
require_once '../../includes/footer.php';
?>