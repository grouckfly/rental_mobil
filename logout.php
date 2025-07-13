<?php
// File: logout.php (Versi Final)

// Panggil file konfigurasi untuk memulai session yang ada
require_once 'includes/config.php';

// Hapus semua variabel session yang ada
session_unset();

// Hancurkan session secara total
session_destroy();

// Alihkan ke halaman login dengan parameter status di URL untuk memicu notifikasi
header('Location: login.php?status=logout_success');
exit;

?>