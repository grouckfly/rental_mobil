<?php
// File: pelanggan/ajukan_pembatalan.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan === 0) {
    redirect_with_message('pemesanan.php', 'ID Pemesanan tidak valid.', 'error');
}

// Ambil data pemesanan untuk memastikan valid
try {
    $stmt = $pdo->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ? AND id_pengguna = ?");
    $stmt->execute([$id_pemesanan, $_SESSION['id_pengguna']]);
    $pemesanan = $stmt->fetch();

    // Pelanggan hanya boleh mengajukan pembatalan jika statusnya 'Dikonfirmasi'
    if (!$pemesanan || $pemesanan['status_pemesanan'] !== 'Dikonfirmasi') {
        redirect_with_message('pemesanan.php', 'Pemesanan ini tidak dapat dibatalkan.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('pemesanan.php', 'Error database.', 'error');
}

$page_title = 'Ajukan Pembatalan Pesanan #' . $id_pemesanan;
require_once '../includes/header.php';
?>

<div class="page-header"><h1>Formulir Pengajuan Pembatalan</h1></div>

<div class="form-container">
    <div class="form-box">
        <p>Anda akan mengajukan pembatalan untuk pesanan <strong>#<?= $id_pemesanan ?></strong>. Dana akan dikembalikan sesuai syarat dan ketentuan yang berlaku.</p>
        
        <form action="<?= BASE_URL ?>actions/pemesanan/ajukan_pembatalan.php" method="POST">
            <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
            
            <div class="form-group">
                <label for="alasan_pembatalan">Alasan Pembatalan</label>
                <textarea id="alasan_pembatalan" name="alasan_pembatalan" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="rekening_pembatalan">Nomor Rekening untuk Refund</label>
                <input type="text" id="rekening_pembatalan" name="rekening_pembatalan" placeholder="Contoh: BCA 1234567890 a.n. Budi" required>
            </div>
            
            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin mengajukan pembatalan untuk pesanan ini?');">Kirim Pengajuan</button>
            <a href="<?= BASE_URL ?>pelanggan/pemesanan.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>