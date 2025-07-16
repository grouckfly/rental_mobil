<?php
// File: actions/pemesanan/proses.php (Waktu Default 00:00)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Ambil data dari form
$id_mobil = (int)$_POST['id_mobil'];
$id_pengguna = (int)$_POST['id_pengguna'];
$harga_sewa_harian = (float)$_POST['harga_sewa_harian'];

// ========================================================
// PERUBAHAN: Menggunakan tanggal langsung dari input
// ========================================================
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];


// Validasi dasar
if (empty($tanggal_mulai) || empty($tanggal_selesai)) {
     redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Tanggal mulai dan selesai sewa wajib diisi.', 'error');
}

if ($tanggal_selesai < $tanggal_mulai) {
    redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Tanggal selesai tidak boleh sebelum tanggal mulai.', 'error');
}

// Hitung total biaya berdasarkan durasi
$durasi = hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai);
// Jika durasi 0 (sewa dan kembali di hari yg sama), hitung sebagai 1 hari
$durasi = ($durasi < 1) ? 1 : $durasi; 
$total_biaya = $durasi * $harga_sewa_harian;

// Panggil fungsi generator kode
$kode_pemesanan = generate_booking_code($pdo);

try {
    // Masukkan data ke DB dengan tanggal yang dipilih
    $sql = "INSERT INTO pemesanan (kode_pemesanan, id_pengguna, id_mobil, tanggal_mulai, tanggal_selesai, total_biaya, status_pemesanan) 
            VALUES (?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kode_pemesanan, $id_pengguna, $id_mobil, $tanggal_mulai, $tanggal_selesai, $total_biya]);
    
    $id_pemesanan_baru = $pdo->lastInsertId();

    redirect_with_message(BASE_URL . "pelanggan/pembayaran.php?id=$id_pemesanan_baru", 'Pemesanan berhasil dibuat! Kode Pemesanan Anda: ' . $kode_pemesanan);

} catch (PDOException $e) {
    redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Gagal membuat pemesanan: ' . $e->getMessage(), 'error');
}
?>