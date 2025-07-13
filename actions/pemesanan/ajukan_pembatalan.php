<?php
// File: actions/pemesanan/ajukan_pembatalan.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL . 'pelanggan/dashboard.php', 'Akses tidak sah.', 'error');
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$alasan = trim($_POST['alasan_pembatalan']);
$rekening = trim($_POST['rekening_pembatalan']);

if (empty($alasan) || empty($rekening)) {
    redirect_with_message(BASE_URL . "pelanggan/ajukan_pembatalan.php?id=$id_pemesanan", 'Semua field wajib diisi.', 'error');
}

try {
    $sql = "UPDATE pemesanan SET 
                status_pemesanan = 'Pengajuan Pembatalan', 
                alasan_pembatalan = ?, 
                rekening_pembatalan = ? 
            WHERE id_pemesanan = ? AND id_pengguna = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$alasan, $rekening, $id_pemesanan, $_SESSION['id_pengguna']]);

    redirect_with_message(BASE_URL . 'pelanggan/pemesanan.php', 'Pengajuan pembatalan Anda telah berhasil dikirim.');

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . "pelanggan/ajukan_pembatalan.php?id=$id_pemesanan", 'Gagal mengirim pengajuan: ' . $e->getMessage(), 'error');
}
?>