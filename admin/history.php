<?php
// File: admin/history.php (Versi Final Universal untuk Semua Role)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Hak akses untuk semua role yang sudah login
check_auth(['Admin', 'Karyawan', 'Pelanggan']);

$page_title = 'Riwayat Transaksi';
require_once '../includes/header.php';

// Ambil role dan id pengguna dari session
$role_session = $_SESSION['role'];
$id_pengguna_session = $_SESSION['id_pengguna'];

// Ambil semua parameter filter dari URL
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status = $_GET['status'] ?? '';
$id_mobil = isset($_GET['id_mobil']) ? (int)$_GET['id_mobil'] : 0;
$kode_pesanan = $_GET['kode_pesanan'] ?? '';
$nama_pelanggan = $_GET['nama_pelanggan'] ?? '';

// ==========================================================
// LOGIKA QUERY DINAMIS BERDASARKAN ROLE
// ==========================================================
$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model, m.gambar_mobil
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE 1=1";
$params = [];

if ($role_session === 'Pelanggan') {
    $sql .= " AND p.id_pengguna = ?";
    $params[] = $id_pengguna_session;
}

if (!empty($tgl_awal) && !empty($tgl_akhir)) { $sql .= " AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?"; $params[] = $tgl_awal; $params[] = $tgl_akhir; }
if (!empty($status)) { $sql .= " AND p.status_pemesanan = ?"; $params[] = $status; }
if ($id_mobil > 0) { $sql .= " AND p.id_mobil = ?"; $params[] = $id_mobil; }
if (!empty($kode_pesanan)) { $sql .= " AND p.kode_pemesanan LIKE ?"; $params[] = "%$kode_pesanan%"; }

if (in_array($role_session, ['Admin', 'Karyawan']) && !empty($nama_pelanggan)) {
    $sql .= " AND pg.nama_lengkap LIKE ?";
    $params[] = "%$nama_pelanggan%";
}
$sql .= " ORDER BY p.tanggal_pemesanan DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $histories = $stmt->fetchAll();
} catch (PDOException $e) { $histories = []; }

// Daftar status untuk dropdown
$status_list = ['Selesai', 'Dibatalkan', 'Berjalan', 'Dikonfirmasi', 'Menunggu Pembayaran', 'Pengajuan Ditolak'];
?>

<div class="page-header">
    <h1>Riwayat Transaksi</h1>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        </form>
</div>

<?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
<div class="export-buttons">
    <p>Total ditemukan: <strong><?= count($histories) ?></strong> transaksi.</p>
    <?php if (!empty($histories)):
        $export_params = http_build_query($_GET);
    ?>
        <a href="../actions/export/excel.php?<?= $export_params ?>" class="btn btn-success">Export ke Excel</a>
        <a href="../actions/export/pdf.php?<?= $export_params ?>" class="btn btn-danger" target="_blank">Export ke PDF</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
// ==========================================================
// LOGIKA PENANDA AUTO-REFRESH YANG SUDAH DIPERBAIKI
// ==========================================================
$live_context = '';
$live_last_update = '';

if ($role_session === 'Pelanggan') {
    $live_context = 'pelanggan_pemesanan';
    $last_update_stmt = $pdo->prepare("SELECT MAX(tanggal_pemesanan) FROM pemesanan WHERE id_pengguna = ?");
    $last_update_stmt->execute([$id_pengguna_session]);
    $live_last_update = $last_update_stmt->fetchColumn();
} else { // Admin atau Karyawan
    $live_context = 'admin_pemesanan';
    $live_last_update = $pdo->query("SELECT MAX(tanggal_pemesanan) FROM pemesanan")->fetchColumn();
}
?>

<div class="table-container" 
     data-live-context="<?= $live_context ?>" 
     data-live-total="<?= count($histories) ?>" 
     data-live-last-update="<?= $live_last_update ?>">
    
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?><th>Pelanggan</th><?php endif; ?>
                <th>Mobil</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): ?>
                <?php foreach ($histories as $history): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($history['kode_pemesanan']) ?></strong></td>
                        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?><td><?= htmlspecialchars($history['nama_lengkap']) ?></td><?php endif; ?>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= date('d M Y', strtotime($history['tanggal_pemesanan'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                            <?php if ($role_session === 'Pelanggan' && $history['status_pemesanan'] === 'Selesai' && empty($history['review_pelanggan'])): ?>
                                <a href="<?= BASE_URL ?>pelanggan/beri_ulasan.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-primary btn-sm">Beri Review</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">Tidak ada riwayat yang ditemukan sesuai kriteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>