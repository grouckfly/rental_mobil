<?php
// File: pelanggan/ajukan_ambil_cepat.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

// Mengambil data pemesanan yang akan diubah
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan === 0) {
    redirect_with_message('pemesanan.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ? AND id_pengguna = ?");
    $stmt->execute([$id_pemesanan, $_SESSION['id_pengguna']]);
    $pemesanan = $stmt->fetch();
    if (!$pemesanan || $pemesanan['status_pemesanan'] !== 'Dikonfirmasi') {
        redirect_with_message('pemesanan.php', 'Pemesanan ini tidak dapat diubah.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('pemesanan.php', 'Terjadi kesalahan pada database.', 'error');
}

// Menangkap pesan error dari URL (jika ada)
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

$page_title = 'Ajukan Pengambilan Lebih Cepat';
require_once '../includes/header.php';
?>

<div class="page-header"><h1>Ajukan Pengambilan Lebih Cepat</h1></div>

<div class="form-container">
    <div class="form-box">
        <?php if (!empty($error_message)): ?>
            <div class="flash-message flash-error"><?= $error_message ?></div>
        <?php endif; ?>

        <p>Jadwal Pengambilan Awal Anda:</p>
        <h3 style="text-align:center; margin-bottom: 20px;"><?= date('d F Y', strtotime($pemesanan['tanggal_mulai'])) ?></h3>
        <p>Pilih tanggal baru untuk pengambilan.</p>
        <hr>
        
        <form action="<?= BASE_URL ?>actions/pemesanan/ajukan_ambil_cepat.php" method="POST">
            <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
            
            <div class="form-group">
                <label for="tgl_mulai_baru">Pilih Tanggal Pengambilan Baru</label>
                <input type="date" id="tgl_mulai_baru" name="tgl_mulai_baru" required>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $id_pemesanan ?>" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>