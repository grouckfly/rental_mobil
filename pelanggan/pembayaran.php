<?php
// File: pelanggan/pembayaran.php (Versi Perbaikan Final)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Pastikan hanya pelanggan yang bisa mengakses
check_auth('Pelanggan');

// Ambil ID pemesanan dari URL dan ID pengguna dari session
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengguna = $_SESSION['id_pengguna'];

if ($id_pemesanan === 0) {
    // Jika tidak ada ID sama sekali di URL, baru tampilkan error
    redirect_with_message('pemesanan.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Ambil data pemesanan untuk ditampilkan
    $stmt = $pdo->prepare("
        SELECT p.*, m.merk, m.model 
        FROM pemesanan p 
        JOIN mobil m ON p.id_mobil = m.id_mobil 
        WHERE p.id_pemesanan = ? AND p.id_pengguna = ?
    ");
    $stmt->execute([$id_pemesanan, $id_pengguna]);
    $booking = $stmt->fetch();

    // ==========================================================
    // PERBAIKAN LOGIKA VALIDASI
    // ==========================================================
    // Cek apakah pesanan ditemukan. Jika tidak, baru redirect.
    if (!$booking) {
        redirect_with_message('pemesanan.php', 'Pemesanan tidak ditemukan atau bukan milik Anda.', 'error');
    }
    // Cek apakah pesanan ini memang sedang menunggu pembayaran.
    // Jika statusnya sudah lain (misal: sudah dibayar), redirect.
    if ($booking['status_pemesanan'] !== 'Menunggu Pembayaran') {
        redirect_with_message('pemesanan.php', 'Pesanan ini sudah tidak dapat dibayar lagi.', 'error');
    }

} catch (PDOException $e) {
    redirect_with_message('pemesanan.php', 'Terjadi kesalahan pada database.', 'error');
}

// Logika untuk memproses upload bukti pembayaran (tetap sama)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['bukti_pembayaran'], '../assets/img/bukti_pembayaran/');
        if (is_array($upload_result)) {
            redirect_with_message("pembayaran.php?id=$id_pemesanan", $upload_result['error'], 'error');
        } else {
            $nama_file_bukti = $upload_result;
            try {
                $sql_pay = "INSERT INTO pembayaran (id_pemesanan, tanggal_bayar, jumlah_bayar, metode_pembayaran, bukti_pembayaran, status_pembayaran) 
                            VALUES (?, NOW(), ?, ?, ?, 'Menunggu Verifikasi')";
                $stmt_pay = $pdo->prepare($sql_pay);
                $stmt_pay->execute([$id_pemesanan, $booking['total_biaya'], 'Transfer Bank', $nama_file_bukti]);
                redirect_with_message(BASE_URL . "pelanggan/pemesanan.php", 'Terima kasih! Bukti pembayaran Anda telah diunggah.');
            } catch (PDOException $e) { 
                // Error Handling
             }
        }
    } else { 
        // Error Handling
     }
}

$page_title = 'Pembayaran Pesanan #' . $booking['kode_pemesanan'];
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Pembayaran Pesanan</h1>
</div>

<div class="payment-container">
    <div class="payment-details">
        <h3>Detail Tagihan</h3>
        <p>Kode Pemesanan: <strong><?= htmlspecialchars($booking['kode_pemesanan']) ?></strong></p>
        <p>Mobil: <strong><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></strong></p>
        <p>Total Pembayaran:</p>
        <h2 class="total-amount"><?= format_rupiah($booking['total_biaya']) ?></h2>
        <hr>
        <div class="timer-container payment-timer">
            <h4>Sisa Waktu Pembayaran</h4>
            <div id="countdown-timer" data-end-time="<?= $booking['batas_pembayaran'] ?>" data-action-on-expire="redirect"></div>
        </div>
        <hr>
        <h3>Instruksi Pembayaran</h3>
        <p>Silakan lakukan transfer ke rekening berikut:</p>
        <ul><li><strong>BCA:</strong> 1234567890 a.n. Rental Mobil Keren</li></ul>
    </div>
    <div class="payment-form">
        <h3>Unggah Bukti Pembayaran</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bukti_pembayaran">Pilih File (JPG, PNG, PDF)</label>
                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required accept="image/*,application/pdf">
            </div>
            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
            <a href="pemesanan.php" class="btn btn-secondary">Nanti Saja</a>
        </form>
    </div>
</div>

<?php 
require_once '../includes/footer.php';
echo '<script src="'.BASE_URL.'assets/js/rental-timer.js"></script>';
?>