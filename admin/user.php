<?php
// File: admin/user.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Admin');

$page_title = 'Kelola Pengguna';
require_once '../includes/header.php';

// Mengambil semua data pengguna
try {
    $stmt = $pdo->query("SELECT id_pengguna, username, nama_lengkap, email, role, created_at FROM pengguna ORDER BY role, nama_lengkap");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>

<div class="page-header with-action">
    <h1>Kelola Data Pengguna</h1>
    <a href="tambah_user.php" class="btn btn-primary">Tambah Pengguna Baru</a>
</div>

<?php display_flash_message(); ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Role</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id_pengguna']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $user['id_pengguna'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <?php if ($user['id_pengguna'] !== $_SESSION['id_pengguna']): // Admin tidak bisa hapus diri sendiri ?>
                                <form action="hapus_user.php" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                    <input type="hidden" name="id_pengguna" value="<?= $user['id_pengguna'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Belum ada data pengguna.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>