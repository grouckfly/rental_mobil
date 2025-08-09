<?php
// File: actions/pengguna/update_profile.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Akses tidak sah.', 'error'); }

$id_pengguna = $_SESSION['id_pengguna'];
$username = trim($_POST['username']);
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];
$nik = trim($_POST['nik']);

// Validasi
if (empty($username) || empty($_POST['nama_lengkap']) || empty($_POST['email'])) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Username, Nama, dan Email tidak boleh kosong.', 'error');
}

// Validasi NIK
if (empty($nik) || !is_numeric($nik) || strlen($nik) !== 16) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'NIK tidak valid. Harus 16 digit angka.', 'error');
}

// Ambil nama file KTP lama
$stmt_old = $pdo->prepare("SELECT foto_ktp FROM pengguna WHERE id_pengguna = ?");
$stmt_old->execute([$id_pengguna]);
$foto_ktp_lama = $stmt_old->fetchColumn();
$nama_file_ktp = $foto_ktp_lama;

// Proses upload KTP baru jika ada
if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
    $upload_result = upload_file($_FILES['foto_ktp'], '../../assets/img/ktp/');
    if (is_array($upload_result)) {
        redirect_with_message(BASE_URL . 'pelanggan/profile.php', $upload_result['error'], 'error');
    }
    $nama_file_ktp = $upload_result;
    // Hapus file lama jika berhasil upload yang baru
    if ($foto_ktp_lama && file_exists('../../assets/img/ktp/' . $foto_ktp_lama)) {
        unlink('../../assets/img/ktp/' . $foto_ktp_lama);
    }
}

// Jika KTP belum pernah diunggah dan tidak ada file baru, beri error
if (empty($nama_file_ktp)) {
     redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Anda wajib mengunggah foto KTP.', 'error');
}

// Siapkan query update
$sql_parts = ["nik = ?", "nama_lengkap = ?", "email = ?", "no_telp = ?", "alamat = ?", "foto_ktp = ?"];
$params = [$nik, $_POST['nama_lengkap'], $_POST['email'], $_POST['no_telp'], $_POST['alamat'], $nama_file_ktp];

// Logika update username
if (!empty($username)) {
    // Ambil username lama dari database
    $stmt_check = $pdo->prepare("SELECT username FROM pengguna WHERE id_pengguna = ?");
    $stmt_check->execute([$id_pengguna]);
    $username_lama = $stmt_check->fetchColumn();

    // Jika username berubah
    if ($username !== $username_lama) {
        // Validasi: hanya huruf, angka, underscore, 4-20 karakter
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
            redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Username hanya boleh huruf, angka, underscore, dan 4-20 karakter.', 'error');
        }
        // Cek apakah username sudah digunakan user lain
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengguna WHERE username = ? AND id_pengguna != ?");
        $stmt->execute([$username, $id_pengguna]);
        if ($stmt->fetchColumn() > 0) {
            redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Username sudah digunakan pengguna lain.', 'error');
        }
        $sql_parts[] = "username = ?";
        $params[] = $username;
    }
}

// Logika update password 
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
    
    $_SESSION['username'] = $username;
    $_SESSION['nama_lengkap'] = $_POST['nama_lengkap'];

    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Profil berhasil diperbarui.');

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'pelanggan/profile.php', 'Gagal memperbarui profil: Username atau Email mungkin sudah digunakan.', 'error');
}
?>