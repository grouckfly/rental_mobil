<?php
// File: admin/dashboard.php (Versi Dashboard Informatif)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Admin');
$page_title = 'Dashboard Admin';
require_once '../includes/header.php';

// --- DATA UNTUK WIDGET ---
try {
    // Total Pengguna & Mobil
    $total_pengguna = $pdo->query("SELECT COUNT(*) FROM pengguna")->fetchColumn();
    $total_mobil = $pdo->query("SELECT COUNT(*) FROM mobil WHERE status != 'Tidak Aktif'")->fetchColumn();
    
    // Pesanan yang butuh tindakan
    $menunggu_verifikasi = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Menunggu Verifikasi'")->fetchColumn();
    $pengajuan_perubahan = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan IN ('Pengajuan Ambil Cepat', 'Pengajuan Pembatalan')")->fetchColumn();
    
    // Total Pendapatan Bulan Ini (hanya dari pesanan yang 'Selesai')
    $stmt_pendapatan = $pdo->prepare("SELECT SUM(total_biaya + total_denda) FROM pemesanan WHERE status_pemesanan = 'Selesai' AND MONTH(waktu_pengembalian) = MONTH(NOW()) AND YEAR(waktu_pengembalian) = YEAR(NOW())");
    $stmt_pendapatan->execute();
    $pendapatan_bulan_ini = $stmt_pendapatan->fetchColumn();

} catch (PDOException $e) {
    // Set nilai default jika query gagal
    $total_pengguna = $total_mobil = $menunggu_verifikasi = $pengajuan_perubahan = 0;
    $pendapatan_bulan_ini = 0;
}

// --- DATA UNTUK GRAFIK GANDA (Jumlah & Pendapatan) ---
$chart_labels = [];
$chart_jumlah = [];
$chart_pendapatan = [];
try {
    $stmt_chart = $pdo->query("
        SELECT 
            DATE_FORMAT(tanggal_pemesanan, '%Y-%m') AS bulan, 
            COUNT(*) AS jumlah,
            SUM(total_biaya) AS pendapatan
        FROM pemesanan
        WHERE tanggal_pemesanan >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status_pemesanan = 'Selesai'
        GROUP BY bulan
        ORDER BY bulan ASC
    ");
    $chart_data = $stmt_chart->fetchAll();

    foreach ($chart_data as $data) {
        $chart_labels[] = date("M Y", strtotime($data['bulan'] . "-01"));
        $chart_jumlah[] = $data['jumlah'];
        $chart_pendapatan[] = $data['pendapatan'];
    }
} catch (PDOException $e) { /* Biarkan array kosong jika error */ }


// --- DATA UNTUK TABEL TUGAS & TINDAKAN CEPAT ---
try {
    $stmt_tasks = $pdo->query("
        SELECT p.id_pemesanan, p.kode_pemesanan, pg.nama_lengkap, p.status_pemesanan, p.updated_at
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        WHERE p.status_pemesanan IN ('Menunggu Verifikasi', 'Pengajuan Ambil Cepat', 'Pengajuan Pembatalan', 'Menunggu Pembayaran Denda')
        ORDER BY p.updated_at DESC
        LIMIT 5
    ");
    $quick_tasks = $stmt_tasks->fetchAll();
} catch (PDOException $e) { $quick_tasks = []; }
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="page-header">
    <h1>Dashboard Administrator</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>. Ringkasan aktivitas rental Anda.</p>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Pendapatan Bulan Ini</h3>
        <p class="widget-data price"><?= format_rupiah($pendapatan_bulan_ini ?? 0) ?></p>
        <div class="widget-details"><span>Dari pesanan yang selesai</span></div>
        <a href="history.php">Lihat Laporan &rarr;</a>
    </div>
    <div class="widget">
        <h3>Mobil Aktif</h3>
        <p class="widget-data"><?= $total_mobil ?></p>
        <div class="widget-details"><span>Total mobil yang siap disewa</span></div>
        <a href="mobil.php">Kelola Mobil &rarr;</a>
    </div>
    <div class="widget">
        <h3>Menunggu Verifikasi</h3>
        <p class="widget-data"><?= $menunggu_verifikasi ?></p>
        <div class="widget-details"><span>Pembayaran sewa atau denda</span></div>
        <a href="pembayaran.php">Verifikasi Sekarang &rarr;</a>
    </div>
    <div class="widget">
        <h3>Pengajuan Pelanggan</h3>
        <p class="widget-data"><?= $pengajuan_perubahan ?></p>
        <div class="widget-details"><span>Pembatalan atau perubahan jadwal</span></div>
        <a href="pembayaran.php">Tinjau Pengajuan &rarr;</a>
    </div>
</div>

<div class="chart-container">
    <h2>Analisis Kinerja (6 Bulan Terakhir)</h2>
    <div class="chart-wrapper">
        <canvas id="dashboardChart"
                data-labels='<?= json_encode($chart_labels) ?>'
                data-values-jumlah='<?= json_encode($chart_jumlah) ?>'
                data-values-pendapatan='<?= json_encode($chart_pendapatan) ?>'>
        </canvas>
    </div>
</div>

<div class="table-container" data-live-context="admin_pemesanan" data-live-total="..." data-live-last-update="...">
    <h2>Tugas & Tindakan Cepat</h2>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Jenis Tugas</th>
                <th>Update Terakhir</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($quick_tasks)): ?>
                <?php foreach ($quick_tasks as $task): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($task['kode_pemesanan']) ?></strong></td>
                        <td><?= htmlspecialchars($task['nama_lengkap']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $task['status_pemesanan'])) ?>"><?= htmlspecialchars($task['status_pemesanan']) ?></span></td>
                        <td><?= date('d M Y, H:i', strtotime($task['updated_at'])) ?></td>
                        <td><a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $task['id_pemesanan'] ?>" class="btn btn-primary btn-sm">Proses</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">Tidak ada tugas yang menunggu.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>