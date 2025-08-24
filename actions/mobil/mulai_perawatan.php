<?php
// File: actions/mobil/mulai_perawatan.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$id_mobil = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data mobil
$stmt_mobil = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
$stmt_mobil->execute([$id_mobil]);
$mobil = $stmt_mobil->fetch();
if (!$mobil || $mobil['status'] !== 'Tersedia') {
    redirect_with_message('../../admin/mobil.php', 'Mobil ini tidak bisa dimasukkan ke perawatan saat ini.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keterangan = $_POST['keterangan'];
    $estimasi = $_POST['tanggal_estimasi_selesai'];

    try {
        $pdo->beginTransaction();
        // 1. Masukkan ke riwayat perawatan
        $sql1 = "INSERT INTO riwayat_perawatan (id_mobil, tanggal_masuk, tanggal_estimasi_selesai, keterangan) VALUES (?, CURDATE(), ?, ?)";
        $pdo->prepare($sql1)->execute([$id_mobil, $estimasi, $keterangan]);

        // 2. Ubah status mobil menjadi 'Perawatan'
        $sql2 = "UPDATE mobil SET status = 'Perawatan' WHERE id_mobil = ?";
        $pdo->prepare($sql2)->execute([$id_mobil]);

        $pdo->commit();
        redirect_with_message('../../admin/mobil.php', 'Mobil berhasil dimasukkan ke dalam daftar perawatan.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        redirect_with_message('mulai_perawatan.php?id=' . $id_mobil, 'Gagal: ' . $e->getMessage(), 'error');
    }
}

$page_title = 'Mulai Perawatan Mobil';
require_once '../../includes/header.php';
?>
<div class="page-header">
    <h1>Mulai Perawatan: <?= htmlspecialchars($mobil['merk'] . ' ' . $mobil['model']) ?></h1>
</div>
<div class="form-container admin-form">
    <div class="form-box">
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="form-group"><label for="keterangan">Keterangan Perawatan</label><textarea id="keterangan" name="keterangan" rows="4" required placeholder="Contoh: Ganti oli, servis rutin, perbaikan AC..."></textarea></div>
            <div class="form-group"><label for="tanggal_estimasi_selesai">Tanggal Estimasi Selesai</label><input type="date" id="tanggal_estimasi_selesai" name="tanggal_estimasi_selesai"></div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="../../admin/mobil.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>