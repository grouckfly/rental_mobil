<?php
// File: actions/mobil/hapus.php (Versi dengan Pengecekan Riwayat)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Disarankan hanya untuk Admin
check_auth('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../admin/mobil.php', 'Akses tidak sah.', 'error');
}

$id_mobil = isset($_POST['id_mobil']) ? (int)$_POST['id_mobil'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid.', 'error');
}

try {
    // ==========================================================
    // LANGKAH BARU: Cek apakah mobil ini pernah ada di tabel pemesanan
    // ==========================================================
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_mobil = ?");
    $stmt_check->execute([$id_mobil]);
    $booking_count = $stmt_check->fetchColumn();

    if ($booking_count > 0) {
        // JIKA SUDAH ADA RIWAYAT, TOLAK PENGHAPUSAN dan beri pesan jelas
        redirect_with_message('../../admin/mobil.php', 'Gagal menghapus! Mobil ini sudah memiliki riwayat pemesanan. Ubah statusnya jika sudah tidak ingin disewakan.', 'error');
    }

    // --- JIKA TIDAK ADA RIWAYAT, LANJUTKAN PROSES PENGHAPUSAN ---

    // 1. Ambil nama file gambar sebelum menghapus record
    $stmt_select = $pdo->prepare("SELECT gambar_mobil FROM mobil WHERE id_mobil = ?");
    $stmt_select->execute([$id_mobil]);
    $mobil = $stmt_select->fetch();

    if (!$mobil) {
        redirect_with_message('../../admin/mobil.php', 'Mobil tidak ditemukan.', 'error');
    }
    $nama_file_gambar = $mobil['gambar_mobil'];

    // 2. Hapus record mobil dari database
    $stmt_delete = $pdo->prepare("DELETE FROM mobil WHERE id_mobil = ?");
    $stmt_delete->execute([$id_mobil]);

    // 3. Hapus file gambar dari server
    if ($nama_file_gambar && file_exists('../../uploads/mobil/' . $nama_file_gambar)) {
        unlink('../../uploads/mobil/' . $nama_file_gambar);
    }

    // 4. Redirect dengan pesan sukses
    redirect_with_message('../../admin/mobil.php', 'Mobil yang belum memiliki riwayat berhasil dihapus.');

} catch (PDOException $e) {
    // Catch block ini sebagai pengaman tambahan
    redirect_with_message('../../admin/mobil.php', 'Terjadi kesalahan pada database: ' . $e->getMessage(), 'error');
}
?>