<?php
// File: pelanggan/beri_ulasan.php (Versi Tambah & Edit)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// ... (logika untuk mengambil data $pemesanan seperti sebelumnya, pastikan mengambil 'rating_pengguna' juga) ...
try {
    $stmt = $pdo->prepare("SELECT p.*, m.merk, m.model, m.gambar_mobil FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pemesanan = ? AND p.id_pengguna = ?");
    $stmt->execute([$id_pemesanan, $_SESSION['id_pengguna']]);
    $pemesanan = $stmt->fetch();
    if (!$pemesanan || $pemesanan['status_pemesanan'] !== 'Selesai') {
        redirect_with_message('history.php', 'Ulasan hanya untuk pesanan yang sudah selesai.', 'error');
    }
} catch (PDOException $e) { 
    echo "Error: " . $e->getMessage();
    exit;
 }

// Tentukan mode: 'tambah' atau 'edit'
$mode = empty($pemesanan['review_pelanggan']) ? 'tambah' : 'edit';
$page_title = ($mode === 'tambah') ? 'Beri Ulasan' : 'Edit Ulasan';

require_once '../includes/header.php';
?>

<div class="page-header"><h1><?= $page_title ?></h1></div>

<div class="form-container">
    <div class="form-box">
        <h4>Ulasan untuk Pemesanan #<?= htmlspecialchars($pemesanan['kode_pemesanan']) ?></h4>
        <hr>

        <form action="<?= BASE_URL ?>actions/pemesanan/simpan_ulasan.php" method="POST">
            <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
            
            <div class="form-group">
                <label>Rating Anda</label>
                <div class="rating">
                    <input type="radio" id="star5" name="rating" value="5" required <?= ($pemesanan['rating_pengguna'] == 5) ? 'checked' : '' ?>/><label for="star5"></label>
                    <input type="radio" id="star4" name="rating" value="4" <?= ($pemesanan['rating_pengguna'] == 4) ? 'checked' : '' ?>/><label for="star4"></label>
                    <input type="radio" id="star3" name="rating" value="3" <?= ($pemesanan['rating_pengguna'] == 3) ? 'checked' : '' ?>/><label for="star3"></label>
                    <input type="radio" id="star2" name="rating" value="2" <?= ($pemesanan['rating_pengguna'] == 2) ? 'checked' : '' ?>/><label for="star2"></label>
                    <input type="radio" id="star1" name="rating" value="1" <?= ($pemesanan['rating_pengguna'] == 1) ? 'checked' : '' ?>/><label for="star1"></label>
                </div>
            </div>

            <div class="form-group">
                <label for="review_pelanggan">Ulasan Anda</label>
                <textarea id="review_pelanggan" name="review_pelanggan" rows="5" required><?= htmlspecialchars($pemesanan['review_pelanggan'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary"><?= ($mode === 'tambah') ? 'Kirim Ulasan' : 'Simpan Perubahan' ?></button>
            <a href="<?= BASE_URL ?>pelanggan/history.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>