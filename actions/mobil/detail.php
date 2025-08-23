<?php
// File: actions/mobil/detail.php (Versi Lengkap)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid.', 'error');
}

// Ambil role session dengan aman
$role_session = $_SESSION['role'] ?? null;
$user_data = null;

// Cek dan buat token CSRF jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Jika yang melihat pelanggan, ambil data profilnya
if ($role_session === 'Pelanggan') {
    $stmt_user = $pdo->prepare("SELECT nik, foto_ktp FROM pengguna WHERE id_pengguna = ?");
    $stmt_user->execute([$_SESSION['id_pengguna']]);
    $user_data = $stmt_user->fetch();
}

// Ambil data riwayat perbaikan
try {
    // Ambil SEMUA riwayat perawatan (baik yang aktif maupun selesai)
    $stmt_history = $pdo->prepare("SELECT * FROM riwayat_perawatan WHERE id_mobil = ? ORDER BY tanggal_masuk DESC");
    $stmt_history->execute([$id_mobil]);
    $riwayat_perawatan = $stmt_history->fetchAll();
} catch (PDOException $e) {
    $riwayat_perawatan = [];
}

// Ambil semua data mobil dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt->execute([$id_mobil]);
    $mobil = $stmt->fetch();
    if (!$mobil) {
        redirect_with_message('../../admin/mobil.php', 'Mobil dengan ID tersebut tidak ditemukan.', 'error');
    }
    $perawatan_aktif = null;
    if ($mobil['status'] === 'Perawatan') {
        $stmt_perawatan = $pdo->prepare("
            SELECT * FROM riwayat_perawatan 
            WHERE id_mobil = ? AND status_perawatan = 'Dikerjakan' 
            ORDER BY tanggal_masuk DESC LIMIT 1
        ");
        $stmt_perawatan->execute([$id_mobil]);
        $perawatan_aktif = $stmt_perawatan->fetch();
    }
} catch (PDOException $e) {
    redirect_with_message('../../admin/mobil.php', 'Terjadi kesalahan pada database.', 'error');
}

// Ambil data rating dan ulasan untuk mobil
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$reviews = [];
$rating_summary = ['rata_rating' => 0, 'jumlah_review' => 0];

