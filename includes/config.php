<?php
// File: includes/config.php

// ====================================================================
// Atur zona waktu default untuk semua fungsi tanggal di PHP
// ====================================================================
date_default_timezone_set('Asia/Jakarta');

// ====================================================================
// Tentukan Base URL Anda secara manual di sini.
// Pastikan ada tanda '/' di akhir.
// ====================================================================
define('BASE_URL', 'http://localhost/rental_mobil/');


// PENGATURAN KONEKSI DATABASE (tetap sama)
define('DB_HOST', 'localhost');
define('DB_NAME', 'rental_mobil');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// PENGATURAN KEAMANAN SESSION
ini_set('session.use_only_cookies', 1); // Hanya gunakan cookie untuk session
ini_set('session.use_strict_mode', 1); // Pastikan session ID dibuat oleh server

session_set_cookie_params([
    'lifetime' => 1800, // Durasi session 30 menit
    'path' => '/',
    'domain' => '', // Sesuaikan dengan domain Anda jika sudah online
    'secure' => isset($_SERVER['HTTPS']), // Kirim cookie hanya melalui HTTPS
    'httponly' => true, // Cegah akses cookie dari JavaScript
    'samesite' => 'Lax'
]);

// MEMULAI SESSION (tetap sama)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($pdo)) {
    require_once __DIR__ . '/../actions/pemesanan/cek_kedaluwarsa.php';
}
?>