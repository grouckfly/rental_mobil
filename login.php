<?php
// File: login.php (Versi Final, Lengkap, dan Aman)

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Jika pengguna sudah login, langsung alihkan ke dashboard yang sesuai
if (isset($_SESSION['id_pengguna'])) {
    $role_dashboard = strtolower($_SESSION['role']);
    header("Location: " . BASE_URL . "{$role_dashboard}/dashboard.php");
    exit();
}

$error_message = '';
$notification_script = '';

// Menangani notifikasi dari URL (misal: setelah logout atau registrasi)
if (isset($_GET['status'])) {
    $message = '';
    if ($_GET['status'] === 'logout_success') {
        $message = 'Anda telah berhasil logout.';
    } elseif ($_GET['status'] === 'register_success') {
        $message = 'Pendaftaran berhasil! Silakan login.';
    }
    if (!empty($message)) {
        $safe_message = addslashes($message);
        $notification_script = "<script>document.addEventListener('DOMContentLoaded', () => { showToast('{$safe_message}', 'success'); });</script>";
    }
}

// Proses form hanya jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- LAPISAN KEAMANAN 1: Rate Limiting (Mencegah Brute Force) ---
    $max_attempts = 5;
    $lockout_time = 15; // dalam menit
    $ip_address = $_SERVER['REMOTE_ADDR'];

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt_check->execute([$ip_address, $lockout_time]);
    $attempts_count = $stmt_check->fetchColumn();

    if ($attempts_count >= $max_attempts) {
        $error_message = "Anda telah gagal login terlalu banyak. Silakan coba lagi dalam $lockout_time menit.";
    } else {
        // Lanjutkan proses login jika belum diblokir
        if (empty($username) || empty($password)) {
            $error_message = 'Username dan password tidak boleh kosong.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id_pengguna, username, password, role, nama_lengkap FROM pengguna WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // --- LOGIN BERHASIL ---
                    
                    // LAPISAN KEAMANAN 2: Regenerasi Session ID (Mencegah Session Fixation)
                    session_regenerate_id(true);

                    // Set semua data session yang dibutuhkan
                    $_SESSION['id_pengguna'] = $user['id_pengguna'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    
                    // Hapus catatan percobaan gagal untuk IP ini
                    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip_address]);

                    // Siapkan redirect
                    $role_dashboard = strtolower($user['role']);
                    $welcome_message = empty($user['nama_lengkap']) ? $user['username'] : $user['nama_lengkap'];
                    
                    redirect_with_message(BASE_URL . "{$role_dashboard}/dashboard.php", "Selamat datang kembali, " . htmlspecialchars($welcome_message) . "!");
                
                } else {
                    // JIKA LOGIN GAGAL: Catat percobaan dan beri pesan error
                    $pdo->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)")->execute([$username, $ip_address]);
                    $error_message = 'Kombinasi username dan password salah.';
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error_message = "Terjadi kesalahan pada sistem. Silakan coba lagi nanti.";
            }
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
        
        <?php if (!empty($error_message)) {
            echo "<div class='flash-message flash-error'>{$error_message}</div>";
        } ?>

        <form action="login.php" method="POST">
            <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="username" required autocomplete="username"></div>
            <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required autocomplete="current-password"></div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="form-footer"><p>Belum punya akun? <a href="register.php">Daftar di sini</a></p></div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
// Mencetak script notifikasi di akhir halaman jika ada
echo $notification_script;
?>