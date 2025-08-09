<?php
// File: pelanggan/detail_pesan.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Pelanggan');
$id_pesan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Ambil pesan utama dari pelanggan (pastikan milik user yg login)
    $stmt_main = $pdo->prepare("SELECT * FROM pesan_bantuan WHERE id_pesan = ? AND id_pengirim = ?");
    $stmt_main->execute([$id_pesan, $_SESSION['id_pengguna']]);
    $pesan = $stmt_main->fetch();
    if (!$pesan) {
        redirect_with_message('bantuan.php', 'Pesan tidak ditemukan.', 'error');
    }

    // Ambil riwayat balasan
    $stmt_replies = $pdo->prepare("SELECT p.*, u.nama_lengkap FROM pesan_bantuan p JOIN pengguna u ON p.id_pengirim = u.id_pengguna WHERE p.parent_id = ? ORDER BY p.waktu_kirim ASC");
    $stmt_replies->execute([$id_pesan]);
    $balasan = $stmt_replies->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Detail Pesan';
require_once '../includes/header.php';
?>
<div class="page-header">
    <h1>Detail Pesan: <?= htmlspecialchars($pesan['subjek']) ?></h1>
</div>

<div class="message-thread">
    <div class="message-card customer-message">
        <div class="message-header"><strong>Anda</strong><small><?= date('d M Y, H:i', strtotime($pesan['waktu_kirim'])) ?></small></div>
        <div class="message-body">
            <p><?= nl2br(htmlspecialchars($pesan['isi_pesan'])) ?></p>
        </div>
    </div>
    <?php foreach ($balasan as $balas): ?>
        <div class="message-card admin-message">
            <div class="message-header"><strong><?= htmlspecialchars($balas['nama_lengkap']) ?> (Staff)</strong><small><?= date('d M Y, H:i', strtotime($balas['waktu_kirim'])) ?></small></div>
            <div class="message-body">
                <p><?= nl2br(htmlspecialchars($balas['isi_pesan'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<a href="bantuan.php" class="btn btn-secondary" style="margin-top:20px;">Kembali</a>

<?php require_once '../includes/footer.php'; ?>