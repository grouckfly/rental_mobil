<?php
// File: actions/mobil/tambah.php (Versi Aman & Disempurnakan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

// LAPISAN KEAMANAN 1: Proteksi CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$errors = [];
$input = []; // Array untuk menyimpan input pengguna

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi Token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $errors[] = "Sesi tidak valid. Silakan coba lagi.";
    } else {
        // 2. Ambil dan simpan input pengguna untuk ditampilkan kembali jika error
        $input = [
            'merk' => trim($_POST['merk'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'plat_nomor' => strtoupper(trim($_POST['plat_nomor'] ?? '')),
            'tahun' => trim($_POST['tahun'] ?? ''),
            'harga_sewa_harian' => trim($_POST['harga_sewa_harian'] ?? ''),
            'denda_per_hari' => trim($_POST['denda_per_hari'] ?? ''),
            'status' => trim($_POST['status'] ?? ''),
            'spesifikasi' => trim($_POST['spesifikasi'] ?? ''),
            'jenis_mobil' => trim($_POST['jenis_mobil'] ?? ''),
            'kelas_mobil' => trim($_POST['kelas_mobil'] ?? '')
        ];

        // 3. Validasi Input Lebih Ketat
        if (empty($input['merk']) || empty($input['model']) || empty($input['plat_nomor']) || empty($input['tahun']) || empty($input['harga_sewa_harian']) || empty($input['jenis_mobil']) || empty($input['kelas_mobil'])) {
            $errors[] = "Semua field wajib diisi.";
        }
        if (!is_numeric($input['tahun']) || strlen($input['tahun']) != 4) {
            $errors[] = "Format tahun tidak valid.";
        }
        if (!is_numeric($input['harga_sewa_harian']) || $input['harga_sewa_harian'] < 0) {
            $errors[] = "Harga sewa tidak valid.";
        }
        if (!is_numeric($input['denda_per_hari']) || $input['denda_per_hari'] < 0) {
            $errors[] = "Denda tidak valid.";
        }

        // 4. Proses upload gambar
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

        // 5. Jika tidak ada error, simpan ke database
        if (empty($errors)) {
            try {
                $sql = "INSERT INTO mobil (plat_nomor, merk, model, tahun, jenis_mobil, harga_sewa_harian, denda_per_hari, status, spesifikasi, kelas_mobil, gambar_mobil) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $input['plat_nomor'],
                    $input['merk'],
                    $input['model'],
                    $input['tahun'],
                    $input['jenis_mobil'],
                    $input['harga_sewa_harian'],
                    $input['denda_per_hari'],
                    $input['status'],
                    $input['spesifikasi'],
                    $input['kelas_mobil'],
                    $nama_file_gambar
                ]);

                // Hapus token setelah berhasil digunakan
                unset($_SESSION['csrf_token']);
                redirect_with_message('../../admin/mobil.php', 'Mobil baru berhasil ditambahkan!');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = "Plat nomor '{$input['plat_nomor']}' sudah terdaftar.";
                } else {
                    $errors[] = 'Gagal menyimpan data: ' . $e->getMessage();
                }
            }
        }
    }
}

$page_title = 'Tambah Mobil Baru';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <h1>Tambah Mobil Baru</h1>
</div>
<div class="form-container admin-form">
    <div class="form-box">
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error">
                <ul><?php foreach ($errors as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-grid">
                <div class="form-group"><label for="merk">Merk Mobil</label><input type="text" id="merk" name="merk" required value="<?= htmlspecialchars($input['merk'] ?? '') ?>"></div>
                <div class="form-group"><label for="model">Model Mobil</label><input type="text" id="model" name="model" required value="<?= htmlspecialchars($input['model'] ?? '') ?>"></div>
                <div class="form-group"><label for="plat_nomor">Plat Nomor</label><input type="text" id="plat_nomor" name="plat_nomor" required value="<?= htmlspecialchars($input['plat_nomor'] ?? '') ?>"></div>
                <div class="form-group"><label for="tahun">Tahun</label><input type="number" id="tahun" name="tahun" required min="1990" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($input['tahun'] ?? '') ?>"></div>
                <div class="form-group"><label for="jenis_mobil">Jenis Mobil</label><input type="text" id="jenis_mobil" name="jenis_mobil" required placeholder="e.g., SUV, MPV" value="<?= htmlspecialchars($input['jenis_mobil'] ?? '') ?>"></div>
                <div class="form-group"><label for="kelas_mobil">Kelas Mobil</label>
                    <select id="kelas_mobil" name="kelas_mobil" required>
                        <option value="">Pilih Kelas</option>
                        <option value="Low level" <?= (($input['kelas_mobil'] ?? '') === 'Low level') ? 'selected' : '' ?>>Low level</option>
                        <option value="Mid level" <?= (($input['kelas_mobil'] ?? '') === 'Mid level') ? 'selected' : '' ?>>Mid level</option>
                        <option value="High level" <?= (($input['kelas_mobil'] ?? '') === 'High level') ? 'selected' : '' ?>>High level</option>
                        <option value="Luxury" <?= (($input['kelas_mobil'] ?? '') === 'Luxury') ? 'selected' : '' ?>>Luxury</option>
                    </select>
                </div>
                <div class="form-group"><label for="harga_sewa_harian">Harga Sewa / Hari</label><input type="number" id="harga_sewa_harian" name="harga_sewa_harian" required value="<?= htmlspecialchars($input['harga_sewa_harian'] ?? '') ?>"></div>
                <div class="form-group"><label for="denda_per_hari">Denda / Hari</label><input type="number" id="denda_per_hari" name="denda_per_hari" required value="<?= htmlspecialchars($input['denda_per_hari'] ?? '') ?>"></div>
                <div class="form-group full-width"><label for="status">Status Awal</label>
                    <select id="status" name="status" required>
                        <option value="Tersedia" <?= (($input['status'] ?? '') === 'Tersedia') ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Perawatan" <?= (($input['status'] ?? '') === 'Perawatan') ? 'selected' : '' ?>>Perawatan</option>
                        <option value="Tidak Aktif" <?= (($input['status'] ?? '') === 'Tidak Aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div class="form-group full-width"><label for="spesifikasi">Spesifikasi</label><textarea id="spesifikasi" name="spesifikasi" rows="4"><?= htmlspecialchars($input['spesifikasi'] ?? '') ?></textarea></div>
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