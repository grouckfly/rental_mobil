<?php
// File: actions/pengguna/hapus_akun.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_with_message(BASE_URL, 'Akses tidak sah.', 'error'); }

$id_pengguna = $_SESSION['id_pengguna'];

try {
    // PENTING: Untuk menjaga integritas data, kita tidak benar-benar menghapus pengguna.
    // Kita akan menonaktifkannya dan mengubah datanya agar tidak bisa login lagi.
    $anon_email = "deleted_" . time() . "@deleted.com";
    $anon_username = "deleted_" . time();
    $hashed_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    $sql = "UPDATE pengguna SET 
                username = ?, 
                email = ?, 
                password = ?, 
                nama_lengkap = 'Pengguna Dihapus',
                no_telp = NULL,
                alamat = NULL,
                role = 'Pelanggan' -- downgrade role untuk keamanan
            WHERE id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anon_username, $anon_email, $hashed_password, $id_pengguna]);

    // Hancurkan session dan logout
    session_unset();
    session_destroy();
    
    // Redirect ke halaman utama
    header('Location: ' . BASE_URL . '?status=account_deleted');
    exit;

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Gagal menghapus akun: ' . $e->getMessage(), 'error');
}
?>