<?php
// File: karyawan/pembayaran.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$page_title = 'Verifikasi Pembayaran & Pemesanan';
require_once '../includes/header.php';

// Mengambil pemesanan yang perlu tindakan
try {
    $stmt = $pdo->query("
        SELECT p.*, pg.nama_lengkap, m.merk, m.model, pay.bukti_pembayaran
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
        WHERE p.status_pemesanan IN ('Menunggu Pembayaran', 'Dikonfirmasi', 'Berjalan')
        ORDER BY p.tanggal_pemesanan DESC
    ");
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}
?>

<div class="page-header">
    <h1>Verifikasi & Pemesanan Aktif</h1>
    <p>Kelola pemesanan yang menunggu pembayaran atau sedang berjalan.</p>
</div>

<div class="table-container"
    data-live-context="admin_pemesanan"
    data-live-total="<?= count($bookings) ?>"
    data-live-last-update="<?= $pdo->query("SELECT MAX(tanggal_pemesanan) FROM pemesanan")->fetchColumn() ?>">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Mobil</th>
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
                        <td><?= htmlspecialchars($booking['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></td>
                        <td><?= format_rupiah($booking['total_biaya']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking['status_pemesanan'])) ?>"><?= htmlspecialchars($booking['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="../actions/pemesanan/detail.php?id=<?= $booking['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Tidak ada pemesanan yang perlu ditindaklanjuti.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>