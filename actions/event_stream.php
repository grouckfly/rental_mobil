<?php
// File: actions/event_stream.php

require_once '../includes/config.php';

// Header wajib untuk Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$last_update_pemesanan = '';

while (true) {
    // Cek koneksi client
    if (connection_aborted()) {
        exit();
    }
    
    // Query untuk mendapatkan "versi" data terbaru dari tabel pemesanan
    // Kita gunakan kombinasi jumlah dan waktu update terakhir
    $stmt = $pdo->query("SELECT COUNT(*) as total, MAX(updated_at) as last_update FROM pemesanan");
    $current_update_pemesanan = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_update_pemesanan_checksum = $current_update_pemesanan['total'] . $current_update_pemesanan['last_update'];

    // Bandingkan dengan versi data terakhir yang kita kirim
    if ($last_update_pemesanan !== $current_update_pemesanan_checksum) {
        
        // JIKA ADA PERBEDAAN, kirim event 'update' ke browser
        echo "event: update\n";
        echo 'data: {"pesan": "Data pemesanan telah diperbarui."}' . "\n\n";
        
        // Simpan versi data yang baru
        $last_update_pemesanan = $current_update_pemesanan_checksum;
        
        // Kirim data ke client
        ob_flush();
        flush();
    }
    
    // Tunggu 5 detik sebelum memeriksa lagi
    sleep(5);
}
?>