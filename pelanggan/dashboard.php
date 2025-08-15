<?php
// File: pelanggan/dashboard.php (Versi Lebih Informatif)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');
$page_title = 'Dashboard Pelanggan';
require_once '../includes/header.php';

$id_pengguna = $_SESSION['id_pengguna'];

// Notifikasi dari URL (misal: pembayaran kedaluwarsa)
$notification_script = '';
if (isset($_GET['status']) && $_GET['status'] === 'payment_expired') {
    $message = addslashes('Waktu pembayaran telah habis dan pesanan Anda telah dibatalkan.');
    $notification_script = "<script>document.addEventListener('DOMContentLoaded', () => { showToast('{$message}', 'error'); });</script>";
}

// Ambil data untuk penanda auto-refresh
$stmt_live = $pdo->prepare("SELECT COUNT(*) as total, MAX(updated_at) as last_update FROM pemesanan WHERE id_pengguna = ?");
$stmt_live->execute([$id_pengguna]);
$live_data = $stmt_live->fetch();

try {
    // Menghitung jumlah pemesanan
    $stmt_aktif = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_pengguna = ? AND status_pemesanan IN ('Menunggu Pembayaran', 'Menunggu Verifikasi', 'Dikonfirmasi', 'Berjalan')");
    $stmt_aktif->execute([$id_pengguna]);
    $pemesanan_aktif = $stmt_aktif->fetchColumn();

    $stmt_selesai = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_pengguna = ? AND status_pemesanan = 'Selesai'");
    $stmt_selesai->execute([$id_pengguna]);
    $pemesanan_selesai = $stmt_selesai->fetchColumn();

    // Menghitung total pengeluaran dari pesanan yang selesai
    $stmt_total = $pdo->prepare("SELECT SUM(total_biaya + total_denda) FROM pemesanan WHERE id_pengguna = ? AND status_pemesanan = 'Selesai'");
    $stmt_total->execute([$id_pengguna]);
    $total_pengeluaran = $stmt_total->fetchColumn();
    
    // Mengambil 1 pemesanan paling mendesak untuk ditampilkan
    $stmt_booking = $pdo->prepare("SELECT p.*, m.merk, m.model, m.gambar_mobil FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pengguna = ? AND p.status_pemesanan IN ('Menunggu Pembayaran', 'Menunggu Verifikasi', 'Dikonfirmasi', 'Berjalan') ORDER BY p.updated_at DESC LIMIT 1");
    $stmt_booking->execute([$id_pengguna]);
    $booking_terbaru = $stmt_booking->fetch();
} catch (PDOException $e) {
    $pemesanan_aktif = $pemesanan_selesai = 0;
    $total_pengeluaran = 0;
    $booking_terbaru = null;
}
?>

<div class="page-header with-action">
    <h1>Dashboard Saya</h1>
    <div class="header-actions">
        <a href="profile.php" class="btn btn-secondary">Profil Saya</a>
        <a href="<?= BASE_URL ?>actions/pesan/inbox.php" class="btn btn-primary">Bantuan & Pesan</a>
    </div>
</div>

<div class="dashboard-wrapper">
    <div class="dashboard-widgets">
        <div class="widget">
            <h3>Pemesanan Aktif</h3>
            <p class="widget-data"><?= $pemesanan_aktif ?></p>
            <a href="pemesanan.php">Lihat Detail &rarr;</a>
        </div>
        <div class="widget">
            <h3>Sewa Selesai</h3>
            <p class="widget-data"><?= $pemesanan_selesai ?></p>
            <a href="<?= BASE_URL ?>admin/history.php">Lihat Riwayat &rarr;</a>
        </div>
        <div class="widget">
            <h3>Total Pengeluaran</h3>
            <p class="widget-data price"><?= format_rupiah($total_pengeluaran ?? 0) ?></p>
            <div class="widget-details"><span>Dari semua pesanan selesai</span></div>
        </div>
    </div>

    <div class="section-container">
        <h2>Pemesanan Paling Mendesak</h2>
        <?php if ($booking_terbaru): ?>
            <div class="active-booking-card">
                <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($booking_terbaru['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil">
                <div class="booking-details">
                    <h3><?= htmlspecialchars($booking_terbaru['merk'] . ' ' . $booking_terbaru['model']) ?></h3>
                    <p><strong>Kode:</strong> <?= htmlspecialchars($booking_terbaru['kode_pemesanan']) ?></p>
                    <p><strong>Status:</strong> <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $booking_terbaru['status_pemesanan'])) ?>"><?= htmlspecialchars($booking_terbaru['status_pemesanan']) ?></span></p>
                    
                    <?php if ($booking_terbaru['status_pemesanan'] === 'Menunggu Pembayaran'): ?>
                        <div class="timer-container payment-timer mini">
                            <small>Sisa Waktu Pembayaran</small>
                            <div id="countdown-timer" data-end-time="<?= $booking_terbaru['batas_pembayaran'] ?>" data-action-on-expire="redirect"></div>
                        </div>
                    <?php elseif ($booking_terbaru['status_pemesanan'] === 'Berjalan'): ?>
                        <div class="timer-container mini">
                            <small>Sisa Waktu Sewa</small>
                            <div id="countdown-timer" data-end-time="<?= $booking_terbaru['tanggal_selesai'] ?>"></div>
                        </div>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $booking_terbaru['id_pemesanan'] ?>" class="btn btn-primary">Lihat & Kelola Pesanan</a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Saat ini tidak ada pemesanan aktif yang perlu ditindaklanjuti.</p>
                <a href="<?= BASE_URL ?>mobil.php" class="btn btn-primary">Sewa Mobil Sekarang</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once '../includes/footer.php';
// Panggil script notifikasi dan timer
echo $notification_script;
echo '<script src="'.BASE_URL.'assets/js/rental-timer.js"></script>';
?>