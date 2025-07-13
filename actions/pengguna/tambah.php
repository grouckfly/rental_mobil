<?php
// File: actions/pengguna/tambah.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Admin');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Logika validasi dan INSERT sama seperti sebelumnya) ...
    // ... Cek password, cek duplikat email/username ...
    // ... Hash password ...
    // ... Insert ke DB ...
    // ... Redirect on success or error ...
}

$page_title = 'Tambah Pengguna Baru';
require_once '../../includes/header.php';
?>
<div class="page-header"><h1>Tambah Pengguna Baru</h1></div>
<?php display_flash_message(); ?>
<div class="form-container admin-form">
    <div class="form-box">
        <form action="" method="POST">
             <div class="form-grid">
                <div class="form-group"><label for="username">Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label for="nama_lengkap">Nama Lengkap</label><input type="text" name="nama_lengkap" required></div>
                <div class="form-group"><label for="email">Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label for="no_telp">No. Telepon</label><input type="tel" name="no_telp"></div>
                <div class="form-group"><label for="password">Password</label><input type="password" name="password" required></div>
                <div class="form-group"><label for="password_confirm">Konfirmasi Password</label><input type="password" name="password_confirm" required></div>
                <div class="form-group"><label for="role">Role</label>
                    <select name="role" required>
                        <option value="Pelanggan">Pelanggan</option>
                        <option value="Karyawan">Karyawan</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="../../admin/user.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>