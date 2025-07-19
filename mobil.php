<?php
// File: mobil.php (Versi Publik dengan Filter Lanjutan)

require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Daftar Mobil Tersedia';
require_once 'includes/header.php';

// Ambil parameter filter dari URL
$search_query = $_GET['q'] ?? '';
$kelas_filter = $_GET['kelas'] ?? '';
$jenis_filter = $_GET['jenis'] ?? '';

// Ambil daftar unik untuk dropdown filter
try {
    $stmt_jenis = $pdo->query("SELECT DISTINCT jenis_mobil FROM mobil WHERE jenis_mobil IS NOT NULL AND jenis_mobil != '' ORDER BY jenis_mobil ASC");
    $daftar_jenis = $stmt_jenis->fetchAll(PDO::FETCH_COLUMN);
    $kelas_list = ['Low level', 'Mid level', 'High level', 'Luxury'];
} catch (PDOException $e) {
    $daftar_jenis = [];
    $kelas_list = [];
}

// ==========================================================
// LOGIKA QUERY DINAMIS
// ==========================================================
// Query dasar HANYA mengambil mobil yang statusnya 'Tersedia'
$sql = "SELECT * FROM mobil WHERE status = 'Tersedia'";
$params = [];

// Terapkan filter pencarian teks (merk, model)
if (!empty($search_query)) {
    $sql .= " AND (merk LIKE :q OR model LIKE :q)";
    $params[':q'] = "%$search_query%";
}

// Terapkan filter kelas mobil
if (!empty($kelas_filter)) {
    $sql .= " AND kelas_mobil = :kelas";
    $params[':kelas'] = $kelas_filter;
}

// Terapkan filter jenis mobil
if (!empty($jenis_filter)) {
    $sql .= " AND jenis_mobil = :jenis";
    $params[':jenis'] = $jenis_filter;
}

$sql .= " ORDER BY merk ASC, model ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
}
?>

<div class="page-header">
    <h1>Armada Kami</h1>
    <p>Temukan mobil yang paling sesuai dengan kebutuhan perjalanan Anda.</p>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        <div class="form-group" style="flex-grow: 1;">
            <label>Cari Mobil</label>
            <input type="text" name="q" placeholder="Ketik Merk atau Model..." value="<?= htmlspecialchars($search_query) ?>">
        </div>
        <div class="form-group">
            <label>Kelas</label>
            <select name="kelas">
                <option value="">Semua Kelas</option>
                <?php foreach($kelas_list as $k): ?>
                    <option value="<?= $k ?>" <?= ($kelas_filter === $k) ? 'selected' : '' ?>><?= $k ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Jenis</label>
            <select name="jenis">
                <option value="">Semua Jenis</option>
                <?php foreach($daftar_jenis as $jenis): ?>
                    <option value="<?= htmlspecialchars($jenis) ?>" <?= ($jenis_filter === $jenis) ? 'selected' : '' ?>><?= htmlspecialchars($jenis) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="mobil.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<section class="car-listing-section" style="padding-top: 20px;">
    <div class="container">
        <div class="car-grid">
            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-image">
                            <img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($car['gambar_mobil'] ?: 'default-car.png') ?>" alt="<?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?>">
                        </div>
                        <div class="car-card-content">
                            <h3><?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?></h3>
                            <p class="car-price">
                                <strong><?= format_rupiah($car['harga_sewa_harian']) ?></strong> / hari
                            </p>
                            <a href="<?= BASE_URL ?>actions/mobil/detail.php?id=<?= $car['id_mobil'] ?>" class="btn btn-secondary">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1 / -1; text-align:center;">
                    <p>Mohon maaf, tidak ada mobil yang ditemukan sesuai kriteria Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>