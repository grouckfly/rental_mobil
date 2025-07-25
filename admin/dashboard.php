<?php
// File: admin/dashboard.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Admin yang bisa mengakses halaman ini
check_auth('Admin');

$page_title = 'Dashboard Admin';
// PENTING: Untuk grafik, kita perlu memanggil header di sini
require_once '../includes/header.php';

// --- DATA UNTUK WIDGET ---
try {
    $total_pengguna = $pdo->query("SELECT COUNT(*) FROM pengguna")->fetchColumn();
    $total_mobil = $pdo->query("SELECT COUNT(*) FROM mobil")->fetchColumn();
    $pemesanan_aktif = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Berjalan'")->fetchColumn();
    $menunggu_konfirmasi = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Menunggu Pembayaran'")->fetchColumn();
} catch (PDOException $e) {
    $total_pengguna = $total_mobil = $pemesanan_aktif = $menunggu_konfirmasi = 'N/A';
}

// --- DATA UNTUK GRAFIK (6 BULAN TERAKHIR) ---
$chart_labels = [];
$chart_values = [];
try {
    $stmt_chart = $pdo->query("
        SELECT DATE_FORMAT(tanggal_pemesanan, '%Y-%m') AS bulan, COUNT(*) AS jumlah
        FROM pemesanan
        WHERE tanggal_pemesanan >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY bulan
        ORDER BY bulan ASC
    ");
    $chart_data = $stmt_chart->fetchAll();

    foreach ($chart_data as $data) {
        $chart_labels[] = date("F Y", strtotime($data['bulan'] . "-01")); // Format nama bulan (e.g., Juli 2025)
        $chart_values[] = $data['jumlah'];
    }
} catch (PDOException $e) {
    // Biarkan array kosong jika error
}

// --- DATA UNTUK TABEL AKTIVITAS TERBARU ---
try {
    $stmt_recent = $pdo->query("
        SELECT p.id_pemesanan, pg.nama_lengkap, m.merk, m.model, p.status_pemesanan
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        ORDER BY p.tanggal_pemesanan DESC
        LIMIT 5
    ");
    $recent_bookings = $stmt_recent->fetchAll();
} catch (PDOException $e) {
    $recent_bookings = [];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header"
    data-live-context="admin_dashboard"
    data-live-total="<?= $pdo->query("SELECT COUNT(*) FROM pemesanan")->fetchColumn() ?>"
    data-live-last-update="<?= $pdo->query("SELECT MAX(tanggal_pemesanan) FROM pemesanan")->fetchColumn() ?>">
    <h1>Dashboard Administrator</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>. Berikut ringkasan aktivitas rental mobil Anda.</p>
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

<div class="chart-container">
    <h2>Grafik Pemesanan (6 Bulan Terakhir)</h2>
    <div class="chart-wrapper">
        <canvas id="dashboardChart"
            data-labels='<?= json_encode($chart_labels) ?>'
            data-values='<?= json_encode($chart_values) ?>'>
        </canvas>
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