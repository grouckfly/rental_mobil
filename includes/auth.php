<?php
// File: includes/auth.php (Versi Final & Aman)

// Pastikan session sudah berjalan dan functions.php sudah dimuat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

/**
 * Fungsi untuk memproteksi halaman berdasarkan status login dan role pengguna.
 */
function check_auth($required_roles = [])
{

    // 1. Periksa apakah pengguna sudah login
    if (!isset($_SESSION['id_pengguna']) || !isset($_SESSION['role'])) {
        redirect_with_message(BASE_URL . 'login.php', 'Anda harus login untuk mengakses halaman ini.', 'error');
    }

    // 2. KEAMANAN TAMBAHAN: Validasi User Agent untuk mencegah Session Hijacking
    // if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    //     session_unset();
    //     session_destroy();
    //     redirect_with_message(BASE_URL . 'login.php', 'Sesi tidak valid, silakan login kembali.', 'error');
    // }

    // 3. Periksa apakah role pengguna sesuai dengan yang dibutuhkan
    $user_role = $_SESSION['role'];

    if (is_string($required_roles)) {
        $required_roles = [$required_roles];
    }

    if (!empty($required_roles) && !in_array($user_role, $required_roles)) {
        // Jika akses ditolak, arahkan ke dashboard mereka sendiri, bukan ke halaman login
        $role_dashboard = strtolower($user_role);
        redirect_with_message(BASE_URL . "{$role_dashboard}/dashboard.php", 'Anda tidak memiliki izin untuk mengakses halaman tersebut.', 'error');
    }
}
