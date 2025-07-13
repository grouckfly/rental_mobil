<?php
// File: includes/config.php

// PENGATURAN KONEKSI DATABASE
// =============================================================================
// Ganti nilai-nilai ini sesuai dengan pengaturan server database Anda.
define('DB_HOST', 'localhost');
define('DB_NAME', 'rental_mobil');
define('DB_USER', 'root');
define('DB_PASS', ''); // Biasanya kosong jika menggunakan XAMPP default


// PENGATURAN KONEKSI PDO (PHP Data Objects)
// =============================================================================
// Menggunakan PDO lebih disarankan karena lebih aman dan fleksibel.
try {
    // Buat objek koneksi PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );

    // Atur atribut error mode ke exception untuk menampilkan error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Atur mode pengambilan data default menjadi associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan eksekusi dan tampilkan pesan error.
    // Di lingkungan produksi, pesan ini sebaiknya tidak ditampilkan ke pengguna.
    die("Koneksi ke database gagal: " . $e->getMessage());
}


// MEMULAI SESSION
// =============================================================================
// Memeriksa apakah session belum dimulai, lalu memulainya.
// Ini memastikan session selalu tersedia di setiap halaman yang menyertakan file ini.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>