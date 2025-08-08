<?php
// File: pelanggan/edit_profile.php (Versi Lengkap)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth();
$id_pengguna = $_SESSION['id_pengguna'];
$page_title = 'Edit Profil';
require_once '../includes/header.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_pengguna]);
    $user = $stmt->fetch();
} catch (PDOException $e) { die("Gagal mengambil data profil."); }
?>

<div class="page-header"><h1>Edit Profil</h1></div>

<div class="form-container">
    <div class="form-box">
        
        <form action="<?= BASE_URL ?>actions/pengguna/update_profile.php" method="POST" enctype="multipart/form-data">
            <h3>Informasi Akun</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>
            <hr>
            <h3>Data Pribadi</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="nik">Nomor Induk Kependudukan (NIK)</label>
                    <input type="text" id="nik" name="nik" value="<?= htmlspecialchars($user['nik'] ?? '') ?>" required minlength="16" maxlength="16">
                </div>
                <div class="form-group">
                    <label for="no_telp">No. Telepon</label>
                    <input type="tel" id="no_telp" name="no_telp" value="<?= htmlspecialchars($user['no_telp'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="foto_ktp">Upload Foto KTP</label>
                    <input type="file" id="foto_ktp" name="foto_ktp" accept="image/jpeg, image/png">
                    <?php if (!empty($user['foto_ktp'])): ?>
                        <small>KTP sudah diunggah. <a href="<?= BASE_URL ?>assets/img/ktp/<?= $user['foto_ktp'] ?>" target="_blank">Lihat</a>. Ganti file jika ingin memperbarui.</small>
                    <?php endif; ?>
                </div>
                <div class="form-group full-width">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                </div>
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
            <a href="profile.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>