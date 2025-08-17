<?php
// File: mobil.php (Versi Final dengan Filter & Pagination)

require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Daftar Mobil Tersedia';
require_once 'includes/header.php';

// --- BAGIAN LOGIKA ---

// 1. Ambil semua parameter filter dari URL
$search_query = $_GET['q'] ?? '';
$kelas_filter = $_GET['kelas'] ?? '';
$jenis_filter = $_GET['jenis'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Menampilkan 6 mobil per halaman
$offset = ($page - 1) * $limit;

// 2. Ambil daftar unik untuk dropdown filter
try {
    $stmt_jenis = $pdo->query("SELECT DISTINCT jenis_mobil FROM mobil WHERE jenis_mobil IS NOT NULL AND jenis_mobil != '' ORDER BY jenis_mobil ASC");
    $daftar_jenis = $stmt_jenis->fetchAll(PDO::FETCH_COLUMN);
    $kelas_list = ['Low level', 'Mid level', 'High level', 'Luxury'];
} catch (PDOException $e) {
    $daftar_jenis = [];
    $kelas_list = [];
}

// 3. Bangun query SQL secara dinamis
// Query dasar HANYA mengambil mobil yang statusnya 'Tersedia'
$sql_base = "FROM mobil WHERE status = 'Tersedia'";
$params = [];

// Terapkan filter
if (!empty($search_query)) {
    $sql_base .= " AND (merk LIKE :q OR model LIKE :q)";
    $params[':q'] = "%$search_query%";
}
if (!empty($kelas_filter)) {
    $sql_base .= " AND kelas_mobil = :kelas";
    $params[':kelas'] = $kelas_filter;
}
if (!empty($jenis_filter)) {
    $sql_base .= " AND jenis_mobil = :jenis";
    $params[':jenis'] = $jenis_filter;
}

// 4. Query untuk MENGHITUNG TOTAL DATA (untuk pagination)
$sql_count = "SELECT COUNT(*) " . $sql_base;
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_cars = $stmt_count->fetchColumn();
$total_pages = ceil($total_cars / $limit);

// 5. Query UTAMA untuk MENGAMBIL DATA sesuai halaman
$sql_data = "SELECT * " . $sql_base . " ORDER BY merk ASC, model ASC LIMIT :limit OFFSET :offset";
$stmt_data = $pdo->prepare($sql_data);
// Bind parameter filter dan pagination
foreach ($params as $key => &$val) {
    $stmt_data->bindParam($key, $val);
}
$stmt_data->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt_data->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_data->execute();
$cars = $stmt_data->fetchAll();

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
            <label>Jenis</label>
            <select name="jenis">
                <option value="">Semua Jenis</option>
                <?php foreach ($daftar_jenis as $jenis): ?>
                    <option value="<?= htmlspecialchars($jenis) ?>" <?= ($jenis_filter === $jenis) ? 'selected' : '' ?>><?= htmlspecialchars($jenis) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Kelas</label>
            <select name="kelas">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelas_list as $k): ?>
                    <option value="<?= $k ?>" <?= ($kelas_filter === $k) ? 'selected' : '' ?>><?= $k ?></option>
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

        <nav class="pagination-container">
            <ul class="pagination">
                <?php
                if ($total_pages > 1):
                    $window = 2 ; // Jumlah link di kiri & kanan halaman aktif

                    // Tombol "Sebelumnya"
                    if ($page > 1):
                        $query_params['page'] = $page - 1; ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query($query_params) ?>">«</a></li>
                        <?php endif;

                    // Tampilkan nomor halaman
                    for ($i = 1; $i <= $total_pages; $i++):
                        // Tentukan kapan harus menampilkan link:
                        // 1. Selalu tampilkan halaman pertama & terakhir
                        // 2. Tampilkan link di dalam "jendela" di sekitar halaman aktif
                        if ($i == 1 || $i == $total_pages || ($i >= $page - $window && $i <= $page + $window)):
                        ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php
                        // Tampilkan elipsis (...) jika ada jeda
                        elseif ($i == 2 && $page > $window + 2 || $i == $total_pages - 1 && $page < $total_pages - $window - 1):
                        ?>
                            <li class="page-item disabled"><span class="page-link page-item-ellipsis">...</span></li>
                        <?php
                        endif;
                    endfor;

                    // Tombol "Berikutnya"
                    if ($page < $total_pages):
                        $query_params['page'] = $page + 1; ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query($query_params) ?>">»</a></li>
                    <?php endif; ?>

                <?php endif; ?>
            </ul>
        </nav>

    </div>
</section>

<?php
require_once 'includes/footer.php';
?>