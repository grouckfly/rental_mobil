<?php
// File: includes/auth.php (Versi Perbaikan)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk memproteksi halaman berdasarkan status login dan role pengguna.
 */
function check_auth($required_roles = []) {
    // 1. Periksa apakah pengguna sudah login
    if (!isset($_SESSION['id_pengguna']) || !isset($_SESSION['role'])) {
        // PERBAIKAN: Gunakan BASE_URL untuk path yang absolut dan pasti benar
        $error_message = urlencode("Anda harus login untuk mengakses halaman ini.");
        header("Location: " . BASE_URL . "login.php?error=" . $error_message);
        exit;
    }

    // 2. Periksa apakah role pengguna sesuai dengan yang dibutuhkan
    $user_role = $_SESSION['role'];

    if (is_string($required_roles)) {
        $required_roles = [$required_roles];
    }

    if (!empty($required_roles) && !in_array($user_role, $required_roles)) {
        // PERBAIKAN: Gunakan BASE_URL untuk path yang absolut dan pasti benar
        $error_message = urlencode("Anda tidak memiliki izin untuk mengakses halaman ini.");
        header("Location: " . BASE_URL . "login.php?error=" . $error_message);
        exit;
    }
}
?>