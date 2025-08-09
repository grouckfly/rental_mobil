<?php
// File: actions/pesan/detail.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$id_pesan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Update status menjadi 'Sudah Dibaca' saat pesan dibuka
    $stmt_update = $pdo->prepare("UPDATE pesan_bantuan SET status_pesan = 'Sudah Dibaca' WHERE id_pesan = ? AND status_pesan = 'Belum Dibaca'");
    $stmt_update->execute([$id_pesan]);

    // Ambil pesan utama dari pelanggan
    $stmt_main = $pdo->prepare("SELECT p.*, u.nama_lengkap, u.email FROM pesan_bantuan p JOIN pengguna u ON p.id_pengirim = u.id_pengguna WHERE p.id_pesan = ?");
    $stmt_main->execute([$id_pesan]);
    $pesan = $stmt_main->fetch();

    // Ambil riwayat balasan untuk pesan ini
    $stmt_replies = $pdo->prepare("SELECT p.*, u.nama_lengkap FROM pesan_bantuan p JOIN pengguna u ON p.id_pengirim = u.id_pengguna WHERE p.parent_id = ? ORDER BY p.waktu_kirim ASC");
    $stmt_replies->execute([$id_pesan]);
    $balasan = $stmt_replies->fetchAll();
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = 'Detail Pesan';
require_once '../../includes/header.php';
?>
<div class="page-header">
    <h1>Detail Pesan</h1>
</div>

<div class="message-thread">
    <div class="message-card customer-message">
        <div class="message-header">
            <strong><?= htmlspecialchars($pesan['nama_lengkap']) ?></strong>
            <small><?= date('d M Y, H:i', strtotime($pesan['waktu_kirim'])) ?></small>
        </div>
        <div class="message-body">
            <h4><?= htmlspecialchars($pesan['subjek']) ?></h4>
            <p><?= nl2br(htmlspecialchars($pesan['isi_pesan'])) ?></p>
        </div>
    </div>

    <?php foreach ($balasan as $balas): ?>
        <div class="message-card admin-message">
            <div class="message-header">
                <strong><?= htmlspecialchars($balas['nama_lengkap']) ?> (Staff)</strong>
                <small><?= date('d M Y, H:i', strtotime($balas['waktu_kirim'])) ?></small>
            </div>
            <div class="message-body">
                <p><?= nl2br(htmlspecialchars($balas['isi_pesan'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="message-reply-form">
        <hr>
        <h4>Balas Pesan</h4>
        <form action="balas.php" method="POST">
            <input type="hidden" name="parent_id" value="<?= $id_pesan ?>">
            <input type="hidden" name="id_penerima" value="<?= $pesan['id_pengirim'] ?>">
            <div class="form-group">
                <textarea name="isi_pesan" rows="5" required placeholder="Tulis balasan Anda di sini..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Balasan</button>
            <a href="<?= BASE_URL ?>admin/pesan.php" class="btn btn-secondary">Kembali ke Kotak Masuk</a>
        </form>
    </div>
</div>

<style>
    /* CSS untuk tampilan pesan */
    .message-thread {
        max-width: 800px;
        margin: auto;
    }

    .message-card {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        margin-bottom: 15px;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        background-color: var(--bg-color);
        padding: 10px 15px;
        border-bottom: 1px solid var(--border-color);
    }

    .message-body {
        padding: 15px;
    }

    .admin-message {
        border-left: 4px solid var(--primary-color);
    }

    .customer-message {
        border-left: 4px solid var(--secondary-color);
    }
</style>

<?php require_once '../../includes/footer.php'; ?>