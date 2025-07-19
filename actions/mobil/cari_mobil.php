<?php
// File: actions/cari_mobil.php
require_once '../../includes/config.php';
header('Content-Type: application/json');

// Ambil kata kunci pencarian dari Select2
$searchTerm = $_GET['q'] ?? '';

try {
    // Siapkan query untuk mencari mobil berdasarkan merk atau model
    $sql = "SELECT id_mobil as id, CONCAT(merk, ' ', model) as text 
            FROM mobil 
            WHERE merk LIKE :term OR model LIKE :term 
            ORDER BY merk, model";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':term' => "%$searchTerm%"]);
    $results = $stmt->fetchAll();

    // Kembalikan hasil dalam format JSON yang dimengerti oleh Select2
    echo json_encode(['results' => $results]);

} catch (PDOException $e) {
    echo json_encode(['results' => []]);
}
?>