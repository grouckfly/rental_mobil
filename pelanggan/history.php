<?php
// File: pelanggan/history.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$page_title = 'Riwayat Sewa Saya';
require_once '../includes/header.php';

$id_pengguna = $_SESSION['id_pengguna'];

// Mengambil riwayat pemesanan yang sudah selesai atau dibatalkan
try {
    $stmt = $pdo->prepare("
        SELECT p.*, m.merk, m.model
        FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.id_pengguna = ? AND p.status_pemesanan IN ('Selesai', 'Dibatalkan')
        ORDER BY p.tanggal_pemesanan DESC
    ");
    $stmt->execute([$id_pengguna]);
    $histories = $stmt->fetchAll();
} catch (PDOException $e) {
    $histories = [];
}
?>

<div class="page-header">
    <h1>Riwayat Sewa</h1>
    <p>Daftar semua transaksi sewa mobil Anda yang telah lalu.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Mobil</th>
                <th>Tanggal Sewa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): ?>
                <?php foreach ($histories as $history): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($history['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= date('d M Y', strtotime($history['tanggal_mulai'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="detail_pemesanan.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-secondary btn-sm">Lihat Detail</a>
                            <?php if ($history['status_pemesanan'] == 'Selesai'): ?>
                                <?php endif; ?>
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