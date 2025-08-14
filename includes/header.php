<?php
// File: includes/header.php
// File ini tidak lagi menebak-nebak base url, tapi menggunakan konstanta dari config.php

// ===============================
// MENJALANKAN PEMBATALAN OTOMATIS
// ===============================
require_once __DIR__ . '/../actions/pemesanan/cek_kedaluwarsa.php';

// ===================
// Fitur Pesan Bantuan
// ===================
$jumlah_pesan_baru = 0;
$link_pesan = '#'; // Link default jika tidak ada

if (isset($_SESSION['role'])) {
    $role_session = $_SESSION['role'];
    $id_pengguna_session = $_SESSION['id_pengguna'];

    // Tentukan link inbox universal
    $link_pesan = BASE_URL . 'actions/pesan/inbox.php';

    try {
        if (in_array($role_session, ['Admin', 'Karyawan'])) {
            // Admin/Karyawan: Hitung utas percakapan baru dari pelanggan
            $stmt_pesan = $pdo->query("SELECT COUNT(*) FROM pesan_bantuan WHERE status_pesan = 'Belum Dibaca' AND parent_id IS NULL");
            $jumlah_pesan_baru = $stmt_pesan->fetchColumn();
        } elseif ($role_session === 'Pelanggan') {
            // Pelanggan: Hitung utas percakapan mereka yang sudah dibalas oleh admin
            $stmt_pesan = $pdo->prepare("SELECT COUNT(*) FROM pesan_bantuan WHERE id_pengirim = ? AND status_pesan = 'Dibalas' AND parent_id IS NULL");
            $stmt_pesan->execute([$id_pengguna_session]);
            $jumlah_pesan_baru = $stmt_pesan->fetchColumn();
        }
    } catch (PDOException $e) {
        $jumlah_pesan_baru = 0; // Abaikan jika ada error
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Rental Mobil' ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark-mode.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/fleksibel.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <?php
    // ==========================================================
    // Memuat CSS Role berdasarkan SESSION, bukan folder
    // ==========================================================
    if (isset($_SESSION['role'])) {
        // 2. Jika sudah login, muat CSS umum untuk dashboard
        echo "<link rel=\"stylesheet\" href=\"" . BASE_URL . "assets/css/dashboard.css\">";

        // 3. Muat CSS spesifik untuk role tersebut (jika ada)
        $role_folder = strtolower($_SESSION['role']);
        $role_css_path = $role_folder . '/css/' . $role_folder . '.css';
        if (file_exists(dirname(__DIR__) . '/' . $role_css_path)) {
            echo "<link rel=\"stylesheet\" href=\"" . BASE_URL . $role_css_path . "\">";
        }
    }
    ?>

</head>

<body>

    <header class="main-header">
        <div class="container header-container">
            <div class="logo-container">
                <?php if (isset($_SESSION['id_pengguna'])): ?>
                    <button id="sidebar-toggle-btn" class="icon-btn">&#9776;</button>
                <?php endif; ?>
                <h1 class="header-title">Rental Mobil</h1>
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
                <?php if (isset($_SESSION['id_pengguna'])):

                    $sapaan = $_SESSION['username']; // Default fallback adalah username

                    if (!empty($_SESSION['nama_lengkap'])) {
                        $nama_parts = explode(' ', trim($_SESSION['nama_lengkap']));
                        $jumlah_kata = count($nama_parts);

                        if ($jumlah_kata >= 3) {
                            // Jika nama terdiri dari 3 kata atau lebih, ambil nama tengah
                            $sapaan = $nama_parts[1];
                        } elseif ($jumlah_kata == 2) {
                            // Jika nama terdiri dari 2 kata, ambil nama akhir
                            $sapaan = $nama_parts[1];
                        } else {
                            // Jika hanya 1 kata, gunakan nama itu
                            $sapaan = $nama_parts[0];
                        }
                    }
                ?>

                    <!-- Notifikasi Pesan Bantuan -->
                    <?php if (isset($_SESSION['role'])): ?>
                        <a href="<?= $link_pesan ?>" class="notification-icon" title="Pesan">
                            <span class="icon">&#9993;</span> <?php if ($jumlah_pesan_baru > 0): ?>
                                <span class="badge" id="pesan-badge"><?= $jumlah_pesan_baru ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <span class="welcome-user">Halo, <?= htmlspecialchars($sapaan) ?></span>
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