<?php
// File: actions/pengguna/detail.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Admin');
$id_user = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_user === 0) { redirect_with_message('../../admin/user.php', 'ID tidak valid.', 'error'); }

try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch();
    if (!$user) { redirect_with_message('../../admin/user.php', 'Pengguna tidak ditemukan.', 'error'); }
} catch (PDOException $e) { redirect_with_message('../../admin/user.php', 'Error database.', 'error'); }

$page_title = 'Detail Pengguna: ' . htmlspecialchars($user['username']);
require_once '../../includes/header.php';
?>

<div class="page-header"><h1>Detail Pengguna</h1></div>

<div class="detail-container">
    <div class="detail-info">
        <h2><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
        <span class="status-badge status-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>

        <div class="info-grid">
            <div class="info-item"><span class="label">ID Pengguna</span><span class="value"><?= htmlspecialchars($user['id_pengguna']) ?></span></div>
            <div class="info-item"><span class="label">Username</span><span class="value"><?= htmlspecialchars($user['username']) ?></span></div>
            <div class="info-item"><span class="label">Email</span><span class="value"><?= htmlspecialchars($user['email']) ?></span></div>
            <div class="info-item"><span class="label">No. Telepon</span><span class="value"><?= htmlspecialchars($user['no_telp'] ?: '-') ?></span></div>
            <div class="info-item full-width"><span class="label">Alamat</span><div class="value description"><?= htmlspecialchars($user['alamat'] ?: '-') ?></div></div>
            <div class="info-item"><span class="label">Tanggal Daftar</span><span class="value"><?= date('d F Y, H:i', strtotime($user['created_at'])) ?></span></div>
        </div>

        <div class="detail-actions">
            <a href="../../admin/user.php" class="btn btn-secondary">Kembali ke Daftar</a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>