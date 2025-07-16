<?php
// File: actions/pemesanan/ajukan_ambil_cepat.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect_with_message(BASE_URL, 'Akses tidak sah', 'error'); }

$id_pemesanan = (int)$_POST['id_pemesanan'];
$tgl_mulai_baru = $_POST['tgl_mulai_baru'];

// Validasi
if (empty($tgl_mulai_baru)) { redirect_with_message(BASE_URL."pelanggan/ajukan_ambil_cepat.php?id=$id_pemesanan", 'Tanggal baru harus diisi.', 'error'); }
if (new DateTime($tgl_mulai_baru) < new DateTime()) { redirect_with_message(BASE_URL."pelanggan/ajukan_ambil_cepat.php?id=$id_pemesanan", 'Waktu tidak boleh di masa lalu.', 'error'); }

try {
    $sql = "UPDATE pemesanan SET status_pemesanan = 'Pengajuan Ambil Cepat', tgl_mulai_diajukan = ? WHERE id_pemesanan = ? AND id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tgl_mulai_baru, $id_pemesanan, $_SESSION['id_pengguna']]);
    redirect_with_message(BASE_URL."actions/pemesanan/detail.php?id=$id_pemesanan", 'Pengajuan berhasil dikirim dan sedang menunggu persetujuan admin.');
} catch (PDOException $e) { 
    redirect_with_message(BASE_URL."pelanggan/ajukan_ambil_cepat.php?id=$id_pemesanan", 'Terjadi kesalahan saat mengajukan pengambilan cepat: ' . $e->getMessage(), 'error');
 }
?>