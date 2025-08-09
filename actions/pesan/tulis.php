<?php
// File: actions/pesan/tulis.php (Universal)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(); // Semua role yang login bisa mengakses
$page_title = 'Tulis Pesan Baru';
require_once '../../includes/header.php';
?>

<div class="page-header"><h1>Tulis Pesan Baru</h1></div>

<div class="form-container">
    <div class="form-box">
        <p>Pesan baru akan dikirimkan ke administrator untuk ditinjau.</p>
        <form action="kirim.php" method="POST">
            <div class="form-group"><label for="subjek">Subjek</label><input type="text" id="subjek" name="subjek" required></div>
            <div class="form-group"><label for="isi_pesan">Pesan Anda</label><textarea id="isi_pesan" name="isi_pesan" rows="6" required></textarea></div>
            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
            <a href="inbox.php" class="btn btn-secondary">Kembali ke Inbox</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>