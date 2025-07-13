<?php
// File: includes/header.php

// Menentukan base URL secara dinamis agar path aset selalu benar
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
// Menyesuaikan path jika proyek ada di dalam subfolder
$base_url = $protocol . "://" . $host . str_replace('/includes', '', $script_name);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Rental Mobil Keren' ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/dark-mode.css">
</head>
<body>

    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <a href="<?= $base_url ?>index.php">
                    <span>RentalMobil</span>
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?= $base_url ?>index.php">Home</a></li>
                    <li><a href="<?= $base_url ?>mobil.php">Daftar Mobil</a></li>
                    <li><a href="<?= $base_url ?>about.php">Tentang</a></li>
                    <li><a href="<?= $base_url ?>services.php">Layanan</a></li>
                </ul>
            </nav>

            <div class="user-actions">
                <?php if (isset($_SESSION['id_pengguna'])): ?>
                    <span class="welcome-user">Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
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
        // Hanya tampilkan sidebar jika pengguna sudah login
        if (isset($_SESSION['id_pengguna'])) {
            // Path menuju sidebar harus disesuaikan dari file yang memanggil header ini
            $sidebar_path = __DIR__ . '/sidebar.php';
            if (file_exists($sidebar_path)) {
                include $sidebar_path;
            }
        }
        ?>
        <main class="main-content">
            <div class="container">