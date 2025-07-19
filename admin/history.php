<?php
// File: admin/history.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$page_title = 'Riwayat Transaksi';
require_once '../includes/header.php';

// Ambil tanggal dari parameter GET, atau set default ke bulan ini
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

// Siapkan query untuk mengambil data sesuai filter tanggal
$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE DATE(p.tanggal_pemesanan) BETWEEN ? AND ?
        ORDER BY p.tanggal_pemesanan DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tgl_awal, $tgl_akhir]);
    $histories = $stmt->fetchAll();
} catch (PDOException $e) {
    $histories = [];
}
?>

<div class="page-header">
    <h1>Riwayat Transaksi</h1>
    <p>Daftar semua transaksi yang telah selesai atau dibatalkan.</p>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        <div class="form-group">
            <label for="tgl_awal">Dari Tanggal</label>
            <input type="date" id="tgl_awal" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>" class="form-control">
        </div>
        <div class="form-group">
            <label for="tgl_akhir">Sampai Tanggal</label>
            <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<div class="export-buttons">
    <p>Total ditemukan: <strong><?= count($histories) ?></strong> transaksi.</p>
    <?php if (!empty($histories)): ?>
        <a href="../actions/export/excel.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" class="btn btn-success">Export ke Excel</a>
        <a href="../actions/export/pdf.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" class="btn btn-danger" target="_blank">Export ke PDF</a>
    <?php endif; ?>
</div>


<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Mobil</th>
                <th>Total Biaya</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): ?>
                <?php foreach ($histories as $history): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($history['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($history['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= format_rupiah($history['total_biaya']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($history['tanggal_selesai'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Belum ada riwayat transaksi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>