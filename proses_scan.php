<?php
// File: proses_scan.php (Versi Final dengan Transaksi Diperbaiki)

require_once 'includes/config.php';
require_once 'includes/functions.php';
header('Content-Type: application/json');

// Validasi dasar
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Akses tidak sah.']));
}

$kode = $_POST['kode'] ?? '';
if (empty($kode)) {
    die(json_encode(['success' => false, 'message' => 'Tidak ada kode yang diterima.']));
}

try {
    // Ambil data pemesanan yang relevan
    $stmt = $pdo->prepare("SELECT p.*, m.harga_sewa_harian, m.denda_per_hari FROM pemesanan p JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.kode_pemesanan = ?");
    $stmt->execute([$kode]);
    $pemesanan = $stmt->fetch();

    if (!$pemesanan) {
        die(json_encode(['success' => false, 'message' => 'Kode pemesanan tidak ditemukan.']));
    }

    // ==========================================================
    // LOGIKA UTAMA: TENTUKAN AKSI BERDASARKAN STATUS
    // ==========================================================

    // KASUS 1: PENGAMBILAN
    if ($pemesanan['status_pemesanan'] === 'Dikonfirmasi') {
        $waktu_sekarang = new DateTime();
        $jadwal_mulai = new DateTime($pemesanan['tanggal_mulai']);

        if ($waktu_sekarang < $jadwal_mulai) {
            // PENGAMBILAN LEBIH CEPAT -> Kirim sinyal konfirmasi ke JavaScript
            $durasi_baru = hitung_durasi_sewa($waktu_sekarang->format('Y-m-d H:i:s'), $pemesanan['tanggal_selesai']);
            $biaya_baru = ($durasi_baru < 1 ? 1 : $durasi_baru) * $pemesanan['harga_sewa_harian'];

            echo json_encode([
                'success' => true,
                'action' => 'confirm_early_pickup',
                'id_pemesanan' => $pemesanan['id_pemesanan'],
                'biaya_baru' => format_rupiah($biaya_baru),
                'jadwal_asli' => date('d M Y, H:i', strtotime($pemesanan['tanggal_mulai']))
            ]);
        } else {
            // PENGAMBILAN TEPAT WAKTU -> Langsung mulai sewa
            $pdo->beginTransaction();
            $stmt_update = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Berjalan', waktu_pengambilan = NOW() WHERE id_pemesanan = ?");
            $stmt_update->execute([$pemesanan['id_pemesanan']]);
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Penyewaan telah dimulai.']);
        }
        exit;
    }

    // KASUS 2: PENGEMBALIAN
    if ($pemesanan['status_pemesanan'] === 'Berjalan') {
        $pdo->beginTransaction(); // Mulai transaksi untuk proses pengembalian

        // a. Hitung denda jika ada
        $denda = 0;
        $waktu_sekarang = new DateTime();
        $jadwal_selesai = new DateTime($pemesanan['tanggal_selesai']);
        if ($waktu_sekarang > $jadwal_selesai) {
            $selisih_terlambat = $jadwal_selesai->diff($waktu_sekarang);
            $hari_terlambat = (int)$selisih_terlambat->days;
            if ($selisih_terlambat->h >= 2) {
                $hari_terlambat += 1;
            }
            if ($hari_terlambat > 0) {
                $denda = $hari_terlambat * $pemesanan['denda_per_hari'];
            }
        }

        // b. Cek apakah ada denda untuk menentukan alur
        if ($denda > 0) {
            // JIKA ADA DENDA: Update status ke 'Menunggu Pembayaran Denda'
            $stmt_update = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Menunggu Pembayaran Denda', total_denda = ? WHERE id_pemesanan = ?");
            $stmt_update->execute([$denda, $pemesanan['id_pemesanan']]);
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Mobil dikembalikan terlambat! Denda telah tercatat.',
                'redirect_url' => BASE_URL . 'actions/pemesanan/detail.php?id=' . $pemesanan['id_pemesanan']
            ]);
        } else {
            // JIKA TEPAT WAKTU: Langsung selesaikan
            $stmt_order = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Selesai', waktu_pengembalian = NOW(), total_denda = 0 WHERE id_pemesanan = ?");
            $stmt_order->execute([$pemesanan['id_pemesanan']]);
            $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ?");
            $stmt_car->execute([$pemesanan['id_mobil']]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Mobil berhasil dikembalikan tepat waktu.']);
        }
        exit;
    }

    // Jika statusnya tidak sesuai untuk diproses
    echo json_encode(['success' => false, 'message' => 'Pemesanan ini tidak dapat diproses (Status saat ini: ' . $pemesanan['status_pemesanan'] . ').']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Error Server: ' . $e->getMessage()]));
}
