<?php
// File: karyawan/dashboard.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Karyawan atau Admin yang bisa mengakses
check_auth(['Admin', 'Karyawan']);

$page_title = 'Dashboard Karyawan';
require_once '../includes/header.php';

// Mengambil data statistik operasional
try {
    $perlu_verifikasi = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Menunggu Pembayaran'")->fetchColumn();
    $sedang_disewa = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Berjalan'")->fetchColumn();
    $mobil_perawatan = $pdo->query("SELECT COUNT(*) FROM mobil WHERE status = 'Perawatan'")->fetchColumn();

    // Mengambil 5 pemesanan yang butuh tindakan
    $stmt = $pdo->query("
        SELECT p.id_pemesanan, pg.nama_lengkap, m.merk, m.model, p.status_pemesanan
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.status_pemesanan IN ('Menunggu Pembayaran', 'Dikonfirmasi')
        ORDER BY p.tanggal_pemesanan DESC
        LIMIT 5
    ");
    $recent_tasks = $stmt->fetchAll();

} catch (PDOException $e) {
    $perlu_verifikasi = $sedang_disewa = $mobil_perawatan = 'N/A';
    $recent_tasks = [];
}
?>

<div class="page-header">
    <h1>Dashboard Operasional</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>. Berikut adalah tugas operasional Anda.</p>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Perlu Verifikasi</h3>
        <p class="widget-data"><?= $perlu_verifikasi ?></p>
        <a href="pembayaran.php">Verifikasi Sekarang &rarr;</a>
    </div>
    <div class="widget">
        <h3>Mobil Sedang Disewa</h3>
        <p class="widget-data"><?= $sedang_disewa ?></p>
        <a href="pembayaran.php">Lihat Daftar &rarr;</a>
    </div>
    <div class="widget">
        <h3>Mobil Dalam Perawatan</h3>
        <p class="widget-data"><?= $mobil_perawatan ?></p>
        <a href="mobil.php">Kelola Status &rarr;</a>
    </div>
</div>

<div class="table-container">
    <h2>Tugas Terbaru</h2>
    <table>
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Pelanggan</th>
                <th>Mobil</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recent_tasks)): ?>
                <?php foreach ($recent_tasks as $task): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($task['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($task['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($task['merk'] . ' ' . $task['model']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $task['status_pemesanan'])) ?>"><?= htmlspecialchars($task['status_pemesanan']) ?></span></td>
                        <td>
                           <a href="../actions/pemesanan/detail.php?id=<?= $task['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Tidak ada tugas yang menunggu.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>