<?php
// File: actions/mobil/detail.php

// Panggil semua file konfigurasi yang dibutuhkan
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk melihat halaman ini
check_auth(['Admin', 'Karyawan']);

// Ambil dan validasi ID mobil dari URL
$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid.', 'error');
}

// Ambil data lengkap mobil dari database
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

<div class="page-header">
    <h1>Detail Mobil</h1>
</div>

<div class="detail-container">
    <div class="detail-image">
        <img src="../../assets/img/mobil/<?= htmlspecialchars($mobil['gambar_mobil'] ?: 'default-car.png') ?>" alt="Gambar <?= htmlspecialchars($mobil['merk']) ?>">
    </div>
    <div class="detail-info">
        <h2><?= htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']) ?></h2>
        <span class="status-badge status-<?= strtolower($mobil['status']) ?>"><?= htmlspecialchars($mobil['status']) ?></span>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">Plat Nomor</span>
                <span class="value"><?= htmlspecialchars($mobil['plat_nomor']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Tahun</span>
                <span class="value"><?= htmlspecialchars($mobil['tahun']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Jenis Mobil</span>
                <span class="value"><?= htmlspecialchars($mobil['jenis_mobil']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Harga Sewa / Hari</span>
                <span class="value price"><?= format_rupiah($mobil['harga_sewa_harian']) ?></span>
            </div>
            <div class="info-item">
                <span class="label">Denda / Hari</span>
                <span class="value price"><?= format_rupiah($mobil['denda_per_hari']) ?></span>
            </div>
        </div>

        <div class="info-item full-width">
            <span class="label">Spesifikasi & Fitur</span>
            <div class="value description">
                <?= nl2br(htmlspecialchars($mobil['spesifikasi'])) ?>
            </div>
        </div>

        <div class="detail-actions">
            <a href="../../admin/mobil.php" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>