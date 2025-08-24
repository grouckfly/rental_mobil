<?php
// File: pelanggan/profile.php (Tampilan Baru)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(); // Berlaku untuk semua role

$id_pengguna = $_SESSION['id_pengguna'];
$page_title = 'Profil Saya';
require_once '../includes/header.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_pengguna]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Gagal mengambil data profil.");
}
?>


<div class="detail-container single-column">
    <div class="detail-main">

        <div class="page-header with-action">
            <h1>Profil Saya</h1>
            <a href="edit_profile.php" class="btn btn-primary">Edit Profil</a>
        </div>
        <hr>

        <h3>Informasi Akun</h3>
        <div class="info-grid">
            <div class="info-item"><span class="label">ID Pengguna</span><span class="value"><?= htmlspecialchars($user['id_pengguna']) ?></span></div>
            <div class="info-item"><span class="label">Username</span><span class="value"><?= htmlspecialchars($user['username']) ?></span></div>
            <div class="info-item"><span class="label">Role</span><span class="value"><span class="status-badge status-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></span></div>
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
                        <a href="<?= BASE_URL ?>assets/img/ktp/<?= htmlspecialchars($user['foto_ktp']) ?>" target="_blank">Lihat Foto KTP</a>
                    <?php else: ?>
                        Belum diunggah
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="detail-actions">
    <form action="<?= BASE_URL ?>actions/pengguna/hapus_akun.php" method="POST" onsubmit="return confirm('PERINGATAN: Anda akan menghapus akun Anda. Lanjutkan?');">
        
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <button type="submit" class="btn btn-danger">Hapus Akun Saya</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>