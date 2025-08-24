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
        <form action="kirim.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <!-- Dropdown -->
            <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?> 
            <div class="form-group">
                <label for="select-penerima">Kirim Ke</label>
                <select id="select-penerima" name="id_penerima" required style="width: 100%;">
                    </select>
            </div>
            <?php else: ?>
                 <p>Pesan baru akan dikirimkan ke administrator untuk ditinjau.</p>
            <?php endif; ?>

            <div class="form-group"><label for="subjek">Subjek</label><input type="text" id="subjek" name="subjek" required></div>
            <div class="form-group"><label for="isi_pesan">Pesan Anda</label><textarea id="isi_pesan" name="isi_pesan" rows="6" required></textarea></div>
            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
            <a href="inbox.php" class="btn btn-secondary">Kembali ke Inbox</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPenerima = $('#select-penerima');
    if (selectPenerima.length) {
        selectPenerima.select2({
            placeholder: 'Ketik nama atau username pengguna...',
            allowClear: true,
            ajax: {
                url: '<?= BASE_URL ?>actions/pengguna/cari.php',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            }
        });
    }
});
</script>