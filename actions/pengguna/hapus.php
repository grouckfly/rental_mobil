<?php
// File: actions/pengguna/hapus.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses: HANYA ADMIN
check_auth('Admin');

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../admin/user.php', 'Akses tidak sah.', 'error');
}

// Validasi ID Pengguna
$id_pengguna = isset($_POST['id_pengguna']) ? (int)$_POST['id_pengguna'] : 0;
if ($id_pengguna === 0) {
    redirect_with_message('../../admin/user.php', 'ID pengguna tidak valid.', 'error');
}

// Mencegah admin menghapus akunnya sendiri
if ($id_pengguna === $_SESSION['id_pengguna']) {
    redirect_with_message('../../admin/user.php', 'Anda tidak bisa menghapus akun Anda sendiri.', 'error');
}

try {
    // Hapus pengguna dari database
    $stmt = $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_pengguna]);

    redirect_with_message('../../admin/user.php', 'Pengguna berhasil dihapus.');

} catch (PDOException $e) {
    // Tangani error jika pengguna terikat dengan data lain (misal: pemesanan)
    if ($e->getCode() == '23000') {
        redirect_with_message('../../admin/user.php', 'Gagal menghapus! Pengguna ini masih terikat dengan data pemesanan.', 'error');
    } else {
        redirect_with_message('../../admin/user.php', 'Gagal menghapus pengguna: ' . $e->getMessage(), 'error');
    }
}
?>