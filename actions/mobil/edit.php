<?php
// File: actions/mobil/edit.php

// Panggil semua file konfigurasi yang dibutuhkan
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses untuk halaman ini: Admin dan Karyawan
check_auth(['Admin', 'Karyawan']);

$errors = [];
// Ambil ID dari URL jika method GET, atau dari form jika method POST
$id_mobil = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

if ($id_mobil === 0) {
    $role_folder = strtolower($_SESSION['role']); // Hasilnya 'admin' atau 'karyawan'
    redirect_with_message(BASE_URL . $role_folder . '/mobil.php', 'Mobil tidak ditemukan.', 'error');
}


// ===================================================================
// BAGIAN 1: PROSES FORM JIKA DISUBMIT (METHOD POST)
// ===================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $gambar_lama = trim($_POST['gambar_lama']);

    // Validasi dasar
    if (empty($merk) || empty($model) || empty($plat_nomor) || empty($harga_sewa_harian)) {
        $errors[] = "Merk, Model, Plat Nomor, dan Harga Sewa wajib diisi.";
    }

    // Logika untuk menangani upload gambar baru
    $nama_file_gambar = $gambar_lama; // Defaultnya adalah gambar lama
    if (isset($_FILES['gambar_mobil']) && $_FILES['gambar_mobil']['error'] === UPLOAD_ERR_OK) {
        // Jika ada file baru yang diunggah, proses
        $upload_result = upload_file($_FILES['gambar_mobil'], '../../assets/img/mobil/');
        if (is_array($upload_result)) {
            // Jika upload gagal, tambahkan error
            $errors[] = $upload_result['error'];
        } else {
            // Jika upload berhasil, gunakan nama file baru dan hapus file lama
            $nama_file_gambar = $upload_result;
            if ($gambar_lama && file_exists('../../assets/img/mobil/' . $gambar_lama)) {
                unlink('../../assets/img/mobil/' . $gambar_lama);
            }
        }
    }

    // Jika tidak ada error, perbarui data di database
    if (empty($errors)) {
        try {
            $sql = "UPDATE mobil SET 
                        merk = ?, model = ?, plat_nomor = ?, tahun = ?, jenis_mobil = ?, 
                        harga_sewa_harian = ?, denda_per_hari = ?, status = ?, spesifikasi = ?, gambar_mobil = ?
                    WHERE id_mobil = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $merk,
                $model,
                $plat_nomor,
                $tahun,
                $jenis_mobil,
                $harga_sewa_harian,
                $denda_per_hari,
                $status,
                $spesifikasi,
                $nama_file_gambar,
                $id_mobil
            ]);

            $role_folder = strtolower($_SESSION['role']); // Hasilnya 'admin' atau 'karyawan'
            redirect_with_message(BASE_URL . $role_folder . '/mobil.php', 'Mobil berhasil diperbarui!');
        } catch (PDOException $e) {
            $errors[] = 'Gagal memperbarui data: ' . $e->getMessage();
        }
    }
}


// ===================================================================
// BAGIAN 2: AMBIL DATA UNTUK DITAMPILKAN DI FORM (METHOD GET)
// ===================================================================
try {
    $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt->execute([$id_mobil]);
    $mobil = $stmt->fetch();
    if (!$mobil) {
        $role_folder = strtolower($_SESSION['role']); // Hasilnya 'admin' atau 'karyawan'
        redirect_with_message(BASE_URL . $role_folder . '/mobil.php', 'Mobil tidak ditemukan.', 'error');
    }
} catch (PDOException $e) {
    $role_folder = strtolower($_SESSION['role']); // Hasilnya 'admin' atau 'karyawan'
    redirect_with_message(BASE_URL . $role_folder . '/mobil.php', 'Terjadi kesalahan pada database: ' . $e->getMessage(), 'error');
}

$page_title = 'Edit Mobil';
require_once '../../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Mobil: <?= htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']) ?></h1>
</div>

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

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $mobil['id_mobil'] ?>">
            <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($mobil['gambar_mobil']) ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label for="merk">Merk Mobil</label>
                    <input type="text" id="merk" name="merk" required value="<?= htmlspecialchars($mobil['merk']) ?>">
                </div>
                <div class="form-group">
                    <label for="model">Model Mobil</label>
                    <input type="text" id="model" name="model" required value="<?= htmlspecialchars($mobil['model']) ?>">
                </div>
                <div class="form-group">
                    <label for="plat_nomor">Plat Nomor</label>
                    <input type="text" id="plat_nomor" name="plat_nomor" required value="<?= htmlspecialchars($mobil['plat_nomor']) ?>">
                </div>
                <div class="form-group">
                    <label for="tahun">Tahun</label>
                    <input type="number" id="tahun" name="tahun" required min="1990" max="<?= date('Y') + 1 ?>" value="<?= htmlspecialchars($mobil['tahun']) ?>">
                </div>
                <div class="form-group">
                    <label for="jenis_mobil">Jenis Mobil (e.g., SUV, MPV)</label>
                    <input type="text" id="jenis_mobil" name="jenis_mobil" value="<?= htmlspecialchars($mobil['jenis_mobil']) ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Tersedia" <?= ($mobil['status'] == 'Tersedia') ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Disewa" <?= ($mobil['status'] == 'Disewa') ? 'selected' : '' ?>>Disewa</option>
                        <option value="Perawatan" <?= ($mobil['status'] == 'Perawatan') ? 'selected' : '' ?>>Perawatan</option>
                        <option value="Tidak Aktif" <?= ($mobil['status'] == 'Tidak Aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga_sewa_harian">Harga Sewa / Hari (Rp)</label>
                    <input type="number" id="harga_sewa_harian" name="harga_sewa_harian" required step="1000" value="<?= htmlspecialchars($mobil['harga_sewa_harian']) ?>">
                </div>
                <div class="form-group">
                    <label for="denda_per_hari">Denda / Hari (Rp)</label>
                    <input type="number" id="denda_per_hari" name="denda_per_hari" required step="1000" value="<?= htmlspecialchars($mobil['denda_per_hari']) ?>">
                </div>
                <div class="form-group full-width">
                    <label for="spesifikasi">Spesifikasi & Fitur</label>
                    <textarea id="spesifikasi" name="spesifikasi" rows="4"><?= htmlspecialchars($mobil['spesifikasi']) ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label for="gambar_mobil">Ganti Gambar Mobil (Opsional)</label>
                    <input type="file" id="gambar_mobil" name="gambar_mobil" accept="image/*">
                    <p style="font-size: 0.9rem; color: #6c757d; margin-top: 5px;">
                        Gambar saat ini: <a href="../../assets/img/mobil/<?= htmlspecialchars($mobil['gambar_mobil']) ?>" target="_blank"><?= htmlspecialchars($mobil['gambar_mobil']) ?></a>
                    </p>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= BASE_URL . strtolower($_SESSION['role']) ?>/mobil.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once '../../includes/footer.php';
?>