<?php
// File: actions/pengguna/hapus_akun.php (Versi Disempurnakan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_with_message(BASE_URL, 'Akses tidak sah.', 'error'); }

// 1. Validasi Token CSRF untuk keamanan
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Sesi tidak valid. Silakan coba lagi.', 'error');
}

$id_pengguna = $_SESSION['id_pengguna'];

try {
    // 2. Ambil nama file Foto KTP sebelum data diubah
    $stmt_get_ktp = $pdo->prepare("SELECT foto_ktp FROM pengguna WHERE id_pengguna = ?");
    $stmt_get_ktp->execute([$id_pengguna]);
    $foto_ktp_file = $stmt_get_ktp->fetchColumn();

    // 3. Anonymize data pengguna
    $anon_email = "deleted_" . time() . "@deleted.com";
    $anon_username = "deleted_" . time();
    $hashed_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    // 4. PERBAIKAN: Tambahkan NIK dan Foto KTP untuk dibersihkan
    $sql = "UPDATE pengguna SET 
                username = ?, 
                email = ?, 
                password = ?, 
                nama_lengkap = 'Pengguna Dihapus',
                nik = NULL,
                foto_ktp = NULL,
                no_telp = NULL,
                alamat = NULL,
                role = 'Pelanggan'
            WHERE id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$anon_username, $anon_email, $hashed_password, $id_pengguna]);

    // 5. Hapus file fisik Foto KTP dari server
    if ($foto_ktp_file && file_exists('../../assets/img/ktp/' . $foto_ktp_file)) {
        unlink('../../assets/img/ktp/' . $foto_ktp_file);
    }
    
    // 6. Hancurkan session dan logout
    session_unset();
    session_destroy();
    
    // 7. Redirect ke halaman utama dengan notifikasi toast
    // (Kita perlu memulai session baru yang bersih hanya untuk membawa pesan)
    session_start();
    redirect_with_message(BASE_URL . 'index.php', 'Akun Anda telah berhasil dihapus.');
    exit;

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Gagal menghapus akun: ' . $e->getMessage(), 'error');
}
?>