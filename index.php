<?php
// File: index.php

// Memanggil file konfigurasi, fungsi, dan header
require_once 'includes/config.php';
require_once 'includes/functions.php';
$page_title = 'Sewa Mobil Mudah & Cepat';
require_once 'includes/header.php';

// Mengambil data mobil unggulan dari database
$featured_cars = [];
try {
    $stmt = $pdo->prepare("SELECT id_mobil, merk, model, harga_sewa_harian, gambar_mobil FROM mobil WHERE status = 'Tersedia' ORDER BY RAND() LIMIT 4");
    $stmt->execute();
    $featured_cars = $stmt->fetchAll();
} catch (PDOException $e) {
    // Biarkan array kosong jika ada error, agar halaman tidak rusak
}
?>

<section class="hero-section">
    <div class="container hero-content">
        <h1>Sewa Mobil Terbaik Untuk Perjalanan Anda</h1>
        <p>Jelajahi Indonesia dengan nyaman bersama armada pilihan kami. Proses cepat, harga bersahabat.</p>
        <a href="mobil.php" class="btn btn-primary btn-lg">Lihat Pilihan Mobil</a>
    </div>
</section>

<section class="services-section">
    <div class="container">
        <h2 class="section-title">Kenapa Memilih Kami?</h2>
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

<section class="featured-cars-section">
    <div class="container">
        <h2 class="section-title">Mobil Populer Kami</h2>
        <div class="car-grid">
            <?php if (!empty($featured_cars)): ?>
                <?php foreach ($featured_cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-image">
                            <img src="assets/img/mobil/<?= htmlspecialchars($car['gambar_mobil'] ?: 'default-car.png') ?>" alt="<?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?>">
                        </div>
                        <div class="car-card-content">
                            <h3><?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?></h3>
                            <p class="car-price"><strong><?= format_rupiah($car['harga_sewa_harian']) ?></strong> / hari</p>
                            <a href="<?= BASE_URL ?>actions/mobil/detail.php?id=<?= $car['id_mobil'] ?>" class="btn btn-secondary">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Saat ini belum ada mobil yang tersedia.</p>
            <?php endif; ?>
        </div>
        <div class="section-cta">
            <a href="mobil.php" class="btn btn-primary">Lihat Semua Armada</a>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>