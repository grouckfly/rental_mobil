<?php
// File: actions/pemesanan/proses.php (Versi Perbaikan Typo)

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Ambil data dari form
$id_mobil = (int)$_POST['id_mobil'];
$id_pengguna = (int)$_POST['id_pengguna'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$harga_sewa_harian = (float)$_POST['harga_sewa_harian'];

// Validasi dasar
if (empty($tanggal_mulai) || empty($tanggal_selesai)) {
     redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Tanggal mulai dan selesai sewa wajib diisi.', 'error');
}
if ($tanggal_selesai < $tanggal_mulai) {
    redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Tanggal selesai tidak boleh sebelum tanggal mulai.', 'error');
}

// Hitung total biaya berdasarkan durasi
$durasi = hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai);
$durasi = ($durasi < 1) ? 1 : $durasi; 
$total_biaya = $durasi * $harga_sewa_harian;

// Panggil fungsi generator kode
$kode_pemesanan = generate_booking_code($pdo);

try {
    // Mulai transaksi database untuk memastikan semua proses aman
    $pdo->beginTransaction();

    // ==========================================================
    // LOGIKA KUNCI MOBIL ("Siapa Cepat Dia Dapat")
    // ==========================================================
    // 1. Cek apakah ada jadwal yang bentrok untuk mobil ini
    $stmt_check = $pdo->prepare(
        "SELECT id_pemesanan FROM pemesanan 
         WHERE id_mobil = ? 
         AND status_pemesanan NOT IN ('Selesai', 'Dibatalkan', 'Pengajuan Ditolak')
         AND ? < tanggal_selesai AND ? > tanggal_mulai"
    );
    $stmt_check->execute([$id_mobil, $tanggal_mulai, $tanggal_selesai]);
    
    if ($stmt_check->fetch()) {
        // JIKA ADA JADWAL BENTROK, batalkan transaksi dan beri pesan
        $pdo->rollBack();
        redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Maaf, mobil ini sudah dipesan orang lain pada rentang tanggal tersebut. Silakan pilih tanggal lain.', 'error');
    }

    // 2. Jika aman, "kunci" mobil dengan mengubah statusnya menjadi 'Dipesan'
    // Ini mencegah orang lain memesan saat Anda sedang dalam proses pembayaran.
    $stmt_lock_car = $pdo->prepare("UPDATE mobil SET status = 'Dipesan' WHERE id_mobil = ? AND status = 'Tersedia'");
    $stmt_lock_car->execute([$id_mobil]);
    // Cek apakah ada baris yang terpengaruh. Jika 0, berarti mobil sudah diambil orang lain.
    if ($stmt_lock_car->rowCount() === 0) {
        $pdo->rollBack();
        redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Maaf, mobil ini baru saja dipesan. Silakan coba lagi.', 'error');
    }

    // ==========================================================
    // LOGIKA BATAS WAKTU PEMBAYARAN
    // ==========================================================
    // 3. Hitung batas waktu pembayaran (3 jam dari sekarang)
    $batas_pembayaran = (new DateTime())->modify('+3 hour')->format('Y-m-d H:i:s');

    // Hitung total biaya
    $durasi = hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai);
    $total_biaya = ($durasi < 1 ? 1 : $durasi) * $harga_sewa_harian;
    $kode_pemesanan = generate_booking_code($pdo);

    // 4. Masukkan data pemesanan baru
    $sql = "INSERT INTO pemesanan (kode_pemesanan, id_pengguna, id_mobil, tanggal_mulai, tanggal_selesai, total_biaya, status_pemesanan, batas_pembayaran) 
            VALUES (?, ?, ?, ?, ?, ?, 'Menunggu Pembayaran', ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kode_pemesanan, $id_pengguna, $id_mobil, $tanggal_mulai, $tanggal_selesai, $total_biaya, $batas_pembayaran]);
    
    $id_pemesanan_baru = $pdo->lastInsertId();

    // Jika semua berhasil, commit transaksi
    $pdo->commit();
    redirect_with_message(BASE_URL . "pelanggan/pembayaran.php?id=$id_pemesanan_baru", 'Pemesanan berhasil dibuat! Silakan lakukan pembayaran sebelum batas waktu.');

} catch (PDOException $e) {
    // Jika ada error, batalkan semua perubahan
    $pdo->rollBack();
    redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Gagal membuat pemesanan: ' . $e->getMessage(), 'error');
}
?>