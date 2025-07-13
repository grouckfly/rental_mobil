<?php
// File: login.php

// Memanggil file-file penting
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 1. Jika pengguna sudah login, langsung alihkan ke dashboard yang sesuai
if (isset($_SESSION['id_pengguna'])) {
    $role_dashboard = strtolower($_SESSION['role']); // contoh: 'admin', 'karyawan'
    header("Location: {$role_dashboard}/dashboard.php");
    exit();
}

// Inisialisasi variabel untuk menampung pesan error
$error_message = '';

// 2. Proses form hanya jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil input dari form dan bersihkan spasi yang tidak perlu
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validasi dasar: pastikan input tidak kosong
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        try {
            // 3. Siapkan query untuk mengambil data pengguna berdasarkan username
            $stmt = $pdo->prepare("SELECT id_pengguna, username, password, role FROM pengguna WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // 4. Periksa apakah pengguna ditemukan DAN password cocok
            if ($user && password_verify($password, $user['password'])) {
                
                // --- LOGIN BERHASIL ---
                
                // Set semua data session yang dibutuhkan
                $_SESSION['id_pengguna'] = $user['id_pengguna'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Alihkan ke dashboard yang sesuai dengan peran (role) pengguna
                $role_dashboard = strtolower($user['role']);
                redirect_with_message("{$role_dashboard}/dashboard.php", "Selamat datang kembali, " . htmlspecialchars($user['username']) . "!");

            } else {
                // Jika pengguna tidak ditemukan atau password salah, beri pesan error yang sama
                $error_message = 'Kombinasi username dan password salah.';
            }

        } catch (PDOException $e) {
            // Tangani error database
            $error_message = "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
            // Untuk debugging, Anda bisa mencatat error: error_log($e->getMessage());
        }
    }
}

// Set judul halaman dan panggil header
$page_title = 'Login';
require_once 'includes/header.php';
?>

<div class="form-container">
    <div class="form-box">
        <h2>Login Akun</h2>
        <p>Silakan masuk untuk melanjutkan.</p>
        
        <?php 
        // Tampilkan pesan error jika ada
        if(!empty($error_message)) {
            echo "<div class='flash-message flash-error'>{$error_message}</div>";
        }
        
        // Tampilkan pesan flash dari halaman lain (misalnya setelah logout)
        display_flash_message(); 
        ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="form-footer">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</div>

<?php
// Panggil footer
require_once 'includes/footer.php';
?>