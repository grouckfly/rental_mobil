<?php
// File: actions/cek_update.php (Versi Lengkap untuk Semua Role)

require_once '../includes/config.php';
header('Content-Type: application/json');

$context = $_GET['context'] ?? '';
$response = ['update' => false]; // Default response

if (empty($context)) {
    echo json_encode($response);
    exit;
}

try {
    $sql = '';
    $params = [];

    switch ($context) {
        case 'admin_mobil':
            $sql = "SELECT COUNT(*) as total, MAX(updated_at) as last_update FROM mobil";
            break;
        case 'admin_user':
            $sql = "SELECT COUNT(*) as total, MAX(created_at) as last_update FROM pengguna";
            break;
        case 'admin_pemesanan':
            $sql = "SELECT COUNT(*) as total, MAX(tanggal_pemesanan) as last_update FROM pemesanan";
            break;
        case 'pelanggan_pemesanan':
            if (isset($_SESSION['id_pengguna'])) {
                $sql = "SELECT COUNT(*) as total, MAX(tanggal_pemesanan) as last_update FROM pemesanan WHERE id_pengguna = ?";
                $params[] = $_SESSION['id_pengguna'];
            }
            break;
    }

    if (!empty($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $response = [
            'total' => $result['total'],
            'last_update' => $result['last_update']
        ];
    }
} catch (PDOException $e) {
    // Abaikan error agar tidak merusak script
}

echo json_encode($response);