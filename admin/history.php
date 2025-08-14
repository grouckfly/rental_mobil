<?php
// File: admin/history.php (Versi Final Universal & Lengkap)

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

// ==========================================================
// 1. PENGAMBILAN SEMUA PARAMETER FILTER
// ==========================================================
$search_query = $_GET['q'] ?? '';
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';
$id_mobil = isset($_GET['id_mobil']) ? (int)$_GET['id_mobil'] : 0;
$kelas_mobil = $_GET['kelas_mobil'] ?? '';
$jenis_mobil = $_GET['jenis'] ?? '';
$nama_pelanggan = $_GET['nama_pelanggan'] ?? '';

// ==========================================================
// 2. PENGAMBILAN DATA UNTUK DROPDOWN
// ==========================================================
try {
    $stmt_jenis = $pdo->query("SELECT DISTINCT jenis_mobil FROM mobil WHERE jenis_mobil IS NOT NULL AND jenis_mobil != '' ORDER BY jenis_mobil ASC");
    $daftar_jenis = $stmt_jenis->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $daftar_jenis = [];
}

$status_list = ['Selesai', 'Dibatalkan', 'Berjalan', 'Dikonfirmasi', 'Menunggu Verifikasi', 'Menunggu Pembayaran', 'Pengajuan Ditolak'];
$kelas_list = ['Low level', 'Mid level', 'High level', 'Luxury'];

// ==========================================================
// 3. LOGIKA QUERY DINAMIS BERDASARKAN ROLE DAN FILTER
// ==========================================================
$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model, m.gambar_mobil FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil WHERE 1=1";
$params = [];

if ($role_session === 'Pelanggan') {
    $sql .= " AND p.id_pengguna = ?";
    $params[] = $id_pengguna_session;
}
if (!empty($search_query)) { 
    $sql .= " AND (p.kode_pemesanan LIKE :q OR pg.nama_lengkap LIKE :q OR m.merk LIKE :q OR m.model LIKE :q)"; 
    $params[':q'] = "%$search_query%"; 
}
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql .= " AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
}
if (!empty($status_filter)) {
    $sql .= " AND p.status_pemesanan = ?";
    $params[] = $status_filter;
}
if ($id_mobil > 0) {
    $sql .= " AND p.id_mobil = ?";
    $params[] = $id_mobil;
}
if (!empty($kelas_mobil)) {
    $sql .= " AND m.kelas_mobil = ?";
    $params[] = $kelas_mobil;
}
if (!empty($jenis_mobil)) {
    $sql .= " AND m.jenis_mobil = ?";
    $params[] = $jenis_mobil;
}
if (in_array($role_session, ['Admin', 'Karyawan']) && !empty($nama_pelanggan)) {
    $sql .= " AND pg.nama_lengkap LIKE ?";
    $params[] = "%$nama_pelanggan%";
}

$sql .= " ORDER BY p.updated_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $histories = $stmt->fetchAll();
} catch (PDOException $e) {
    $histories = [];
}
?>

<div class="page-header">
    <h1>Riwayat Transaksi</h1>
</div>

<div class="search-and-filter-container">
    <div class="main-search-bar">
        <span class="search-icon">&#128269;</span>
        <input type="text" name="q" form="filter-form" placeholder="Cari Kode, Pelanggan, atau Mobil..." value="<?= htmlspecialchars($search_query) ?>">
        <button type="button" id="toggle-filter-btn" class="btn btn-secondary">Filter Lanjutan</button>
    </div>

    <div class="filter-container" id="advanced-filter-container" style="display:none;">
        <form action="" method="GET" id="filter-form" class="filter-form">
            <div class="form-group"><label>Dari Tgl</label><input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
            <div class="form-group"><label>Sampai Tgl</label><input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
            <div class="form-group"><label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <?php foreach ($status_list as $s): ?><option value="<?= $s ?>" <?= ($status_filter === $s) ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Mobil</label>
                <select name="id_mobil" id="filter-mobil" style="width: 200px;">
                    <?php if ($id_mobil > 0 && $mobil_pilihan = $pdo->query("SELECT merk, model FROM mobil WHERE id_mobil=$id_mobil")->fetch()): ?>
                        <option value="<?= $id_mobil ?>" selected><?= htmlspecialchars($mobil_pilihan['merk'] . ' ' . $mobil_pilihan['model']) ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group"><label>Kelas</label>
                <select name="kelas_mobil">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas_list as $k): ?><option value="<?= $k ?>" <?= ($kelas_mobil === $k) ? 'selected' : '' ?>><?= $k ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Jenis</label>
                <select name="jenis">
                    <option value="">Semua Jenis</option>
                    <?php foreach ($daftar_jenis as $j): ?><option value="<?= $j ?>" <?= ($jenis_mobil === $j) ? 'selected' : '' ?>><?= $j ?></option><?php endforeach; ?>
                </select>
            </div>
            <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
                <div class="form-group"><label>Pelanggan</label><input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan..." value="<?= htmlspecialchars($nama_pelanggan) ?>"></div>
            <?php endif; ?>
            <div class="form-group action-group"><button type="submit" class="btn btn-primary">Terapkan</button><a href="history.php" class="btn btn-secondary">Reset</a></div>
        </form>
    </div>
</div>

<?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
    <div class="export-buttons">
        <p>Total ditemukan: <strong><?= count($histories) ?></strong> transaksi.</p>
        <?php if (!empty($histories)): ?><a href="../actions/export/excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success">Export Excel</a><a href="../actions/export/pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger" target="_blank">Export PDF</a><?php endif; ?>
    </div>
<?php endif; ?>

<?php
$live_context = ($role_session === 'Pelanggan') ? 'pelanggan_pemesanan' : 'admin_pemesanan';
$live_last_update = !empty($histories) ? $histories[0]['updated_at'] : date('Y-m-d H:i:s');
?>
<div class="table-container" data-live-context="<?= $live_context ?>" data-live-total="<?= count($histories) ?>" data-live-last-update="<?= $live_last_update ?>">
    <table>
        <thead>
            <tr>
                <th>Kode</th><?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?><th>Pelanggan</th><?php endif; ?><th>Mobil</th>
                <th>Tanggal</th>
                <th>Total Bayar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): foreach ($histories as $history): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($history['kode_pemesanan']) ?></strong></td>
                        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?><td><?= htmlspecialchars($history['nama_lengkap']) ?></td><?php endif; ?>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= date('d M Y', strtotime($history['tanggal_pemesanan'])) ?></td>
                        <td><?= format_rupiah($history['total_biaya'] + $history['total_denda']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td><a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="7">Tidak ada riwayat yang ditemukan sesuai kriteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>