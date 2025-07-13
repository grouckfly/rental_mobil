<?php
// File: includes/footer.php

// Mendefinisikan base URL lagi untuk konsistensi jika footer dipanggil terpisah
// Ini memastikan $base_url selalu tersedia
if (!isset($base_url)) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $project_path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false || strpos($_SERVER['REQUEST_URI'], '/karyawan/') !== false || strpos($_SERVER['REQUEST_URI'], '/pelanggan/') !== false) {
        $project_path = dirname($project_path) . '/';
    }
    $base_url = $protocol . "://" . $host . $project_path;
}
?>

            </div> </main> </div> <footer class="main-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Rental Mobil Keren. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="<?= $base_url ?>assets/js/script.js"></script>
    <script src="<?= $base_url ?>assets/js/dark-mode.js"></script>

    <?php
    // Memuat JS spesifik role jika ada
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    if (in_array($current_dir, ['admin', 'karyawan', 'pelanggan'])) {
        $js_file_path = "js/{$current_dir}.js";
        if (file_exists($js_file_path)) {
            echo "<script src=\"{$js_file_path}\"></script>";
        }
    }
    ?>

</body>
</html>