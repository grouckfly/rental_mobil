<?php
// File: actions/pemesanan/detail.php (Versi Final, Lengkap, dan Stabil)

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

// QUERY LENGKAP: Mengambil semua data yang dibutuhkan dari semua tabel terkait
try {
    $sql = "SELECT p.*, 
                   u.nama_lengkap AS nama_pelanggan, u.email AS email_pelanggan, u.no_telp AS telp_pelanggan,
                   m.merk, m.model, m.plat_nomor, m.gambar_mobil, m.denda_per_hari,
                   pay.status_pembayaran, pay.bukti_pembayaran, pay.tanggal_bayar
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
        redirect_with_message(BASE_URL . strtolower($role_session) . '/dashboard.php', 'Pemesanan tidak ditemukan atau Anda tidak memiliki akses.', 'error');
    }
} catch (PDOException $e) {
    die("Terjadi kesalahan pada database: " . $e->getMessage());
}

$page_title = 'Detail Pemesanan #' . htmlspecialchars($pemesanan['kode_pemesanan']);
require_once '../../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<div style="background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin: 20px 0; border-radius: 5px;">
    <strong>Info Debug:</strong> Status Pemesanan:
    <span style="color:blue">'<?= $pemesanan['status_pemesanan'] ?>'</span> | Role Saat Ini:
    <span style="color:blue">'<?= $role_session ?>'</span>
</div>

<div class="page-header">
    <h1>Detail Pemesanan</h1>
</div>

<?php
// ==========================================================
// BLOK INI UNTUK MENAMPILKAN NOTIFIKASI PENOLAKAN
// ==========================================================
if ($role_session === 'Pelanggan' && !empty($pemesanan['catatan_admin'])):
?>
    <div class="flash-message flash-error">
        <strong>Pemberitahuan dari Admin:</strong><br>
        <?= htmlspecialchars($pemesanan['catatan_admin']) ?>

        <form action="<?= BASE_URL ?>actions/pemesanan/hapus_catatan.php" method="POST" style="margin-top:10px;">
            <input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>">
            <button type="submit" class="btn btn-sm btn-light">Saya Mengerti</button>
        </form>
    </div>
<?php endif; ?>

