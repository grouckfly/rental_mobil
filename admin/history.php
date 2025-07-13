<?php
// File: admin/history.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Admin');

$page_title = 'Riwayat Transaksi';
require_once '../includes/header.php';

// Mengambil data pemesanan yang sudah selesai atau dibatalkan
try {
    $stmt = $pdo->query("
        SELECT p.*, pg.nama_lengkap, m.merk, m.model
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.status_pemesanan IN ('Selesai', 'Dibatalkan')
        ORDER BY p.tanggal_pemesanan DESC
    ");
    $histories = $stmt->fetchAll();
} catch (PDOException $e) {
    $histories = [];
}
?>

<div class="page-header">
    <h1>Riwayat Transaksi</h1>
    <p>Daftar semua transaksi yang telah selesai atau dibatalkan.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Mobil</th>
                <th>Total Biaya</th>
                <th>Tgl Selesai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): ?>
                <?php foreach ($histories as $history): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($history['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($history['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= format_rupiah($history['total_biaya']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($history['tanggal_selesai'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="detail_pemesanan.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-secondary btn-sm">Lihat Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Belum ada riwayat transaksi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>