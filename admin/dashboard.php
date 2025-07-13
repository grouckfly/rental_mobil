<?php
// File: admin/dashboard.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Admin yang bisa mengakses halaman ini
check_auth('Admin');

$page_title = 'Dashboard Admin';
require_once '../includes/header.php';

// Mengambil data statistik dari database
try {
    $total_pengguna = $pdo->query("SELECT COUNT(*) FROM pengguna")->fetchColumn();
    $total_mobil = $pdo->query("SELECT COUNT(*) FROM mobil")->fetchColumn();
    $pemesanan_aktif = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Berjalan'")->fetchColumn();
    $menunggu_konfirmasi = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Menunggu Pembayaran'")->fetchColumn();

    // Mengambil 5 pemesanan terbaru
    $stmt = $pdo->query("
        SELECT p.id_pemesanan, pg.nama_lengkap, m.merk, m.model, p.status_pemesanan
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        ORDER BY p.tanggal_pemesanan DESC
        LIMIT 5
    ");
    $recent_bookings = $stmt->fetchAll();

} catch (PDOException $e) {
    // Tangani error jika query gagal
    $total_pengguna = $total_mobil = $pemesanan_aktif = $menunggu_konfirmasi = 'N/A';
    $recent_bookings = [];
}
?>

<div class="page-header">
    <h1>Dashboard Administrator</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>. Berikut ringkasan aktivitas rental mobil Anda.</p>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Total Pengguna</h3>
        <p class="widget-data"><?= $total_pengguna ?></p>
        <a href="user.php">Kelola Pengguna &rarr;</a>
    </div>
    <div class="widget">
        <h3>Total Mobil</h3>
        <p class="widget-data"><?= $total_mobil ?></p>
        <a href="mobil.php">Kelola Mobil &rarr;</a>
    </div>
    <div class="widget">
        <h3>Pemesanan Aktif</h3>
        <p class="widget-data"><?= $pemesanan_aktif ?></p>
        <a href="pembayaran.php">Lihat Detail &rarr;</a>
    </div>
    <div class="widget">
        <h3>Menunggu Konfirmasi</h3>
        <p class="widget-data"><?= $menunggu_konfirmasi ?></p>
        <a href="pembayaran.php">Konfirmasi Sekarang &rarr;</a>
    </div>
</div>

<div class="table-container">
    <h2>Aktivitas Pemesanan Terbaru</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Mobil</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recent_bookings)): ?>
                <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($booking['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking['status_pemesanan'])) ?>"><?= htmlspecialchars($booking['status_pemesanan']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Tidak ada aktivitas terbaru.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>