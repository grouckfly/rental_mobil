<?php
// File: index.php (Versi Final Disempurnakan)

// Panggil file konfigurasi untuk memeriksa session
require_once 'includes/config.php';

// Redirect otomatis jika pengguna sudah login
if (isset($_SESSION['id_pengguna']) && isset($_SESSION['role'])) {
    $role_folder = strtolower($_SESSION['role']);
    if ($role_folder === 'karyawan') {
        $role_folder = 'admin'; // Arahkan karyawan ke dashboard admin
    }
    // Pastikan folder untuk role tersebut ada
    if (is_dir($role_folder)) {
        header("Location: " . BASE_URL . "{$role_folder}/dashboard.php");
        exit;
    }
}

// Lanjutkan memuat file lain jika pengguna belum login
require_once 'includes/functions.php';

$page_title = 'Sewa Mobil Mudah & Cepat';
require_once 'includes/header.php';

// Mengambil 6 mobil 'Tersedia' secara acak untuk ditampilkan
$featured_cars = [];
try {
    $stmt = $pdo->query("SELECT id_mobil, merk, model, harga_sewa_harian, gambar_mobil 
                         FROM mobil 
                         WHERE status = 'Tersedia' 
                         ORDER BY RAND() 
                         LIMIT 6");
    $featured_cars = $stmt->fetchAll();
} catch (PDOException $e) {
    // Biarkan array kosong jika ada error, agar halaman tidak rusak
    error_log("Gagal mengambil mobil unggulan: " . $e->getMessage());
}
?>

<section class="hero-section">
    <div class="container hero-content">
        <h1>Sewa Mobil Terbaik Untuk Perjalanan Anda</h1>
        <p>Jelajahi Indonesia dengan nyaman bersama armada pilihan kami. Proses cepat, harga bersahabat.</p>
        <a href="mobil.php" class="btn btn-primary">Lihat Pilihan Mobil</a>
    </div>
</section>

<section class="services-section" style="padding: 60px 0;">
    <div class="container">
        <h2 style="text-align:center; margin-bottom: 40px; font-size: 2rem;">Kenapa Memilih Kami?</h2>
        <div class="services-grid">
            <div class="service-item">
                <h3>Harga Terjangkau</h3>
                <p>Kami memberikan penawaran harga sewa terbaik dan transparan tanpa ada biaya tersembunyi.</p>
            </div>
            <div class="service-item">
                <h3>Armada Terawat</h3>
                <p>Setiap unit mobil kami selalu dalam kondisi prima berkat perawatan berkala yang kami lakukan.</p>
            </div>
            <div class="service-item">
                <h3>Proses Cepat & Mudah</h3>
                <p>Pesan mobil impian Anda hanya dalam beberapa langkah mudah melalui website kami.</p>
            </div>
        </div>
    </div>
</section>

<section class="featured-cars-section" style="background-color: var(--surface-color); padding: 60px 0;">
    <div class="container">
        <h2 style="text-align:center; margin-bottom: 40px; font-size: 2rem;">Mobil Populer Kami</h2>
        <div class="car-grid">
            <?php if (!empty($featured_cars)): ?>
                <?php foreach ($featured_cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-image">
                            <img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($car['gambar_mobil'] ?: 'default-car.png') ?>" alt="<?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?>">
                        </div>
                        <div class="car-card-content">
                            <h3><?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?></h3>
                            <p class="car-price"><strong><?= format_rupiah($car['harga_sewa_harian']) ?></strong> / hari</p>
                            <a href="<?= BASE_URL ?>actions/mobil/detail.php?id=<?= $car['id_mobil'] ?>" class="btn btn-secondary">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align:center;">Saat ini belum ada mobil yang tersedia.</p>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="mobil.php" class="btn btn-primary">Lihat Semua Armada</a>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>