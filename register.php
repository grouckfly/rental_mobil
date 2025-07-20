<?php
// File: register.php (Versi Simple)

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Jika sudah login, alihkan ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    header("Location: pelanggan/dashboard.php");
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data yang dibutuhkan
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validasi
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Semua field wajib diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    // Jika tidak ada error, proses pendaftaran
    if(empty($errors)) {
        try {
            // Cek apakah username atau email sudah ada
            $stmt_check = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE username = ? OR email = ?");
            $stmt_check->execute([$username, $email]);
            if ($stmt_check->fetch()) {
                $errors[] = "Username atau email sudah terdaftar.";
            } else {
                // Hash password dan simpan pengguna baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO pengguna (username, email, password, role) VALUES (?, ?, ?, 'Pelanggan')");
                $stmt_insert->execute([$username, $email, $hashed_password]);
                
                // Redirect ke halaman login dengan notifikasi sukses
                header('Location: login.php?status=register_success');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan pada database.";
        }
    }
}

$page_title = 'Daftar Akun Baru';
require_once 'includes/header.php';
?>

<div class="form-container">
    <div class="form-box">
        <h2>Daftar Akun Baru</h2>
        <p>Buat akun untuk memulai penyewaan.</p>
        
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
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
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