<?php
// File: admin/user.php

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth('Admin');
$page_title = 'Kelola Pengguna';
require_once '../includes/header.php';

// Ambil parameter filter dari URL
$search_query = $_GET['q'] ?? '';
$role_filter = $_GET['role'] ?? '';

// LOGIKA QUERY DINAMIS
$sql = "SELECT DISTINCT u.* FROM pengguna u
        LEFT JOIN pemesanan p ON u.id_pengguna = p.id_pengguna
        LEFT JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE 1=1";
$params = [];

// Terapkan filter pencarian teks
if (!empty($search_query)) {
    $sql .= " AND (u.nama_lengkap LIKE :q OR u.username LIKE :q OR u.email LIKE :q OR u.nik LIKE :q OR p.kode_pemesanan LIKE :q OR m.merk LIKE :q OR m.model LIKE :q)";
    $params[':q'] = "%$search_query%";
}

// Terapkan filter role
if (!empty($role_filter)) {
    // Jika user memilih 'Pengguna Dihapus', cari nama_lengkap yang spesifik
    if ($role_filter === 'dihapus') {
        $sql .= " AND u.nama_lengkap = 'Pengguna Dihapus oleh Admin'";
    } else {
        $sql .= " AND u.role = :role";
        $params[':role'] = $role_filter;
    }
} else {
    // PERBAIKAN: Secara default, sembunyikan pengguna yang sudah dihapus
    $sql .= " AND u.nama_lengkap NOT LIKE 'Pengguna Dihapus%'";
}

$sql .= " ORDER BY u.role, u.nama_lengkap";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Daftar role untuk dropdown
$role_list = ['Admin', 'Karyawan', 'Pelanggan'];
?>

<div class="page-header with-action">
    <h1>Kelola Data Pengguna</h1>
    <a href="../actions/pengguna/tambah.php" class="btn btn-primary">Tambah Pengguna Baru</a>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        <div class="form-group" style="flex-grow: 1;">
            <label>Cari Pengguna</label>
            <input type="text" name="q" placeholder="Cari berdasarkan Nama, NIK, dll..." value="<?= htmlspecialchars($search_query) ?>" class="form-control">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="">Semua</option>
                <?php foreach ($role_list as $role): ?>
                    <option value="<?= $role ?>" <?= ($role_filter === $role) ? 'selected' : '' ?>><?= $role ?></option>
                <?php endforeach; ?>
                <option value="dihapus" <?= ($role_filter === 'dihapus') ? 'selected' : '' ?>>Pengguna Dihapus</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="user.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Role</th>
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
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $user['role'])) ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                        <td>
                            <?php
                            if ($user['id_pengguna'] === $_SESSION['id_pengguna']):
                            ?>
                                <a href="<?= BASE_URL ?>pelanggan/profile.php" class="btn btn-info btn-sm">Profil Saya</a>
                            <?php else: ?>
                                <a href="../actions/pengguna/detail.php?id=<?= $user['id_pengguna'] ?>" class="btn btn-info btn-sm">Detail</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Tidak ada pengguna yang ditemukan sesuai kriteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>