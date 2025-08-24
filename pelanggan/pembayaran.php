<?php
// File: pelanggan/pembayaran.php (Versi Perbaikan Final)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Pastikan hanya pelanggan yang bisa mengakses
check_auth('Pelanggan');

// Ambil ID pemesanan dari URL dan ID pengguna dari session
$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pemesanan === 0 && isset($_SESSION['id_pemesanan_untuk_bayar'])) {
    $id_pemesanan = $_SESSION['id_pemesanan_untuk_bayar'];
    // Hapus session setelah digunakan agar tidak dipakai lagi
    unset($_SESSION['id_pemesanan_untuk_bayar']);
}

$id_pengguna = $_SESSION['id_pengguna'];

if ($id_pemesanan === 0) {
    // Jika tidak ada ID sama sekali di URL, baru tampilkan error
    redirect_with_message('pemesanan.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Ambil data pemesanan untuk ditampilkan
    $stmt = $pdo->prepare("
        SELECT p.*, m.merk, m.model, m.harga_sewa_harian
        FROM pemesanan p 
        JOIN mobil m ON p.id_mobil = m.id_mobil 
        WHERE p.id_pemesanan = ? AND p.id_pengguna = ?
    ");
    $stmt->execute([$id_pemesanan, $_SESSION['id_pengguna']]);
    $booking = $stmt->fetch();

    if (!$booking || $booking['status_pemesanan'] !== 'Menunggu Pembayaran') {
        redirect_with_message('pemesanan.php', 'Pemesanan ini tidak valid atau sudah diproses.', 'error');
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
                // Gunakan transaksi untuk memastikan kedua query berhasil
                $pdo->beginTransaction();

                // 1. Masukkan data ke tabel pembayaran
                $sql_pay = "INSERT INTO pembayaran (id_pemesanan, tipe_pembayaran, tanggal_bayar, jumlah_bayar, metode_pembayaran, bukti_pembayaran, status_pembayaran) 
                            VALUES (?, 'Sewa', NOW(), ?, ?, ?, 'Menunggu Verifikasi')";
                $stmt_pay = $pdo->prepare($sql_pay);
                $stmt_pay->execute([$id_pemesanan, $booking['total_biaya'], 'Transfer Bank', $nama_file_bukti]);

                // 2. PERBAIKAN: Update status di tabel pemesanan
                $sql_order = "UPDATE pemesanan SET status_pemesanan = 'Menunggu Verifikasi' WHERE id_pemesanan = ?";
                $stmt_order = $pdo->prepare($sql_order);
                $stmt_order->execute([$id_pemesanan]);

                // Selesaikan transaksi
                $pdo->commit();

                redirect_with_message(BASE_URL . "pelanggan/pemesanan.php", 'Terima kasih! Bukti pembayaran Anda telah diunggah dan sedang menunggu verifikasi.');
            } catch (PDOException $e) {
                $pdo->rollBack(); // Batalkan semua jika ada error
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

// Hitung durasi untuk ditampilkan
$durasi_sewa = hitung_durasi_sewa($booking['tanggal_mulai'], $booking['tanggal_selesai']);
?>

<div class="page-header">
    <h1>Pembayaran Pesanan</h1>
</div>

<div class="payment-container">
    <div class="payment-details">
        <h3>Detail Tagihan</h3>
        <div class="info-item">
            <span class="label">Kode Pemesanan</span>
            <strong class="value"><?= htmlspecialchars($booking['kode_pemesanan']) ?></strong>
        </div>
        <div class="info-item">
            <span class="label">Mobil</span>
            <strong class="value"><?= htmlspecialchars($booking['merk'] . ' ' . $booking['model']) ?></strong>
        </div>
        <hr>

        <h4>Rincian Biaya</h4>
        <div class="info-item">
            <span class="label">Durasi Sewa</span>
            <span class="value"><?= $durasi_sewa ?> hari</span>
        </div>
        <div class="info-item">
            <span class="label">Harga per Hari</span>
            <span class="value"><?= format_rupiah($booking['harga_sewa_harian']) ?></span>
        </div>
        <div class="info-item">
            <span class="label">Subtotal</span>
            <span class="value"><?= format_rupiah($booking['total_biaya']) ?></span>
        </div>
        <hr>

        <p>Total Pembayaran:</p>
        <h2 class="total-amount"><?= format_rupiah($booking['total_biaya']) ?></h2>
    </div>
    <div class="payment-form">
        <h3>Instruksi Pembayaran</h3>
        <p>Silakan lakukan transfer ke rekening berikut:</p>
        <ul>
            <li><strong>BCA:</strong> 1234567890 a.n. Rental Mobil Keren</li>
        </ul>
        <br>
        <hr>
        <h3>Unggah Bukti Pembayaran</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="form-group">
                <label for="bukti_pembayaran">Pilih File (JPG, PNG, PDF)</label>
                <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required accept="image/*,application/pdf">
            </div>
            <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
            <a href="pemesanan.php" class="btn btn-secondary">Nanti Saja</a>
        </form>
        <br>
        <hr>
        <div class="timer-container payment-timer">
            <h4>Sisa Waktu Pembayaran</h4>
            <div id="countdown-timer" data-end-time="<?= $booking['batas_pembayaran'] ?>" data-action-on-expire="redirect"></div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
echo '<script src="' . BASE_URL . 'assets/js/rental-timer.js"></script>';
?>