<?php
// File: actions/pengguna/detail.php (Versi Lengkap)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses hanya untuk Admin
check_auth('Admin');

$id_user = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_user === 0) {
    redirect_with_message('../../admin/user.php', 'ID pengguna tidak valid.', 'error');
}

try {
    // Ambil semua data pengguna dari database
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch();
    if (!$user) {
        redirect_with_message('../../admin/user.php', 'Pengguna tidak ditemukan.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('../../admin/user.php', 'Terjadi kesalahan pada database.', 'error');
}

$page_title = 'Detail Pengguna: ' . htmlspecialchars($user['username']);
require_once '../../includes/header.php';
?>

<div class="page-header">
    <h1>Detail Pengguna</h1>
</div>

<div class="detail-container single-column">
    <div class="detail-main">
        <h3>Informasi Akun</h3>
        <div class="info-grid">
            <div class="info-item"><span class="label">ID Pengguna</span><span class="value"><?= htmlspecialchars($user['id_pengguna']) ?></span></div>
            <div class="info-item"><span class="label">Username</span><span class="value"><?= htmlspecialchars($user['username']) ?></span></div>
            <div class="info-item"><span class="label">Role</span><span class="value"><span class="status-badge status-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></span></div>
            <div class="info-item"><span class="label">Tanggal Daftar</span><span class="value"><?= date('d F Y, H:i', strtotime($user['created_at'])) ?></span></div>
        </div>
        <hr>
        <h3>Data Pribadi</h3>
        <div class="info-grid">
            <div class="info-item"><span class="label">Nama Lengkap</span><span class="value"><?= htmlspecialchars($user['nama_lengkap']) ?></span></div>
            <div class="info-item"><span class="label">NIK</span><span class="value"><?= htmlspecialchars($user['nik'] ?: 'Belum diisi') ?></span></div>
            <div class="info-item"><span class="label">Email</span><span class="value"><?= htmlspecialchars($user['email']) ?></span></div>
            <div class="info-item"><span class="label">No. Telepon</span><span class="value"><?= htmlspecialchars($user['no_telp'] ?: 'Belum diisi') ?></span></div>
            <div class="info-item full-width"><span class="label">Alamat</span>
                <div class="value description"><?= htmlspecialchars($user['alamat'] ?: 'Belum diisi') ?></div>
            </div>
            <div class="info-item full-width"><span class="label">Foto KTP</span>
                <div class="value">
                    <?php if (!empty($user['foto_ktp'])): ?>
                        <a href="<?= BASE_URL ?>uploads/ktp/<?= htmlspecialchars($user['foto_ktp']) ?>" target="_blank">Lihat Foto KTP</a>
                    <?php else: ?>
                        Belum diunggah
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="detail-actions">
    <a href="../../admin/user.php" class="btn btn-secondary">Kembali ke Daftar</a>
    <a href="edit.php?id=<?= $user['id_pengguna'] ?>" class="btn btn-primary">Edit Pengguna Ini</a>
    <form action="hapus.php" method="POST" style="display:inline;" onsubmit="return confirm('Peringatan: Yakin ingin menghapus pengguna ini?');">
        <input type="hidden" name="id_pengguna" value="<?= $user['id_pengguna'] ?>">
        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>