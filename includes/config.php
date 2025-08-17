<?php
// File: includes/config.php (Versi Final & Disempurnakan)

// ===================================================================
// 1. PENGATURAN LINGKUNGAN & ERROR REPORTING
// ===================================================================
// Ubah menjadi 'development' saat Anda sedang coding untuk melihat semua error.
// Ubah menjadi 'production' saat website sudah online untuk menyembunyikan error teknis.
define('ENVIRONMENT', 'development');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    // Di mode production, error akan dicatat ke file log server, bukan ditampilkan.
}

// ===================================================================
// 2. KONSTANTA & PENGATURAN DASAR
// ===================================================================
// Atur zona waktu default
date_default_timezone_set('Asia/Jakarta');

// Tentukan Base URL
define('BASE_URL', 'http://localhost/rental_mobil/');

// ===================================================================
// 3. KONEKSI DATABASE
// ===================================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'rental_mobil');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // PERBAIKAN: Jangan tampilkan pesan error database yang detail ke pengguna.
    error_log("Database Connection Error: " . $e->getMessage()); // Catat error ke log server
    die("Terjadi masalah koneksi ke database. Silakan coba lagi nanti."); // Pesan umum untuk pengguna
}

// ===================================================================
// 4. PENGATURAN KEAMANAN SESSION
// ===================================================================
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 0, // Sesi berakhir saat browser ditutup
    'path' => '/',
    'domain' => '', // Untuk localhost, biarkan kosong. Untuk domain online, isi dengan '.domainanda.com'
    'secure' => isset($_SERVER['HTTPS']), // Wajib TRUE jika sudah online (HTTPS)
    'httponly' => true, // Mencegah akses cookie dari JavaScript (Melawan XSS)
    'samesite' => 'Lax' // Mencegah beberapa serangan CSRF
]);

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===================================================================
// 5. MENJALANKAN TUGAS OTOMATIS (JIKA PERLU)
// ===================================================================
// Memanggil skrip pengecekan pesanan kedaluwarsa.
if (isset($pdo)) {
    require_once __DIR__ . '/../actions/pemesanan/cek_kedaluwarsa.php';
}
