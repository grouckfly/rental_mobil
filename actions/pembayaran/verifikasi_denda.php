<?php
// File: actions/pembayaran/verifikasi_denda.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Akses tidak sah'); }

$id_pembayaran = (int)$_POST['id_pembayaran'];
$id_pemesanan = (int)$_POST['id_pemesanan'];
$id_mobil = (int)$_POST['id_mobil'];
$id_karyawan_verif = $_SESSION['id_pengguna'];

if ($id_pembayaran === 0 || $id_pemesanan === 0 || $id_mobil === 0) {
    redirect_with_message(BASE_URL, 'Data tidak lengkap.', 'error');
}

try {
    $pdo->beginTransaction();
    
    // 1. Verifikasi pembayaran denda
    $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'Diverifikasi', id_karyawan_verif = ? WHERE id_pembayaran = ?");
    $stmt_pay->execute([$id_karyawan_verif, $id_pembayaran]);

    // 2. Selesaikan sewa & kembalikan mobil
    $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Selesai', waktu_pengembalian = NOW() WHERE id_pemesanan = ?");
    $stmt_order->execute([$id_pemesanan]);
    $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
    $stmt_car->execute([$id_mobil]);
    
    $pdo->commit();
    redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Pembayaran denda diverifikasi dan penyewaan telah diselesaikan.');

} catch (PDOException $e) {
    $pdo->rollBack();
    redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Gagal memverifikasi: ' . $e->getMessage(), 'error');
}
?>