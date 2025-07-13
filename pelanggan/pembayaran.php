<?php
// File: pelanggan/pembayaran.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengguna = $_SESSION['id_pengguna'];

// Ambil data pemesanan, pastikan pemesanan ini milik user yang login
try {
    $stmt = $pdo->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ? AND id_pengguna = ? AND status_pemesanan = 'Menunggu Pembayaran'");
    $stmt->execute([$id_pemesanan, $id_pengguna]);
    $booking = $stmt->fetch();
} catch (PDOException $e) {
    $booking = null;
}

if (!$booking) {
    redirect_with_message('pemesanan.php', 'Pemesanan tidak ditemukan atau sudah dibayar.', 'error');
}

// Proses form upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['bukti_pembayaran'], '../uploads/bukti_pembayaran/');

        if (is_array($upload_result) && isset($upload_result['error'])) {
            redirect_with_message("pembayaran.php?id=$id_pemesanan", $upload_result['error'], 'error');
        } else {
            $nama_file_bukti = $upload_result;
            // Simpan data pembayaran dan update status pemesanan
            try {
                $pdo->beginTransaction();

                // 1. Insert ke tabel pembayaran
                $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (id_pemesanan, tanggal_bayar, jumlah_bayar, metode_pembayaran, bukti_pembayaran, status_pembayaran) VALUES (?, NOW(), ?, ?, ?, 'Lunas')");
                $stmt_pay->execute([$id_pemesanan, $booking['total_biaya'], 'Transfer Bank', $nama_file_bukti]);

                // 2. Update status di tabel pemesanan menjadi 'Dikonfirmasi' (menunggu verifikasi admin)
                $stmt_update = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Dikonfirmasi' WHERE id_pemesanan = ?");
                $stmt_update->execute([$id_pemesanan]);

                $pdo->commit();
                redirect_with_message('pemesanan.php', 'Terima kasih! Bukti pembayaran Anda telah diunggah dan sedang menunggu verifikasi.');

            } catch (PDOException $e) {
                $pdo->rollBack();
                redirect_with_message("pembayaran.php?id=$id_pemesanan", 'Terjadi kesalahan saat menyimpan data pembayaran.', 'error');
            }
        }
    } else {
        redirect_with_message("pembayaran.php?id=$id_pemesanan", 'Anda harus memilih file bukti pembayaran.', 'error');
    }
}

$page_title = 'Pembayaran Pesanan #' . $id_pemesanan;
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Pembayaran Pesanan #<?= htmlspecialchars($id_pemesanan) ?></h1>
</div>

<div class="payment-container">
    <div class="payment-details">
        <h3>Detail Tagihan</h3>
        <p><strong>Total Pembayaran:</strong></p>
        <h2 class="total-amount"><?= format_rupiah($booking['total_biaya']) ?></h2>
        <hr>
        <h3>Instruksi Pembayaran</h3>
        <p>Silakan lakukan transfer ke salah satu rekening berikut:</p>
        <ul>
            <li><strong>Bank BCA:</strong> 1234-5678-90 a.n. PT Rental Mobil Keren</li>
            <li><strong>Bank Mandiri:</strong> 098-765-4321 a.n. PT Rental Mobil Keren</li>
        </ul>
        <p>Setelah melakukan pembayaran, mohon unggah bukti transfer Anda pada form di samping.</p>
    </div>
    <div class="payment-form">
        <h3>Unggah Bukti Pembayaran</h3>
        <form action="pembayaran.php?id=<?= $id_pemesanan ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bukti_pembayaran">Pilih File (JPG, PNG, PDF)</label>
                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required>
            </div>
            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
        </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>