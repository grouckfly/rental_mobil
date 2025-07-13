<?php
// File: admin/edit_mobil.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Memastikan hanya Admin atau Karyawan yang bisa mengakses
check_auth(['Admin', 'Karyawan']);

// Ambil ID mobil dari URL
$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_mobil === 0) {
    redirect_with_message('mobil.php', 'ID Mobil tidak valid.', 'error');
}

// Ambil data mobil saat ini dari database untuk ditampilkan di form
try {
    $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
    $stmt->execute([$id_mobil]);
    $mobil = $stmt->fetch();
    if (!$mobil) {
        redirect_with_message('mobil.php', 'Mobil tidak ditemukan.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('mobil.php', 'Terjadi kesalahan saat mengambil data mobil.', 'error');
}

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
    $nama_file_gambar_lama = $mobil['gambar_mobil'];

    // Validasi dasar
    if (empty($merk) || empty($model) || empty($plat_nomor) || empty($harga_sewa_harian)) {
        $errors[] = "Merk, Model, Plat Nomor, dan Harga Sewa wajib diisi.";
    }

    // Proses upload gambar baru jika ada
    $nama_file_gambar_baru = $nama_file_gambar_lama;
    if (isset($_FILES['gambar_mobil']) && $_FILES['gambar_mobil']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file($_FILES['gambar_mobil'], '../assets/img/mobil/');
        if (is_array($upload_result) && isset($upload_result['error'])) {
            $errors[] = $upload_result['error'];
        } else {
            $nama_file_gambar_baru = $upload_result;
            // Hapus gambar lama jika upload gambar baru berhasil
            if ($nama_file_gambar_lama && file_exists('../assets/img/mobil/' . $nama_file_gambar_lama)) {
                unlink('../assets/img/mobil/' . $nama_file_gambar_lama);
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
                $merk, $model, $plat_nomor, $tahun, $jenis_mobil, 
                $harga_sewa_harian, $denda_per_hari, $status, $spesifikasi, $nama_file_gambar_baru,
                $id_mobil
            ]);
            
            redirect_with_message('mobil.php', 'Data mobil berhasil diperbarui!');

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Plat nomor '{$plat_nomor}' sudah terdaftar untuk mobil lain.";
            } else {
                $errors[] = "Terjadi kesalahan saat memperbarui data: " . $e->getMessage();
            }
        }
    }
    
    // Jika ada error, isi kembali data form dengan data yang baru diinput
    $mobil['merk'] = $merk;
    $mobil['model'] = $model;
    // ... dan seterusnya untuk semua field
}

$page_title = 'Edit Mobil: ' . htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']);
require_once '../includes/header.php';
?>

<div class="page-header">
    <h1>Edit Mobil</h1>
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

        <form action="edit_mobil.php?id=<?= $id_mobil ?>" method="POST" enctype="multipart/form-data">
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
                    <label for="jenis_mobil">Jenis Mobil</label>
                    <input type="text" id="jenis_mobil" name="jenis_mobil" value="<?= htmlspecialchars($mobil['jenis_mobil']) ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Tersedia" <?= ($mobil['status'] == 'Tersedia') ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Disewa" <?= ($mobil['status'] == 'Disewa') ? 'selected' : '' ?>>Disewa</option>
                        <option value="Perawatan" <?= ($mobil['status'] == 'Perawatan') ? 'selected' : '' ?>>Perawatan</option>
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
                    <input type="file" id="gambar_mobil" name="gambar_mobil" accept="image/png, image/jpeg, image/gif">
                    <p style="font-size: 0.9rem; color: #6c757d; margin-top: 5px;">
                        Gambar saat ini: 
                        <a href="../uploads/mobil/<?= htmlspecialchars($mobil['gambar_mobil']) ?>" target="_blank"><?= htmlspecialchars($mobil['gambar_mobil']) ?></a>
                    </p>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="mobil.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>