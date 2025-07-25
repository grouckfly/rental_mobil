<?php
// File: actions/mobil/detail.php (Versi Lengkap)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid.', 'error');
}

// Ambil role session dengan aman
$role_session = $_SESSION['role'] ?? null;
$user_data = null;

// Jika yang melihat pelanggan, ambil data profilnya
if ($role_session === 'Pelanggan') {
    $stmt_user = $pdo->prepare("SELECT nik, foto_ktp FROM pengguna WHERE id_pengguna = ?");
    $stmt_user->execute([$_SESSION['id_pengguna']]);
    $user_data = $stmt_user->fetch();
}

// Ambil semua data mobil dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt->execute([$id_mobil]);
    $mobil = $stmt->fetch();
    if (!$mobil) {
        redirect_with_message('../../admin/mobil.php', 'Mobil dengan ID tersebut tidak ditemukan.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('../../admin/mobil.php', 'Terjadi kesalahan pada database.', 'error');
}

$page_title = 'Detail Mobil: ' . htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']);
require_once '../../includes/header.php';
?>

<div class="page-top-bar">
    <div class="page-header">
        <h1>Detail Mobil</h1>
    </div>
    <div class="detail-actions">
        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
            <a href="<?= BASE_URL . strtolower($role_session) ?>/mobil.php" class="btn btn-secondary">Kembali</a>
            <a href="edit.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-primary">Edit</a>
            <?php if ($role_session === 'Admin'): ?>
                <form action="hapus.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus mobil ini?');">
                    <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="detail-container">
    <div class="detail-image">
        <img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($mobil['gambar_mobil'] ?: 'default-car.png') ?>" alt="Gambar <?= htmlspecialchars($mobil['merk']) ?>">
    </div>

    <div class="detail-info">
        <h2><?= htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']) ?></h2>
        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $mobil['status'])) ?>"><?= htmlspecialchars($mobil['status']) ?></span>

        <div class="info-grid">
            <div class="info-item"><span class="label">Plat Nomor</span><span class="value"><?= htmlspecialchars($mobil['plat_nomor']) ?></span></div>
            <div class="info-item"><span class="label">Tahun</span><span class="value"><?= htmlspecialchars($mobil['tahun']) ?></span></div>
            <div class="info-item"><span class="label">Jenis Mobil</span><span class="value"><?= htmlspecialchars($mobil['jenis_mobil']) ?></span></div>
            <div class="info-item"><span class="label">Kelas Mobil</span><span class="value"><?= htmlspecialchars($mobil['kelas_mobil']) ?></span></div>
            <div class="info-item"><span class="label">Harga Sewa / Hari</span><span class="value price"><?= format_rupiah($mobil['harga_sewa_harian']) ?></span></div>
            <div class="info-item"><span class="label">Denda / Hari</span><span class="value price"><?= format_rupiah($mobil['denda_per_hari']) ?></span></div>
        </div>
    </div>

    <div class="detail-full-width">
        <div class="info-item">
            <span class="label">Spesifikasi & Fitur</span>
            <div class="value description">
                <?= nl2br(htmlspecialchars($mobil['spesifikasi'])) ?>
            </div>
        </div>
    </div>
</div>

<div class="booking-section">
    <?php // --- Jika yang melihat adalah PELANGGAN dan mobil TERSEDIA ---
    if ($role_session === 'Pelanggan' && $mobil['status'] === 'Tersedia'):

        // Cek kelengkapan profil di sini
        if (!empty($user_data['nik']) && !empty($user_data['foto_ktp'])):?>
            <div class="form-container">
                <div class="form-box">
                    <h3>Formulir Pemesanan</h3>
                    <form action="<?= BASE_URL ?>actions/pemesanan/proses.php" method="POST">
                        <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
                        <input type="hidden" name="id_pengguna" value="<?= $_SESSION['id_pengguna'] ?>">
                        <input type="hidden" name="harga_sewa_harian" value="<?= $mobil['harga_sewa_harian'] ?>">
                        <div class="form-grid">
                            <div class="form-group"><label for="tanggal_mulai">Tanggal Mulai</label><input type="date" id="tanggal_mulai" name="tanggal_mulai" required min="<?= date('Y-m-d') ?>"></div>
                            <div class="form-group"><label for="tanggal_selesai">Tanggal Selesai</label><input type="date" id="tanggal_selesai" name="tanggal_selesai" required min="<?= date('Y-m-d') ?>"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Sewa Sekarang</button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div class="info-box" style="border-color: var(--warning-color);">
                <h3>Profil Belum Lengkap</h3>
                <p>Anda harus melengkapi NIK dan Foto KTP di profil Anda sebelum dapat melakukan pemesanan.</p>
                <a href="<?= BASE_URL ?>pelanggan/profile.php" class="btn btn-primary">Lengkapi Profil Sekarang</a>
            </div>

        <?php // --- Jika yang melihat adalah PENGUNJUNG (belum login) dan mobil TERSEDIA ---
        endif;
    elseif ($role_session === null && $mobil['status'] === 'Tersedia'): ?>
        <div class="info-box">
            <h3>Ingin Menyewa Mobil Ini?</h3>
            <p>Silakan login terlebih dahulu untuk melanjutkan proses pemesanan.</p>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-primary">Login untuk Memesan</a>
            <a href="<?= BASE_URL ?>mobil.php" class="btn btn-secondary">Kembali</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../../includes/footer.php';
?>