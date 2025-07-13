<?php
// File: admin/tambah_mobil.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Admin atau Karyawan yang bisa mengakses
check_auth(['Admin', 'Karyawan']);

$errors = [];
// Proses form jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $merk = trim($_POST['merk']);
    $model = trim($_POST['model']);
    $plat_nomor = trim($_POST['plat_nomor']);
    $tahun = trim($_POST['tahun']);
    $jenis_mobil = trim($_POST['jenis_mobil']);
    $harga_sewa_harian = trim($_POST['harga_sewa_harian']);
    $denda_per_hari = trim($_POST['denda_per_hari']);
    $status = trim($_POST['status']);
    $spesifikasi = trim($_POST['spesifikasi']);
    $nama_file_gambar = '';

    // Validasi dasar
    if (empty($merk) || empty($model) || empty($plat_nomor) || empty($harga_sewa_harian)) {
        $errors[] = "Merk, Model, Plat Nomor, dan Harga Sewa wajib diisi.";
    }

    // Validasi dan proses upload gambar
    if (isset($_FILES['gambar_mobil']) && $_FILES['gambar_mobil']['error'] === UPLOAD_ERR_OK) {
        // Gunakan fungsi upload_file dari functions.php
        $upload_result = upload_file($_FILES['gambar_mobil'], '../assets/img/mobil/');
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $errors[] = $upload_result['error'];
        } else {
            $nama_file_gambar = $upload_result;
        }
    } else {
        $errors[] = "Gambar mobil wajib diunggah.";
    }

    // Jika tidak ada error, masukkan data ke database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO mobil (merk, model, plat_nomor, tahun, jenis_mobil, harga_sewa_harian, denda_per_hari, status, spesifikasi, gambar_mobil) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $merk, $model, $plat_nomor, $tahun, $jenis_mobil, 
                $harga_sewa_harian, $denda_per_hari, $status, $spesifikasi, $nama_file_gambar
            ]);
            
            // Redirect ke halaman kelola mobil dengan pesan sukses
            redirect_with_message('mobil.php', 'Mobil baru berhasil ditambahkan!');

        } catch (PDOException $e) {
            // Tangani error duplikat plat nomor atau error database lainnya
            if ($e->getCode() == 23000) {
                $errors[] = "Plat nomor '{$plat_nomor}' sudah terdaftar. Silakan gunakan plat nomor lain.";
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan data ke database: " . $e->getMessage();
            }
        }
    }
}

$page_title = 'Tambah Mobil Baru';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Tambah Mobil Baru</h1>
</div>

<div class="form-container admin-form">
    <div class="form-box">
        <?php if(!empty($errors)): ?>
            <div class="flash-message flash-error">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="tambah_mobil.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="merk">Merk Mobil</label>
                    <input type="text" id="merk" name="merk" required value="<?= htmlspecialchars($_POST['merk'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="model">Model Mobil</label>
                    <input type="text" id="model" name="model" required value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="plat_nomor">Plat Nomor</label>
                    <input type="text" id="plat_nomor" name="plat_nomor" required value="<?= htmlspecialchars($_POST['plat_nomor'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="tahun">Tahun</label>
                    <input type="number" id="tahun" name="tahun" required min="1990" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($_POST['tahun'] ?? date('Y')) ?>">
                </div>
                <div class="form-group">
                    <label for="jenis_mobil">Jenis Mobil (e.g., SUV, MPV)</label>
                    <input type="text" id="jenis_mobil" name="jenis_mobil" value="<?= htmlspecialchars($_POST['jenis_mobil'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Tersedia" <?= (($_POST['status'] ?? '') == 'Tersedia') ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Perawatan" <?= (($_POST['status'] ?? '') == 'Perawatan') ? 'selected' : '' ?>>Perawatan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga_sewa_harian">Harga Sewa / Hari (Rp)</label>
                    <input type="number" id="harga_sewa_harian" name="harga_sewa_harian" required step="1000" value="<?= htmlspecialchars($_POST['harga_sewa_harian'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="denda_per_hari">Denda / Hari (Rp)</label>
                    <input type="number" id="denda_per_hari" name="denda_per_hari" required step="1000" value="<?= htmlspecialchars($_POST['denda_per_hari'] ?? '') ?>">
                </div>
                <div class="form-group full-width">
                    <label for="spesifikasi">Spesifikasi & Fitur</label>
                    <textarea id="spesifikasi" name="spesifikasi" rows="4"><?= htmlspecialchars($_POST['spesifikasi'] ?? '') ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label for="gambar_mobil">Gambar Mobil</label>
                    <input type="file" id="gambar_mobil" name="gambar_mobil" required accept="image/png, image/jpeg, image/gif">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Simpan Mobil</button>
                <a href="mobil.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>