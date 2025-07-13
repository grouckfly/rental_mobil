<?php
// File: actions/pemesanan/detail.php (Versi Perbaikan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan', 'Pelanggan']);

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan === 0) {
    redirect_with_message(BASE_URL, 'ID Pemesanan tidak valid.', 'error');
}

$id_pengguna_session = $_SESSION['id_pengguna'];
$role_session = $_SESSION['role'];

try {
    // Query dasar untuk mengambil semua data terkait
    $sql = "SELECT p.*, 
                   u.nama_lengkap AS nama_pelanggan, u.email AS email_pelanggan, u.no_telp AS telp_pelanggan,
                   m.merk, m.model, m.plat_nomor, m.gambar_mobil,
                   pay.metode_pembayaran, pay.status_pembayaran, pay.bukti_pembayaran, 
                   pay.tanggal_bayar -- << PERBAIKAN DI SINI (sebelumnya tgl_pembayaran)
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

$page_title = 'Detail Pemesanan #' . htmlspecialchars($pemesanan['id_pemesanan']);
require_once '../../includes/header.php';
?>

<div class="page-header">
    <h1>Detail Pemesanan #<?= htmlspecialchars($pemesanan['id_pemesanan']) ?></h1>
</div>

<div class="detail-container">
    <div class="detail-main">
        <h3>Informasi Mobil</h3>
        <div class="info-item-row">
            <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($pemesanan['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil" class="info-item-image">
            <div>
                <strong><?= htmlspecialchars($pemesanan['merk'] . ' ' . $pemesanan['model']) ?></strong><br>
                Plat: <?= htmlspecialchars($pemesanan['plat_nomor']) ?>
            </div>
        </div>
        <hr>
        <h3>Informasi Pelanggan</h3>
        <div class="info-item">
            <span class="label">Nama</span>
            <span class="value"><?= htmlspecialchars($pemesanan['nama_pelanggan']) ?></span>
        </div>
        <div class="info-item">
            <span class="label">Email</span>
            <span class="value"><?= htmlspecialchars($pemesanan['email_pelanggan']) ?></span>
        </div>
        <div class="info-item">
            <span class="label">No. Telepon</span>
            <span class="value"><?= htmlspecialchars($pemesanan['telp_pelanggan']) ?></span>
        </div>
    </div>

    <div class="detail-sidebar">
        <h3>Status & Biaya</h3>
        <div class="info-item">
            <span class="label">Status Pemesanan</span>
            <span class="value"><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pemesanan['status_pemesanan'])) ?>"><?= htmlspecialchars($pemesanan['status_pemesanan']) ?></span></span>
        </div>
        <div class="info-item">
            <span class="label">Status Pembayaran</span>
            <span class="value"><?= htmlspecialchars($pemesanan['status_pembayaran'] ?: 'Belum Ada') ?></span>
        </div>
        <div class="info-item">
            <span class="label">Tanggal Pesan</span>
            <span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_pemesanan'])) ?></span>
        </div>
        <div class="info-item">
            <span class="label">Sewa</span>
            <span class="value"><?= date('d M Y', strtotime($pemesanan['tanggal_mulai'])) ?> s/d <?= date('d M Y', strtotime($pemesanan['tanggal_selesai'])) ?></span>
        </div>
        <?php if ($pemesanan['tanggal_bayar']): // Tampilkan tanggal bayar jika sudah ada 
        ?>
            <div class="info-item">
                <span class="label">Tanggal Bayar</span>
                <span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_bayar'])) ?></span>
            </div>
        <?php endif; ?>
        <div class="info-item">
            <span class="label">Total Biaya</span>
            <span class="value price"><?= format_rupiah($pemesanan['total_biaya']) ?></span>
        </div>
        <?php if ($pemesanan['bukti_pembayaran']): ?>
            <div class="info-item">
                <span class="label">Bukti Pembayaran</span>
                <span class="value"><a href="<?= BASE_URL ?>assets/img/bukti_pembayaran/<?= htmlspecialchars($pemesanan['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti</a></span>
            </div>
        <?php endif; ?>

        <div class="detail-actions">
            <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>

                <?php // Tampilkan tombol Verifikasi jika bukti sudah diunggah dan statusnya 'Menunggu Verifikasi'
                if ($pemesanan['bukti_pembayaran'] && $pemesanan['status_pembayaran'] === 'Menunggu Verifikasi'): ?>
                    <form action="<?= BASE_URL ?>actions/pembayaran/verifikasi.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini?');">
                        <input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>">
                        <button type="submit" class="btn btn-primary">Verifikasi Pembayaran</button>
                    </form>

                <?php // Tombol lain untuk Admin/Karyawan
                elseif ($pemesanan['status_pemesanan'] === 'Dikonfirmasi'): ?>
                    <a href="#" class="btn btn-success">Mulai Penyewaan</a>
                <?php endif; ?>

            <?php elseif ($role_session === 'Pelanggan'): ?>

                <?php // Tombol untuk Pelanggan
                if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran' && !$pemesanan['bukti_pembayaran']): ?>
                    <a href="<?= BASE_URL ?>pelanggan/pembayaran.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-primary">Lakukan Pembayaran</a>
                <?php endif; ?>

            <?php endif; ?>

            <a href="<?= BASE_URL . strtolower($role_session) ?>/dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>