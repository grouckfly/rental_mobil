<?php
// File: admin/pesan.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$page_title = 'Kotak Masuk Pesan';
require_once '../includes/header.php';

try {
    $stmt = $pdo->query("SELECT p.*, u.nama_lengkap, u.email FROM pesan_bantuan p JOIN pengguna u ON p.id_pengirim = u.id_pengguna ORDER BY p.waktu_kirim DESC");
    $pesan_list = $stmt->fetchAll();
} catch (PDOException $e) { $pesan_list = []; }
?>

<div class="page-header"><h1>Kotak Masuk Pesan Bantuan</h1></div>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Dari</th>
                <th>Subjek</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pesan_list)): ?>
                <tr><td colspan="5">Tidak ada pesan masuk.</td></tr>
            <?php else: foreach ($pesan_list as $pesan): ?>
                <tr style="<?= ($pesan['status_pesan'] === 'Belum Dibaca') ? 'font-weight: bold;' : '' ?>">
                    <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pesan['status_pesan'])) ?>"><?= $pesan['status_pesan'] ?></span></td>
                    <td><?= htmlspecialchars($pesan['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($pesan['subjek']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($pesan['waktu_kirim'])) ?></td>
                    <td><a href="#" class="btn btn-info btn-sm">Lihat & Balas</a></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php require_once '../includes/footer.php'; ?>