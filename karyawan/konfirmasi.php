<?php
// File: karyawan/konfirmasi.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$kode = isset($_GET['kode']) ? trim($_GET['kode']) : '';

if (empty($kode)) {
    redirect_with_message('dashboard.php', 'Kode pemesanan tidak boleh kosong.', 'error');
}

try {
    // Cari id_pemesanan berdasarkan kode
    $stmt = $pdo->prepare("SELECT id_pemesanan FROM pemesanan WHERE kode_pemesanan = ?");
    $stmt->execute([$kode]);
    $result = $stmt->fetch();

    if ($result) {
        // Jika ditemukan, langsung arahkan ke halaman detail pemesanan
        $id_pemesanan = $result['id_pemesanan'];
        header("Location: " . BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan");
        exit;
    } else {
        // Jika tidak ditemukan
        redirect_with_message('dashboard.php', "Pemesanan dengan kode '$kode' tidak ditemukan.", 'error');
    }

} catch (PDOException $e) {
     redirect_with_message('dashboard.php', 'Error database.', 'error');
}
?>