<?php
// File: actions/pesan/detail.php (Versi Universal Dua Arah)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan', 'Pelanggan']);

$id_pesan_utama = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_pengguna_session = $_SESSION['id_pengguna'];
$role_session = $_SESSION['role'];

try {
    // 1. Ambil pesan utama (parent) untuk mendapatkan info dasar
    $stmt_main = $pdo->prepare("SELECT p.*, u.nama_lengkap FROM pesan_bantuan p JOIN pengguna u ON p.id_pengirim = u.id_pengguna WHERE p.id_pesan = ?");
    $stmt_main->execute([$id_pesan_utama]);
    $pesan_utama = $stmt_main->fetch();

    if (!$pesan_utama) { redirect_with_message(BASE_URL, 'Pesan tidak ditemukan.', 'error'); }

    // 2. Keamanan: Pastikan pelanggan hanya bisa melihat pesannya sendiri
    if ($role_session === 'Pelanggan' && $pesan_utama['id_pengirim'] !== $id_pengguna_session) {
        redirect_with_message(BASE_URL, 'Anda tidak memiliki akses ke pesan ini.', 'error');
    }

    // 3. Update status menjadi 'Sudah Dibaca' jika yang membuka adalah Admin/Karyawan
    if (in_array($role_session, ['Admin', 'Karyawan']) && $pesan_utama['status_pesan'] === 'Belum Dibaca') {
        $pdo->prepare("UPDATE pesan_bantuan SET status_pesan = 'Sudah Dibaca' WHERE id_pesan = ?")->execute([$id_pesan_utama]);
    }

    // 4. Ambil SEMUA pesan dalam percakapan ini (pesan utama + semua balasan)
    $stmt_thread = $pdo->prepare("
        SELECT p.*, u.nama_lengkap, u.role FROM pesan_bantuan p 
        JOIN pengguna u ON p.id_pengirim = u.id_pengguna 
        WHERE p.id_pesan = ? OR p.parent_id = ? 
        ORDER BY p.waktu_kirim ASC
    ");
    $stmt_thread->execute([$id_pesan_utama, $id_pesan_utama]);
    $semua_pesan = $stmt_thread->fetchAll();

} catch (PDOException $e) { die("Error: ".$e->getMessage()); }

$page_title = 'Detail Pesan: ' . htmlspecialchars($pesan_utama['subjek']);
require_once '../../includes/header.php';
?>

<div class="page-header sticky-page-header"><h1>Percakapan: <?= htmlspecialchars($pesan_utama['subjek']) ?></h1></div>

<div class="message-thread">
    <?php foreach ($semua_pesan as $pesan):
        // Tentukan style berdasarkan siapa pengirimnya
        $is_pengirim_session = ($pesan['id_pengirim'] === $id_pengguna_session);
        $card_class = $is_pengirim_session ? 'my-message' : 'their-message';
        $nama_pengirim = $is_pengirim_session ? 'Anda' : htmlspecialchars($pesan['nama_lengkap']);
        if (!$is_pengirim_session && in_array($pesan['role'], ['Admin', 'Karyawan'])) {
            $nama_pengirim .= ' (Staff)';
        }
    ?>
        <div class="message-card <?= $card_class ?>">
            <div class="message-header">
                <strong><?= $nama_pengirim ?></strong>
                <small><?= date('d M Y, H:i', strtotime($pesan['waktu_kirim'])) ?></small>
            </div>
            <div class="message-body">
                <p><?= nl2br(htmlspecialchars($pesan['isi_pesan'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="message-reply-form">
        <hr>
        <h4>Balas Percakapan</h4>
        <form action="balas.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="parent_id" value="<?= $id_pesan_utama ?>">
            <input type="hidden" name="id_penerima_asli" value="<?= $pesan_utama['id_pengirim'] ?>">
            <div class="form-group">
                <textarea name="isi_pesan" rows="5" required placeholder="Tulis balasan Anda..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Balasan</button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>