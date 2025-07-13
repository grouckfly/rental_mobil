<?php
// File: admin/hapus_mobil.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Admin yang bisa mengakses
check_auth(['Admin']);

// 1. Pastikan request adalah POST untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('mobil.php', 'Akses tidak sah.', 'error');
}

// 2. Validasi ID Mobil
$id_mobil = isset($_POST['id_mobil']) ? (int)$_POST['id_mobil'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('mobil.php', 'ID Mobil tidak valid.', 'error');
}

try {
    // 3. Ambil nama file gambar sebelum menghapus record dari database
    $stmt_select = $pdo->prepare("SELECT gambar_mobil FROM mobil WHERE id_mobil = ?");
    $stmt_select->execute([$id_mobil]);
    $mobil = $stmt_select->fetch();

    if (!$mobil) {
        redirect_with_message('mobil.php', 'Mobil tidak ditemukan.', 'error');
    }

    $nama_file_gambar = $mobil['gambar_mobil'];

    // 4. Hapus record mobil dari database
    $stmt_delete = $pdo->prepare("DELETE FROM mobil WHERE id_mobil = ?");
    $stmt_delete->execute([$id_mobil]);

    // 5. Hapus file gambar dari server jika ada
    if ($nama_file_gambar && file_exists('../assets/img/mobil/' . $nama_file_gambar)) {
        unlink('../assets/img/mobil/' . $nama_file_gambar);
    }

    // 6. Redirect dengan pesan sukses
    redirect_with_message('mobil.php', 'Mobil berhasil dihapus.');

} catch (PDOException $e) {
    // Tangani error jika mobil tidak bisa dihapus (misal: karena terkait dengan pemesanan)
    if ($e->getCode() == '23000') {
         redirect_with_message('mobil.php', 'Gagal menghapus mobil karena masih terkait dengan data pemesanan.', 'error');
    } else {
         redirect_with_message('mobil.php', 'Terjadi kesalahan pada database: ' . $e->getMessage(), 'error');
    }
}
?>