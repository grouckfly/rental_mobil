<?php
// File: actions/pengguna/hapus.php (Versi Anonymize oleh Admin)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../admin/user.php', 'Akses tidak sah.', 'error');
}

// 1. Validasi Token CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirect_with_message('../../admin/user.php', 'Sesi tidak valid. Silakan coba lagi.', 'error');
}

// Validasi ID Pengguna yang akan dihapus
$id_pengguna_hapus = isset($_POST['id_pengguna']) ? (int)$_POST['id_pengguna'] : 0;
if ($id_pengguna_hapus === 0) {
    redirect_with_message('../../admin/user.php', 'ID pengguna tidak valid.', 'error');
}

// Mencegah admin menghapus akunnya sendiri
if ($id_pengguna_hapus === $_SESSION['id_pengguna']) {
    redirect_with_message('../../admin/user.php', 'Anda tidak bisa menghapus akun Anda sendiri melalui halaman ini.', 'error');
}

try {
    // 2. Ambil nama file Foto KTP sebelum data diubah
    $stmt_get_ktp = $pdo->prepare("SELECT foto_ktp FROM pengguna WHERE id_pengguna = ?");
    $stmt_get_ktp->execute([$id_pengguna_hapus]);
    $foto_ktp_file = $stmt_get_ktp->fetchColumn();

    // 3. Siapkan data anonim
    $anon_email = "deleted_" . time() . "_" . $id_pengguna_hapus . "@deleted.com";
    $anon_username = "deleted_" . time() . "_" . $id_pengguna_hapus;
    $hashed_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    // 4. BUKAN DELETE, TAPI UPDATE UNTUK ANONYMIZE DATA PENGGUNA
    $sql = "UPDATE pengguna SET 
                username = ?, 
                email = ?, 
                password = ?, 
                nama_lengkap = 'Pengguna Dihapus oleh Admin',
                nik = NULL,
                foto_ktp = NULL,
                no_telp = NULL,
                alamat = NULL
            WHERE id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anon_username, $anon_email, $hashed_password, $id_pengguna_hapus]);

    // 5. Hapus file fisik Foto KTP dari server
    if ($foto_ktp_file && file_exists('../../assets/img/ktp/' . $foto_ktp_file)) {
        unlink('../../assets/img/ktp/' . $foto_ktp_file);
    }

    redirect_with_message('../../admin/user.php', 'Pengguna telah berhasil dinonaktifkan (dihapus).');

} catch (PDOException $e) {
    redirect_with_message('../../admin/user.php', 'Gagal menonaktifkan pengguna: ' . $e->getMessage(), 'error');
}
?>