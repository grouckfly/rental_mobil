<?php
// File: register.php

require_once 'includes/config.php';
require_once 'includes/functions.php';
$page_title = 'Daftar Akun Baru';
require_once 'includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dan sanitasi sederhana
    $username = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validasi
    if (empty($username) || empty($nama_lengkap) || empty($email) || empty($password)) $errors[] = "Semua field wajib diisi.";
    if (strlen($username) < 4) $errors[] = "Username minimal 4 karakter.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if ($password !== $password_confirm) $errors[] = "Konfirmasi password tidak cocok.";
    if (strlen($password) < 6) $errors[] = "Password minimal 6 karakter.";

    // Cek apakah username atau email sudah ada
    if(empty($errors)) {
        $stmt = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username atau email sudah terdaftar.";
        }
    }
    
    // Jika tidak ada error, proses pendaftaran
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO pengguna (username, password, nama_lengkap, email, alamat, no_telp, role) VALUES (?, ?, ?, ?, ?, ?, 'Pelanggan')");
            $stmt->execute([$username, $hashed_password, $nama_lengkap, $email, $alamat, $no_telp]);
            // Mengarahkan ke login.php dengan status sukses di URL
            header('Location: login.php?status=register_success');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan pada database. Silakan coba lagi.";
        }
    }
}
?>

<div class="form-container">
    <div class="form-box">
        <h2>Daftar Akun Baru</h2>
        <p>Isi data diri Anda untuk membuat akun.</p>
        
        <?php if(!empty($errors)): ?>
            <div class="flash-message flash-error">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="no_telp">No. Telepon</label>
                <input type="tel" id="no_telp" name="no_telp" required value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" id="alamat" name="alamat" required value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn btn-primary">Daftar</button>
        </form>
        <div class="form-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>