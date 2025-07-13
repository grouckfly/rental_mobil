<?php
// File: actions/mobil/tambah.php

// Panggil semua file konfigurasi yang dibutuhkan
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk halaman ini
check_auth(['Admin', 'Karyawan']);

$errors = [];

// ===================================================================
// BAGIAN 1: PROSES FORM JIKA DISUBMIT (METHOD POST)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan validasi data...
    $merk = trim($_POST['merk']);
    if (empty($merk)) { $errors[] = "Merk wajib diisi."; }
    // ... (lakukan validasi lain untuk semua field) ...

    // Proses upload gambar
    $nama_file_gambar = '';
    if (isset($_FILES['gambar_mobil']) && $_FILES['gambar_mobil']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['gambar_mobil'], '../../assets/img/mobil/');
        if (is_array($upload_result)) {
            $errors[] = $upload_result['error'];
        } else {
            $nama_file_gambar = $upload_result;
        }
    } else {
        $errors[] = "Gambar mobil wajib diunggah.";
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO mobil (merk, model, jenis_mobil, plat_nomor, tahun, harga_sewa_harian, denda_per_hari, spesifikasi, gambar_mobil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['merk'], $_POST['model'], $_POST['jenis_mobil'], $_POST['plat_nomor'], $_POST['tahun'], $_POST['harga_sewa_harian'], $_POST['denda_per_hari'], $_POST['spesifikasi'], $nama_file_gambar]);
            $role_folder = strtolower($_SESSION['role']); // Hasilnya 'admin' atau 'karyawan'
            redirect_with_message(BASE_URL . $role_folder . '/mobil.php', 'Mobil baru berhasil ditambahkan!');
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}


// ===================================================================
// BAGIAN 2: TAMPILAN HTML FORM
// ===================================================================
$page_title = 'Tambah Mobil Baru';
require_once '../../includes/header.php';
?>

<div class="page-header"><h1>Tambah Mobil Baru</h1></div>

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

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group"><label for="merk">Merk Mobil</label><input type="text" id="merk" name="merk" required></div>
                <div class="form-group"><label for="model">Model Mobil</label><input type="text" id="model" name="model" required></div>
                <div class="form-group"><label for="jenis_mobil">Jenis Mobil</label><input type="text" id="jenis_mobil" name="jenis_mobil" required></div>
                <div class="form-group"><label for="plat_nomor">Plat Nomor</label><input type="text" id="plat_nomor" name="plat_nomor" required></div>
                <div class="form-group"><label for="tahun">Tahun</label><input type="number" id="tahun" name="tahun" required min="1990" max="<?= date('Y') + 1 ?>"></div>
                <div class="form-group"><label for="harga_sewa_harian">Harga Sewa / Hari</label><input type="number" id="harga_sewa_harian" name="harga_sewa_harian" required></div>
                <div class="form-group"><label for="denda_per_hari">Denda / Hari</label><input type="number" id="denda_per_hari" name="denda_per_hari" required></div>
                <div class="form-group full-width"><label for="spesifikasi">Spesifikasi</label><textarea id="spesifikasi" name="spesifikasi" rows="4"></textarea></div>
                <div class="form-group full-width"><label for="gambar_mobil">Gambar Mobil</label><input type="file" id="gambar_mobil" name="gambar_mobil" required accept="image/*"></div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Mobil</button>
            <a href="<?= BASE_URL . strtolower($_SESSION['role']) ?>/mobil.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>