<?php
// File: admin/pembayaran.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$page_title = 'Kelola Pemesanan & Pembayaran';
require_once '../includes/header.php';

// Mengambil data pemesanan yang statusnya belum selesai atau batal
try {
    $stmt = $pdo->query("
        SELECT p.*, pg.nama_lengkap, m.merk, m.model
        FROM pemesanan p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.status_pemesanan NOT IN ('Selesai', 'Dibatalkan')
        ORDER BY p.tanggal_pemesanan DESC
    ");
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}
?>

<div class="page-header">
    <h1>Kelola Pemesanan Aktif</h1>
    <p>Halaman ini berisi daftar pemesanan yang menunggu pembayaran, konfirmasi, atau sedang berjalan.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Mobil</th>
                <th>Total Biaya</th>
                <th>Tgl Mulai</th>
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
                        <td><?= date('d M Y, H:i', strtotime($booking['tanggal_mulai'])) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking['status_pemesanan'])) ?>"><?= htmlspecialchars($booking['status_pemesanan']) ?></span></td>
                        <td>
                            <a href="../actions/pemesanan/detail.php?id=<?= $booking['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a>

                            <?php
                            // Tombol Batalkan hanya muncul jika statusnya memungkinkan untuk dibatalkan
                            // (misalnya, belum 'Selesai' atau sudah 'Dibatalkan')
                            $cancellable_statuses = ['Menunggu Pembayaran', 'Dikonfirmasi', 'Pengajuan Ambil Cepat', 'Pengajuan Pembatalan', 'Menunggu Pembayaran Denda', 'Pengajuan Ditolak'];
                            if (in_array($booking['status_pemesanan'], $cancellable_statuses)):
                            ?>
                                <form action="../actions/pemesanan/batalkan.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                    <input type="hidden" name="id_pemesanan" value="<?= $booking['id_pemesanan'] ?>">
                                </form>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Tidak ada pemesanan yang sedang aktif.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>