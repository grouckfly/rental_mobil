<?php
// File: includes/header.php

// =============================================================================
// BAGIAN PERBAIKAN: Membuat Base URL Dinamis
// =============================================================================
// Kode ini secara otomatis mendeteksi alamat dasar website Anda,
// baik di localhost maupun di server online.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// Mendapatkan path ke direktori root proyek (misal: /rental-mobil/)
$project_path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
// Jika proyek berada di dalam sub-sub direktori, kode ini akan menyesuaikan
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/karyawan/') !== false || strpos($_SERVER['REQUEST_URI'], '/pelanggan/') !== false) {
    $project_path = dirname($project_path) . '/';
}
$base_url = $protocol . "://" . $host . $project_path;
// =============================================================================

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Rental Mobil' ?></title>
    
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/dark-mode.css">
    
    <?php
    // Memuat CSS spesifik role jika ada
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    if (in_array($current_dir, ['admin', 'karyawan', 'pelanggan'])) {
        echo "<link rel=\"stylesheet\" href=\"css/{$current_dir}.css\">";
    }
    ?>
</head>
<body>

    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <a href="<?= $base_url ?>index.php"><span>RentalMobil</span></a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?= $base_url ?>index.php">Home</a></li>
                    <li><a href="<?= $base_url ?>mobil.php">Daftar Mobil</a></li>
                </ul>
            </nav>

            <div class="user-actions">
                <?php if (isset($_SESSION['id_pengguna'])): ?>
                    <span>Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="<?= $base_url ?>logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="<?= $base_url ?>login.php" class="btn">Login</a>
                <?php endif; ?>
                <button id="dark-mode-toggle" class="icon-btn">🌙</button>
            </div>
             <button class="mobile-menu-toggle icon-btn">☰</button>
        </div>
    </header>

    <div class="page-wrapper">
        <?php
        if (isset($_SESSION['id_pengguna'])) {
            $sidebar_path = __DIR__ . '/sidebar.php';
            if (file_exists($sidebar_path)) {
                include $sidebar_path;
            }
        }
        ?>
        <main class="main-content">
            <div class="container">
                