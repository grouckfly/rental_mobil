<?php
// File: includes/footer.php (Versi Final dengan Auto-Refresh)
?>
            </div> </main> </div> <footer class="main-footer">
        <div class="container"><p>&copy; <?= date('Y') ?> Rental Mobil Keren. All Rights Reserved.</p></div>
    </footer>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/script.js"></script>
    <script src="<?= BASE_URL ?>assets/js/dark-mode.js"></script>
    <script src="<?= BASE_URL ?>assets/js/live-update.js"></script>
    <script src="<?= BASE_URL ?>assets/js/live-notifications.js"></script>
    
    <?php
    // Memuat JS Role berdasarkan SESSION
    if (isset($_SESSION['role'])) {
        $role_folder = strtolower($_SESSION['role']);
        $role_js_path = $role_folder . '/js/' . $role_folder . '.js';
        if (file_exists(dirname(__DIR__) . '/' . $role_js_path)) {
            echo "<script src=\"" . BASE_URL . $role_js_path . "\"></script>";
        }
    }
    
    // Menampilkan notifikasi jika ada
    echo $notification_script; 
    ?>
</body>
</html>