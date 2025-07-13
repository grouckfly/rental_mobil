<?php
// File: actions/pengguna/edit.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Admin');

$id_user = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if ($id_user === 0) {
    redirect_with_message('../../admin/user.php', 'ID pengguna tidak valid.', 'error');
}

// Bagian Logika: Memproses form saat di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengguna = (int)$_POST['id_pengguna'];
    $username = trim($_POST['username']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $alamat = trim($_POST['alamat']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Query UPDATE dasar
    $sql = "UPDATE pengguna SET username = ?, nama_lengkap = ?, email = ?, no_telp = ?, alamat = ?, role = ?";
    $params = [$username, $nama_lengkap, $email, $no_telp, $alamat, $role];

    // Logika pembaruan password: hanya update jika password baru diisi
    if (!empty($password)) {
        if (strlen($password) < 6) {
            redirect_with_message("edit.php?id=$id_pengguna", 'Password baru minimal 6 karakter.', 'error');
        }
        $sql .= ", password = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    $sql .= " WHERE id_pengguna = ?";
    $params[] = $id_pengguna;

    try {
        // Cek duplikat username/email, kecuali untuk pengguna ini sendiri
        $stmt_check = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE (username = ? OR email = ?) AND id_pengguna != ?");
        $stmt_check->execute([$username, $email, $id_pengguna]);
        if ($stmt_check->fetch()) {
            redirect_with_message("edit.php?id=$id_pengguna", 'Username atau email sudah digunakan oleh pengguna lain.', 'error');
        }

        // Jalankan query UPDATE
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        redirect_with_message('../../admin/user.php', 'Data pengguna berhasil diperbarui.');

    } catch (PDOException $e) {
        redirect_with_message("edit.php?id=$id_pengguna", 'Gagal memperbarui data: ' . $e->getMessage(), 'error');
    }
}

// Bagian Tampilan: Ambil data untuk ditampilkan di form
try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch();
    if (!$user) {
        redirect_with_message('../../admin/user.php', 'Pengguna tidak ditemukan.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('../../admin/user.php', 'Error database.', 'error');
}

$page_title = 'Edit Pengguna';
require_once '../../includes/header.php';
?>

<div class="page-header"><h1>Edit Pengguna: <?= htmlspecialchars($user['username']) ?></h1></div>

<div class="form-container admin-form">
    <div class="form-box">
        <form action="" method="POST">
            <input type="hidden" name="id_pengguna" value="<?= $user['id_pengguna'] ?>">
            <div class="form-grid">
                <div class="form-group"><label>Username</label><input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>"></div>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($user['nama_lengkap']) ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"></div>
                <div class="form-group"><label>No. Telepon</label><input type="tel" name="no_telp" value="<?= htmlspecialchars($user['no_telp']) ?>"></div>
                <div class="form-group full-width"><label>Alamat</label><textarea name="alamat" rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea></div>
                <div class="form-group"><label>Password Baru</label><input type="password" name="password"><small>Kosongkan jika tidak ingin mengubah password.</small></div>
                <div class="form-group"><label>Role</label>
                    <select name="role" required>
                        <option value="Pelanggan" <?= ($user['role'] == 'Pelanggan') ? 'selected' : '' ?>>Pelanggan</option>
                        <option value="Karyawan" <?= ($user['role'] == 'Karyawan') ? 'selected' : '' ?>>Karyawan</option>
                        <option value="Admin" <?= ($user['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="../../admin/user.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>