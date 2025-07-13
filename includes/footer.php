<?php
// File: includes/footer.php
?>
            </div> </main> </div> <footer class="main-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Rental Mobil Keren. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>assets/js/script.js"></script>
    <script src="<?= BASE_URL ?>assets/js/dark-mode.js"></script>

    <?php
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $js_file_path = "js/{$current_dir}.js";
    if (file_exists($js_file_path)) {
        echo "<script src=\"{$js_file_path}\"></script>";
    }
    display_flash_message();
    ?>
</body>
</html>