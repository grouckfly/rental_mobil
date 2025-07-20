<?php
// File: actions/pemesanan/proses.php (Versi Final Paling Stabil)

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
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$harga_sewa_harian = (float)$_POST['harga_sewa_harian'];

// Validasi dasar
if (empty($tanggal_mulai) || empty($tanggal_selesai) || $tanggal_selesai < $tanggal_mulai) {
    redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Tanggal yang Anda masukkan tidak valid.', 'error');
}

try {
    $pdo->beginTransaction();

    // 1. Cek jadwal bentrok
    $stmt_check = $pdo->prepare(
        "SELECT id_pemesanan FROM pemesanan 
         WHERE id_mobil = ? 
         AND status_pemesanan NOT IN ('Selesai', 'Dibatalkan', 'Pengajuan Ditolak')
         AND ? < tanggal_selesai AND ? > tanggal_mulai"
    );
    $stmt_check->execute([$id_mobil, $tanggal_mulai, $tanggal_selesai]);
    
    if ($stmt_check->fetch()) {
        $pdo->rollBack();
        redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Maaf, mobil ini sudah dipesan orang lain pada rentang tanggal tersebut.', 'error');
    }

    // 2. Kunci mobil
    $stmt_lock_car = $pdo->prepare("UPDATE mobil SET status = 'Dipesan' WHERE id_mobil = ? AND status = 'Tersedia'");
    $stmt_lock_car->execute([$id_mobil]);
    if ($stmt_lock_car->rowCount() === 0) {
        $pdo->rollBack();
        redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Maaf, mobil ini baru saja dipesan. Silakan coba lagi.', 'error');
    }

    // 3. Hitung batas waktu bayar & biaya
    $batas_pembayaran = (new DateTime())->modify('+3 hour')->format('Y-m-d H:i:s');
    $durasi = hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai);
    $total_biaya = ($durasi < 1 ? 1 : $durasi) * $harga_sewa_harian;
    $kode_pemesanan = generate_booking_code($pdo);

    // 4. Insert pemesanan baru
    $sql = "INSERT INTO pemesanan (kode_pemesanan, id_pengguna, id_mobil, tanggal_mulai, tanggal_selesai, total_biaya, status_pemesanan, batas_pembayaran) 
            VALUES (?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kode_pemesanan, $id_pengguna, $id_mobil, $tanggal_mulai, $tanggal_selesai, $total_biaya, $batas_pembayaran]);
    
    $id_pemesanan_baru = $pdo->lastInsertId();
    $pdo->commit();

    // 5. Redirect ke halaman pembayaran dengan ID yang BENAR
    redirect_with_message(BASE_URL . "pelanggan/pembayaran.php?id=" . $id_pemesanan_baru, 'Pemesanan berhasil dibuat! Segera lakukan pembayaran.');

} catch (PDOException $e) {
    $pdo->rollBack();
    redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Gagal membuat pemesanan: ' . $e->getMessage(), 'error');
}
?>