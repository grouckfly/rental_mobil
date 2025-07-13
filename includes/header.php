<?php
// File: includes/header.php
// File ini tidak lagi menebak-nebak base url, tapi menggunakan konstanta dari config.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Rental Mobil' ?></title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark-mode.css">
    
    <?php
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    if (in_array($current_dir, ['admin', 'karyawan', 'pelanggan'])) {
        // Path ke CSS role akan relatif dari halaman yang memanggilnya, ini sudah benar
        echo "<link rel=\"stylesheet\" href=\"css/{$current_dir}.css\">";
    }
    ?>
</head>
<body>

    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <h2>Rental Mobil</h2>
            </div>

            <?php if (!isset($_SESSION['id_pengguna'])): ?>
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                        <li><a href="<?= BASE_URL ?>about.php">Tentang Kami</a></li>
                        <li><a href="<?= BASE_URL ?>services.php">Layanan</a></li>
                        <li><a href="<?= BASE_URL ?>mobil.php">Daftar Mobil</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="user-actions">
                <?php if (isset($_SESSION['id_pengguna'])): ?>
                    <span>Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="btn">Login</a>
                <?php endif; ?>
                <button id="dark-mode-toggle" class="icon-btn">🌙</button>

                <?php if (!isset($_SESSION['id_pengguna'])): ?>
                    <button class="mobile-menu-toggle icon-btn">☰</button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <?php
        if (isset($_SESSION['id_pengguna'])) {
            // Path include ini menggunakan path server fisik, jadi sudah benar
            require_once __DIR__ . '/sidebar.php';
        }
        ?>
        <main class="main-content">
            <div class="container">