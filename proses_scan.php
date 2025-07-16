<?php
// File: proses_scan.php (Versi Final dengan Logika Pengambilan & Pengembalian)

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Atur header untuk merespon dengan format JSON
header('Content-Type: application/json');

// Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// Ambil kode dari hasil scan
$kode = $_POST['kode'] ?? '';
if (empty($kode)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada kode yang diterima.']);
    exit;
}

try {
    // Mulai transaksi database untuk memastikan semua query berhasil
    $pdo->beginTransaction();

    // 1. Cari pemesanan berdasarkan kode, gabungkan dengan tabel mobil untuk dapat data denda
    $stmt = $pdo->prepare("
        SELECT p.*, m.denda_per_hari 
        FROM pemesanan p
        JOIN mobil m ON p.id_mobil = m.id_mobil
        WHERE p.kode_pemesanan = ?
    ");
    $stmt->execute([$kode]);
    $pemesanan = $stmt->fetch();

    if (!$pemesanan) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Kode pemesanan tidak ditemukan.']);
        exit;
    }

    // ==========================================================
    // LOGIKA UTAMA: TENTUKAN AKSI BERDASARKAN STATUS PEMESANAN
    // ==========================================================

    // KASUS 1: Jika status 'Dikonfirmasi', berarti ini proses PENGAMBILAN
    if ($pemesanan['status_pemesanan'] === 'Dikonfirmasi') {
        
        $waktu_sekarang = new DateTime();
        $jadwal_mulai = new DateTime($pemesanan['tanggal_mulai']);
        $jadwal_mulai->modify('-1 hour'); // Toleransi 1 jam
        if ($waktu_sekarang < $jadwal_mulai) {
            $pdo->rollBack();
            die(json_encode(['success' => false, 'message' => 'Gagal: Belum waktunya pengambilan mobil.']));
        }

        // Update status menjadi 'Berjalan'
        $stmt_update = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Berjalan', waktu_pengambilan = NOW() WHERE id_pemesanan = ?");
        $stmt_update->execute([$pemesanan['id_pemesanan']]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Penyewaan telah dimulai.']);
        exit;

    // KASUS 2: Jika status 'Berjalan', berarti ini proses PENGEMBALIAN
    } elseif ($pemesanan['status_pemesanan'] === 'Berjalan') {
        
        // a. Hitung denda jika ada
        $denda = 0;
        $waktu_sekarang = new DateTime();
        $jadwal_selesai = new DateTime($pemesanan['tanggal_selesai']);
        if ($waktu_sekarang > $jadwal_selesai) {
            $selisih_terlambat = $jadwal_selesai->diff($waktu_sekarang);
            $hari_terlambat = (int)$selisih_terlambat->days;
            // Toleransi keterlambatan 2 jam, lebih dari itu dihitung 1 hari denda
            if ($selisih_terlambat->h >= 2) { 
                $hari_terlambat += 1; 
            }
            $denda = $hari_terlambat * $pemesanan['denda_per_hari'];
        }
        
        // b. Update status pemesanan menjadi 'Selesai' dan catat denda
        $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Selesai', waktu_pengembalian = NOW(), total_denda = ? WHERE id_pemesanan = ?");
        $stmt_order->execute([$denda, $pemesanan['id_pemesanan']]);
        
        // c. Update status mobil kembali menjadi 'Tersedia'
        $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
        $stmt_car->execute([$pemesanan['id_mobil']]);
        
        $pdo->commit();

        // Siapkan pesan balasan
        $pesan_balasan = 'Mobil berhasil dikembalikan';
        if ($denda > 0) {
            $pesan_balasan .= ' dengan denda keterlambatan ' . format_rupiah($denda) . '.';
        } else {
            $pesan_balasan .= ' tepat waktu.';
        }
        echo json_encode(['success' => true, 'message' => $pesan_balasan]);
        exit;

    } else {
        // Status lain (Selesai, Dibatalkan, dll) tidak bisa diproses lagi
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Pemesanan ini tidak dapat diproses (Status saat ini: ' . $pemesanan['status_pemesanan'] . ').']);
        exit;
    }

} catch (Exception $e) {
    // Jika terjadi error di tengah proses, batalkan semua query
    $pdo->rollBack();
    die(json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]));
}
?>