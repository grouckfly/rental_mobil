<?php
// File: includes/footer.php (Versi Final dengan Pemuatan Script Cerdas)
?>
</div>
</main>
</div>
<footer class="main-footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Rental Mobil Keren. All Rights Reserved.</p>
    </div>
</footer>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?= BASE_URL ?>assets/js/script.js"></script>
<script src="<?= BASE_URL ?>assets/js/dark-mode.js"></script>
<script src="<?= BASE_URL ?>assets/js/notification-handler.js"></script>

<?php
// Ambil path skrip saat ini untuk pengecekan
$current_script = $_SERVER['SCRIPT_NAME'];

// ==========================================================
// 3. LOGIKA PEMUATAN SCRIPT KONDISIONAL
// ==========================================================
if (isset($_SESSION['role'])) {

    // Skrip untuk notifikasi pesan real-time (hanya untuk yang login)
    echo '<script src="' . BASE_URL . 'assets/js/live-notifications.js"></script>';

    // Skrip auto-refresh untuk halaman daftar (admin, karyawan, pelanggan)
    if (str_contains($current_script, 'history.php') || str_contains($current_script, 'mobil.php') || str_contains($current_script, 'user.php') || str_contains($current_script, 'pembayaran.php') || str_contains($current_script, 'pemesanan.php')) {
        echo '<script src="' . BASE_URL . 'assets/js/live-update.js"></script>';
    }

    // Skrip untuk timer (hanya di halaman detail pesanan & pembayaran)
    if (str_contains($current_script, 'actions/pemesanan/detail.php') || str_contains($current_script, 'pelanggan/pembayaran.php')) {
        echo '<script src="' . BASE_URL . 'assets/js/rental-timer.js"></script>';
    }

    // Skrip untuk QR Code (hanya di halaman detail pesanan)
    if (str_contains($current_script, 'actions/pemesanan/detail.php')) {
        echo '<script src="' . BASE_URL . 'assets/js/qr-generator.js"></script>';
    }

    // Jika role adalah Admin atau Karyawan, muat file admin.js
    if (in_array($role_session, ['Admin', 'Karyawan'])) {
        $role_js_path = 'admin/js/admin.js';
        if (file_exists(dirname(__DIR__) . '/' . $role_js_path)) {
            echo "<script src=\"" . BASE_URL . $role_js_path . "\"></script>";
        }
    } 
    // Jika role adalah Pelanggan, muat file pelanggan.js
    elseif ($role_session === 'Pelanggan') {
        $role_js_path = 'pelanggan/js/pelanggan.js';
        if (file_exists(dirname(__DIR__) . '/' . $role_js_path)) {
            echo "<script src=\"" . BASE_URL . $role_js_path . "\"></script>";
        }
    }
}
?>
</body>

</html>