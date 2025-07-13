<?php
// File: pelanggan/pemesanan.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$page_title = 'Pemesanan Saya';
require_once '../includes/header.php';

$id_pengguna = $_SESSION['id_pengguna'];

// Mengambil data pemesanan yang aktif atau menunggu
try {
    $stmt = $pdo->prepare("
        SELECT p.*, m.merk, m.model
        FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.id_pengguna = ? AND p.status_pemesanan IN ('Menunggu Pembayaran', 'Dikonfirmasi', 'Berjalan')
        ORDER BY p.tanggal_pemesanan DESC
    ");
    $stmt->execute([$id_pengguna]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}
?>

<div class="page-header">
    <h1>Pemesanan Aktif Saya</h1>
    <p>Daftar pemesanan yang sedang berjalan atau menunggu pembayaran.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Mobil</th>
                <th>Tanggal Sewa</th>
                <th>Total Biaya</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($booking['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></td>
                        <td><?= date('d M Y', strtotime($booking['tanggal_mulai'])) ?></td>
                        <td><?= format_rupiah($booking['total_biaya']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking['status_pemesanan'])) ?>"><?= htmlspecialchars($booking['status_pemesanan']) ?></span></td>
                        <td>
                            <?php if ($booking['status_pemesanan'] == 'Menunggu Pembayaran'): ?>
                                <a href="../actions/pemesanan/detail.php?id=<?= $booking['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                            <?php else: ?>
                                <a href="../actions/pemesanan/detail.php?id=<?= $booking['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Tidak ada pemesanan aktif.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>