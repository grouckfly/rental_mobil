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
// 2. HTTP SECURITY HEADERS (PENINGKATAN KEAMANAN)
// ===================================================================

// Content Security Policy (CSP) - Melindungi dari XSS dan injeksi data
// Hanya izinkan sumber daya (gambar, script, font) dari domain kita sendiri dan CDN terpercaya yang kita gunakan.
$csp_directives = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdn.jsdelivr.net https://unpkg.com", // 'unsafe-inline' untuk script kecil
    "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net", // 'unsafe-inline' untuk <style>
    "img-src 'self' data:",
    "font-src 'self'",
    "object-src 'none'",
    "frame-ancestors 'none'"
];
header("Content-Security-Policy: " . implode('; ', $csp_directives));

// Melarang browser menebak-nebak tipe file (mencegah serangan MIME sniffing)
header("X-Content-Type-Options: nosniff");

// Melarang website lain menampilkan website Anda di dalam <iframe> (mencegah Clickjacking)
header("X-Frame-Options: DENY");

// (Hanya untuk HTTPS) Memaksa browser selalu menggunakan koneksi aman
// JANGAN AKTIFKAN baris di bawah ini jika Anda masih di localhost (HTTP)
// header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

// ===================================================================
// 3. KONSTANTA & PENGATURAN DASAR
// ===================================================================
// Atur zona waktu default
date_default_timezone_set('Asia/Jakarta');

// Tentukan Base URL
define('BASE_URL', 'http://localhost/rental_mobil/');

// ===================================================================
// 4. KONEKSI DATABASE
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
// 5. PENGATURAN KEAMANAN SESSION
// ===================================================================
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 28800, // Sesi berakhir dalam 8 jam
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
// 6. MENJALANKAN TUGAS OTOMATIS (JIKA PERLU)
// ===================================================================
// Memanggil skrip pengecekan pesanan kedaluwarsa.
if (isset($pdo)) {
    require_once __DIR__ . '/../actions/pemesanan/cek_kedaluwarsa.php';
}
