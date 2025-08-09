<?php
// File: actions/pesan/balas.php (Versi Universal Dua Arah)
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan', 'Pelanggan']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Akses tidak sah'); }

$parent_id = (int)$_POST['parent_id'];
$id_penerima_asli = (int)$_POST['id_penerima_asli']; // ID pelanggan yang memulai percakapan
$id_pengirim = $_SESSION['id_pengguna'];
$role_pengirim = $_SESSION['role'];
$isi_pesan = trim($_POST['isi_pesan']);

if (empty($isi_pesan) || $parent_id === 0) {
    redirect_with_message("detail.php?id=$parent_id", 'Balasan tidak boleh kosong.', 'error');
}

// Tentukan siapa penerima balasan ini
$id_penerima = null;
if (in_array($role_pengirim, ['Admin', 'Karyawan'])) {
    // Jika admin/karyawan yang balas, penerimanya adalah pelanggan asli
    $id_penerima = $id_penerima_asli;
}
// Jika pelanggan yang balas, penerima bisa dikosongkan (ditujukan untuk staff)

try {
    $pdo->beginTransaction();
    // 1. Simpan pesan balasan
    $sql_reply = "INSERT INTO pesan_bantuan (id_pengirim, id_penerima, parent_id, isi_pesan, subjek) VALUES (?, ?, ?, ?, ?)";
    $stmt_reply = $pdo->prepare($sql_reply);
    $stmt_reply->execute([$id_pengirim, $id_penerima, $parent_id, $isi_pesan, 'Re:']); // Subjek diisi 'Re:'

    // 2. Update status pesan utama menjadi 'Dibalas' (jika admin/karyawan yg balas) atau kembali 'Belum Dibaca' (jika pelanggan yg balas)
    $status_baru = in_array($role_pengirim, ['Admin', 'Karyawan']) ? 'Dibalas' : 'Belum Dibaca';
    $sql_update = "UPDATE pesan_bantuan SET status_pesan = ?, updated_at = NOW() WHERE id_pesan = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$status_baru, $parent_id]);
    $pdo->commit();

    redirect_with_message("detail.php?id=$parent_id", 'Balasan berhasil dikirim.');
} catch (PDOException $e) {
    $pdo->rollBack();
    redirect_with_message("detail.php?id=$parent_id", 'Gagal mengirim balasan: ' . $e->getMessage(), 'error');
}
?>