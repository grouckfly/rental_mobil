<?php
// File: actions/pesan/balas.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Akses tidak sah'); }

$parent_id = (int)$_POST['parent_id'];
$id_penerima = (int)$_POST['id_penerima'];
$id_pengirim = $_SESSION['id_pengguna'];
$isi_pesan = trim($_POST['isi_pesan']);

if (empty($isi_pesan) || $parent_id === 0) {
    redirect_with_message("detail.php?id=$parent_id", 'Balasan tidak boleh kosong.', 'error');
}

try {
    $pdo->beginTransaction();
    // 1. Simpan pesan balasan
    $sql_reply = "INSERT INTO pesan_bantuan (id_pengirim, id_penerima, parent_id, isi_pesan) VALUES (?, ?, ?, ?)";
    $stmt_reply = $pdo->prepare($sql_reply);
    $stmt_reply->execute([$id_pengirim, $id_penerima, $parent_id, $isi_pesan]);

    // 2. Update status pesan utama menjadi 'Dibalas'
    $sql_update = "UPDATE pesan_bantuan SET status_pesan = 'Dibalas' WHERE id_pesan = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$parent_id]);
    $pdo->commit();

    redirect_with_message("detail.php?id=$parent_id", 'Balasan berhasil dikirim.');
} catch (PDOException $e) {
    $pdo->rollBack();
    redirect_with_message("detail.php?id=$parent_id", 'Gagal mengirim balasan: ' . $e->getMessage(), 'error');
}
?>