<?php
// File: pelanggan/profile.php (Universal untuk Semua Role)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah pengguna sudah login (berlaku untuk semua role)
check_auth();

$id_pengguna = $_SESSION['id_pengguna'];
$page_title = 'Profil Saya';
require_once '../includes/header.php';

// Ambil data pengguna saat ini dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_pengguna]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Gagal mengambil data profil.");
}
?>

<div class="page-header">
    <h1>Profil Saya</h1>
    <p>Kelola informasi akun dan data pribadi Anda.</p>
</div>

<?php display_flash_message(); ?>

<div class="form-container">
    <div class="form-box">
        <h3>Ubah Informasi Profil</h3>
        <form action="<?= BASE_URL ?>actions/pengguna/update_profile.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="no_telp">No. Telepon</label>
                <input type="tel" id="no_telp" name="no_telp" value="<?= htmlspecialchars($user['no_telp']) ?>">
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea>
            </div>
            <hr>
            <h3>Ubah Password</h3>
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">
            </div>
            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirm" name="password_confirm">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <div class="form-box danger-zone">
        <h3>Zona Berbahaya</h3>
        <p>Tindakan ini tidak dapat dibatalkan. Menghapus akun akan menghilangkan semua riwayat pemesanan Anda secara permanen.</p>
        <form action="<?= BASE_URL ?>actions/pengguna/hapus_akun.php" method="POST" onsubmit="return confirm('PERINGATAN: Anda akan menghapus akun Anda secara permanen. Tindakan ini tidak bisa dibatalkan. Lanjutkan?');">
            <button type="submit" class="btn btn-danger">Hapus Akun Saya</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>