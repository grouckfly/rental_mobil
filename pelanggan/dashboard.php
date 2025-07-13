<?php
// File: pelanggan/dashboard.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Pelanggan yang bisa mengakses
check_auth('Pelanggan');

$page_title = 'Dashboard Pelanggan';
require_once '../includes/header.php';

$id_pengguna = $_SESSION['id_pengguna'];

// Mengambil data statistik pelanggan
try {
    $stmt_aktif = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_pengguna = ? AND status_pemesanan IN ('Menunggu Pembayaran', 'Dikonfirmasi', 'Berjalan')");
    $stmt_aktif->execute([$id_pengguna]);
    $pemesanan_aktif = $stmt_aktif->fetchColumn();

    $stmt_selesai = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_pengguna = ? AND status_pemesanan = 'Selesai'");
    $stmt_selesai->execute([$id_pengguna]);
    $pemesanan_selesai = $stmt_selesai->fetchColumn();

    // Mengambil 1 pemesanan aktif terbaru untuk ditampilkan
    $stmt_booking = $pdo->prepare("
        SELECT p.*, m.merk, m.model, m.gambar_mobil
        FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.id_pengguna = ? AND p.status_pemesanan IN ('Menunggu Pembayaran', 'Dikonfirmasi', 'Berjalan')
        ORDER BY p.tanggal_pemesanan DESC LIMIT 1
    ");
    $stmt_booking->execute([$id_pengguna]);
    $booking_terbaru = $stmt_booking->fetch();

} catch (PDOException $e) {
    $pemesanan_aktif = $pemesanan_selesai = 'N/A';
    $booking_terbaru = null;
}
?>

<div class="page-header">
    <h1>Dashboard Saya</h1>
    <p>Selamat datang kembali, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
</div>

<?php display_flash_message(); ?>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Pemesanan Aktif</h3>
        <p class="widget-data"><?= $pemesanan_aktif ?></p>
        <a href="pemesanan.php">Lihat Detail &rarr;</a>
    </div>
    <div class="widget">
        <h3>Sewa Selesai</h3>
        <p class="widget-data"><?= $pemesanan_selesai ?></p>
        <a href="history.php">Lihat Riwayat &rarr;</a>
    </div>
</div>

<div class="section-container">
    <h2>Pemesanan Anda Saat Ini</h2>
    <?php if ($booking_terbaru): ?>
        <div class="active-booking-card">
            <img src="../uploads/mobil/<?= htmlspecialchars($booking_terbaru['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil">
            <div class="booking-details">
                <h3><?= htmlspecialchars($booking_terbaru['merk'] . ' ' . $booking_terbaru['model']) ?></h3>
                <p><strong>Tanggal Sewa:</strong> <?= date('d M Y', strtotime($booking_terbaru['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($booking_terbaru['tanggal_selesai'])) ?></p>
                <p><strong>Total Biaya:</strong> <?= format_rupiah($booking_terbaru['total_biaya']) ?></p>
                <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking_terbaru['status_pemesanan'])) ?>"><?= htmlspecialchars($booking_terbaru['status_pemesanan']) ?></span></p>
                <a href="pemesanan.php" class="btn btn-primary">Kelola Pemesanan</a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Anda belum memiliki pemesanan aktif.</p>
            <a href="../mobil.php" class="btn btn-primary">Sewa Mobil Sekarang</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '../includes/footer.php';
?>