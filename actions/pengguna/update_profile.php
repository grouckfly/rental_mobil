<?php
// File: actions/pengguna/update_profile.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Akses tidak sah.', 'error'); }

$id_pengguna = $_SESSION['id_pengguna'];
$username = trim($_POST['username']);
// ... (ambil semua data lain dari form) ...
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

// Validasi
if (empty($username) || empty($_POST['nama_lengkap']) || empty($_POST['email'])) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Username, Nama, dan Email tidak boleh kosong.', 'error');
}

$sql_parts = ["username = ?", "nama_lengkap = ?", "email = ?", "no_telp = ?", "alamat = ?"];
$params = [$username, $_POST['nama_lengkap'], $_POST['email'], $_POST['no_telp'], $_POST['alamat']];

// Logika update password jika diisi
if (!empty($password)) {
    if ($password !== $password_confirm) {
        redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Konfirmasi password baru tidak cocok.', 'error');
    }
    if (strlen($password) < 6) {
        redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Password baru minimal 6 karakter.', 'error');
    }
    $sql_parts[] = "password = ?";
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}

$params[] = $id_pengguna;
$sql = "UPDATE pengguna SET " . implode(', ', $sql_parts) . " WHERE id_pengguna = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    // Update session username jika berubah
    $_SESSION['username'] = $username;
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Profil berhasil diperbarui.');
} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Gagal memperbarui profil: Username atau Email mungkin sudah digunakan.', 'error');
}
?>