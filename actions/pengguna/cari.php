<?php
// File: actions/pengguna/cari.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Pastikan hanya admin/karyawan yang bisa mencari daftar pengguna
check_auth(['Admin', 'Karyawan']);
header('Content-Type: application/json');

$searchTerm = $_GET['q'] ?? '';
$response = ['results' => []];

try {
    // Cari pengguna berdasarkan nama atau username
    $sql = "SELECT 
                id_pengguna as id, 
                CONCAT(nama_lengkap, ' (', username, ' - ', role, ')') as text 
            FROM pengguna 
            WHERE nama_lengkap LIKE :term 
               OR username LIKE :term
            ORDER BY nama_lengkap 
            LIMIT 20";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':term' => "%$searchTerm%"]);
    
    $response['results'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Biarkan response kosong jika ada error
}

echo json_encode($response);
exit;