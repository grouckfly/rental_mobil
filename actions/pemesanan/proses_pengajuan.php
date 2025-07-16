<?php
// File: actions/pemesanan/proses_pengajuan.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    redirect_with_message(BASE_URL, 'Akses tidak sah', 'error');
 }

$id_pemesanan = (int)$_POST['id_pemesanan'];
$keputusan = $_POST['keputusan'];

try {
    // Ambil data pemesanan saat ini
    $stmt = $pdo->prepare("SELECT * FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pemesanan = ?");
    $stmt->execute([$id_pemesanan]);
    $pemesanan = $stmt->fetch();

    if ($keputusan === 'setuju') {
        // Kalkulasi ulang biaya
        $durasi_baru = hitung_durasi_sewa($pemesanan['tgl_mulai_diajukan'], $pemesanan['tanggal_selesai']);
        $biaya_baru = $durasi_baru * $pemesanan['harga_sewa_harian'];

        $sql = "UPDATE pemesanan SET tanggal_mulai = tgl_mulai_diajukan, total_biaya = ?, status_pemesanan = 'Dikonfirmasi', tgl_mulai_diajukan = NULL WHERE id_pemesanan = ?";
        $params = [$biaya_baru, $id_pemesanan];
        $pesan = 'Pengajuan disetujui. Jadwal dan biaya telah diperbarui.';
    } else { // Jika ditolak
        $sql = "UPDATE pemesanan SET status_pemesanan = 'Dikonfirmasi', tgl_mulai_diajukan = NULL WHERE id_pemesanan = ?";
        $params = [$id_pemesanan];
        $pesan = 'Pengajuan telah ditolak. Jadwal kembali seperti semula.';
    }

    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute($params);
    redirect_with_message("detail.php?id=$id_pemesanan", $pesan);

} catch (PDOException $e) { 
    redirect_with_message("detail.php?id=$id_pemesanan", 'Terjadi kesalahan saat memproses pengajuan: ' . $e->getMessage(), 'error');
 }
?>