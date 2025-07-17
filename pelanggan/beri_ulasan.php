<?php
// File: pelanggan/beri_ulasan.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pemesanan === 0) {
    redirect_with_message('history.php', 'ID Pemesanan tidak valid.', 'error');
}

try {
    // Ambil data pemesanan untuk memastikan valid dan belum diulas
    $stmt = $pdo->prepare("
        SELECT p.id_pemesanan, p.kode_pemesanan, p.review_pelanggan, p.status_pemesanan, m.merk, m.model, m.gambar_mobil
        FROM pemesanan p 
        JOIN mobil m ON p.id_mobil = m.id_mobil 
        WHERE p.id_pemesanan = ? AND p.id_pengguna = ?
    ");
    $stmt->execute([$id_pemesanan, $_SESSION['id_pengguna']]);
    $pemesanan = $stmt->fetch();

    // Validasi: Pesanan harus ada, statusnya 'Selesai', dan belum pernah diulas
    if (!$pemesanan) {
        redirect_with_message('history.php', 'Pemesanan tidak ditemukan.', 'error');
    }
    if ($pemesanan['status_pemesanan'] !== 'Selesai') {
        redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Anda hanya bisa memberi ulasan untuk pesanan yang sudah selesai.', 'error');
    }
    if (!empty($pemesanan['review_pelanggan'])) {
        redirect_with_message(BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan", 'Anda sudah pernah memberikan ulasan untuk pesanan ini.', 'error');
    }

} catch (PDOException $e) {
    redirect_with_message('history.php', 'Error database.', 'error');
}

$page_title = 'Beri Ulasan';
require_once '../includes/header.php';
?>

<div class="page-header"><h1>Beri Ulasan</h1></div>

<div class="form-container">
    <div class="form-box">
        <h4>Ulasan untuk Pemesanan #<?= htmlspecialchars($pemesanan['kode_pemesanan']) ?></h4>
        <div class="info-item-row">
            <img src="<?= BASE_URL ?>uploads/mobil/<?= htmlspecialchars($pemesanan['gambar_mobil'] ?: 'default-car.png') ?>" alt="Mobil" class="info-item-image">
            <div><strong><?= htmlspecialchars($pemesanan['merk'] . ' ' . $pemesanan['model']) ?></strong></div>
        </div>
        <hr>

        <form action="<?= BASE_URL ?>actions/pemesanan/simpan_ulasan.php" method="POST">
            <input type="hidden" name="id_pemesanan" value="<?= $id_pemesanan ?>">
            
            <div class="form-group">
                <label>Rating Anda</label>
                <div class="rating">
                    <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" title="5 stars"></label>
                    <input type="radio" id="star4" name="rating" value="4"/><label for="star4" title="4 stars"></label>
                    <input type="radio" id="star3" name="rating" value="3"/><label for="star3" title="3 stars"></label>
                    <input type="radio" id="star2" name="rating" value="2"/><label for="star2" title="2 stars"></label>
                    <input type="radio" id="star1" name="rating" value="1"/><label for="star1" title="1 star"></label>
                </div>
            </div>

            <div class="form-group">
                <label for="review_pelanggan">Ulasan Anda</label>
                <textarea id="review_pelanggan" name="review_pelanggan" rows="5" placeholder="Bagaimana pengalaman Anda menyewa mobil ini?" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
            <a href="<?= BASE_URL ?>pelanggan/history.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>