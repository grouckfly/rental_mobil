<?php
// File: proses_scan.php (Terbaru)

require_once 'includes/config.php';
// Tidak memanggil auth.php karena ini adalah endpoint yang bisa diakses dari perangkat scan
// Keamanan bisa ditambahkan di level API Key atau token jika diperlukan nanti

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$kode = $_POST['kode'] ?? '';
if (empty($kode)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada kode yang diterima.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Cari pemesanan berdasarkan kode
    $stmt = $pdo->prepare("SELECT id_pemesanan, status_pemesanan FROM pemesanan WHERE kode_pemesanan = ?");
    $stmt->execute([$kode]);
    $pemesanan = $stmt->fetch();

    if (!$pemesanan) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Kode pemesanan tidak ditemukan.']);
        exit;
    }

    // 2. Validasi status. Hanya pesanan 'Dikonfirmasi' yang bisa dimulai
    if ($pemesanan['status_pemesanan'] !== 'Dikonfirmasi') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Pemesanan ini tidak dapat dimulai (Status saat ini: ' . $pemesanan['status_pemesanan'] . ').']);
        exit;
    }

    // 3. Update status pemesanan menjadi 'Berjalan' dan catat waktu pengambilan
    $stmt_update = $pdo->prepare("UPDATE pemesanan SET status_pemesanan = 'Berjalan', waktu_pengambilan = NOW() WHERE id_pemesanan = ?");
    $stmt_update->execute([$pemesanan['id_pemesanan']]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Verifikasi berhasil! Penyewaan untuk kode ' . htmlspecialchars($kode) . ' telah dimulai.'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
}
?>