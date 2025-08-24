<?php
// File: actions/mobil/selesaikan_perawatan.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data perawatan yang sedang berjalan untuk mobil ini
$stmt_perawatan = $pdo->prepare("SELECT * FROM riwayat_perawatan WHERE id_mobil = ? AND status_perawatan = 'Dikerjakan' ORDER BY tanggal_masuk DESC LIMIT 1");
$stmt_perawatan->execute([$id_mobil]);
$perawatan = $stmt_perawatan->fetch();
if (!$perawatan) {
    redirect_with_message('../../admin/mobil.php', 'Tidak ditemukan data perawatan aktif untuk mobil ini.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $biaya = $_POST['biaya'];
    $nama_file_nota = '';

    // Proses upload foto nota jika ada
    if (isset($_FILES['foto_nota']) && $_FILES['foto_nota']['error'] === UPLOAD_ERR_OK) {

        // --- LAPISAN KEAMANAN TAMBAHAN ---

        $file_tmp_path = $_FILES['foto_nota']['tmp_name'];
        $file_size = $_FILES['foto_nota']['size'];
        $original_filename = basename($_FILES['foto_nota']['name']);

        // 1. Pengecekan Ukuran File (contoh: maks 2MB)
        $max_file_size = 2 * 1024 * 1024; // 2MB
        if ($file_size > $max_file_size) {
            redirect_with_message('selesaikan_perawatan.php?id=' . $id_mobil, 'Ukuran file terlalu besar. Maksimal 2MB.', 'error');
        }

        // 2. Validasi Tipe File Sebenarnya (MIME Type)
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp_path);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mime_types)) {
            redirect_with_message('selesaikan_perawatan.php?id=' . $id_mobil, 'Tipe file tidak diizinkan. Hanya JPG, dan PNG.', 'error');
        }

        // 3. Buat Nama File Baru yang Aman & Unik
        $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        $nama_file_nota = uniqid('nota_', true) . '.' . strtolower($file_extension);
        $dest_path = '../../assets/img/nota_perawatan/' . $nama_file_nota;

        // 4. Pindahkan File dengan Nama Baru
        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            // File berhasil di-upload dan divalidasi, nama file sudah disimpan di $nama_file_nota
        } else {
            redirect_with_message('selesaikan_perawatan.php?id=' . $id_mobil, 'Gagal memindahkan file yang diunggah.', 'error');
        }
    } else {
        // Biarkan $nama_file_nota kosong jika tidak ada file yang di-upload
        $nama_file_nota = '';
    }

    try {
        $pdo->beginTransaction();
        // 1. Update riwayat perawatan dengan data baru
        $sql1 = "UPDATE riwayat_perawatan SET status_perawatan = 'Selesai', tanggal_selesai_aktual = CURDATE(), biaya = ?, foto_nota = ? WHERE id_perawatan = ?";
        $pdo->prepare($sql1)->execute([$biaya, $nama_file_nota, $perawatan['id_perawatan']]);

        // 2. Ubah status mobil kembali menjadi 'Tersedia'
        $sql2 = "UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?";
        $pdo->prepare($sql2)->execute([$id_mobil]);

        $pdo->commit();
        redirect_with_message('../../admin/mobil.php', 'Perawatan mobil telah selesai.');
    } catch (PDOException $e) {
        redirect_with_message('selesaikan_perawatan.php?id=' . $id_mobil, 'Terjadi kesalahan: ' . $e->getMessage(), 'error');
    }
}

$page_title = 'Selesaikan Perawatan';
require_once '../../includes/header.php';
?>
<div class="page-header">
    <h1>Selesaikan Perawatan</h1>
</div>
<div class="form-container admin-form">
    <div class="form-box">
        <p><strong>Keterangan Awal:</strong> <?= htmlspecialchars($perawatan['keterangan']) ?></p>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="form-group"><label for="biaya">Total Biaya Perawatan (Rp)</label>
                <input type="number" id="biaya" name="biaya" placeholder="Kosongkan jika tidak ada biaya">
            </div>

            <div class="form-group">
                <label for="foto_nota">Upload Foto Nota (Opsional)</label>
                <input type="file" id="foto_nota" name="foto_nota" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Tandai Selesai</button>
            <a href="../../admin/mobil.php" class="btn btn-danger">Batal</a>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>