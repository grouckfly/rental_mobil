<?php
// File: karyawan/konfirmasi_pengembalian.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);

$id_pemesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Ambil semua data pemesanan termasuk detail pelanggan dan mobil
try {
    $stmt = $pdo->prepare("SELECT p.*, u.nama_lengkap, m.merk, m.model FROM pemesanan p JOIN pengguna u ON p.id_pengguna = u.id_pengguna JOIN mobil m ON p.id_mobil = m.id_mobil WHERE p.id_pemesanan = ?");
    $stmt->execute([$id_pemesanan]);
    $pemesanan = $stmt->fetch();
    if (!$pemesanan) { redirect_with_message('dashboard.php', 'Pemesanan tidak ditemukan.', 'error'); }
} catch (PDOException $e) { die("Error: ".$e->getMessage()); }

$page_title = 'Konfirmasi Pengembalian';
require_once '../includes/header.php';
?>

<div class="page-header"><h1>Konfirmasi Pengembalian & Denda</h1></div>

<div class="form-container">
    <div class="form-box">
        <h3>Detail Pemesanan #<?= htmlspecialchars($pemesanan['kode_pemesanan']) ?></h3>
        <p>Pelanggan: <strong><?= htmlspecialchars($pemesanan['nama_lengkap']) ?></strong></p>
        <p>Mobil: <strong><?= htmlspecialchars($pemesanan['merk'] . ' ' . $pemesanan['model']) ?></strong></p>
        <hr>
        <div class="denda-info" style="text-align:center; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h4>Total Denda Keterlambatan</h4>
            <p style="font-size: 2rem; font-weight: bold; color: var(--danger-color);"><?= format_rupiah($pemesanan['total_denda']) ?></p>
        </div>
        <p style="text-align:center; margin-top:20px;">Silakan terima pembayaran denda dari pelanggan. Setelah pembayaran diterima, selesaikan proses sewa.</p>

        <form action="<?= BASE_URL ?>actions/pemesanan/proses_penyelesaian.php" method="POST" style="margin-top: 20px;" onsubmit="return confirm('Anda yakin denda sudah dibayar dan ingin menyelesaikan penyewaan ini?');">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">    
        <input type="hidden" name="id_pemesanan" value="<?= $pemesanan['id_pemesanan'] ?>">
            <input type="hidden" name="id_mobil" value="<?= $pemesanan['id_mobil'] ?>">
            <button type="submit" class="btn btn-success" style="width: 100%;">Denda Sudah Dibayar & Selesaikan Sewa</button>
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>