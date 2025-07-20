<?php
// File: actions/pemesanan/cek_kedaluwarsa.php
// PENTING: File ini tidak boleh memanggil config.php lagi karena akan dipanggil dari file yang sudah memanggilnya.

// Pastikan variabel koneksi $pdo sudah ada
if (!isset($pdo)) {
    return; // Hentikan jika file ini dipanggil secara langsung
}

try {
    // 1. Cari semua pemesanan yang statusnya 'Menunggu Pembayaran' DAN sudah melewati batas waktu
    $sql_select = "SELECT id_pemesanan, id_mobil FROM pemesanan WHERE status_pemesanan = 'Menunggu Pembayaran' AND batas_pembayaran < NOW()";
    $stmt_select = $pdo->query($sql_select);
    $expired_bookings = $stmt_select->fetchAll();

    // Jika tidak ada pesanan yang kedaluwarsa, tidak perlu melakukan apa-apa
    if (empty($expired_bookings)) {
        return;
    }

    // Siapkan query update untuk digunakan berulang kali di dalam loop
    $sql_update_order = "UPDATE pemesanan SET status_pemesanan = 'Dibatalkan' WHERE id_pemesanan = ?";
    $stmt_update_order = $pdo->prepare($sql_update_order);

    $sql_update_car = "UPDATE mobil SET status = 'Tersedia' WHERE id_mobil = ? AND status = 'Dipesan'";
    $stmt_update_car = $pdo->prepare($sql_update_car);

    // 2. Lakukan loop untuk setiap pesanan yang kedaluwarsa dan batalkan
    foreach ($expired_bookings as $booking) {
        $pdo->beginTransaction();
        try {
            // Batalkan pesanan
            $stmt_update_order->execute([$booking['id_pemesanan']]);
            // Kembalikan status mobil menjadi tersedia
            $stmt_update_car->execute([$booking['id_mobil']]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            // Abaikan error pada satu pesanan dan lanjutkan ke pesanan berikutnya
        }
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>