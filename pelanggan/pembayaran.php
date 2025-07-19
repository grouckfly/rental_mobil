<?php
// File: pelanggan/pembayaran.php (Terbaru)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Pastikan hanya pelanggan yang bisa mengakses halaman ini
check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengguna = $_SESSION['id_pengguna'];

if ($id_pemesanan === 0) {
    redirect_with_message('pemesanan.php', 'ID Pemesanan tidak valid.', 'error');
}

// Ambil data pemesanan untuk ditampilkan dan diproses
try {
    $stmt = $pdo->prepare("SELECT p.*, m.merk, m.model FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pemesanan = ? AND p.id_pengguna = ?");
    $stmt->execute([$id_pemesanan, $id_pengguna]);
    $booking = $stmt->fetch();

    // Validasi: Arahkan pergi jika pesanan tidak ditemukan atau sudah dibayar/diproses
    if (!$booking || $booking['status_pemesanan'] !== 'Menunggu Pembayaran') {
        redirect_with_message('pemesanan.php', 'Pemesanan ini tidak ditemukan atau sudah dalam proses pembayaran.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('pemesanan.php', 'Terjadi kesalahan pada database.', 'error');
}

// Logika untuk memproses upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['bukti_pembayaran'], '../assets/img/bukti_pembayaran/');

        if (is_array($upload_result) && isset($upload_result['error'])) {
            redirect_with_message("pembayaran.php?id=$id_pemesanan", $upload_result['error'], 'error');
        } else {
            $nama_file_bukti = $upload_result;
            try {
                // Masukkan data ke tabel pembayaran dengan status 'Menunggu Verifikasi'
                $sql_pay = "INSERT INTO pembayaran (id_pemesanan, tanggal_bayar, jumlah_bayar, metode_pembayaran, bukti_pembayaran, status_pembayaran) 
                            VALUES (?, NOW(), ?, ?, ?, 'Menunggu Verifikasi')";
                $stmt_pay = $pdo->prepare($sql_pay);
                $stmt_pay->execute([$id_pemesanan, $booking['total_biaya'], 'Transfer Bank', $nama_file_bukti]);
                
                redirect_with_message(BASE_URL . "pelanggan/pemesanan.php", 'Terima kasih! Bukti pembayaran Anda telah diunggah dan sedang menunggu verifikasi.');

            } catch (PDOException $e) {
                // Jika sudah ada pembayaran untuk ID pesanan ini, beri pesan error
                if ($e->getCode() == '23000') {
                     redirect_with_message("pembayaran.php?id=$id_pemesanan", 'Anda sudah pernah mengunggah bukti untuk pesanan ini.', 'error');
                }
                redirect_with_message("pembayaran.php?id=$id_pemesanan", 'Gagal menyimpan data pembayaran.', 'error');
            }
        }
    } else {
        redirect_with_message("pembayaran.php?id=$id_pemesanan", 'Anda harus memilih file bukti pembayaran.', 'error');
    }
}

$page_title = 'Pembayaran Pesanan #' . $booking['kode_pemesanan'];
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Pembayaran Pesanan</h1>
</div>

<?php display_flash_message(); ?>

<div class="payment-container">
    <div class="payment-details">
        <h3>Detail Tagihan</h3>
        <p>Kode Pemesanan: <strong><?= htmlspecialchars($booking['kode_pemesanan']) ?></strong></p>
        <p>Mobil: <strong><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></strong></p>
        <p>Total Pembayaran:</p>
        <h2 class="total-amount"><?= format_rupiah($booking['total_biaya']) ?></h2>
        <hr>
        <h3>Instruksi Pembayaran</h3>
        <p>Silakan lakukan transfer ke salah satu rekening berikut:</p>
        <ul>
            <li><strong>Bank BCA:</strong> 1234-5678-90 a.n. PT Rental Mobil</li>
            <li><strong>Bank Mandiri:</strong> 098-765-4321 a.n. PT Rental Mobil</li>
        </ul>
        <p>Setelah melakukan pembayaran, mohon unggah bukti transfer Anda pada form di samping dalam waktu 1x24 jam.</p>
    </div>
    <div class="payment-form">
        <h3>Unggah Bukti Pembayaran</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bukti_pembayaran">Pilih File (JPG, PNG, PDF)</label>
                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required accept="image/png, image/jpeg, application/pdf">
            </div>
            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
            <a href="pemesanan.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>