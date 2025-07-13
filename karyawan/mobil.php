<?php
// File: karyawan/mobil.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$page_title = 'Data Mobil';
require_once '../includes/header.php';

// Mengambil semua data mobil
try {
    $stmt = $pdo->query("SELECT * FROM mobil ORDER BY id_mobil DESC");
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
}
?>

<div class="page-header">
    <h1>Data Armada Mobil</h1>
    <p>Lihat dan kelola status ketersediaan armada mobil.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Plat Nomor</th>
                <th>Merk & Model</th>
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
                        <td>
                            <img src="../assets/img/mobil/<?= htmlspecialchars($car['gambar_mobil'] ?: 'default-car.png') ?>" alt="Gambar Mobil" width="80">
                        </td>
                        <td><?= htmlspecialchars($car['plat_nomor']) ?></td>
                        <td><?= htmlspecialchars($car['merk'] . ' ' . $car['model']) ?></td>
                        <td><?= format_rupiah($car['harga_sewa_harian']) ?></td>
                        <td><span class="status-badge status-<?= strtolower($car['status']) ?>"><?= htmlspecialchars($car['status']) ?></span></td>
                        <td>
                            <a href="../actions/mobil/detail.php?id=<?= $car['id_mobil'] ?>" class="btn btn-info btn-sm">Detail</a>
                            <a href="../actions/mobil/edit.php?id=<?= $car['id_mobil'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Belum ada data mobil.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>