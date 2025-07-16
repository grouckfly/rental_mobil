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
    // Ambil data pengajuan
    $stmt = $pdo->prepare("SELECT * FROM pemesanan WHERE id_pemesanan = ?");
    $stmt->execute([$id_pemesanan]);
    $pemesanan = $stmt->fetch();
    if (!$pemesanan) { redirect_with_message("detail.php?id=$id_pemesanan", 'Pemesanan tidak ditemukan.', 'error'); }

    // Jika KEPUTUSAN DITOLAK
    if ($keputusan === 'tolak') {
        $sql = "UPDATE pemesanan SET status_pemesanan = 'Pengajuan Ditolak', tgl_mulai_diajukan = NULL, total_biaya_diajukan = NULL WHERE id_pemesanan = ?";
        $params = [$id_pemesanan];
        $pesan = 'Pengajuan telah ditolak.';
    } 
    // Jika KEPUTUSAN DISETUJUI
    elseif ($keputusan === 'setuju') {
        $id_mobil = $pemesanan['id_mobil'];
        $waktu_mulai_baru = $pemesanan['tgl_mulai_diajukan'];
        $waktu_selesai_lama = $pemesanan['tanggal_selesai'];

        // RULE 4: Lakukan pengecekan jadwal bentrok
        $stmt_konflik = $pdo->prepare(
            "SELECT id_pemesanan FROM pemesanan 
             WHERE id_mobil = ? 
             AND id_pemesanan != ? 
             AND status_pemesanan IN ('Dikonfirmasi', 'Berjalan')
             AND ? < tanggal_selesai AND ? > tanggal_mulai"
        );
        $stmt_konflik->execute([$id_mobil, $id_pemesanan, $waktu_mulai_baru, $waktu_selesai_lama]);
        
        if ($stmt_konflik->fetch()) {
            // JIKA ADA JADWAL BENTROK
            redirect_with_message("detail.php?id=$id_pemesanan", 'Gagal! Jadwal yang diajukan bentrok dengan pemesanan lain.', 'error');
        }

        // JIKA AMAN, LANJUTKAN PERSETUJUAN
        $sql = "UPDATE pemesanan SET 
                    tanggal_mulai = ?, 
                    total_biaya = ?, 
                    status_pemesanan = 'Dikonfirmasi', 
                    tgl_mulai_diajukan = NULL, 
                    total_biaya_diajukan = NULL 
                WHERE id_pemesanan = ?";
        $params = [$pemesanan['tgl_mulai_diajukan'], $pemesanan['total_biaya_diajukan'], $id_pemesanan];
        $pesan = 'Pengajuan disetujui. Jadwal dan biaya telah diperbarui.';
    }

    // Eksekusi query update
    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute($params);
    redirect_with_message("detail.php?id=$id_pemesanan", $pesan);

} catch (PDOException $e) { 
    redirect_with_message("detail.php?id=$id_pemesanan", 'Terjadi kesalahan saat memproses pengajuan: ' . $e->getMessage(), 'error');
 }
?>