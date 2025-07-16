<?php
// File: actions/pemesanan/ajukan_ambil_cepat.php (Versi Perbaikan)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'pelanggan/dashboard.php');
    exit;
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$tgl_mulai_baru = $_POST['tgl_mulai_baru'];

// Fungsi untuk redirect dengan pesan error via GET
function redirect_with_error($id, $message) {
    header('Location: ' . BASE_URL . "pelanggan/ajukan_ambil_cepat.php?id=$id&error=" . urlencode($message));
    exit;
}

// Validasi
if (empty($tgl_mulai_baru)) {
    redirect_with_error($id_pemesanan, 'Waktu pengambilan baru wajib diisi.');
}
if (new DateTime($tgl_mulai_baru) < new DateTime()) {
    redirect_with_error($id_pemesanan, 'Waktu pengambilan tidak boleh di masa lalu.');
}

try {
    // Ambil tanggal selesai dari database untuk validasi akhir
    $stmt_check = $pdo->prepare("SELECT tanggal_selesai FROM pemesanan WHERE id_pemesanan = ?");
    $stmt_check->execute([$id_pemesanan]);
    $pemesanan = $stmt_check->fetch();

    if (new DateTime($tgl_mulai_baru) >= new DateTime($pemesanan['tanggal_selesai'])) {
        redirect_with_error($id_pemesanan, 'Waktu pengambilan baru tidak boleh sama atau setelah waktu selesai sewa.');
    }

    // Jika semua validasi lolos, update database
    $sql = "UPDATE pemesanan SET status_pemesanan = 'Pengajuan Ambil Cepat', tgl_mulai_diajukan = ? WHERE id_pemesanan = ? AND id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tgl_mulai_baru, $id_pemesanan, $_SESSION['id_pengguna']]);

    // Redirect ke halaman detail dengan status sukses
    header('Location: ' . BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan&status=pengajuan_sukses");
    exit;

} catch (PDOException $e) {
    redirect_with_error($id_pemesanan, 'Gagal mengirim pengajuan: ' . $e->getMessage());
}
?>