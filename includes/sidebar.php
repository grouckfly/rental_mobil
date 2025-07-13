<?php
// File: includes/sidebar.php

// Mendapatkan nama file saat ini untuk menandai link yang aktif
$current_page = basename($_SERVER['PHP_SELF']);
$current_role_dir = basename(dirname($_SERVER['PHP_SELF']));

// Fungsi helper untuk menambahkan class 'active'
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
                <li <?= is_active('dashboard.php', $current_page) ?>><a href="dashboard.php">Dashboard</a></li>
                <li <?= is_active('mobil.php', $current_page) ?>><a href="mobil.php">Kelola Mobil</a></li>
                <li <?= is_active('user.php', $current_page) ?>><a href="user.php">Kelola Pengguna</a></li>
                <li <?= is_active('pembayaran.php', $current_page) ?>><a href="pembayaran.php">Kelola Pemesanan</a></li>
                <li <?= is_active('history.php', $current_page) ?>><a href="history.php">Riwayat Transaksi</a></li>

            <?php elseif ($_SESSION['role'] === 'Karyawan'): ?>
                <li <?= is_active('dashboard.php', $current_page) ?>><a href="dashboard.php">Dashboard</a></li>
                <li <?= is_active('mobil.php', $current_page) ?>><a href="mobil.php">Data Mobil</a></li>
                <li <?= is_active('pembayaran.php', $current_page) ?>><a href="pembayaran.php">Verifikasi Pembayaran</a></li>
                <li <?= is_active('history.php', $current_page) ?>><a href="history.php">Riwayat Transaksi</a></li>

            <?php elseif ($_SESSION['role'] === 'Pelanggan'): ?>
                <li <?= is_active('dashboard.php', $current_page) ?>><a href="dashboard.php">Dashboard</a></li>
                <li <?= is_active('pemesanan.php', $current_page) ?>><a href="pemesanan.php">Pemesanan Saya</a></li>
                <li <?= is_active('pembayaran.php', $current_page) ?>><a href="pembayaran.php">Pembayaran</a></li>
                <li <?= is_active('history.php', $current_page) ?>><a href="history.php">Histori Sewa</a></li>

            <?php endif; ?>
        </ul>
    </nav>
</aside>