<?php
// File: login.php (Versi Final dengan Notifikasi Pendaftaran)

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

// PERBAIKAN: Cek berbagai status dari URL
if (isset($_GET['status'])) {
    $message = '';
    $type = 'success'; // Tipe default

    if ($_GET['status'] === 'logout_success') {
        $message = 'Anda telah berhasil logout.';
    } elseif ($_GET['status'] === 'register_success') {
        $message = 'Pendaftaran berhasil! Silakan login.';
    }

    // Jika ada pesan yang perlu ditampilkan, siapkan script-nya
    if (!empty($message)) {
        // addslashes untuk memastikan pesan aman disisipkan di dalam string JavaScript
        $safe_message = addslashes($message);
        $notification_script = "<script>document.addEventListener('DOMContentLoaded', () => { showToast('{$safe_message}', '{$type}'); });</script>";
    }
}

// Proses form login jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        // ...
    } else {
        try {
            // PERBAIKAN 1: Pastikan query mengambil 'nama_lengkap'
            $stmt = $pdo->prepare("SELECT id_pengguna, username, password, role, nama_lengkap FROM pengguna WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // --- LOGIN BERHASIL ---
                $_SESSION['id_pengguna'] = $user['id_pengguna'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // PERBAIKAN 2: Simpan 'nama_lengkap' ke dalam session
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

                $role_dashboard = strtolower($user['role']);
                redirect_with_message("{$role_dashboard}/dashboard.php", "Selamat datang kembali, " . htmlspecialchars($user['nama_lengkap']) . "!");
            } else {
                $error_message = 'Kombinasi username dan password salah.';
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan pada sistem.";
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
        if(!empty($error_message)) {
            echo "<div class='flash-message flash-error'>{$error_message}</div>";
        }
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