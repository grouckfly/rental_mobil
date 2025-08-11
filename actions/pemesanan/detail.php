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
    // PERBAIKAN: Query utama sekarang mengambil semua kolom dari `pemesanan` dengan alias p.*
    $stmt_main = $pdo->prepare(
        "SELECT p.*, 
                u.nama_lengkap AS nama_pelanggan, u.email AS email_pelanggan, u.no_telp AS telp_pelanggan,
                m.merk, m.model, m.plat_nomor, m.gambar_mobil, m.denda_per_hari
         FROM pemesanan p
         JOIN pengguna u ON p.id_pengguna = u.id_pengguna
         JOIN mobil m ON p.id_mobil = m.id_mobil
         WHERE p.id_pemesanan = ?"
    );
    $stmt_main->execute([$id_pemesanan]);
    $pemesanan = $stmt_main->fetch();

    if (!$pemesanan) {
        redirect_with_message(BASE_URL . strtolower($role_session) . '/dashboard.php', 'Pemesanan tidak ditemukan.', 'error');
    }
    if ($role_session === 'Pelanggan' && $pemesanan['id_pengguna'] !== $id_pengguna_session) {
        redirect_with_message(BASE_URL . strtolower($role_session) . '/dashboard.php', 'Anda tidak memiliki akses.', 'error');
    }

    // PERBAIKAN: Mengambil data pembayaran sewa & denda secara terpisah agar akurat
    $stmt_sewa = $pdo->prepare("SELECT * FROM pembayaran WHERE id_pemesanan = ? AND tipe_pembayaran = 'Sewa'");
    $stmt_sewa->execute([$id_pemesanan]);
    $pembayaran_sewa = $stmt_sewa->fetch();

    $stmt_denda = $pdo->prepare("SELECT * FROM pembayaran WHERE id_pemesanan = ? AND tipe_pembayaran = 'Denda'");
    $stmt_denda->execute([$id_pemesanan]);
    $pembayaran_denda = $stmt_denda->fetch();

} catch (PDOException $e) {
    die("Terjadi kesalahan pada database: " . $e->getMessage());
}

// Logika hitung denda real-time
$denda_realtime = 0; $hari_terlambat = 0;
if ($pemesanan['status_pemesanan'] === 'Berjalan' && (new DateTime() > new DateTime($pemesanan['tanggal_selesai']))) {
    $selisih = (new DateTime($pemesanan['tanggal_selesai']))->diff(new DateTime());
    $hari_terlambat = $selisih->days;
    if ($selisih->h >= 2) { $hari_terlambat++; }
    if ($hari_terlambat > 0) { $denda_realtime = $hari_terlambat * $pemesanan['denda_per_hari']; }
}

