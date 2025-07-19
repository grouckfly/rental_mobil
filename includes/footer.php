<?php
// File: includes/footer.php
?>
            </div> </main> </div> <footer class="main-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Rental Mobil Keren. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script src="<?= BASE_URL ?>assets/js/script.js"></script>
    <script src="<?= BASE_URL ?>assets/js/dark-mode.js"></script>

    <?php
    // ================================================
    // Memuat JS Role berdasarkan SESSION, bukan folder
    // ================================================
    if (isset($_SESSION['role'])) {
        $role_folder = strtolower($_SESSION['role']);
        $role_js_path = $role_folder . '/js/' . $role_folder . '.js';
        // Langsung cetak script, browser yang akan menangani jika file tidak ada (404)
        echo "<script src=\"" . BASE_URL . $role_js_path . "\"></script>";
    }
    
    // Menampilkan notifikasi toast jika ada
    display_flash_message(); 
    ?>
</body>
</html>