<div class="detail-container"
    data-live-context="detail_pemesanan"
    data-live-id="<?= $pemesanan['id_pemesanan'] ?>"
    data-live-status="<?= $pemesanan['status_pemesanan'] ?>"
    data-live-last-update="<?= $pemesanan['updated_at'] ?>">
    <div class="detail-main">
        <div class="info-item booking-code-item">
            <span class="label">Kode Pemesanan</span>
            <span class="value code"><?= htmlspecialchars($pemesanan['kode_pemesanan']) ?></span>
        </div>
        <div class="info-item"><span class="label">Status Pemesanan</span><span class="value"><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pemesanan['status_pemesanan'])) ?>"><?= htmlspecialchars($pemesanan['status_pemesanan']) ?></span></span></div>
        <div class="info-item"><span class="label">Tanggal Pesan</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_pemesanan'])) ?></span></div>
        <div class="info-item"><span class="label">Jadwal Sewa</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_mulai'])) ?> s/d <?= date('d M Y, H:i', strtotime($pemesanan['tanggal_selesai'])) ?></span></div>
        <?php if ($pemesanan['waktu_pengambilan']): ?>
            <div class="info-item"><span class="label">Waktu Aktual Pengambilan</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['waktu_pengambilan'])) ?></span></div>
        <?php endif; ?>
        <?php if ($pemesanan['waktu_pengembalian']): ?>
            <div class="info-item"><span class="label">Waktu Aktual Pengembalian</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['waktu_pengembalian'])) ?></span></div>
        <?php endif; ?>

        <hr>
        <h3>Informasi Pembayaran</h3>
        <div class="info-item"><span class="label">Total Biaya Sewa</span><span class="value price"><?= format_rupiah($pemesanan['total_biaya']) ?></span></div>
        <div class="info-item"><span class="label">Denda Keterlambatan</span><span class="value price"><?= format_rupiah($pemesanan['total_denda']) ?></span></div>
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
            <img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($pemesanan['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil" class="info-item-image">
            <div><strong><?= htmlspecialchars($pemesanan['merk'] . ' ' . $pemesanan['model']) ?></strong><br>Plat: <?= htmlspecialchars($pemesanan['plat_nomor']) ?></div>
        </div>

        <?php if ($role_session === 'Pelanggan'):
            $qr_title = '';
            if ($pemesanan['status_pemesanan'] === 'Dikonfirmasi') {
                $qr_title = 'Tunjukkan QR Code ini Saat Pengambilan';
            } elseif ($pemesanan['status_pemesanan'] === 'Berjalan') {
                $qr_title = 'Tunjukkan QR Code ini Saat Pengembalian';
            }
            if (!empty($qr_title)): ?>
                <div class="qr-code-container">
                    <h4><?= $qr_title ?></h4>
                    <div id="qrcode" data-kode="<?= htmlspecialchars($pemesanan['kode_pemesanan']) ?>"></div>
                </div>
        <?php endif;
        endif; ?>

        <?php if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran' && !empty($pemesanan['batas_pembayaran'])): ?>
            <div class="timer-container payment-timer">
                <h4>Sisa Waktu Pembayaran</h4>
                <div id="countdown-timer"
                    data-end-time="<?= $pemesanan['batas_pembayaran'] ?>"
                    data-action-on-expire="redirect">
                </div>
            </div>
        <?php endif; ?>

        <?php if ($pemesanan['status_pemesanan'] === 'Berjalan'): ?>
            <div class="timer-container">
                <h4>Sisa Waktu Sewa</h4>
                <div id="countdown-timer" data-end-time="<?= $pemesanan['tanggal_selesai'] ?>"></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($pemesanan['review_pelanggan'])): ?>
            <hr>
            <h3>Ulasan Pelanggan</h3>
            <div class="info-item">
                <span class="label">Rating</span>
                <div class="value star-rating" data-rating="<?= $pemesanan['rating_pengguna'] ?>">
                </div>
            </div>
            <div class="info-item">
                <span class="label">Ulasan</span>
                <div class="value description">
                    <?= nl2br(htmlspecialchars($pemesanan['review_pelanggan'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($pemesanan['status_pemesanan'] === 'Pengajuan Pembatalan' && !empty($pemesanan['alasan_pembatalan'])): ?>
    <div class="cancellation-info">
        <h4>Informasi Pengajuan Pembatalan</h4>
        <div class="info-item"><span class="label">Alasan Pelanggan</span>
            <div class="value description"><?= htmlspecialchars($pemesanan['alasan_pembatalan']) ?></div>
        </div>
        <div class="info-item"><span class="label">No. Rekening Refund</span>
            <div class="value"><?= htmlspecialchars($pemesanan['rekening_pembatalan']) ?></div>
        </div>
        <form action="<?= BASE_URL ?>actions/pemesanan/proses_pembatalan.php" method="POST" onsubmit="return confirm('Anda akan membatalkan pesanan ini. Lanjutkan?');" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>">
            <input type="hidden" name="id_mobil" value="<?= $pemesanan['id_mobil'] ?>"><button type="submit" class="btn btn-danger">Proses Pembatalan</button>
        </form>
    </div>
<?php endif; ?>

<?php if ($pemesanan['status_pemesanan'] === 'Pengajuan Ambil Cepat' && in_array($role_session, ['Admin', 'Karyawan'])): ?>
    <div class="cancellation-info">
        <h4>Pengajuan Perubahan Jadwal</h4>
        <div class="info-item"><span class="label">Jadwal Awal</span>
            <div class="value"><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_mulai'])) ?></div>
        </div>
        <div class="info-item"><span class="label">Jadwal Diajukan</span>
            <div class="value" style="color:var(--success-color); font-weight:bold;"><?= date('d M Y, H:i', strtotime($pemesanan['tgl_mulai_diajukan'])) ?></div>
        </div>
        <div class="info-item"><span class="label">Biaya Awal</span>
            <div class="value"><?= format_rupiah($pemesanan['total_biaya']) ?></div>
        </div>
        <div class="info-item"><span class="label">Estimasi Biaya Baru</span>
            <div class="value price"><?= format_rupiah($pemesanan['total_biaya_diajukan']) ?></div>
        </div>
        <div class="detail-actions">
            <form action="<?= BASE_URL ?>actions/pemesanan/proses_pengajuan.php" method="POST" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>"><input type="hidden" name="keputusan" value="setuju"><button type="submit" class="btn btn-success">Setujui</button></form>
            <form action="<?= BASE_URL ?>actions/pemesanan/proses_pengajuan.php" method="POST" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>"><input type="hidden" name="keputusan" value="tolak"><button type="submit" class="btn btn-danger">Tolak</button></form>
        </div>
    </div>
<?php endif; ?>

<?php if ($pemesanan['status_pemesanan'] === 'Pengajuan Ditolak' && $role_session === 'Pelanggan'): ?>
    <div class="flash-message flash-error">Pengajuan perubahan jadwal Anda sebelumnya telah ditolak. Jadwal kembali ke semula.</div>
<?php endif; ?>
<?php if ($pemesanan['status_pemesanan'] === 'Pengajuan Ambil Cepat' && $role_session === 'Pelanggan'): ?>
    <div class="flash-message flash-info">Pengajuan perubahan jadwal Anda sedang diproses oleh admin.</div>
<?php endif; ?>


<div class="detail-actions">
    <button onclick="window.print();" class="btn btn-info">Cetak</button>

    <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
        <?php if ($pemesanan['status_pembayaran'] === 'Menunggu Verifikasi'): ?>
            <form action="<?= BASE_URL ?>actions/pembayaran/verifikasi.php" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini?');" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>"><input type="hidden" name="id_mobil" value="<?= $pemesanan['id_mobil'] ?>"><button type="submit" class="btn btn-primary">Verifikasi</button></form>
        <?php elseif ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran Denda'): ?>
            <form action="<?= BASE_URL ?>actions/pemesanan/proses_penyelesaian.php" method="POST" onsubmit="return confirm('Konfirmasi denda telah dibayar dan selesaikan penyewaan?');" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>"><input type="hidden" name="id_mobil" value="<?= $pemesanan['id_mobil'] ?>"><button type="submit" class="btn btn-success">Selesaikan Sewa</button></form>
        <?php endif; ?>

        <?php
        $cancellable_statuses = ['Menunggu Pembayaran', 'Dikonfirmasi', 'Pengajuan Ambil Cepat', 'Menunggu Pembayaran Denda', 'Pengajuan Ditolak'];
        if (in_array($pemesanan['status_pemesanan'], $cancellable_statuses)):
        ?>
            <form action="<?= BASE_URL ?>actions/pemesanan/batalkan.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini secara permanen?');">
                <input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>">
                <button type="submit" class="btn btn-danger">Batalkan Pesanan</button>
            </form>
        <?php endif; ?>

    <?php elseif ($role_session === 'Pelanggan'): ?>

        <?php
        // Tampilkan tombol berdasarkan status pemesanan

        // 1. Jika status 'Menunggu Pembayaran' dan belum ada bukti bayar
        if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran' && empty($pemesanan['bukti_pembayaran'])): ?>
            <a href="<?= BASE_URL ?>pelanggan/pembayaran.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-primary">Bayar Sekarang</a>

        <?php
        // 2. Jika status 'Dikonfirmasi', tampilkan tombol aksi yang relevan
        elseif ($pemesanan['status_pemesanan'] === 'Dikonfirmasi'): ?>
            <a href="<?= BASE_URL ?>pelanggan/ajukan_pembatalan.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-danger">Ajukan Pembatalan</a>
            <a href="<?= BASE_URL ?>pelanggan/ajukan_ambil_cepat.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-info">Ambil Lebih Cepat</a>

            <?php
        // 3. Jika status 'Selesai', tampilkan tombol untuk ulasan
        elseif ($pemesanan['status_pemesanan'] === 'Selesai'):
            // Cek apakah ulasan sudah ada atau belum
            if (empty($pemesanan['review_pelanggan'])): ?>
                <a href="<?= BASE_URL ?>pelanggan/beri_ulasan.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-primary">Beri Review</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>pelanggan/beri_ulasan.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-secondary">Edit Ulasan</a>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

    <a href="<?= BASE_URL . strtolower($role_session) ?>/dashboard.php" class="btn btn-secondary">Kembali</a>
</div>

<?php
require_once '../../includes/footer.php';
?>

<script src="<?= BASE_URL ?>assets/js/qr-generator.js"></script>
<script src="<?= BASE_URL ?>assets/js/rental-timer.js"></script>