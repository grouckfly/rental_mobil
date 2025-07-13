<?php
// File: actions/mobil/hapus.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// -----------------------------------------------------------------------------
// HAK AKSES
// Sangat disarankan agar hanya 'Admin' yang dapat menghapus data aset utama.
// Jika Anda ingin Karyawan juga bisa menghapus, ubah menjadi: check_auth(['Admin', 'Karyawan']);
// -----------------------------------------------------------------------------
check_auth('Admin');

// 1. Pastikan permintaan menggunakan metode POST untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika bukan POST, alihkan ke halaman utama mobil dengan pesan error
    redirect_with_message('../../admin/mobil.php', 'Akses tidak sah.', 'error');
}

// 2. Validasi ID Mobil yang dikirim dari form
$id_mobil = isset($_POST['id_mobil']) ? (int)$_POST['id_mobil'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid atau tidak ditemukan.', 'error');
}

try {
    // 3. Ambil nama file gambar dari database SEBELUM record dihapus
    $stmt_select = $pdo->prepare("SELECT gambar_mobil FROM mobil WHERE id_mobil = ?");
    $stmt_select->execute([$id_mobil]);
    $mobil = $stmt_select->fetch();

    if (!$mobil) {
        // Jika mobil dengan ID tersebut tidak ada
        redirect_with_message('../../admin/mobil.php', 'Mobil tidak ditemukan.', 'error');
    }

    $nama_file_gambar = $mobil['gambar_mobil'];

    // 4. Hapus record mobil dari tabel 'mobil' di database
    $stmt_delete = $pdo->prepare("DELETE FROM mobil WHERE id_mobil = ?");
    $stmt_delete->execute([$id_mobil]);

    // 5. Hapus file gambar terkait dari folder 'uploads/mobil/'
    if ($nama_file_gambar && file_exists('../../assets/img/mobil/' . $nama_file_gambar)) {
        unlink('../../assets/img/mobil/' . $nama_file_gambar);
    }

    // 6. Alihkan kembali ke halaman daftar mobil dengan pesan sukses
    redirect_with_message('../../admin/mobil.php', 'Mobil berhasil dihapus.');

} catch (PDOException $e) {
    // Tangani error jika mobil tidak bisa dihapus (misalnya: karena terkait dengan data pemesanan)
    // Kode error '23000' menandakan adanya pelanggaran foreign key constraint
    if ($e->getCode() == '23000') {
         redirect_with_message('../../admin/mobil.php', 'Gagal menghapus! Mobil ini masih terikat dengan riwayat pemesanan.', 'error');
    } else {
         redirect_with_message('../../admin/mobil.php', 'Terjadi kesalahan pada database: ' . $e->getMessage(), 'error');
    }
}
?>