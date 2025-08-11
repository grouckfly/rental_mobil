<?php
// File: pelanggan/bayar_denda.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Pastikan hanya pelanggan yang bisa mengakses
check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengguna = $_SESSION['id_pengguna'];

if ($id_pemesanan === 0) {
    redirect_with_message('history.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Ambil data pemesanan yang akan dibayar dendanya
    $stmt = $pdo->prepare("
        SELECT p.*, m.merk, m.model 
        FROM pemesanan p 
        JOIN mobil m ON p.id_mobil = m.id_mobil 
        WHERE p.id_pemesanan = ? AND p.id_pengguna = ?
    ");
    $stmt->execute([$id_pemesanan, $id_pengguna]);
    $booking = $stmt->fetch();

    // Validasi: Pastikan statusnya benar-benar menunggu pembayaran denda
    if (!$booking || $booking['status_pemesanan'] !== 'Menunggu Pembayaran Denda') {
        redirect_with_message('history.php', 'Pemesanan ini tidak valid atau tidak memiliki denda yang harus dibayar.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('history.php', 'Terjadi kesalahan pada database.', 'error');
}

// Logika untuk memproses upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['bukti_pembayaran'], '../assets/img/bukti_pembayaran/');

        if (is_array($upload_result) && isset($upload_result['error'])) {
            redirect_with_message("bayar_denda.php?id=$id_pemesanan", $upload_result['error'], 'error');
        } else {
            $nama_file_bukti = $upload_result;
            try {
                // Masukkan data ke tabel pembayaran dengan tipe 'Denda' dan status 'Menunggu Verifikasi'
                $sql_pay = "INSERT INTO pembayaran (id_pemesanan, tipe_pembayaran, tanggal_bayar, jumlah_bayar, metode_pembayaran, bukti_pembayaran, status_pembayaran) 
                            VALUES (?, 'Denda', NOW(), ?, ?, ?, 'Menunggu Verifikasi')";
                $stmt_pay = $pdo->prepare($sql_pay);
                $stmt_pay->execute([$id_pemesanan, $booking['total_denda'], 'Transfer Bank', $nama_file_bukti]);
                
                // Redirect kembali ke halaman detail dengan notifikasi sukses
                redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Terima kasih! Bukti pembayaran denda Anda telah diunggah dan sedang menunggu verifikasi.');

            } catch (PDOException $e) {
                redirect_with_message("bayar_denda.php?id=$id_pemesanan", 'Gagal menyimpan data pembayaran denda.', 'error');
            }
        }
    } else {
        redirect_with_message("bayar_denda.php?id=$id_pemesanan", 'Anda harus memilih file bukti pembayaran.', 'error');
    }
}

$page_title = 'Pembayaran Denda #' . $booking['kode_pemesanan'];
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Pembayaran Denda Keterlambatan</h1>
</div>

<div class="payment-container">
    <div class="payment-details">
        <h3>Detail Tagihan Denda</h3>
        <p>Kode Pemesanan: <strong><?= htmlspecialchars($booking['kode_pemesanan']) ?></strong></p>
        <p>Mobil: <strong><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></strong></p>
        <p>Total Denda yang Harus Dibayar:</p>
        <h2 class="total-amount"><?= format_rupiah($booking['total_denda']) ?></h2>
        <hr>
        <h3>Instruksi Pembayaran</h3>
        <p>Silakan lakukan transfer ke rekening yang sama seperti pembayaran sewa sebelumnya.</p>
        <ul>
            <li><strong>BCA:</strong> 1234567890 a.n. Rental Mobil Keren</li>
        </ul>
        <p>Setelah itu, unggah bukti transfer Anda pada form di samping.</p>
    </div>
    <div class="payment-form">
        <h3>Unggah Bukti Pembayaran Denda</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bukti_pembayaran">Pilih File (JPG, PNG, PDF)</label>
                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required accept="image/*,application/pdf">
            </div>
            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran Denda</button>
            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $id_pemesanan ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>