try {
    // 1. Ambil ringkasan (rata-rata & jumlah total ulasan), tidak terpengaruh filter
    $stmt_summary = $pdo->prepare("
        SELECT AVG(rating_pengguna) as rata_rating, COUNT(review_pelanggan) as jumlah_review
        FROM pemesanan
        WHERE id_mobil = ? AND rating_pengguna IS NOT NULL AND review_pelanggan IS NOT NULL AND review_pelanggan != ''
    ");
    $stmt_summary->execute([$id_mobil]);
    $summary = $stmt_summary->fetch();
    if ($summary && $summary['jumlah_review'] > 0) {
        $rating_summary = $summary;
    }

    // 2. Siapkan query dasar untuk mengambil ulasan
    $sql_reviews = "
        SELECT p.rating_pengguna, p.review_pelanggan, p.updated_at, u.nama_lengkap
        FROM pemesanan p
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna
        WHERE p.id_mobil = ? AND p.review_pelanggan IS NOT NULL AND p.review_pelanggan != ''
    ";
    $params_reviews = [$id_mobil];

    // Tambahkan filter rating jika dipilih oleh pengguna
    if ($rating_filter >= 1 && $rating_filter <= 5) {
        $sql_reviews .= " AND p.rating_pengguna = ?";
        $params_reviews[] = $rating_filter;
    }

    $sql_reviews .= " ORDER BY p.updated_at DESC";

    // Hanya batasi jumlah jika pengguna melihat 'Semua' ulasan
    if ($rating_filter === 0) {
        $sql_reviews .= " LIMIT 10"; // Batasi 10 ulasan terbaru untuk tampilan awal
    }

    // Eksekusi query ulasan yang sudah dinamis
    $stmt_reviews = $pdo->prepare($sql_reviews);
    $stmt_reviews->execute($params_reviews);
    $reviews = $stmt_reviews->fetchAll();
} catch (PDOException $e) {
    // Biarkan array kosong jika terjadi error agar tidak merusak halaman
    error_log("Gagal mengambil data ulasan: " . $e->getMessage());
}

$page_title = 'Detail Mobil: ' . htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']);
require_once '../../includes/header.php';
?>

<div class="page-top-bar">
    <div class="tab-nav">
        <a href="#detail-utama" class="tab-link active">Detail Utama</a>

        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
            <a href="#riwayat-perawatan" class="tab-link">Riwayat Perawatan (<?= count($riwayat_perawatan) ?>)</a>
        <?php endif; ?>

    </div>
    <div class="detail-actions">

        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
            <a href="edit.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-primary">Edit</a>
            <?php // --- TOMBOL AKSI CERDAS UNTUK PERAWATAN ---
            if ($mobil['status'] === 'Tersedia'): ?>
                <a href="<?= BASE_URL ?>actions/mobil/mulai_perawatan.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-danger">Perawatan</a>
            <?php elseif ($mobil['status'] === 'Perawatan'): ?>
                <a href="<?= BASE_URL ?>actions/mobil/selesaikan_perawatan.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-primary">Selesaikan</a>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- Detail Utama -->
<div class="tab-content">
    <div id="detail-utama" class="tab-pane active">

        <div class="detail-container">
            <div class="detail-image">
                <img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($mobil['gambar_mobil'] ?: 'default-car.png') ?>" alt="Gambar <?= htmlspecialchars($mobil['merk']) ?>">
            </div>

            <div class="detail-info">
                <h2><?= htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']) ?></h2>
                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $mobil['status'])) ?>"><?= htmlspecialchars($mobil['status']) ?></span>

                <div class="info-grid">
                    <div class="info-item"><span class="label">Plat Nomor</span><span class="value"><?= htmlspecialchars($mobil['plat_nomor']) ?></span></div>
                    <div class="info-item"><span class="label">Tahun</span><span class="value"><?= htmlspecialchars($mobil['tahun']) ?></span></div>
                    <div class="info-item"><span class="label">Jenis Mobil</span><span class="value"><?= htmlspecialchars($mobil['jenis_mobil']) ?></span></div>
                    <div class="info-item"><span class="label">Kelas Mobil</span><span class="value"><?= htmlspecialchars($mobil['kelas_mobil']) ?></span></div>
                    <div class="info-item"><span class="label">Harga Sewa / Hari</span><span class="value price"><?= format_rupiah($mobil['harga_sewa_harian']) ?></span></div>
                    <div class="info-item"><span class="label">Denda / Hari</span><span class="value price"><?= format_rupiah($mobil['denda_per_hari']) ?></span></div>
                </div>
            </div>


            <?php if ($perawatan_aktif): ?>
                <div class="info-box maintenance-info">
                    <h4>Informasi Perawatan</h4>
                    <p><?= htmlspecialchars($perawatan_aktif['keterangan']) ?></p>
                    <?php if (!empty($perawatan_aktif['tanggal_estimasi_selesai'])): ?>
                        <small>Estimasi selesai pada: <strong><?= date('d F Y', strtotime($perawatan_aktif['tanggal_estimasi_selesai'])) ?></strong></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="detail-full-width">
                <div class="info-item">
                    <div class="spec-header">
                        <span class="label">Spesifikasi & Fitur</span>
                        <button id="toggle-spec-btn" class="btn btn-sm btn-secondary">Lihat Selengkapnya</button>
                    </div>
                    <div id="spec-content" class="value description collapsed">
                        <?= nl2br(htmlspecialchars($mobil['spesifikasi'])) ?>
                    </div>
                </div>
            </div>

            <?php if ($role_session === 'Admin'): ?>
                <form action="<?= BASE_URL ?>actions/mobil/hapus.php" method="POST" style="display:inline;" onsubmit="return confirm('Anda yakin? Mobil yang belum pernah disewa akan dihapus permanen, sedangkan yang sudah memiliki riwayat akan dinonaktifkan.');">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detail Perawatan -->
    <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
        <div id="riwayat-perawatan" class="tab-pane">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal Masuk</th>
                            <th>Keterangan</th>
                            <th>Biaya</th>
                            <th>Nota</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayat_perawatan)): ?>
                            <tr>
                                <td colspan="5">Belum ada riwayat perawatan untuk mobil ini.</td>
                            </tr>
                            <?php else: foreach ($riwayat_perawatan as $item): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($item['tanggal_masuk'])) ?></td>
                                    <td><?= htmlspecialchars($item['keterangan']) ?></td>
                                    <td><?= format_rupiah($item['biaya'] ?? 0) ?></td>
                                    <td>
                                        <?php if (!empty($item['foto_nota'])): ?>
                                            <a href="<?= BASE_URL ?>assets/img/nota_perawatan/<?= $item['foto_nota'] ?>" target="_blank">Lihat Nota</a>
                                        <?php else: echo '-';
                                        endif; ?>
                                    </td>
                                    <td><span class="status-badge status-<?= strtolower($item['status_perawatan']) ?>"><?= $item['status_perawatan'] ?></span></td>
                                </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Review Section -->
