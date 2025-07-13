<?php
// File: login.php (Versi Final)

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek jika pengguna sudah login, alihkan ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    $role_dashboard = strtolower($_SESSION['role']);
    header("Location: {$role_dashboard}/dashboard.php");
    exit();
}

// Inisialisasi variabel untuk pesan error dan notifikasi
$error_message = '';
$notification_script = '';

// Cek status dari URL (misalnya setelah logout)
if (isset($_GET['status']) && $_GET['status'] === 'logout_success') {
    // Siapkan script untuk memanggil notifikasi toast
    $notification_script = "<script>document.addEventListener('DOMContentLoaded', () => { showToast('Anda telah berhasil logout.', 'success'); });</script>";
}

// Proses form login jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_pengguna, username, password, role FROM pengguna WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login Berhasil
                $_SESSION['id_pengguna'] = $user['id_pengguna'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                $role_dashboard = strtolower($user['role']);
                redirect_with_message("{$role_dashboard}/dashboard.php", "Selamat datang kembali, " . htmlspecialchars($user['username']) . "!");
            } else {
                $error_message = 'Kombinasi username dan password salah.';
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
        }
    }
}

$page_title = 'Login';
require_once 'includes/header.php';
?>

<div class="form-container">
    <div class="form-box">
        <h2>Login Akun</h2>
        <p>Silakan masuk untuk melanjutkan.</p>
        
        <?php 
        // Menampilkan pesan error dari proses login
        if(!empty($error_message)) {
            echo "<div class='flash-message flash-error'>{$error_message}</div>";
        }
        
        // Menampilkan pesan flash dari aksi lain (misal: setelah registrasi)
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
require_once 'includes/footer.php';

// Mencetak script notifikasi di akhir halaman jika ada
echo $notification_script;
?>