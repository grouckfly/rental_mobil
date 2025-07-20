<?php
// File: login.php (Versi Final Disempurnakan)

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek jika pengguna sudah login, alihkan ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    $role_dashboard = strtolower($_SESSION['role']);
    header("Location: {$role_dashboard}/dashboard.php");
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

// Proses form login hanya jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- PENAMBAHAN: LOGIKA RATE LIMITING ---
    $max_attempts = 5;
    $lockout_time = 15; // dalam menit
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Cek percobaan gagal dari IP ini dalam 15 menit terakhir
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
                    // Login Berhasil
                    session_regenerate_id(true);
                    $_SESSION['id_pengguna'] = $user['id_pengguna'];
                    // ... (set session lainnya) ...

                    // Hapus catatan percobaan gagal untuk IP ini karena sudah berhasil login
                    $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip_address]);

                    redirect_with_message("{$role_dashboard}/dashboard.php", "Selamat datang kembali, " . htmlspecialchars($welcome_message) . "!");
                } else {
                    // JIKA LOGIN GAGAL: Catat percobaan
                    $pdo->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)")->execute([$username, $ip_address]);
                    $error_message = 'Kombinasi username dan password salah.';
                }
            } catch (PDOException $e) {
                // Catat error ke log server, jangan tampilkan ke pengguna
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

        <?php
        // Menampilkan pesan error dari proses login
        if (!empty($error_message)) {
            echo "<div class='flash-message flash-error'>{$error_message}</div>";
        }
        ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
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