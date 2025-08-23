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
    try {
        $pdo->beginTransaction();
        // 1. Update riwayat perawatan
        $sql1 = "UPDATE riwayat_perawatan SET status_perawatan = 'Selesai', tanggal_selesai_aktual = CURDATE(), biaya = ? WHERE id_perawatan = ?";
        $pdo->prepare($sql1)->execute([$biaya, $perawatan['id_perawatan']]);
        // 2. Ubah status mobil kembali menjadi 'Tersedia'
        $sql2 = "UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?";
        $pdo->prepare($sql2)->execute([$id_mobil]);
        $pdo->commit();
        redirect_with_message('../../admin/mobil.php', 'Perawatan mobil telah selesai.');
    } catch (PDOException $e) { /* ... */
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
        <form action="" method="POST">
            <div class="form-group"><label for="biaya">Total Biaya Perawatan (Rp)</label><input type="number" id="biaya" name="biaya" placeholder="Kosongkan jika tidak ada biaya"></div>
            <button type="submit" class="btn btn-success">Tandai Selesai & Jadikan Tersedia</button>
            <a href="../../admin/mobil.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>