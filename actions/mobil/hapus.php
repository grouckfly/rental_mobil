<?php
// File: actions/mobil/hapus.php (Versi Cerdas dengan Soft & Hard Delete)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses hanya untuk Admin
check_auth('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../admin/mobil.php', 'Akses tidak sah.', 'error');
}

// Validasi Token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirect_with_message('../../admin/mobil.php', 'Sesi tidak valid. Silakan coba lagi.', 'error');
}

$id_mobil = isset($_POST['id_mobil']) ? (int)$_POST['id_mobil'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('../../admin/mobil.php', 'ID Mobil tidak valid.', 'error');
}

try {
    // 1. Cek apakah mobil ini memiliki riwayat di tabel pemesanan
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pemesanan WHERE id_mobil = ?");
    $stmt_check->execute([$id_mobil]);
    $booking_count = $stmt_check->fetchColumn();

    // ==========================================================
    // LOGIKA KONDISIONAL BARU
    // ==========================================================
    if ($booking_count > 0) {
        // JIKA SUDAH ADA RIWAYAT: Lakukan "Soft Delete" (Ubah status menjadi Tidak Aktif)
        $stmt_soft_delete = $pdo->prepare("UPDATE mobil SET status = 'Tidak Aktif' WHERE id_mobil = ?");
        $stmt_soft_delete->execute([$id_mobil]);
        redirect_with_message('../../admin/mobil.php', 'Mobil berhasil dinonaktifkan karena memiliki riwayat pemesanan.');
    } else {
        // JIKA TIDAK ADA RIWAYAT: Lakukan "Hard Delete" (Hapus permanen)

        // a. Ambil nama file gambar sebelum menghapus record
        $stmt_select = $pdo->prepare("SELECT gambar_mobil FROM mobil WHERE id_mobil = ?");
        $stmt_select->execute([$id_mobil]);
        $mobil = $stmt_select->fetch();

        if ($mobil) {
            $nama_file_gambar = $mobil['gambar_mobil'];

            // b. Hapus record mobil dari database
            $stmt_delete = $pdo->prepare("DELETE FROM mobil WHERE id_mobil = ?");
            $stmt_delete->execute([$id_mobil]);

            // c. Hapus file gambar dari server
            if ($nama_file_gambar && file_exists('../../assets/img/mobil/' . $nama_file_gambar)) {
                unlink('../../assets/img/mobil/' . $nama_file_gambar);
            }
        }

        redirect_with_message('../../admin/mobil.php', 'Mobil yang belum memiliki riwayat berhasil dihapus permanen.');
    }
} catch (PDOException $e) {
    // Blok catch ini sebagai pengaman tambahan
    redirect_with_message('../../admin/mobil.php', 'Terjadi kesalahan pada database: ' . $e->getMessage(), 'error');
}
