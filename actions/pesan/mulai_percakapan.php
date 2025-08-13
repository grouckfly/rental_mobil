<?php
// File: actions/pesan/mulai_percakapan.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$id_penerima = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengirim = $_SESSION['id_pengguna'];

if ($id_penerima === 0) {
    redirect_with_message(BASE_URL . 'admin/user.php', 'ID pengguna tidak valid.', 'error');
}
if ($id_penerima === $id_pengirim) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Anda tidak dapat mengirim pesan ke diri sendiri.', 'error');
}

try {
    // 1. Cek apakah sudah ada percakapan antara kedua pengguna ini
    $sql_check = "SELECT id_pesan FROM pesan_bantuan 
                  WHERE parent_id IS NULL 
                  AND ((id_pengirim = ? AND id_penerima = ?) OR (id_pengirim = ? AND id_penerima = ?))";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_pengirim, $id_penerima, $id_penerima, $id_pengirim]);
    $existing_thread = $stmt_check->fetch();

    if ($existing_thread) {
        // JIKA SUDAH ADA: Langsung redirect ke detail percakapan
        header('Location: ' . BASE_URL . 'actions/pesan/detail.php?id=' . $existing_thread['id_pesan']);
        exit;
    } else {
        // JIKA BELUM ADA: Buat utas percakapan baru
        $stmt_penerima = $pdo->prepare("SELECT nama_lengkap FROM pengguna WHERE id_pengguna = ?");
        $stmt_penerima->execute([$id_penerima]);
        $nama_penerima = $stmt_penerima->fetchColumn();

        $subjek = "Percakapan dengan " . $nama_penerima;
        $isi_pesan = "(Percakapan ini dimulai oleh admin/karyawan)";
        
        $sql_insert = "INSERT INTO pesan_bantuan (id_pengirim, id_penerima, subjek, isi_pesan, status_pesan) VALUES (?, ?, ?, ?, 'Dibalas')";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$id_pengirim, $id_penerima, $subjek, $isi_pesan]);
        $new_thread_id = $pdo->lastInsertId();

        // Redirect ke detail percakapan yang baru dibuat
        header('Location: ' . BASE_URL . 'actions/pesan/detail.php?id=' . $new_thread_id);
        exit;
    }
} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'admin/user.php', 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
}
?>