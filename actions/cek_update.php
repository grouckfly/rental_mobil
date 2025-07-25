<?php
// File: actions/cek_update.php (Versi dengan Pengecekan Detail)

require_once '../includes/config.php';
header('Content-Type: application/json');

$context = $_GET['context'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$response = [];

try {
    $sql = '';
    $params = [];

    switch ($context) {
        // Pengecekan untuk daftar data (tetap sama, tapi lebih baik dengan updated_at)
        case 'admin_mobil':
            $sql = "SELECT COUNT(*) as total, MAX(updated_at) as last_update FROM mobil";
            break;
        case 'admin_pemesanan':
            $sql = "SELECT COUNT(*) as total, MAX(updated_at) as last_update FROM pemesanan";
            break;
        
        // PENAMBAHAN: Pengecekan untuk satu item spesifik
        case 'detail_pemesanan':
            if ($id > 0) {
                // Ambil status dan waktu update terakhir dari satu pesanan
                $sql = "SELECT status_pemesanan, updated_at FROM pemesanan WHERE id_pemesanan = ?";
                $params[] = $id;
            }
            break;
    }

    if (!empty($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Abaikan error
}

echo json_encode($response ?: []); // Kirim array kosong jika tidak ada hasil