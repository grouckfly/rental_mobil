<?php
// File: proses_scan.php (Versi Final dengan Logika Pengambilan & Pengembalian)

require_once 'includes/config.php';
require_once 'includes/functions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die(json_encode(['success' => false, 'message' => 'Akses tidak sah.'])); }

$kode = $_POST['kode'] ?? '';
if (empty($kode)) { die(json_encode(['success' => false, 'message' => 'Kode tidak diterima.'])); }

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT * FROM pemesanan WHERE kode_pemesanan = ?");
    $stmt->execute([$kode]);
    $pemesanan = $stmt->fetch();

    if (!$pemesanan) { die(json_encode(['success' => false, 'message' => 'Kode tidak ditemukan.'])); }

    if ($pemesanan['status_pemesanan'] === 'Dikonfirmasi') {
        // --- PROSES PENGAMBILAN MOBIL ---
        // (Logika validasi waktu dari langkah 2 tetap di sini)
        $waktu_sekarang = new DateTime();
        $jadwal_mulai = new DateTime($pemesanan['tanggal_mulai']);
        $jadwal_mulai->modify('-1 hour'); 
        if ($waktu_sekarang < $jadwal_mulai) {
            die(json_encode(['success' => false, 'message' => 'Gagal: Belum waktunya pengambilan.']));
        }
        $stmt_update = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Berjalan', waktu_pengambilan = NOW() WHERE id_pemesanan = ?");
        $stmt_update->execute([$pemesanan['id_pemesanan']]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Penyewaan telah dimulai.']);

    } elseif ($pemesanan['status_pemesanan'] === 'Berjalan') {
        // --- PROSES PENGEMBALIAN MOBIL ---
        // 1. Hitung denda jika ada
        $denda = 0;
        $waktu_sekarang = new DateTime();
        $jadwal_selesai = new DateTime($pemesanan['tanggal_selesai']);
        if ($waktu_sekarang > $jadwal_selesai) {
            $selisih_terlambat = $jadwal_selesai->diff($waktu_sekarang);
            $hari_terlambat = $selisih_terlambat->days;
            if ($selisih_terlambat->h > 2) { $hari_terlambat += 1; }
            $denda = $hari_terlambat * $pemesanan['denda_per_hari'];
        }
        
        // 2. Update status pemesanan menjadi 'Selesai'
        $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Selesai', waktu_pengembalian = NOW(), total_denda = ? WHERE id_pemesanan = ?");
        $stmt_order->execute([$denda, $pemesanan['id_pemesanan']]);
        
        // 3. Update status mobil menjadi 'Tersedia'
        $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
        $stmt_car->execute([$pemesanan['id_mobil']]);
        
        $pdo->commit();

        $pesan_denda = ($denda > 0) ? " dengan denda keterlambatan " . format_rupiah($denda) . "." : ".";
        echo json_encode(['success' => true, 'message' => 'Mobil berhasil dikembalikan' . $pesan_denda]);
        
    } else {
        // Status lain tidak bisa di-scan
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Pemesanan ini tidak dapat diproses (Status: ' . $pemesanan['status_pemesanan'] . ').']);
    }

} catch (Exception $e) {
    $pdo->rollBack();
    die(json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]));
}
?>