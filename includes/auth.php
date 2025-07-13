<?php
// File: includes/auth.php

/**
 * Fungsi untuk memproteksi halaman berdasarkan status login dan role pengguna.
 *
 * Fungsi ini akan memeriksa session. Jika pengguna belum login atau
 * role-nya tidak sesuai, ia akan dialihkan ke halaman login.
 *
 * @param string|array $required_roles Role yang diizinkan untuk mengakses halaman.
 * Bisa berupa string tunggal (misal: 'Admin')
 * atau array (misal: ['Admin', 'Karyawan']).
 * @return void
 */
function check_auth($required_roles = [])
{
    // Pastikan session sudah ada (seharusnya sudah dimulai oleh config.php)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Periksa apakah pengguna sudah login
    if (!isset($_SESSION['id_pengguna']) || !isset($_SESSION['username'])) {
        // Jika belum, redirect ke halaman login dengan pesan error
        header("Location: /rental-mobil/login.php?error=Anda harus login untuk mengakses halaman ini.");
        exit;
    }

    // Jika tidak ada role spesifik yang dibutuhkan, cukup periksa status login saja.
    if (empty($required_roles)) {
        return; // Pengguna sudah login, akses diizinkan.
    }

    // 2. Periksa apakah role pengguna sesuai dengan yang dibutuhkan
    $user_role = $_SESSION['role'];

    // Ubah $required_roles menjadi array jika masih dalam bentuk string
    if (is_string($required_roles)) {
        $required_roles = [$required_roles];
    }

    // Periksa apakah role pengguna ada di dalam array role yang diizinkan
    if (!in_array($user_role, $required_roles)) {
        // Jika tidak sesuai, redirect ke halaman login dengan pesan error
        header("Location: /rental-mobil/login.php?error=Anda tidak memiliki izin untuk mengakses halaman ini.");
        exit;
    }
}

?>