<?php if ($rating_summary['jumlah_review'] > 0): ?>
    <div class="review-section">
        <div class="container">
            <h3 class="section-title" style="text-align:left; font-size:1.8rem;">Ulasan Pelanggan</h3>
            <div class="rating-summary">
                <div class="star-rating" data-rating="<?= $rating_summary['rata_rating'] ?>"></div>
                <div class="summary-text">
                    <strong><?= number_format($rating_summary['rata_rating'], 1) ?></strong> dari 5 Bintang
                    <span>(Berdasarkan <?= $rating_summary['jumlah_review'] ?> ulasan)</span>
                </div>
            </div>

            <div class="review-filter">
                <a href="?id=<?= $id_mobil ?>" class="btn btn-sm <?= ($rating_filter == 0) ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <a href="?id=<?= $id_mobil ?>&rating=<?= $i ?>" class="btn btn-sm <?= ($rating_filter == $i) ? 'btn-primary' : 'btn-secondary' ?>">&#9733; <?= $i ?></a>
                <?php endfor; ?>
            </div>

            <div class="review-list">
                <?php if (empty($reviews)): ?>
                    <p>Tidak ada ulasan dengan rating ini.</p>
                    <?php else: foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="star-rating" data-rating="<?= $review['rating_pengguna'] ?>"></div>
                                <strong><?= htmlspecialchars($review['nama_lengkap']) ?></strong>
                            </div>
                            <p class="review-body">
                                <?= nl2br(htmlspecialchars($review['review_pelanggan'])) ?>
                            </p>
                            <small class="review-date">Diulas pada <?= date('d F Y', strtotime($review['updated_at'])) ?></small>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="booking-section">
    <?php // --- Jika yang melihat adalah PELANGGAN dan mobil TERSEDIA ---
    if ($role_session === 'Pelanggan' && $mobil['status'] === 'Tersedia'):

        // Cek kelengkapan profil di sini
        if (!empty($user_data['nik']) && !empty($user_data['foto_ktp'])): ?>
            <div class="form-container">
                <div class="form-box">
                    <h3>Formulir Pemesanan</h3>
                    <form action="<?= BASE_URL ?>actions/pemesanan/proses.php" method="POST">
                        <input type="hidden" name="id_mobil" value="<?= $mobil['id_mobil'] ?>">
                        <input type="hidden" name="id_pengguna" value="<?= $_SESSION['id_pengguna'] ?>">
                        <input type="hidden" name="harga_sewa_harian" value="<?= $mobil['harga_sewa_harian'] ?>">
                        <div class="form-grid">
                            <div class="form-group"><label for="tanggal_mulai">Tanggal Mulai</label><input type="date" id="tanggal_mulai" name="tanggal_mulai" required min="<?= date('Y-m-d') ?>"></div>
                            <div class="form-group"><label for="tanggal_selesai">Tanggal Selesai</label><input type="date" id="tanggal_selesai" name="tanggal_selesai" required min="<?= date('Y-m-d') ?>"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Sewa Sekarang</button>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div class="info-box" style="border-color: var(--warning-color);">
                <h3>Profil Belum Lengkap</h3>
                <p>Anda harus melengkapi NIK dan Foto KTP di profil Anda sebelum dapat melakukan pemesanan.</p>
                <a href="<?= BASE_URL ?>pelanggan/profile.php" class="btn btn-primary">Lengkapi Profil Sekarang</a>
            </div>

        <?php // --- Jika yang melihat adalah PENGUNJUNG (belum login) dan mobil TERSEDIA ---
        endif;
    elseif ($role_session === null && $mobil['status'] === 'Tersedia'):
        // PERBAIKAN: Simpan URL halaman saat ini untuk redirect setelah login
        $redirect_url = urlencode($_SERVER['REQUEST_URI']);
        ?>
        <div class="info-box">
            <h3>Ingin Menyewa Mobil Ini?</h3>
            <p>Silakan login terlebih dahulu untuk melanjutkan proses pemesanan.</p>
            <a href="<?= BASE_URL ?>login.php?redirect_url=<?= $redirect_url ?>" class="btn btn-primary">Login untuk Memesan</a>
            <a href="<?= BASE_URL ?>mobil.php" class="btn btn-secondary">Kembali</a>
        </div>
    <?php endif; ?>
</div>



<?php
require_once '../../includes/footer.php';
?>