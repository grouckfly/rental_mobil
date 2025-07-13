<?php
// File: actions/pengguna/tambah.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses: HANYA ADMIN
check_auth('Admin');

$errors = [];

// Bagian Logika: Memproses form saat di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $alamat = trim($_POST['alamat']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $role = $_POST['role'];

    // Validasi
    if (empty($username) || empty($nama_lengkap) || empty($email) || empty($password)) {
        $errors[] = "Username, Nama Lengkap, Email, dan Password wajib diisi.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    // Jika tidak ada error, lanjutkan proses
    if (empty($errors)) {
        try {
            // Cek duplikat username atau email
            $stmt = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = "Username atau email sudah terdaftar.";
            } else {
                // Hash password sebelum disimpan
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO pengguna (username, password, nama_lengkap, email, no_telp, alamat, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $pdo->prepare($sql);
                $stmt_insert->execute([$username, $hashed_password, $nama_lengkap, $email, $no_telp, $alamat, $role]);

                redirect_with_message('../../admin/user.php', 'Pengguna baru berhasil ditambahkan.');
            }
        } catch (PDOException $e) {
            $errors[] = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Pengguna Baru';
require_once '../../includes/header.php';
?>

<div class="page-header"><h1>Tambah Pengguna Baru</h1></div>

<div class="form-container admin-form">
    <div class="form-box">
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"></div>
                <div class="form-group"><label for="nama_lengkap">Nama Lengkap</label><input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"></div>
                <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
                <div class="form-group"><label for="no_telp">No. Telepon</label><input type="tel" id="no_telp" name="no_telp" value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>"></div>
                <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
                <div class="form-group"><label for="password_confirm">Konfirmasi Password</label><input type="password" id="password_confirm" name="password_confirm" required></div>
                <div class="form-group full-width"><label for="alamat">Alamat</label><textarea id="alamat" name="alamat" rows="3"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea></div>
                <div class="form-group full-width"><label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="Pelanggan">Pelanggan</option>
                        <option value="Karyawan">Karyawan</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            <a href="../../admin/user.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>