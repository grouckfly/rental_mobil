<?php
// File: actions/pesan/kirim.php (Versi Dinamis)
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Akses tidak sah'); }

$id_pengirim = $_SESSION['id_pengguna'];
$subjek = trim($_POST['subjek']);
$isi_pesan = trim($_POST['isi_pesan']);

// PERBAIKAN: Ambil id_penerima jika ada (dari admin/karyawan), jika tidak, set NULL (dari pelanggan)
$id_penerima = isset($_POST['id_penerima']) ? (int)$_POST['id_penerima'] : null;

if (empty($subjek) || empty($isi_pesan)) {
    redirect_with_message('tulis.php', 'Subjek dan pesan tidak boleh kosong.', 'error');
}
// Jika admin/karyawan, pastikan penerima dipilih
if (in_array($_SESSION['role'], ['Admin', 'Karyawan']) && empty($id_penerima)) {
    redirect_with_message('tulis.php', 'Anda harus memilih penerima pesan.', 'error');
}

try {
    // PERBAIKAN: Sertakan id_penerima dalam query
    $sql = "INSERT INTO pesan_bantuan (id_pengirim, id_penerima, subjek, isi_pesan) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_pengirim, $id_penerima, $subjek, $isi_pesan]);

    redirect_with_message('inbox.php', 'Pesan Anda telah berhasil terkirim.');
} catch (PDOException $e) {
    redirect_with_message('tulis.php', 'Gagal mengirim pesan: ' . $e->getMessage(), 'error');
}
?>