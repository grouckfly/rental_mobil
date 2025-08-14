<?php
// File: includes/header.php (Versi Final Dirapikan)

// Pastikan config.php sudah dipanggil (sebagai pengaman)
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}

// 2. Inisialisasi variabel untuk semua pengguna
$jumlah_pesan_baru = 0;
$link_pesan = '#';
$role_session = $_SESSION['role'] ?? null;
$id_pengguna_session = $_SESSION['id_pengguna'] ?? 0;
$sapaan = $_SESSION['username'] ?? '';

// 3. Jika pengguna sudah login, jalankan logika spesifik
if ($id_pengguna_session > 0) {
    // Tentukan link inbox universal
    $link_pesan = BASE_URL . 'actions/pesan/inbox.php';

    // Logika Hitung Pesan Baru sesuai Role
    try {
        if (in_array($role_session, ['Admin', 'Karyawan'])) {
            $stmt_pesan = $pdo->query("SELECT COUNT(*) FROM pesan_bantuan WHERE status_pesan = 'Belum Dibaca' AND parent_id IS NULL");
            $jumlah_pesan_baru = $stmt_pesan->fetchColumn();
        } elseif ($role_session === 'Pelanggan') {
            $stmt_pesan = $pdo->prepare("SELECT COUNT(*) FROM pesan_bantuan WHERE id_penerima = ? AND status_pesan = 'Dibalas'");
            $stmt_pesan->execute([$id_pengguna_session]);
            $jumlah_pesan_baru = $stmt_pesan->fetchColumn();
        }
    } catch (PDOException $e) {
        $jumlah_pesan_baru = 0;
    }

    // Logika Penentuan Nama Sapaan
    if (!empty($_SESSION['nama_lengkap'])) {
        $nama_parts = explode(' ', trim($_SESSION['nama_lengkap']));
        $sapaan = $nama_parts[0]; // Ambil nama depan saja untuk sapaan yang lebih umum
    }
}

// 4. Menangkap notifikasi dari URL (untuk toast)
$notification_script = '';
if (isset($_GET['status_type']) && isset($_GET['status_msg'])) {
    $message = addslashes(htmlspecialchars($_GET['status_msg']));
    $type = htmlspecialchars($_GET['status_type']);
    $notification_script = "<script>document.addEventListener('DOMContentLoaded', () => { showToast('{$message}', '{$type}'); });</script>";
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php
    if ($role_session) {
        echo "<link rel=\"stylesheet\" href=\"" . BASE_URL . "assets/css/dashboard.css\">";
        $role_css_path = strtolower($role_session) . '/css/' . strtolower($role_session) . '.css';
        if (file_exists(dirname(__DIR__) . '/' . $role_css_path)) {
            echo "<link rel=\"stylesheet\" href=\"" . BASE_URL . $role_css_path . "\">";
        }
    }
    echo "<link rel=\"stylesheet\" href=\"" . BASE_URL . "assets/css/fleksibel.css\">";
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

            <?php if (!$id_pengguna_session): // Tampilkan navigasi hanya jika belum login 
            ?>
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
                <?php if ($id_pengguna_session > 0): // Tampilan jika sudah login 
                ?>
                    <a href="<?= $link_pesan ?>" class="notification-icon" title="Pesan"><span class="icon">&#9993;</span><?php if ($jumlah_pesan_baru > 0): ?><span class="badge" id="pesan-badge"><?= $jumlah_pesan_baru ?></span><?php endif; ?></a>
                    <span class="welcome-user">Halo, <?= htmlspecialchars($sapaan) ?></span>
                <?php else: // Tampilan jika belum login 
                ?>
                    <a href="<?= BASE_URL ?>login.php" class="btn">Login</a>
                <?php endif; ?>
                <button id="dark-mode-toggle" class="icon-btn">🌙</button>
                <?php if (!$id_pengguna_session): ?><button class="mobile-menu-toggle icon-btn">☰</button><?php endif; ?>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="sidebar-overlay"></div>
        <?php
        if ($id_pengguna_session > 0) {
            require_once __DIR__ . '/sidebar.php';
        }
        ?>
        <main class="main-content">
            <div class="container">