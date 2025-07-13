<?php
// File: actions/pemesanan/detail.php (Versi Final Paling Lengkap)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk semua role yang sudah login
check_auth(['Admin', 'Karyawan', 'Pelanggan']);

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan === 0) {
    redirect_with_message(BASE_URL, 'ID Pemesanan tidak valid.', 'error');
}

$id_pengguna_session = $_SESSION['id_pengguna'];
$role_session = $_SESSION['role'];

// Query LENGKAP untuk mengambil semua data yang mungkin ada
try {
    $sql = "SELECT p.*, 
                   u.nama_lengkap AS nama_pelanggan, u.email AS email_pelanggan, u.no_telp AS telp_pelanggan,
                   m.merk, m.model, m.plat_nomor, m.gambar_mobil,
                   pay.metode_pembayaran, pay.status_pembayaran, pay.bukti_pembayaran, pay.tanggal_bayar
            FROM pemesanan p
            JOIN pengguna u ON p.id_pengguna = u.id_pengguna
            JOIN mobil m ON p.id_mobil = m.id_mobil
            LEFT JOIN pembayaran pay ON p.id_pemesanan = pay.id_pemesanan
            WHERE p.id_pemesanan = ?";

    $params = [$id_pemesanan];

    if ($role_session === 'Pelanggan') {
        $sql .= " AND p.id_pengguna = ?";
        $params[] = $id_pengguna_session;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pemesanan = $stmt->fetch();

    if (!$pemesanan) {
        redirect_with_message(BASE_URL, 'Pemesanan tidak ditemukan atau Anda tidak memiliki akses.', 'error');
    }
} catch (PDOException $e) {
    die("Terjadi kesalahan pada database: " . $e->getMessage());
}

$page_title = 'Detail Pemesanan #' . htmlspecialchars($pemesanan['kode_pemesanan']);
require_once '../../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<div class="page-header">
    <h1>Detail Pemesanan</h1>
</div>

<div class="detail-container">
    <div class="detail-main">
        <div class="info-item booking-code-item">
            <span class="label">Kode Pemesanan</span>
            <span class="value code"><?= htmlspecialchars($pemesanan['kode_pemesanan']) ?></span>
        </div>
        <div class="info-item"><span class="label">Status Pemesanan</span><span class="value"><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pemesanan['status_pemesanan'])) ?>"><?= htmlspecialchars($pemesanan['status_pemesanan']) ?></span></span></div>
        <div class="info-item"><span class="label">Tanggal Pesan</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_pemesanan'])) ?></span></div>
        <div class="info-item"><span class="label">Durasi Sewa</span><span class="value"><?= date('d M Y', strtotime($pemesanan['tanggal_mulai'])) ?> s/d <?= date('d M Y', strtotime($pemesanan['tanggal_selesai'])) ?></span></div>
        <?php if ($pemesanan['waktu_pengambilan']): ?>
            <div class="info-item"><span class="label">Waktu Aktual Pengambilan</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['waktu_pengambilan'])) ?></span></div>
        <?php endif; ?>
        
        <hr>
        <h3>Informasi Pembayaran</h3>
        <div class="info-item"><span class="label">Total Biaya</span><span class="value price"><?= format_rupiah($pemesanan['total_biaya']) ?></span></div>
        <div class="info-item"><span class="label">Status Pembayaran</span><span class="value"><?= htmlspecialchars($pemesanan['status_pembayaran'] ?: 'Belum Bayar') ?></span></div>
        <?php if ($pemesanan['tanggal_bayar']): ?>
            <div class="info-item"><span class="label">Tanggal Bayar</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_bayar'])) ?></span></div>
        <?php endif; ?>
        <?php if ($pemesanan['bukti_pembayaran']): ?>
            <div class="info-item bukti-pembayaran-item"><span class="label">Bukti Pembayaran</span><span class="value"><a href="<?= BASE_URL ?>assets/img/bukti_pembayaran/<?= htmlspecialchars($pemesanan['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti</a></span></div>
        <?php endif; ?>
    </div>

    <div class="detail-sidebar">
        <h3>Informasi Pelanggan</h3>
        <div class="info-item"><span class="label">Nama</span><span class="value"><?= htmlspecialchars($pemesanan['nama_pelanggan']) ?></span></div>
        <div class="info-item"><span class="label">Email</span><span class="value"><?= htmlspecialchars($pemesanan['email_pelanggan']) ?></span></div>
        <div class="info-item"><span class="label">No. Telepon</span><span class="value"><?= htmlspecialchars($pemesanan['telp_pelanggan']) ?></span></div>
        <hr>
        <h3>Informasi Mobil</h3>
        <div class="info-item-row">
            <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($pemesanan['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil" class="info-item-image">
            <div>
                <strong><?= htmlspecialchars($pemesanan['merk'] . ' ' . $pemesanan['model']) ?></strong><br>
                Plat: <?= htmlspecialchars($pemesanan['plat_nomor']) ?>
            </div>
        </div>
        
        <?php if ($pemesanan['status_pemesanan'] === 'Dikonfirmasi' && $role_session === 'Pelanggan'): ?>
            <div class="qr-code-container">
                <h4>Tunjukkan QR Code ini Saat Pengambilan Mobil</h4>
                <div id="qrcode" data-kode="<?= htmlspecialchars($pemesanan['kode_pemesanan']) ?>"></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($pemesanan['status_pemesanan'] === 'Pengajuan Pembatalan'): ?>
<div class="cancellation-info">
    <h4>Informasi Pengajuan Pembatalan</h4>
    <div class="info-item"><span class="label">Alasan Pelanggan</span><div class="value description"><?= htmlspecialchars($pemesanan['alasan_pembatalan']) ?></div></div>
    <div class="info-item"><span class="label">No. Rekening Refund</span><div class="value"><?= htmlspecialchars($pemesanan['rekening_pembatalan']) ?></div></div>
</div>
<?php endif; ?>

<div class="detail-actions">
    <button onclick="window.print();" class="btn btn-info">Cetak</button>

    <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
        <?php if ($pemesanan['status_pembayaran'] === 'Menunggu Verifikasi'): ?>
            <form action="<?= BASE_URL ?>actions/pembayaran/verifikasi.php" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini?');" style="display:inline-block;">
                <input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>"><input type="hidden" name="id_mobil" value="<?= $pemesanan['id_mobil'] ?>">
                <button type="submit" class="btn btn-primary">Verifikasi</button>
            </form>
        <?php elseif ($pemesanan['status_pemesanan'] === 'Pengajuan Pembatalan'): ?>
             <form action="<?= BASE_URL ?>actions/pemesanan/proses_pembatalan.php" method="POST" onsubmit="return confirm('Anda akan membatalkan pesanan ini. Lanjutkan?');" style="display:inline-block;">
                <input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>"><input type="hidden" name="id_mobil" value="<?= $pemesanan['id_mobil'] ?>">
                <button type="submit" class="btn btn-danger">Proses Pembatalan</button>
            </form>
        <?php endif; ?>
    <?php elseif ($role_session === 'Pelanggan'): ?>
        <?php if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran' && !$pemesanan['bukti_pembayaran']): ?>
            <a href="<?= BASE_URL ?>pelanggan/pembayaran.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-primary">Bayar</a>
        <?php elseif ($pemesanan['status_pemesanan'] === 'Dikonfirmasi'): ?>
             <a href="<?= BASE_URL ?>pelanggan/ajukan_pembatalan.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-danger">Ajukan Pembatalan</a>
        <?php endif; ?>
    <?php endif; ?>

    <a href="<?= BASE_URL . strtolower($role_session) ?>/dashboard.php" class="btn btn-secondary">Kembali</a>
</div>

<?php
require_once '../../includes/footer.php';
?>

<script src="<?= BASE_URL ?>assets/js/qr-generator.js"></script>