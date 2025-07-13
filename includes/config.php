<?php
// File: includes/config.php

// ====================================================================
// PERBAIKAN FINAL: Tentukan Base URL Anda secara manual di sini.
// Ini adalah satu-satunya tempat yang perlu Anda ubah.
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

// MEMULAI SESSION (tetap sama)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>