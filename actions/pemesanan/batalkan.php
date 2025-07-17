<?php
// File: actions/pemesanan/batalkan.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk membatalkan: Admin atau Karyawan
check_auth(['Admin', 'Karyawan']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL, 'Akses tidak sah.', 'error');
}

$id_pemesanan = isset($_POST['id_pemesanan']) ? (int)$_POST['id_pemesanan'] : 0;
if ($id_pemesanan === 0) {
    redirect_with_message(BASE_URL . 'admin/pembayaran.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Mulai transaksi
    $pdo->beginTransaction();

    // 1. Ambil ID mobil dari pemesanan yang akan dibatalkan
    $stmt_get = $pdo->prepare("SELECT id_mobil FROM pemesanan WHERE id_pemesanan = ?");
    $stmt_get->execute([$id_pemesanan]);
    $pemesanan = $stmt_get->fetch();

    if ($pemesanan) {
        // 2. Ubah status pemesanan menjadi 'Dibatalkan'
        $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Dibatalkan' WHERE id_pemesanan = ?");
        $stmt_order->execute([$id_pemesanan]);

        // 3. Kembalikan status mobil menjadi 'Tersedia'
        $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
        $stmt_car->execute([$pemesanan['id_mobil']]);
    }

    // Selesaikan transaksi
    $pdo->commit();

    redirect_with_message(BASE_URL . 'admin/pembayaran.php', 'Pemesanan telah berhasil dibatalkan.');

} catch (PDOException $e) {
    // Jika ada error, batalkan semua perubahan
    $pdo->rollBack();
    redirect_with_message(BASE_URL . 'admin/pembayaran.php', 'Gagal membatalkan pesanan: ' . $e->getMessage(), 'error');
}
?>