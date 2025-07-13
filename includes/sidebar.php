<?php
// File: includes/sidebar.php

// Mendapatkan nama file saat ini untuk menandai link yang aktif
$current_page = basename($_SERVER['PHP_SELF']);

function is_active($page_name, $current_page_name) {
    return $page_name === $current_page_name ? 'class="active"' : '';
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
                    <a href="<?= BASE_URL ?>karyawan/history.php">Riwayat Transaksi</a>
                </li>

            <?php elseif ($_SESSION['role'] === 'Pelanggan'): ?>
                 <li <?= is_active('dashboard.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/dashboard.php">Dashboard</a>
                </li>
                <li <?= is_active('pemesanan.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/pemesanan.php">Pemesanan Saya</a>
                </li>
                <li <?= is_active('pembayaran.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/pembayaran.php">Pembayaran</a>
                </li>
                <li <?= is_active('history.php', $current_page) ?>>
                    <a href="<?= BASE_URL ?>pelanggan/history.php">Histori Sewa</a>
                </li>

            <?php endif; ?>
        </ul>
    </nav>
</aside>