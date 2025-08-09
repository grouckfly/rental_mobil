<?php
// File: actions/pesan/inbox.php (Inbox Universal)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan', 'Pelanggan']);
$page_title = 'Kotak Pesan';
require_once '../../includes/header.php';

$id_pengguna_session = $_SESSION['id_pengguna'];
$role_session = $_SESSION['role'];

try {
    $sql = "
        SELECT DISTINCT p_main.*, u_pengirim.nama_lengkap AS nama_pengirim
        FROM pesan_bantuan AS p_main
        JOIN pengguna AS u_pengirim ON p_main.id_pengirim = u_pengirim.id_pengguna
    ";
    $params = [];

    // Jika yang login adalah PELANGGAN, batasi hanya untuk percakapan mereka
    if ($role_session === 'Pelanggan') {
        $sql .= "
            LEFT JOIN pesan_bantuan AS p_reply ON p_main.id_pesan = p_reply.parent_id
            WHERE p_main.parent_id IS NULL 
            AND (p_main.id_pengirim = ? OR p_reply.id_penerima = ?)
        ";
        $params[] = $id_pengguna_session;
        $params[] = $id_pengguna_session;
    } else { // Jika ADMIN atau KARYAWAN, tampilkan semua percakapan
        $sql .= " WHERE p_main.parent_id IS NULL";
    }

    $sql .= " ORDER BY p_main.updated_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pesan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $pesan_list = [];
}
?>

<div class="page-header with-action">
    <h1>Kotak Pesan</h1>
    <a href="tulis.php" class="btn btn-primary">Tulis Pesan Baru</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Subjek</th>
                <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
                    <th>Dari</th>
                <?php endif; ?>
                <th>Update Terakhir</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pesan_list)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada percakapan.</td>
                </tr>
                <?php else: foreach ($pesan_list as $pesan):
                    // Tentukan apakah pesan ini belum dibaca oleh pengguna saat ini
                    $is_unread = ($pesan['status_pesan'] === 'Belum Dibaca');
                    // Untuk admin/karyawan, pesan dianggap belum dibaca jika statusnya "Belum Dibaca"
                    // Untuk pelanggan, pesan dianggap belum dibaca jika statusnya "Dibalas" (artinya ada balasan dari admin)
                    if ($role_session === 'Pelanggan') {
                        $is_unread = ($pesan['status_pesan'] === 'Dibalas');
                    }
                ?>
                    <tr style="<?= $is_unread ? 'font-weight: bold;' : '' ?>">
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $pesan['status_pesan'])) ?>"><?= $pesan['status_pesan'] ?></span></td>
                        <td><?= htmlspecialchars($pesan['subjek']) ?></td>
                        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?>
                            <td><?= htmlspecialchars($pesan['nama_pengirim']) ?></td>
                        <?php endif; ?>
                        <td><?= date('d M Y, H:i', strtotime($pesan['updated_at'])) ?></td>
                        <td>
                            <a href="detail.php?id=<?= $pesan['id_pesan'] ?>" class="btn btn-info btn-sm">Lihat</a>

                            <form action="hapus.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Anda yakin ingin menghapus seluruh percakapan ini? Tindakan ini tidak bisa dibatalkan.');">
                                <input type="hidden" name="id_pesan" value="<?= $pesan['id_pesan'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
            <?php endforeach;
            endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>