$page_title = 'Detail Pemesanan #' . htmlspecialchars($pemesanan['kode_pemesanan']);
require_once '../../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

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
        <?php if ($pemesanan['waktu_pengambilan']): ?><div class="info-item"><span class="label">Waktu Aktual Pengambilan</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['waktu_pengambilan'])) ?></span></div><?php endif; ?>
        <?php if ($pemesanan['waktu_pengembalian']): ?><div class="info-item"><span class="label">Waktu Aktual Pengembalian</span><span class="value"><?= date('d M Y, H:i', strtotime($pemesanan['waktu_pengembalian'])) ?></span></div><?php endif; ?>

        <hr>
        <h3>Informasi Pembayaran Sewa</h3>
        <div class="info-item"><span class="label">Total Biaya Sewa</span><span class="value price"><?= format_rupiah($pemesanan['total_biaya']) ?></span></div>
        <div class="info-item"><span class="label">Status</span><span class="value"><?= htmlspecialchars($pembayaran_sewa['status_pembayaran'] ?? 'Belum Dibayar') ?></span></div>
        <?php if ($pembayaran_sewa && !empty($pembayaran_sewa['bukti_pembayaran'])): ?>
            <div class="info-item bukti-pembayaran-item"><span class="label">Bukti Pembayaran</span><span class="value"><a href="<?= BASE_URL ?>assets/img/bukti_pembayaran/<?= htmlspecialchars($pembayaran_sewa['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti</a></span></div>
        <?php endif; ?>

        <?php if (!empty($pemesanan['review_pelanggan'])): ?>
            <hr>
            <h3>Ulasan Pelanggan</h3>
            <div class="info-item"><span class="label">Rating</span>
                <div class="value star-rating" data-rating="<?= $pemesanan['rating_pengguna'] ?>"></div>
            </div>
            <div class="info-item"><span class="label">Ulasan</span>
                <div class="value description"><?= nl2br(htmlspecialchars($pemesanan['review_pelanggan'])) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-sidebar">
        <h3>Informasi Pelanggan</h3>
        <div class="info-item"><span class="label">Nama</span><span class="value"><?= htmlspecialchars($pemesanan['nama_pelanggan']) ?></span></div>
        <div class="info-item"><span class="label">No. Telepon</span><span class="value"><?= htmlspecialchars($pemesanan['telp_pelanggan']) ?></span></div>
        <hr>
        <h3>Informasi Mobil</h3>
        <div class="info-item-row"><img src="<?= BASE_URL ?>assets/img/mobil/<?= htmlspecialchars($pemesanan['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil" class="info-item-image">
            <div><strong><?= htmlspecialchars($pemesanan['merk'] . ' ' . $pemesanan['model']) ?></strong><br>Plat: <?= htmlspecialchars($pemesanan['plat_nomor']) ?></div>
        </div>

        <?php if ($role_session === 'Pelanggan' && in_array($pemesanan['status_pemesanan'], ['Dikonfirmasi', 'Berjalan'])):
            $qr_title = ($pemesanan['status_pemesanan'] === 'Dikonfirmasi') ? 'Tunjukkan QR Code Saat Pengambilan' : 'Tunjukkan QR Code Saat Pengembalian';
        ?><div class="qr-code-container">
                <h4><?= $qr_title ?></h4>
                <div id="qrcode" data-kode="<?= htmlspecialchars($pemesanan['kode_pemesanan']) ?>"></div>
            </div>
        <?php endif; ?>

        <?php if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran'): ?><div class="timer-container payment-timer">
                <h4>Sisa Waktu Pembayaran</h4>
                <div id="countdown-timer" data-end-time="<?= $pemesanan['batas_pembayaran'] ?>" data-action-on-expire="redirect"></div>
            </div><?php endif; ?>
        <?php if ($pemesanan['status_pemesanan'] === 'Berjalan'): ?><div class="timer-container">
                <h4>Sisa Waktu Sewa</h4>
                <div id="countdown-timer" data-end-time="<?= $pemesanan['tanggal_selesai'] ?>"></div>
            </div><?php endif; ?>

        <?php if ($pemesanan['total_denda'] > 0 || $denda_realtime > 0): ?>
            <div class="cancellation-info" style="border-color: var(--danger-color); margin-top: 20px;">
                <h4>Informasi Pembayaran Denda</h4>
                <div class="info-item"><span class="label">Total Tagihan Denda</span>
                    <div class="value price"><?= format_rupiah($pemesanan['total_denda'] > 0 ? $pemesanan['total_denda'] : $denda_realtime) ?></div>
                </div>
                <div class="info-item"><span class="label">Status</span>
                    <div class="value"><?= htmlspecialchars($pembayaran_denda['status_pembayaran'] ?? 'Belum Dibayar') ?></div>
                </div>
                <?php if ($pembayaran_denda && !empty($pembayaran_denda['bukti_pembayaran'])): ?>
                    <div class="info-item bukti-pembayaran-item"><span class="label">Bukti Bayar Denda</span><span class="value"><a href="<?= BASE_URL ?>assets/img/bukti_pembayaran/<?= htmlspecialchars($pembayaran_denda['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti</a></span></div>
                <?php endif; ?>
                <?php if ($role_session === 'Pelanggan' && $pemesanan['status_pemesanan'] === 'Menunggu Pembayaran Denda' && !$pembayaran_denda): ?>
                    <div class="detail-actions" style="border-top:none; padding-top:15px; margin-top:15px;"><a href="<?= BASE_URL ?>pelanggan/bayar_denda.php?id=<?= $pemesanan['id_pemesanan'] ?>" class="btn btn-danger">Bayar Denda Sekarang</a></div>
                <?php endif; ?>
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

    <?php 
    // --- Logika Tombol untuk Admin & Karyawan ---
    if (in_array($role_session, ['Admin', 'Karyawan'])) {
        
        // Tombol Verifikasi Pembayaran SEWA
        if ($pembayaran_sewa && $pembayaran_sewa['status_pembayaran'] === 'Menunggu Verifikasi') {
            echo '<form action="'.BASE_URL.'actions/pembayaran/verifikasi.php" method="POST" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="'.$pemesanan['id_pemesanan'].'"><input type="hidden" name="id_mobil" value="'.$pemesanan['id_mobil'].'"><button type="submit" class="btn btn-primary">Verifikasi Bayar Sewa</button></form>';
        }

        // Tombol Verifikasi Pembayaran DENDA
        if ($pembayaran_denda && $pembayaran_denda['status_pembayaran'] === 'Menunggu Verifikasi') {
            echo '<form action="'.BASE_URL.'actions/pembayaran/verifikasi_denda.php" method="POST" style="display:inline-block;"><input type="hidden" name="id_pembayaran" value="'.$pembayaran_denda['id_pembayaran'].'"><input type="hidden" name="id_pemesanan" value="'.$pemesanan['id_pemesanan'].'"><input type="hidden" name="id_mobil" value="'.$pemesanan['id_mobil'].'"><button type="submit" class="btn btn-success">Verifikasi Denda</button></form>';
        }
        
        // Tombol Konfirmasi Bayar Denda di Tempat
        if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran Denda' && !$pembayaran_denda) {
            echo '<form action="'.BASE_URL.'actions/pembayaran/konfirmasi_denda.php" method="POST" style="display:inline-block;"><input type="hidden" name="id_pemesanan" value="'.$pemesanan['id_pemesanan'].'"><input type="hidden" name="id_mobil" value="'.$pemesanan['id_mobil'].'"><button type="submit" class="btn btn-success">Konfirmasi Bayar Denda</button></form>';
        }

        // Tombol Batalkan Pesanan
        $cancellable_statuses = ['Menunggu Pembayaran', 'Dikonfirmasi', 'Pengajuan Ambil Cepat', 'Pengajuan Ditolak'];
        if (in_array($pemesanan['status_pemesanan'], $cancellable_statuses)) {
            echo '<form action="'.BASE_URL.'actions/pemesanan/batalkan.php" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Anda yakin ingin membatalkan pesanan ini?\');"><input type="hidden" name="id_pemesanan" value="'.$pemesanan['id_pemesanan'].'"><button type="submit" class="btn btn-danger">Batalkan Pesanan</button></form>';
        }
    } 
    
    // --- Logika Tombol untuk Pelanggan ---
    elseif ($role_session === 'Pelanggan') {
        
        if ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran' && empty($pembayaran_sewa)) {
            echo '<a href="'.BASE_URL.'pelanggan/pembayaran.php?id='.$pemesanan['id_pemesanan'].'" class="btn btn-primary">Bayar Sekarang</a>';
        } 
        elseif ($pemesanan['status_pemesanan'] === 'Menunggu Pembayaran Denda' && empty($pembayaran_denda)) {
            echo '<a href="'.BASE_URL.'pelanggan/bayar_denda.php?id='.$pemesanan['id_pemesanan'].'" class="btn btn-danger">Bayar Denda</a>';
        } 
        elseif ($pemesanan['status_pemesanan'] === 'Dikonfirmasi') {
            echo '<a href="'.BASE_URL.'pelanggan/ajukan_pembatalan.php?id='.$pemesanan['id_pemesanan'].'" class="btn btn-danger">Ajukan Pembatalan</a> ';
            echo '<a href="'.BASE_URL.'pelanggan/ajukan_ambil_cepat.php?id='.$pemesanan['id_pemesanan'].'" class="btn btn-info">Ambil Lebih Cepat</a>';
        } 
        elseif ($pemesanan['status_pemesanan'] === 'Selesai') {
            if (empty($pemesanan['review_pelanggan'])) {
                echo '<a href="'.BASE_URL.'pelanggan/beri_ulasan.php?id='.$pemesanan['id_pemesanan'].'" class="btn btn-primary">Beri Review</a>';
            } else {
                echo '<a href="'.BASE_URL.'pelanggan/beri_ulasan.php?id='.$pemesanan['id_pemesanan'].'" class="btn btn-secondary">Edit Ulasan</a>';
            }
        }
    } 
    ?>
    
    <a href="<?= BASE_URL . strtolower($role_session) ?>/dashboard.php" class="btn btn-secondary">Kembali</a>
</div>

<?php
require_once '../../includes/footer.php';
?>

<script src="<?= BASE_URL ?>assets/js/qr-generator.js"></script>
<script src="<?= BASE_URL ?>assets/js/rental-timer.js"></script>