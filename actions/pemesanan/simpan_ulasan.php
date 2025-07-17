<?php
// File: actions/pemesanan/simpan_ulasan.php (Versi Perbaikan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(BASE_URL . 'pelanggan/history.php', 'Akses tidak sah.', 'error');
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$rating = (int)$_POST['rating'];
$review = trim($_POST['review_pelanggan']);
$id_pengguna = $_SESSION['id_pengguna'];

// Validasi
if ($id_pemesanan === 0 || empty($rating) || empty($review)) {
    redirect_with_message(BASE_URL . "pelanggan/beri_ulasan.php?id=$id_pemesanan", 'Rating dan ulasan tidak boleh kosong.', 'error');
}
if ($rating < 1 || $rating > 5) {
    redirect_with_message(BASE_URL . "pelanggan/beri_ulasan.php?id=$id_pemesanan", 'Rating tidak valid.', 'error');
}

try {
    // PERBAIKAN: Menggunakan nama kolom 'rating_pengguna' yang benar sesuai database
    $sql = "UPDATE pemesanan SET rating_pengguna = ?, review_pelanggan = ? WHERE id_pemesanan = ? AND id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$rating, $review, $id_pemesanan, $id_pengguna]);

    redirect_with_message(BASE_URL . 'pelanggan/history.php', 'Terima kasih! Ulasan Anda telah berhasil disimpan.');

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . "pelanggan/beri_ulasan.php?id=$id_pemesanan", 'Gagal menyimpan ulasan: ' . $e->getMessage(), 'error');
}
?>