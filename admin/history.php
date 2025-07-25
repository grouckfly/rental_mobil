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
$kode_pesanan = $_GET['kode_pesanan'] ?? '';
$id_mobil = isset($_GET['id_mobil']) ? (int)$_GET['id_mobil'] : 0;
$nama_pelanggan = $_GET['nama_pelanggan'] ?? '';
$kelas_mobil = $_GET['kelas_mobil'] ?? '';
$jenis_mobil = $_GET['jenis'] ?? '';

// Ambil daftar mobil & jenis untuk dropdown
try {
    $stmt_mobil = $pdo->query("SELECT id_mobil, merk, model FROM mobil ORDER BY merk ASC, model ASC");
    $daftar_mobil = $stmt_mobil->fetchAll();
    $stmt_jenis = $pdo->query("SELECT DISTINCT jenis_mobil FROM mobil WHERE jenis_mobil IS NOT NULL AND jenis_mobil != '' ORDER BY jenis_mobil ASC");
    $daftar_jenis = $stmt_jenis->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $daftar_mobil = [];
    $daftar_jenis = [];
}

// Logika Query Dinamis Berdasarkan Role
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

// Terapkan filter
if (!empty($tgl_awal) && !empty($tgl_akhir)) { $sql .= " AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?"; $params[] = $tgl_awal; $params[] = $tgl_akhir; }
if (!empty($status)) { $sql .= " AND p.status_pemesanan = ?"; $params[] = $status; }
if (!empty($kode_pesanan)) { $sql .= " AND p.kode_pemesanan LIKE ?"; $params[] = "%$kode_pesanan%"; }
if ($id_mobil > 0) { $sql .= " AND p.id_mobil = ?"; $params[] = $id_mobil; }
if (!empty($kelas_mobil)) { $sql .= " AND m.kelas_mobil = ?"; $params[] = $kelas_mobil; }
if (!empty($jenis_mobil)) { $sql .= " AND m.jenis_mobil = ?"; $params[] = $jenis_mobil; }
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

$status_list = ['Selesai', 'Dibatalkan'];
$kelas_list = ['Low level', 'Mid level', 'High level', 'Luxury'];
?>

<div class="page-header">
    <h1>Riwayat Transaksi</h1>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        <div class="form-group"><label>Dari Tgl</label><input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
        <div class="form-group"><label>Sampai Tgl</label><input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
        <div class="form-group"><label>Status</label>
            <select name="status"><option value="">Semua</option>
                <?php foreach($status_list as $s): ?><option value="<?= $s ?>" <?= ($status === $s) ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Kode</label><input type="text" name="kode_pesanan" placeholder="Kode Pesanan..." value="<?= htmlspecialchars($kode_pesanan) ?>"></div>
        <div class="form-group"><label>Mobil</label>
            <select name="id_mobil" id="filter-mobil" style="width: 200px;">
                <?php if ($id_mobil > 0 && $mobil_pilihan = $pdo->query("SELECT merk, model FROM mobil WHERE id_mobil=$id_mobil")->fetch()): ?>
                    <option value="<?= $id_mobil ?>" selected><?= htmlspecialchars($mobil_pilihan['merk'] . ' ' . $mobil_pilihan['model']) ?></option>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group"><label>Kelas</label>
            <select name="kelas_mobil"><option value="">Semua Kelas</option>
                <?php foreach($kelas_list as $k): ?><option value="<?= $k ?>" <?= ($kelas_mobil === $k) ? 'selected' : '' ?>><?= $k ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Jenis</label>
            <select name="jenis"><option value="">Semua Jenis</option>
                <?php foreach($daftar_jenis as $j): ?><option value="<?= $j ?>" <?= ($jenis_mobil === $j) ? 'selected' : '' ?>><?= $j ?></option><?php endforeach; ?>
            </select>
        </div>
        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
            <div class="form-group"><label>Pelanggan</label><input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan..." value="<?= htmlspecialchars($nama_pelanggan) ?>"></div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="history.php" class="btn btn-secondary">Reset</a>
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