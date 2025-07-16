<?php
// File: actions/pemesanan/proses_penyelesaian.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    redirect_with_message(BASE_URL . 'dashboard.php', 'Akses tidak valid.', 'error');
 }

$id_pemesanan = (int)$_POST['id_pemesanan'];
$id_mobil = (int)$_POST['id_mobil'];

if ($id_pemesanan === 0 || $id_mobil === 0) { 
    redirect_with_message(BASE_URL . 'dashboard.php', 'ID Pemesanan atau ID Mobil tidak valid.', 'error');
}

try {
    $pdo->beginTransaction();
    // 1. Update status pemesanan menjadi 'Selesai'
    $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Selesai', waktu_pengembalian = NOW() WHERE id_pemesanan = ?");
    $stmt_order->execute([$id_pemesanan]);
    // 2. Update status mobil kembali menjadi 'Tersedia'
    $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
    $stmt_car->execute([$id_mobil]);
    $pdo->commit();

    redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Penyewaan telah berhasil diselesaikan.');
} catch (PDOException $e) {
    $pdo->rollBack();
    redirect_with_message(BASE_URL . "karyawan/konfirmasi_pengembalian.php?id=$id_pemesanan", 'Gagal menyelesaikan: ' . $e->getMessage(), 'error');
}
?>