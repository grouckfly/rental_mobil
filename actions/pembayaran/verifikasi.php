<?php
// File: actions/pembayaran/verifikasi.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses: Hanya Admin dan Karyawan
check_auth(['Admin', 'Karyawan']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL, 'Akses tidak sah.', 'error');
}

$id_pemesanan = isset($_POST['id_pemesanan']) ? (int)$_POST['id_pemesanan'] : 0;
$id_karyawan_verif = $_SESSION['id_pengguna'];

if ($id_pemesanan === 0) {
    redirect_with_message(BASE_URL, 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Gunakan transaksi untuk memastikan kedua query berhasil atau tidak sama sekali
    $pdo->beginTransaction();

    // 1. Update status di tabel 'pembayaran' menjadi 'Diverifikasi'
    $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'Diverifikasi', id_karyawan_verif = ? WHERE id_pemesanan = ?");
    $stmt_pay->execute([$id_karyawan_verif, $id_pemesanan]);

    // 2. Update status di tabel 'pemesanan' menjadi 'Dikonfirmasi'
    $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Dikonfirmasi' WHERE id_pemesanan = ?");
    $stmt_order->execute([$id_pemesanan]);

    // Jika semua berhasil, commit transaksi
    $pdo->commit();

    redirect_with_message("detail.php?id=$id_pemesanan", 'Pembayaran berhasil diverifikasi. Pesanan telah dikonfirmasi.');

} catch (PDOException $e) {
    // Jika ada error, batalkan semua perubahan
    $pdo->rollBack();
    redirect_with_message("detail.php?id=$id_pemesanan", 'Gagal memverifikasi pembayaran: ' . $e->getMessage(), 'error');
}
?>