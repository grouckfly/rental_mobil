<?php
// File: pelanggan/ajukan_ambil_cepat.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengguna = $_SESSION['id_pengguna'];

// Jika ID tidak valid, kembalikan ke halaman sebelumnya
if ($id_pemesanan === 0) {
    redirect_with_message('pemesanan.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Siapkan query untuk mengambil data pemesanan DAN harga sewa harian dari tabel mobil
    $sql = "SELECT p.*, m.harga_sewa_harian 
            FROM pemesanan p 
            JOIN mobil m ON p.id_mobil = m.id_mobil 
            WHERE p.id_pemesanan = ? AND p.id_pengguna = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pemesanan, $id_pengguna]);
    $pemesanan = $stmt->fetch();

    // Pastikan pesanan ditemukan dan statusnya 'Dikonfirmasi'
    // Jika tidak, pelanggan tidak boleh mengajukan perubahan
    if (!$pemesanan || $pemesanan['status_pemesanan'] !== 'Dikonfirmasi') {
        redirect_with_message('pemesanan.php', 'Pemesanan ini tidak ditemukan atau tidak dapat diubah saat ini.', 'error');
    }

} catch (PDOException $e) {
    // Tangani jika ada error saat koneksi atau query ke database
    redirect_with_message('pemesanan.php', 'Terjadi kesalahan pada database.', 'error');
}


$page_title = 'Ajukan Pengambilan Lebih Cepat';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Ajukan Pengambilan Lebih Cepat</h1>
</div>
<?php display_flash_message(); ?>

<div class="form-container">
    <div class="form-box">
        <p>Jadwal Awal: <strong><?= date('d M Y, H:i', strtotime($pemesanan['tanggal_mulai'])) ?></strong></p>
        <p>Total Biaya Awal: <strong><?= format_rupiah($pemesanan['total_biaya']) ?></strong></p>
        <hr>
        <form action="<?= BASE_URL ?>actions/pemesanan/ajukan_ambil_cepat.php" method="POST">
            <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
            <input type="hidden" id="harga_harian" value="<?= $pemesanan['harga_sewa_harian'] ?>">
            <input type="hidden" id="tgl_selesai" value="<?= $pemesanan['tanggal_selesai'] ?>">

            <div class="form-group">
                <label for="tgl_mulai_baru">Pilih Waktu Pengambilan Baru</label>
                <input type="datetime-local" id="tgl_mulai_baru" name="tgl_mulai_baru" required>
            </div>

            <div id="kalkulasi-biaya" style="display:none;">
                <p>Estimasi Biaya Baru: <strong id="biaya-baru" class="price"></strong></p>
                <small>Biaya tambahan akan ditagihkan saat pengambilan mobil.</small>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $id_pemesanan ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script src="<?= BASE_URL ?>assets/js/early-pickup-calculator.js"></script>