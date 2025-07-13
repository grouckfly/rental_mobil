<?php
// File: actions/pemesanan/proses_pembatalan.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL, 'Akses tidak sah.', 'error');
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$id_mobil = (int)$_POST['id_mobil'];

if ($id_pemesanan === 0 || $id_mobil === 0) {
    redirect_with_message(BASE_URL, 'Data tidak lengkap.', 'error');
}

try {
    $pdo->beginTransaction();

    // 1. Ubah status pemesanan menjadi 'Dibatalkan'
    $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Dibatalkan' WHERE id_pemesanan = ?");
    $stmt_order->execute([$id_pemesanan]);

    // 2. Kembalikan status mobil menjadi 'Tersedia'
    $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
    $stmt_car->execute([$id_mobil]);

    $pdo->commit();
    redirect_with_message("detail.php?id=$id_pemesanan", 'Pemesanan telah berhasil dibatalkan.');

} catch (PDOException $e) {
    $pdo->rollBack();
    redirect_with_message("detail.php?id=$id_pemesanan", 'Gagal membatalkan pesanan: ' . $e->getMessage(), 'error');
}
?>