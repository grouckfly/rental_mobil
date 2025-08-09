<?php
// File: actions/pesan/hapus.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Pastikan pengguna sudah login
check_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL . 'actions/pesan/inbox.php', 'Akses tidak sah.', 'error');
}

$id_pesan_utama = isset($_POST['id_pesan']) ? (int)$_POST['id_pesan'] : 0;
$id_pengguna_session = $_SESSION['id_pengguna'];
$role_session = $_SESSION['role'];

if ($id_pesan_utama === 0) {
    redirect_with_message(BASE_URL . 'actions/pesan/inbox.php', 'ID Pesan tidak valid.', 'error');
}

try {
    // Siapkan query dasar untuk menghapus utas percakapan
    $sql = "DELETE FROM pesan_bantuan WHERE id_pesan = :id_pesan OR parent_id = :id_pesan";
    $params = [':id_pesan' => $id_pesan_utama];

    // ==========================================================
    // LAPISAN KEAMANAN: Pastikan pelanggan hanya bisa menghapus pesannya sendiri
    // ==========================================================
    if ($role_session === 'Pelanggan') {
        $sql .= " AND id_pengirim = :id_pengirim";
        $params[':id_pengirim'] = $id_pengguna_session;
    }
    // Admin dan Karyawan bisa menghapus percakapan apa pun, jadi tidak perlu filter tambahan

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Cek apakah ada baris yang terhapus
    if ($stmt->rowCount() > 0) {
        redirect_with_message(BASE_URL . 'actions/pesan/inbox.php', 'Percakapan telah berhasil dihapus.');
    } else {
        // Ini terjadi jika pelanggan mencoba menghapus pesan orang lain
        redirect_with_message(BASE_URL . 'actions/pesan/inbox.php', 'Anda tidak memiliki izin untuk menghapus percakapan ini.', 'error');
    }

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'actions/pesan/inbox.php', 'Gagal menghapus percakapan: ' . $e->getMessage(), 'error');
}
?>