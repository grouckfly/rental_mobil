<?php
// File: actions/pembayaran/verifikasi.php (Versi Perbaikan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL, 'Akses tidak sah.', 'error');
}

$id_pemesanan = isset($_POST['id_pemesanan']) ? (int)$_POST['id_pemesanan'] : 0;
// Ambil id_mobil dari form yang sudah kita tambahkan
$id_mobil = isset($_POST['id_mobil']) ? (int)$_POST['id_mobil'] : 0; 
$id_karyawan_verif = $_SESSION['id_pengguna'];

if ($id_pemesanan === 0 || $id_mobil === 0) {
    redirect_with_message(BASE_URL, 'Data tidak lengkap.', 'error');
}

try {
    // Gunakan transaksi untuk memastikan semua query berhasil
    $pdo->beginTransaction();

    // 1. Update status di tabel 'pembayaran' menjadi 'Diverifikasi'
    $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'Diverifikasi', id_karyawan_verif = ? WHERE id_pemesanan = ?");
    $stmt_pay->execute([$id_karyawan_verif, $id_pemesanan]);

    // 2. Update status di tabel 'pemesanan' menjadi 'Dikonfirmasi'
    $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Dikonfirmasi' WHERE id_pemesanan = ?");
    $stmt_order->execute([$id_pemesanan]);

    // 3. PERBAIKAN: Update status di tabel 'mobil' menjadi 'Disewa'
    $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Disewa' WHERE id_mobil = ?");
    $stmt_car->execute([$id_mobil]);

    // Jika semua berhasil, commit transaksi
    $pdo->commit();

    redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Pembayaran berhasil diverifikasi. Status mobil telah diubah menjadi Disewa.');

} catch (PDOException $e) {
    // Jika ada error, batalkan semua perubahan
    $pdo->rollBack();
    redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Gagal memverifikasi pembayaran: ' . $e->getMessage(), 'error');
}
?>