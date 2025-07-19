<?php
// File: actions/mobil/detail.php

// Memanggil file konfigurasi dan fungsi, tidak memanggil auth.php di awal agar bisa diakses publik
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Ambil dan validasi ID mobil dari URL
$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0) {
    // Jika tidak ada ID, arahkan kembali ke halaman daftar mobil
    header("Location: " . BASE_URL . "mobil.php");
    exit;
}

// Ambil data lengkap mobil dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt->execute([$id_mobil]);
    $mobil = $stmt->fetch();
    // Jika mobil tidak ditemukan atau tidak tersedia, arahkan kembali
    if (!$mobil) {
        header("Location: " . BASE_URL . "mobil.php?status=not_found");
        exit;
    }
} catch (PDOException $e) {
    // Tangani error database
    die("Terjadi kesalahan pada database.");
}

$page_title = 'Detail Mobil: ' . htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']);
require_once '../../includes/header.php';
?>

<div class="page-header">
    <h1>Detail Mobil</h1>
</div>

<div class="detail-container">
    <div class="detail-image">
        <img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($mobil['gambar_mobil'] ?: 'default-car.png') ?>" alt="Gambar <?= htmlspecialchars($mobil['merk']) ?>">
    </div>
    <div class="detail-info">
        <h2><?= htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']) ?></h2>
        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $mobil['status'])) ?>"><?= htmlspecialchars($mobil['status']) ?></span>

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
                <span class="label">Harga Sewa / Hari</span>
                <span class="value price"><?= format_rupiah($mobil['harga_sewa_harian']) ?></span>
            </div>
        </div>

        <div class="info-item full-width">
            <span class="label">Spesifikasi & Fitur</span>
            <div class="value description">
                <?= nl2br(htmlspecialchars($mobil['spesifikasi'])) ?>
            </div>
        </div>
    </div>
</div>

<div class="booking-section">
    <?php if (isset($_SESSION['id_pengguna']) && $_SESSION['role'] === 'Pelanggan'): ?>
        <div class="form-container">
            <div class="form-box">
                <h3>Formulir Pemesanan</h3>
                <form action="<?= BASE_URL ?>actions/pemesanan/proses.php" method="POST">
                    <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
                    <input type="hidden" name="id_pengguna" value="<?= $_SESSION['id_pengguna'] ?>">
                    <input type="hidden" name="harga_sewa_harian" value="<?= $mobil['harga_sewa_harian'] ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai Sewa</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai Sewa</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Sewa Sekarang</button>
                </form>
            </div>
        </div>

    <?php elseif (isset($_SESSION['id_pengguna']) && in_array($_SESSION['role'], ['Admin', 'Karyawan'])): ?>
        <div class="info-box">
            <p>Anda login sebagai <?= $_SESSION['role'] ?>. Tombol pemesanan hanya tersedia untuk pelanggan.</p>
            <a href="<?= BASE_URL . strtolower($_SESSION['role']) ?>/dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

    <?php else: ?>
        <div class="info-box">
            <h3>Ingin Menyewa Mobil Ini?</h3>
            <p>Silakan login terlebih dahulu untuk melanjutkan proses pemesanan.</p>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-primary">Login untuk Memesan</a>
        </div>
    <?php endif; ?>
</div>


<?php
require_once '../../includes/footer.php';
?>