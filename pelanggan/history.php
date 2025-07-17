<?php
// File: pelanggan/history.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Hak akses hanya untuk Pelanggan
check_auth('Pelanggan');

$page_title = 'Riwayat Sewa Saya';
require_once '../includes/header.php';

$id_pengguna = $_SESSION['id_pengguna'];

// Ambil riwayat pemesanan yang sudah selesai atau dibatalkan milik pelanggan ini
try {
    $sql = "SELECT p.id_pemesanan, p.kode_pemesanan, p.tanggal_pemesanan, p.status_pemesanan,
                   m.merk, m.model, m.gambar_mobil
            FROM pemesanan p
            JOIN mobil m ON p.id_mobil = m.id_mobil
            WHERE p.id_pengguna = ? AND p.status_pemesanan IN ('Selesai', 'Dibatalkan')
            ORDER BY p.tanggal_pemesanan DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pengguna]);
    $histories = $stmt->fetchAll();
} catch (PDOException $e) {
    $histories = [];
}
?>

<div class="page-header">
    <h1>Riwayat Sewa Saya</h1>
    <p>Daftar semua transaksi sewa mobil Anda yang telah lalu.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode Pesanan</th>
                <th>Mobil</th>
                <th>Tanggal Pesan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): ?>
                <?php foreach ($histories as $history): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($history['kode_pemesanan']) ?></strong></td>
                        <td>
                            <div class="info-item-row">
                                <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($history['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil" class="info-item-image">
                                <div><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></div>
                            </div>
                        </td>
                        <td><?= date('d M Y', strtotime($history['tanggal_pemesanan'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Anda belum memiliki riwayat sewa.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>