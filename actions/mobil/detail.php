<?php
// File: actions/mobil/detail.php (Versi Final & Lengkap)

require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// --- Inisialisasi Variabel ---
$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$role_session = $_SESSION['role'] ?? null;
$id_pengguna_session = $_SESSION['id_pengguna'] ?? 0;
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$error_booking = '';
$booking_berhasil = false;
$data_pemesanan_baru = null;

if ($id_mobil === 0) {
    redirect_with_message(BASE_URL . 'mobil.php', 'ID Mobil tidak valid.', 'error');
}

// ==========================================================
// 1. PROSES PEMESANAN JIKA FORM DISUBMIT
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sewa_sekarang'])) {
    if ($role_session !== 'Pelanggan') {
        die("Akses tidak sah. Anda harus login sebagai pelanggan untuk memesan.");
    }
    // Lakukan validasi CSRF jika ada
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Sesi tidak valid. Silakan coba lagi.");
    }

    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $harga_sewa_harian = (float)$_POST['harga_sewa_harian'];

    try {
        $pdo->beginTransaction();

        $stmt_check = $pdo->prepare("SELECT id_pemesanan FROM pemesanan WHERE id_mobil = ? AND status_pemesanan NOT IN ('Selesai', 'Dibatalkan', 'Pengajuan Ditolak') AND ? < tanggal_selesai AND ? > tanggal_mulai");
        $stmt_check->execute([$id_mobil, $tanggal_mulai, $tanggal_selesai]);
        if ($stmt_check->fetch()) {
            $pdo->rollBack();
            $error_booking = 'Maaf, mobil ini sudah dipesan orang lain pada rentang tanggal tersebut.';
        } else {
            $stmt_lock_car = $pdo->prepare("UPDATE mobil SET status = 'Dipesan' WHERE id_mobil = ? AND status = 'Tersedia'");
            $stmt_lock_car->execute([$id_mobil]);
            if ($stmt_lock_car->rowCount() === 0) {
                $pdo->rollBack();
                $error_booking = 'Maaf, mobil ini baru saja dipesan. Silakan coba lagi.';
            } else {
                $batas_pembayaran = (new DateTime())->modify('+3 hour')->format('Y-m-d H:i:s');
                $durasi = hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai);
                $total_biaya = ($durasi < 1 ? 1 : $durasi) * $harga_sewa_harian;
                $kode_pemesanan = generate_booking_code($pdo);

                $sql = "INSERT INTO pemesanan (kode_pemesanan, id_pengguna, id_mobil, tanggal_mulai, tanggal_selesai, total_biaya, status_pemesanan, batas_pembayaran) VALUES (?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$kode_pemesanan, $id_pengguna_session, $id_mobil, $tanggal_mulai, $tanggal_selesai, $total_biaya, $batas_pembayaran]);

                $id_pemesanan_baru = $pdo->lastInsertId();
                $pdo->commit();

                redirect_with_message(BASE_URL . "pelanggan/pemesanan.php" , 'Pemesanan berhasil dibuat! Segera lakukan pembayaran.');
            }
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_booking = "Gagal membuat pemesanan: " . $e->getMessage();
    }
}

// ==========================================================
// 2. MENGAMBIL SEMUA DATA UNTUK TAMPILAN
// ==========================================================
try {
    // Ambil data mobil utama
    $stmt_mobil = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt_mobil->execute([$id_mobil]);
    $mobil = $stmt_mobil->fetch();
    if (!$mobil) {
        redirect_with_message(BASE_URL . 'mobil.php', 'Mobil tidak ditemukan.', 'error');
    }

    // Inisialisasi variabel lain
    $user_data = null;
    $riwayat_perawatan = [];
    $perawatan_aktif = null;
    $reviews = [];
    $rating_summary = ['rata_rating' => 0, 'jumlah_review' => 0];

    // Ambil data spesifik jika pengguna sudah login
    if ($id_pengguna_session > 0) {
        if ($role_session === 'Pelanggan') {
            $stmt_user = $pdo->prepare("SELECT nik, foto_ktp FROM pengguna WHERE id_pengguna = ?");
            $stmt_user->execute([$id_pengguna_session]);
            $user_data = $stmt_user->fetch();
        } elseif (in_array($role_session, ['Admin', 'Karyawan'])) {
            $stmt_history = $pdo->prepare("SELECT * FROM riwayat_perawatan WHERE id_mobil = ? ORDER BY tanggal_masuk DESC");
            $stmt_history->execute([$id_mobil]);
            $riwayat_perawatan = $stmt_history->fetchAll();
        }
    }

    // Ambil data perawatan yang sedang aktif
    if ($mobil['status'] === 'Perawatan') {
        $stmt_perawatan = $pdo->prepare("SELECT * FROM riwayat_perawatan WHERE id_mobil = ? AND status_perawatan = 'Dikerjakan' ORDER BY tanggal_masuk DESC LIMIT 1");
        $stmt_perawatan->execute([$id_mobil]);
        $perawatan_aktif = $stmt_perawatan->fetch();
    }

    // Ambil ringkasan rating
    $stmt_summary = $pdo->prepare("SELECT AVG(rating_pengguna) as rata_rating, COUNT(review_pelanggan) as jumlah_review FROM pemesanan WHERE id_mobil = ? AND rating_pengguna IS NOT NULL AND review_pelanggan IS NOT NULL AND review_pelanggan != ''");
    $stmt_summary->execute([$id_mobil]);
    if ($summary = $stmt_summary->fetch()) {
        if ($summary['jumlah_review'] > 0) {
            $rating_summary = $summary;
        }
    }

    // Ambil daftar ulasan sesuai filter
    $sql_reviews = "SELECT p.rating_pengguna, p.review_pelanggan, p.updated_at, u.nama_lengkap FROM pemesanan p JOIN pengguna u ON p.id_pengguna = u.id_pengguna WHERE p.id_mobil = ? AND p.review_pelanggan IS NOT NULL AND p.review_pelanggan != ''";
    $params_reviews = [$id_mobil];
    if ($rating_filter >= 1 && $rating_filter <= 5) {
        $sql_reviews .= " AND p.rating_pengguna = ?";
        $params_reviews[] = $rating_filter;
    }
    $sql_reviews .= " ORDER BY p.updated_at DESC";
    if ($rating_filter === 0) {
        $sql_reviews .= " LIMIT 10";
    }
    $stmt_reviews = $pdo->prepare($sql_reviews);
    $stmt_reviews->execute($params_reviews);
    $reviews = $stmt_reviews->fetchAll();
} catch (PDOException $e) {
    die("Terjadi kesalahan pada database: " . $e->getMessage());
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
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="sewa_sekarang" value="1">
                        <input type="hidden" name="id_pengguna" value="<?= $id_pengguna_session ?>">
                        <input type="hidden" name="harga_sewa_harian" value="<?= $mobil['harga_sewa_harian'] ?>">
                        <div class="form-grid">
                            <div class="form-group"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai" required min="<?= date('Y-m-d') ?>"></div>
                            <div class="form-group"><label>Tanggal Selesai</label><input type="date" name="tanggal_selesai" required min="<?= date('Y-m-d') ?>"></div>
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