<?php
// File: actions/mobil/detail.php (Versi Lengkap)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk Admin dan Karyawan
check_auth(['Admin', 'Karyawan']);

$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid.', 'error');
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
        <a href="../../admin/mobil.php" class="btn btn-secondary">Kembali</a>
        <a href="edit.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-primary">Edit</a>
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <form action="hapus.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus mobil ini?');">
                <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
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

<?php
require_once '../../includes/footer.php';
?>