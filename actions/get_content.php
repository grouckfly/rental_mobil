<?php
// File: actions/get_content.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek otentikasi dasar
check_auth();

$context = $_GET['context'] ?? '';

// Berdasarkan konteks, panggil data dan template yang sesuai
switch ($context) {
    case 'admin_history_table':
        // 1. Ambil data (logika query sama seperti di history.php)
        $role_session = $_SESSION['role'];
        $sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model FROM pemesanan p JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna JOIN mobil m ON p.id_mobil = m.id_mobil WHERE 1=1 ORDER BY p.updated_at DESC";
        $stmt = $pdo->query($sql);
        $histories = $stmt->fetchAll();
        
        // 2. Muat file template tabelnya
        include '../admin/_template_history_table.php';
        break;

    // Tambahkan case lain di sini untuk tabel lain (misal: 'admin_mobil_table')
}
?>