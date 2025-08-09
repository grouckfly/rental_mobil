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
            case 'cek_pesan_baru':
            if ($id_pengguna_session > 0) {
                $role_session = $_SESSION['role'];
                if (in_array($role_session, ['Admin', 'Karyawan'])) {
                    // Admin/Karyawan: Hitung utas percakapan baru dari pelanggan
                    $sql = "SELECT COUNT(*) as unread_count FROM pesan_bantuan WHERE status_pesan = 'Belum Dibaca' AND parent_id IS NULL";
                } else { // Pelanggan
                    // Pelanggan: Hitung utas percakapan mereka yang sudah dibalas admin
                    $sql = "SELECT COUNT(*) as unread_count FROM pesan_bantuan WHERE id_pengirim = ? AND status_pesan = 'Dibalas'";
                    $params[] = $id_pengguna_session;
                }
            }
            break;
    }

    if (!empty($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        // fetch(PDO::FETCH_ASSOC) untuk memastikan hasilnya adalah array asosiatif
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $response = $result;
        }
    }
} catch (PDOException $e) {
    // Abaikan error
}

echo json_encode($response ?: []); // Kirim array kosong jika tidak ada hasil