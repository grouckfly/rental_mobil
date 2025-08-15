<?php
// File: admin/mobil.php (Versi dengan Filter Lanjutan & Sembunyikan Mobil Tidak Aktif)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$page_title = 'Kelola Mobil';
require_once '../includes/header.php';

// Ambil parameter filter dari URL
$search_query = $_GET['q'] ?? '';
$status_filter = $_GET['status'] ?? '';
$kelas_filter = $_GET['kelas'] ?? '';
$jenis_filter = $_GET['jenis'] ?? '';

// ==========================================================
// Ambil daftar semua jenis mobil yang unik
// ==========================================================
try {
    // DISTINCT memastikan setiap jenis mobil hanya muncul sekali
    $stmt_jenis = $pdo->query("SELECT DISTINCT jenis_mobil FROM mobil WHERE jenis_mobil IS NOT NULL AND jenis_mobil != '' ORDER BY jenis_mobil ASC");
    $daftar_jenis = $stmt_jenis->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $daftar_jenis = [];
}

// ==========================================================
// LOGIKA QUERY DINAMIS
// ==========================================================
$sql = "SELECT * FROM mobil WHERE 1=1";
$params = [];

// Terapkan filter pencarian teks (untuk merk, model, jenis, plat)
if (!empty($search_query)) {
    $sql .= " AND (merk LIKE :q OR model LIKE :q OR jenis_mobil LIKE :q OR plat_nomor LIKE :q)";
    $params[':q'] = "%$search_query%";
}

// Terapkan filter kelas
if (!empty($kelas_filter)) {
    $sql .= " AND kelas_mobil = :kelas";
    $params[':kelas'] = $kelas_filter;
}

// Terapkan filter status
if (!empty($status_filter)) {
    // Jika user secara spesifik mencari status, gunakan itu
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

// Terapkan filter jenis mobil
if (!empty($jenis_filter)) {
    $sql .= " AND jenis_mobil = :jenis";
    $params[':jenis'] = $jenis_filter;
}

if (empty($status_filter)) {
    // Jika filter status "Semua", gunakan urutan custom
    $sql .= " ORDER BY FIELD(status, 'Tersedia', 'Disewa', 'Perawatan', 'Tidak Aktif'), id_mobil ASC";
} else {
    // Jika memfilter status tertentu, urutkan biasa
    $sql .= " ORDER BY id_mobil ASC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
}

// Daftar status untuk dropdown filter
$status_list = ['Tersedia', 'Disewa', 'Perawatan', 'Tidak Aktif'];
// Daftar kelas untuk dropdown filter
$kelas_list = ['Low Level', 'Mid Level', 'High Level', 'Luxury'];
?>

<div class="page-header with-action">
    <h1>Kelola Data Mobil</h1>
    <a href="../actions/mobil/tambah.php" class="btn btn-primary">Tambah Mobil Baru</a>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        <div class="form-group" style="flex-grow: 1;">
            <label>Cari Mobil</label>
            <input type="text" name="q" placeholder="Ketik Merk, Model, Jenis, atau Plat Nomor..." value="<?= htmlspecialchars($search_query) ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Jenis Mobil</label>
            <select name="jenis" class="form-control">
                <option value="">Semua</option>
                <?php foreach($daftar_jenis as $jenis): ?>
                    <option value="<?= htmlspecialchars($jenis) ?>" <?= ($jenis_filter === $jenis) ? 'selected' : '' ?>><?= htmlspecialchars($jenis) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Kelas</label>
            <select name="kelas" class="form-control">
                <option value="">Semua</option>
                <?php foreach($kelas_list as $k): ?>
                    <option value="<?= $k ?>" <?= ($kelas_filter === $k) ? 'selected' : '' ?>><?= $k ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">Semua</option>
                <?php foreach($status_list as $s): ?>
                    <option value="<?= $s ?>" <?= ($status_filter === $s) ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="mobil.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Plat Nomor</th>
                <th>Merk & Model</th>
                <th>Jenis</th>
                <th>Kelas</th>
                <th>Harga/Hari</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $car): ?>
                    <tr>
                        <td><?= htmlspecialchars($car['id_mobil']) ?></td>
                        <td><img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($car['gambar_mobil'] ?: 'default-car.png') ?>" alt="Gambar Mobil" width="80"></td>
                        <td><?= htmlspecialchars($car['plat_nomor']) ?></td>
                        <td><?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?></td>
                        <td><?= htmlspecialchars($car['jenis_mobil']) ?></td>
                        <td><?= htmlspecialchars($car['kelas_mobil']) ?></td>
                        <td><?= format_rupiah($car['harga_sewa_harian']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $car['status'])) ?>"><?= htmlspecialchars($car['status']) ?></span></td>
                        <td><a href="<?= BASE_URL ?>actions/mobil/detail.php?id=<?= $car['id_mobil'] ?>" class="btn btn-info btn-sm">Detail</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">Tidak ada mobil yang ditemukan sesuai kriteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>