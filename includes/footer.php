<?php
// File: includes/footer.php

// Mendefinisikan base URL lagi untuk konsistensi jika footer dipanggil terpisah
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$base_url = $protocol . "://" . $host . str_replace('/includes', '', $script_name);
?>

            </div> </main> </div> <footer class="main-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Rental Mobil Keren. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="<?= $base_url ?>assets/js/script.js"></script>
    <script src="<?= $base_url ?>assets/js/dark-mode.js"></script>

</body>
</html>