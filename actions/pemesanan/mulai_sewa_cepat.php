<?php
// File: actions/pemesanan/mulai_sewa_cepat.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Hak akses hanya untuk Admin dan Karyawan
check_auth(['Admin', 'Karyawan']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Akses tidak sah.']));
}

$id_pemesanan = (int)($_POST['id_pemesanan'] ?? 0);
if ($id_pemesanan === 0) {
    die(json_encode(['success' => false, 'message' => 'ID Pemesanan tidak valid.']));
}

try {
    $pdo->beginTransaction();

    // 1. Ambil data pemesanan yang ada untuk kalkulasi
    $stmt = $pdo->prepare("SELECT p.*, m.harga_sewa_harian FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pemesanan = ?");
    $stmt->execute([$id_pemesanan]);
    $pemesanan = $stmt->fetch();

    if (!$pemesanan || $pemesanan['status_pemesanan'] !== 'Dikonfirmasi') {
        $pdo->rollBack();
        die(json_encode(['success' => false, 'message' => 'Pemesanan ini tidak dapat diproses.']));
    }

    // 2. Kalkulasi ulang biaya berdasarkan waktu pengambilan saat ini
    $waktu_mulai_baru = date('Y-m-d H:i:s');
    $durasi_baru = hitung_durasi_sewa($waktu_mulai_baru, $pemesanan['tanggal_selesai']);
    $biaya_baru = ($durasi_baru < 1 ? 1 : $durasi_baru) * $pemesanan['harga_sewa_harian'];

    // 3. Update pemesanan dengan jadwal dan biaya baru
    $stmt_update = $pdo->prepare("
        UPDATE pemesanan SET 
            status_pemesanan = 'Berjalan', 
            waktu_pengambilan = NOW(),
            tanggal_mulai = NOW(),
            total_biaya = ?
        WHERE id_pemesanan = ?
    ");
    $stmt_update->execute([$biaya_baru, $id_pemesanan]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pengambilan lebih cepat berhasil dikonfirmasi. Penyewaan telah dimulai.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
}
?>