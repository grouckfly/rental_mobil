<?php
// File: pelanggan/bantuan.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');

$stmt_history = $pdo->prepare("SELECT * FROM pesan_bantuan WHERE id_pengirim = ? AND parent_id IS NULL ORDER BY waktu_kirim DESC");
$stmt_history->execute([$_SESSION['id_pengguna']]);
$pesan_history = $stmt_history->fetchAll();

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

<div class="history-container">
    <h3>Riwayat Pesan Anda</h3>
    <div class="table-container">
        <table>
            <thead><tr><th>Subjek</th><th>Status</th><th>Terakhir Update</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php if(empty($pesan_history)): ?>
                    <tr><td colspan="4">Anda belum pernah mengirim pesan.</td></tr>
                <?php else: foreach($pesan_history as $pesan): ?>
                    <tr>
                        <td><?= htmlspecialchars($pesan['subjek']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pesan['status_pesan'])) ?>"><?= $pesan['status_pesan'] ?></span></td>
                        <td><?= date('d M Y', strtotime($pesan['waktu_kirim'])) ?></td>
                        <td><a href="detail_pesan.php?id=<?= $pesan['id_pesan'] ?>" class="btn btn-secondary btn-sm">Lihat</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>