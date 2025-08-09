<?php
// File: actions/pesan/kirim.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Akses tidak sah'); }

$id_pengirim = $_SESSION['id_pengguna'];
$subjek = trim($_POST['subjek']);
$isi_pesan = trim($_POST['isi_pesan']);

if (empty($subjek) || empty($isi_pesan)) {
    redirect_with_message(BASE_URL . 'pelanggan/bantuan.php', 'Subjek dan pesan tidak boleh kosong.', 'error');
}

try {
    $sql = "INSERT INTO pesan_bantuan (id_pengirim, subjek, isi_pesan) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pengirim, $subjek, $isi_pesan]);
    redirect_with_message(BASE_URL . 'pelanggan/bantuan.php', 'Pesan Anda telah berhasil terkirim.');
} catch (PDOException $e) {
    redirect_with_message(BASE_URL . 'pelanggan/bantuan.php', 'Gagal mengirim pesan: ' . $e->getMessage(), 'error');
}
?>