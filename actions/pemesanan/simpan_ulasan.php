<?php
// File: actions/pemesanan/simpan_ulasan.php (Versi Debug)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Akses tidak sah.');
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$rating = (int)$_POST['rating'];
$review = trim($_POST['review_pelanggan']);
$id_pengguna = $_SESSION['id_pengguna'];

// Validasi sederhana
if ($id_pemesanan === 0 || empty($rating) || empty($review)) {
    die("Error: Data tidak lengkap. Pastikan rating dan ulasan sudah diisi.");
}

// Menyiapkan query SQL
$sql = "UPDATE pemesanan SET rating_pengguna = ?, review_pelanggan = ? WHERE id_pemesanan = ? AND id_pengguna = ?";
$params = [$rating, $review, $id_pemesanan, $id_pengguna];

try {
    // Mencoba eksekusi query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Jika berhasil, redirect seperti biasa
    redirect_with_message(BASE_URL . 'pelanggan/history.php', 'Terima kasih! Ulasan Anda telah berhasil disimpan.');

} catch (PDOException $e) {
    // ========================================================
    // BAGIAN DEBUGGING: JIKA TERJADI ERROR, TAMPILKAN SEMUANYA
    // ========================================================
    
    // Hentikan redirect dan tampilkan pesan error secara detail
    header("Content-Type: text/plain"); // Tampilkan sebagai teks biasa agar mudah dibaca
    echo "--- TERJADI ERROR DATABASE ---\n\n";
    echo "PESAN ERROR:\n";
    print_r($e->getMessage());
    echo "\n\n";
    
    echo "QUERY SQL YANG DIJALANKAN:\n";
    print_r($sql);
    echo "\n\n";
    
    echo "DATA (PARAMS) YANG DIKIRIM:\n";
    print_r($params);
    echo "\n\n";
    
    echo "LOKASI ERROR:\n";
    print_r($e->getTraceAsString());
    
    // Hentikan eksekusi skrip
    die();
}
?>