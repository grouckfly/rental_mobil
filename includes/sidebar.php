<?php
// File: includes/sidebar.php

// Mendapatkan nama file saat ini untuk menandai link yang aktif
$current_page = basename($_SERVER['PHP_SELF']);

function is_active($page_name, $current_page_name)
{
    return $page_name === $current_page_name ? 'class="active"' : '';
}

// ==========================================================
// Khusus untuk pelanggan, cek apakah ada pembayaran tertunda
// ==========================================================
$id_pembayaran_tertunda = null;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pelanggan') {
    // Cari 1 pesanan terbaru yang statusnya 'Menunggu Pembayaran'
    $stmt_cek_bayar = $pdo->prepare(
        "SELECT id_pemesanan FROM pemesanan 
         WHERE id_pengguna = ? AND status_pemesanan = 'Menunggu Pembayaran' 
         ORDER BY tanggal_pemesanan DESC LIMIT 1"
    );
    $stmt_cek_bayar->execute([$_SESSION['id_pengguna']]);
    $hasil = $stmt_cek_bayar->fetch();
    if ($hasil) {
        $id_pembayaran_tertunda = $hasil['id_pemesanan'];
    }
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h3>Menu <?= htmlspecialchars($_SESSION['role']) ?></h3>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <li>
                    <a href="<?= BASE_URL ?>karyawan/scan_qr.html">Scan QR Code</a>
                </li>
                <li <?= is_active('profile.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/profile.php">Profil Saya</a>
                </li>
                <li <?= is_active('dashboard.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a>
                </li>
                <li <?= is_active('mobil.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/mobil.php">Kelola Mobil</a>
                </li>
                <li <?= is_active('user.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/user.php">Kelola Pengguna</a>
                </li>
                <li <?= is_active('pembayaran.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/pembayaran.php">Kelola Pemesanan</a>
                </li>
                <li <?= is_active('history.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/history.php">Riwayat Transaksi</a>
                </li>

            <?php elseif ($_SESSION['role'] === 'Karyawan'): ?>
                <li>
                    <a href="<?= BASE_URL ?>karyawan/scan_qr.html">Scan QR Code</a>
                </li>
                <li <?= is_active('profile.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/profile.php">Profil Saya</a>
                </li>
                <li <?= is_active('dashboard.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>karyawan/dashboard.php">Dashboard</a>
                </li>
                <li <?= is_active('mobil.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>karyawan/mobil.php">Data Mobil</a>
                </li>
                <li <?= is_active('pembayaran.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>karyawan/pembayaran.php">Verifikasi Pembayaran</a>
                </li>
                <li <?= is_active('history.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/history.php">Histori Sewa</a>
                </li>

            <?php elseif ($_SESSION['role'] === 'Pelanggan'): ?>
                <li <?= is_active('profile.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/profile.php">Profil Saya</a>
                </li>
                <li <?= is_active('dashboard.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/dashboard.php">Dashboard</a>
                </li>
                <li <?= is_active('mobil.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>mobil.php">Mobil Tersedia</a>
                </li>
                <li <?= is_active('pemesanan.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/pemesanan.php">Pemesanan Saya</a>
                </li>
                <?php
                // ================================================
                // Tampilkan menu pembayaran hanya jika ada tagihan
                // ================================================
                if ($id_pembayaran_tertunda):
                ?>
                    <li <?= is_active('pembayaran.php', $current_page) ?>>
                        <a href="<?= BASE_URL ?>pelanggan/pembayaran.php?id=<?= $id_pembayaran_tertunda ?>">
                            Pembayaran <span class="notification-dot"></span>
                        </a>
                    </li>
                <?php endif; ?>
                <li <?= is_active('history.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>admin/history.php">Histori Sewa</a>
                </li>

            <?php endif; ?>
        </ul>
    </nav>
</aside>