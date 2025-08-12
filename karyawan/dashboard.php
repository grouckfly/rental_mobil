<?php
// File: karyawan/dashboard.php (Versi Lebih Informatif)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$page_title = 'Dashboard Karyawan';
require_once '../includes/header.php';

// --- PENGAMBILAN DATA UNTUK WIDGET & TUGAS ---
try {
    // Menghitung jumlah tugas yang menunggu tindakan
    $perlu_verifikasi_bayar = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Menunggu Verifikasi'")->fetchColumn();
    $pengajuan_pelanggan = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan IN ('Pengajuan Ambil Cepat', 'Pengajuan Pembatalan')")->fetchColumn();
    
    // Menghitung data operasional
    $sedang_disewa = $pdo->query("SELECT COUNT(*) FROM pemesanan WHERE status_pemesanan = 'Berjalan'")->fetchColumn();
    $mobil_perawatan = $pdo->query("SELECT COUNT(*) FROM mobil WHERE status = 'Perawatan'")->fetchColumn();

    // Mengambil 5 tugas paling mendesak (prioritaskan verifikasi dan pengajuan)
    $stmt_tasks = $pdo->query("
        SELECT p.id_pemesanan, p.kode_pemesanan, pg.nama_lengkap, p.status_pemesanan, p.updated_at
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        WHERE p.status_pemesanan IN ('Menunggu Verifikasi', 'Pengajuan Ambil Cepat', 'Pengajuan Pembatalan', 'Menunggu Pembayaran Denda')
        ORDER BY p.updated_at DESC
        LIMIT 5
    ");
    $quick_tasks = $stmt_tasks->fetchAll();

} catch (PDOException $e) {
    // Set nilai default jika query gagal
    $perlu_verifikasi_bayar = $pengajuan_pelanggan = $sedang_disewa = $mobil_perawatan = 0;
    $quick_tasks = [];
}
?>

<div class="page-header">
    <h1>Dashboard Operasional</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>. Berikut adalah ringkasan tugas Anda.</p>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Perlu Verifikasi Bayar</h3>
        <p class="widget-data"><?= $perlu_verifikasi_bayar ?></p>
        <div class="widget-details"><span>Bukti transfer baru dari pelanggan</span></div>
        <a href="<?= BASE_URL ?>admin/pembayaran.php">Verifikasi Sekarang &rarr;</a>
    </div>
    <div class="widget">
        <h3>Pengajuan Pelanggan</h3>
        <p class="widget-data"><?= $pengajuan_pelanggan ?></p>
        <div class="widget-details"><span>Perubahan jadwal atau pembatalan</span></div>
        <a href="<?= BASE_URL ?>admin/pembayaran.php">Tinjau Pengajuan &rarr;</a>
    </div>
    <div class="widget">
        <h3>Mobil Sedang Disewa</h3>
        <p class="widget-data"><?= $sedang_disewa ?></p>
        <div class="widget-details"><span>Jumlah mobil yang sedang di luar</span></div>
        <a href="<?= BASE_URL ?>admin/history.php?status=Berjalan">Lihat Daftar &rarr;</a>
    </div>
    <div class="widget">
        <h3>Mobil Dalam Perawatan</h3>
        <p class="widget-data"><?= $mobil_perawatan ?></p>
        <div class="widget-details"><span>Mobil yang tidak tersedia saat ini</span></div>
        <a href="<?= BASE_URL ?>admin/mobil.php?status=Perawatan">Kelola Status &rarr;</a>
    </div>
</div>

<div class="filter-container">
    <div class="filter-form">
        <div class="form-group" style="flex-grow: 1;">
            <label for="kode-pesanan">Konfirmasi Pesanan Cepat</label>
            <form action="konfirmasi.php" method="GET" style="display: flex; gap: 10px;">
                <input type="text" name="kode" id="kode-pesanan" class="form-control" placeholder="Masukkan Kode Pemesanan..." required>
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>
        <div class="form-group">
            <label>Scan di Lapangan</label>
            <a href="scan_qr.php" class="btn btn-info" style="width:100%;">Buka Scanner QR</a>
        </div>
    </div>
</div>

<div class="table-container" data-live-context="admin_pemesanan" data-live-total="..." data-live-last-update="...">
    <h2>Tugas & Tindakan Mendesak</h2>
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
                <tr><td colspan="5" style="text-align:center;">Tidak ada tugas yang menunggu. Pekerjaan bagus!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>