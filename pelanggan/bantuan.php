<?php
// File: pelanggan/bantuan.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');
$page_title = 'Pusat Bantuan';
require_once '../includes/header.php';
?>

<div class="page-header"><h1>Pusat Bantuan</h1></div>

<div class="form-container">
    <div class="form-box">
        <p>Punya pertanyaan atau kendala? Kirimkan pesan kepada kami melalui form di bawah ini.</p>
        <form action="<?= BASE_URL ?>actions/pesan/kirim.php" method="POST">
            <div class="form-group">
                <label for="subjek">Subjek</label>
                <input type="text" id="subjek" name="subjek" required>
            </div>
            <div class="form-group">
                <label for="isi_pesan">Pesan Anda</label>
                <textarea id="isi_pesan" name="isi_pesan" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>