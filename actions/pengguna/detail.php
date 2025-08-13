<?php
// File: actions/pengguna/detail.php (Versi Disempurnakan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$id_user = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_user === 0) {
    redirect_with_message('../../admin/user.php', 'ID pengguna tidak valid.', 'error');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

try {
    // Ambil data utama pengguna
    $stmt_user = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt_user->execute([$id_user]);
    $user = $stmt_user->fetch();
    if (!$user) {
        redirect_with_message('../../admin/user.php', 'Pengguna tidak ditemukan.', 'error');
    }

    // Ambil ringkasan statistik pemesanan pengguna
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as jumlah_pesanan, 
            SUM(total_biaya + total_denda) as total_belanja, 
            MAX(tanggal_pemesanan) as pesanan_terakhir 
        FROM pemesanan 
        WHERE id_pengguna = ? AND status_pemesanan = 'Selesai'
    ");
    $stmt_stats->execute([$id_user]);
    $user_stats = $stmt_stats->fetch();

    // Ambil 5 riwayat pemesanan terakhir dari pengguna ini
    $stmt_history = $pdo->prepare("SELECT p.id_pemesanan, p.kode_pemesanan, p.status_pemesanan, p.tanggal_pemesanan, m.merk, m.model FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pengguna = ? ORDER BY p.tanggal_pemesanan DESC LIMIT 5");
    $stmt_history->execute([$id_user]);
    $user_history = $stmt_history->fetchAll();
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

$page_title = 'Detail Pengguna: ' . htmlspecialchars($user['username']);
require_once '../../includes/header.php';
?>

<div class="page-top-bar">
    <div class="page-header">
        <h1>Detail Pengguna</h1>
    </div>
    <div class="detail-actions">

        <a href="edit.php?id=<?= $user['id_pengguna'] ?>" class="btn btn-secondary">Edit Profil</a>

        <?php // Tombol hanya muncul jika tidak melihat profil sendiri
        if ($user['id_pengguna'] !== $_SESSION['id_pengguna']): ?>
            <a href="../pesan/mulai_percakapan.php?id=<?= $user['id_pengguna'] ?>" class="btn btn-primary">Kirim Pesan</a>

            <form action="hapus.php" method="POST" style="display:inline;" onsubmit="return confirm('Peringatan: Yakin ingin menghapus pengguna ini?');">

                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <input type="hidden" name="id_pengguna" value="<?= $user['id_pengguna'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
        <?php else: ?>
            <a href="../../pelanggan/profile.php" class="btn btn-primary">Edit Profil</a>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Total Pesanan</h3>
        <p class="widget-data"><?= (int)($user_stats['jumlah_pesanan'] ?? 0) ?></p>
        <div class="widget-details"><span>Dari transaksi yang selesai</span></div>
    </div>
    <div class="widget">
        <h3>Total Pengeluaran</h3>
        <p class="widget-data price"><?= format_rupiah($user_stats['total_belanja'] ?? 0) ?></p>
        <div class="widget-details"><span>Termasuk denda</span></div>
    </div>
    <div class="widget">
        <h3>Aktivitas Terakhir</h3>
        <p class="widget-data-small"><?= $user_stats['pesanan_terakhir'] ? date('d M Y', strtotime($user_stats['pesanan_terakhir'])) : 'Belum Ada' ?></p>
        <div class="widget-details"><span>Tanggal pemesanan terakhir</span></div>
    </div>
</div>

<div class="detail-container">
    <div class="detail-main">
        <h3>Informasi Akun & Pribadi</h3>
        <div class="info-grid">
            <div class="info-item"><span class="label">ID</span><span class="value"><?= htmlspecialchars($user['id_pengguna']) ?></span></div>
            <div class="info-item"><span class="label">Username</span><span class="value"><?= htmlspecialchars($user['username']) ?></span></div>
            <div class="info-item"><span class="label">Role</span><span class="value"><span class="status-badge status-<?= strtolower(trim($user['role'])) ?>"><?= htmlspecialchars($user['role']) ?></span></span></div>
            <div class="info-item"><span class="label">Tgl Daftar</span><span class="value"><?= date('d M Y', strtotime($user['created_at'])) ?></span></div>
            <div class="info-item"><span class="label">Nama Lengkap</span><span class="value"><?= htmlspecialchars($user['nama_lengkap']) ?></span></div>
            <div class="info-item"><span class="label">NIK</span><span class="value"><?= htmlspecialchars($user['nik'] ?: '-') ?></span></div>
            <div class="info-item"><span class="label">Email</span><span class="value"><?= htmlspecialchars($user['email']) ?></span></div>
            <div class="info-item"><span class="label">No. Telepon</span><span class="value"><?= htmlspecialchars($user['no_telp'] ?: '-') ?></span></div>
        </div>
    </div>

    <div class="detail-sidebar">
        <h3>Dokumen & Riwayat</h3>
        <div class="info-item">
            <span class="label">Foto KTP</span>
            <div class="value">
                <?php if (!empty($user['foto_ktp'])): ?>
                    <a href="<?= BASE_URL ?>assets/img/ktp/<?= htmlspecialchars($user['foto_ktp']) ?>" target="_blank"><img src="<?= BASE_URL ?>uploads/ktp/<?= htmlspecialchars($user['foto_ktp']) ?>" alt="Foto KTP" style="max-width: 200px; border-radius: 5px;"></a>
                <?php else: ?>
                    Belum diunggah
                <?php endif; ?>
            </div>
        </div>
        <div class="info-item">
            <span class="label">5 Pesanan Terakhir</span>
            <div class="history-list">
                <?php if (empty($user_history)): ?>
                    <p>Pengguna ini belum memiliki riwayat pemesanan.</p>
                    <?php else: foreach ($user_history as $history): ?>
                        <div class="history-item">
                            <a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>">
                                <strong><?= htmlspecialchars($history['kode_pemesanan']) ?></strong>
                                <small><?= date('d M Y', strtotime($history['tanggal_pemesanan'])) ?> - <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></small>
                            </a>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .widget-data-small {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 10px 0;
    }

    .history-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 5px;
    }

    .history-item a {
        display: block;
        padding: 10px;
        background-color: var(--bg-color);
        border-radius: 5px;
        text-decoration: none;
        color: var(--text-color);
        border-left: 3px solid var(--info-color);
    }

    .history-item a:hover {
        background-color: #e9ecef;
    }

    .history-item a strong {
        font-size: 1rem;
    }

    .history-item a small {
        display: block;
        font-size: 0.8rem;
        color: var(--muted-text-color);
        margin-top: 5px;
    }

    .history-item .status-badge {
        font-size: 0.7rem;
        padding: 2px 6px;
    }
</style>

<?php require_once '../../includes/footer.php'; ?>