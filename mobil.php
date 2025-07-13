<?php
// File: mobil.php

require_once 'includes/config.php';
require_once 'includes/functions.php';
$page_title = 'Daftar Mobil Tersedia';
require_once 'includes/header.php';

// Logika pencarian
$params = [];
$sql = "SELECT id_mobil, merk, model, harga_sewa_harian, gambar_mobil FROM mobil WHERE status = 'Tersedia'";

if (!empty($_GET['search'])) {
    $sql .= " AND (merk LIKE :search OR model LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Armada Kami</h1>
    <p>Temukan mobil yang paling sesuai dengan kebutuhan perjalanan Anda.</p>
</div>

<section class="search-section">
    <div class="container">
        <form action="mobil.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Cari merk atau model mobil..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>
</section>

<section class="car-listing-section">
    <div class="container">
        <div class="car-grid">
            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <div class="car-card-image">
                            <img src="uploads/mobil/<?= htmlspecialchars($car['gambar_mobil'] ?: 'default-car.png') ?>" alt="<?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?>">
                        </div>
                        <div class="car-card-content">
                            <h3><?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?></h3>
                            <p class="car-price"><strong><?= format_rupiah($car['harga_sewa_harian']) ?></strong> / hari</p>
                            <a href="detail_mobil.php?id=<?= $car['id_mobil'] ?>" class="btn btn-secondary">Lihat Detail</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Mohon maaf, tidak ada mobil yang ditemukan sesuai kriteria Anda.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>