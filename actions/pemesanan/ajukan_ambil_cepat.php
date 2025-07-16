<?php
// File: actions/pemesanan/ajukan_ambil_cepat.php (Versi Waktu Default 00:00)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'pelanggan/dashboard.php');
    exit;
}

$id_pemesanan = (int)$_POST['id_pemesanan'];
$tanggal_baru_input = $_POST['tgl_mulai_baru'];

// Fungsi untuk redirect dengan pesan error via GET
function redirect_with_error($id, $message) {
    header('Location: ' . BASE_URL . "pelanggan/ajukan_ambil_cepat.php?id=$id&error=" . urlencode($message));
    exit;
}

// Validasi #1: Pastikan tanggal diisi
if (empty($tanggal_baru_input)) {
    redirect_with_error($id_pemesanan, 'Tanggal pengambilan baru wajib diisi.');
}

// Buat objek DateTime untuk perbandingan (PHP otomatis menganggap waktunya 00:00)
$tgl_mulai_baru_obj = new DateTime($tanggal_baru_input);
$hari_ini_obj = new DateTime('today'); // 'today' mengabaikan komponen waktu

// Validasi #2: Pastikan tanggal tidak di masa lalu
if ($tgl_mulai_baru_obj < $hari_ini_obj) {
    redirect_with_error($id_pemesanan, 'Tanggal pengambilan tidak boleh di masa lalu.');
}

try {
    // Ambil data pemesanan untuk menghitung biaya baru
    $stmt_check = $pdo->prepare("SELECT p.tanggal_selesai, m.harga_sewa_harian FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pemesanan = ?");
    $stmt_check->execute([$id_pemesanan]);
    $pemesanan = $stmt_check->fetch();

    if (!$pemesanan) { redirect_with_error($id_pemesanan, 'Pemesanan tidak ditemukan.'); }

    // Validasi #3: Pastikan tanggal baru lebih awal dari jadwal semula
    if ($tgl_mulai_baru_obj >= new DateTime($pemesanan['tanggal_mulai'])) {
        redirect_with_error($id_pemesanan, 'Tanggal baru harus lebih awal dari jadwal pengambilan semula.');
    }

    // Hitung durasi dan biaya baru
    $durasi_baru = hitung_durasi_sewa($tanggal_baru_input, $pemesanan['tanggal_selesai']);
    $biaya_baru = ($durasi_baru < 1 ? 1 : $durasi_baru) * $pemesanan['harga_sewa_harian'];

    // Update database dengan status dan data pengajuan
    $sql = "UPDATE pemesanan SET 
                status_pemesanan = 'Pengajuan Ambil Cepat', 
                tgl_mulai_diajukan = ?, 
                total_biaya_diajukan = ? 
            WHERE id_pemesanan = ? AND id_pengguna = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tanggal_baru_input, $biaya_baru, $id_pemesanan, $_SESSION['id_pengguna']]);

    header('Location: ' . BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan&status=pengajuan_sukses");
    exit;

} catch (PDOException $e) {
    redirect_with_error($id_pemesanan, 'Gagal mengirim pengajuan: ' . $e->getMessage());
}
?>