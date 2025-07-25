<?php
// File: actions/mobil/tambah.php (Versi Terbaru)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk halaman ini: Admin dan Karyawan
check_auth(['Admin', 'Karyawan']);

$errors = [];

// ===================================================================
// BAGIAN 1: PROSES FORM JIKA DISUBMIT (METHOD POST)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $merk = trim($_POST['merk']);
    $model = trim($_POST['model']);
    $plat_nomor = trim($_POST['plat_nomor']);
    $tahun = trim($_POST['tahun']);
    $harga_sewa_harian = trim($_POST['harga_sewa_harian']);
    $denda_per_hari = trim($_POST['denda_per_hari']);
    $status = trim($_POST['status']);
    $spesifikasi = trim($_POST['spesifikasi']);
    
    // Ambil data baru
    $jenis_mobil = trim($_POST['jenis_mobil']);
    $kelas_mobil = trim($_POST['kelas_mobil']);
    
    $nama_file_gambar = '';

    // Validasi dasar
    if (empty($merk) || empty($model) || empty($plat_nomor) || empty($harga_sewa_harian) || empty($jenis_mobil) || empty($kelas_mobil)) {
        $errors[] = "Semua field wajib diisi.";
    }

    // Proses upload gambar
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
            // Perbarui query INSERT dengan kolom baru
            $sql = "INSERT INTO mobil (plat_nomor, merk, model, tahun, jenis_mobil, harga_sewa_harian, denda_per_hari, status, spesifikasi, kelas_mobil, gambar_mobil) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            // Perbarui parameter execute
            $stmt->execute([
                $plat_nomor, $merk, $model, $tahun, $jenis_mobil, 
                $harga_sewa_harian, $denda_per_hari, $status, $spesifikasi, $kelas_mobil, $nama_file_gambar
            ]);
            
            redirect_with_message('../../admin/mobil.php', 'Mobil baru berhasil ditambahkan!');

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Plat nomor '{$plat_nomor}' sudah terdaftar.";
            } else {
                $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
            }
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
                <ul><?php foreach($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group"><label for="merk">Merk Mobil</label><input type="text" id="merk" name="merk" required></div>
                <div class="form-group"><label for="model">Model Mobil</label><input type="text" id="model" name="model" required></div>
                <div class="form-group"><label for="plat_nomor">Plat Nomor</label><input type="text" id="plat_nomor" name="plat_nomor" required></div>
                <div class="form-group"><label for="tahun">Tahun</label><input type="number" id="tahun" name="tahun" required min="1990" max="<?= date('Y') + 1 ?>"></div>
                
                <div class="form-group"><label for="jenis_mobil">Jenis Mobil (e.g., SUV, MPV)</label><input type="text" id="jenis_mobil" name="jenis_mobil" required></div>
                
                <div class="form-group"><label for="kelas_mobil">Kelas Mobil</label>
                    <select id="kelas_mobil" name="kelas_mobil" required>
                        <option value="">Pilih Kelas</option>
                        <option value="Low level">Low level</option>
                        <option value="Mid level">Mid level</option>
                        <option value="High level">High level</option>
                        <option value="Luxury">Luxury</option>
                    </select>
                </div>

                <div class="form-group"><label for="harga_sewa_harian">Harga Sewa / Hari</label><input type="number" id="harga_sewa_harian" name="harga_sewa_harian" required></div>
                <div class="form-group"><label for="denda_per_hari">Denda / Hari</label><input type="number" id="denda_per_hari" name="denda_per_hari" required></div>
                
                <div class="form-group full-width"><label for="status">Status Awal</label>
                    <select id="status" name="status" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Perawatan">Perawatan</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
                
                <div class="form-group full-width"><label for="spesifikasi">Spesifikasi</label><textarea id="spesifikasi" name="spesifikasi" rows="4"></textarea></div>
                <div class="form-group full-width"><label for="gambar_mobil">Gambar Mobil</label><input type="file" id="gambar_mobil" name="gambar_mobil" required accept="image/*"></div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Mobil</button>
            <a href="../../admin/mobil.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>