<?php
// File: cek_upload.php (Letakkan di folder root rental_mobil/)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Cek Folder Upload</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Hasil Pengecekan Folder Upload</h1>
    <table>
        <thead>
            <tr>
                <th>Folder</th>
                <th>Folder Ada?</th>
                <th>Bisa Ditulisi?</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $folders = ['assets/img/', 'assets/img/mobil/', 'assets/img/ktp/', 'assets/img/bukti_pembayaran/'];
            foreach ($folders as $folder) {
                echo '<tr>';
                echo '<td>' . $folder . '</td>';

                // Cek apakah direktori/folder ada
                if (is_dir($folder)) {
                    echo '<td class="ok">✅ ADA</td>';
                    // Jika ada, cek apakah bisa ditulisi oleh server
                    if (is_writable($folder)) {
                        echo '<td class="ok">✅ BISA</td>';
                    } else {
                        echo '<td class="error">❌ TIDAK BISA</td>';
                    }
                } else {
                    echo '<td class="error">❌ TIDAK ADA</td>';
                    echo '<td class="error">❌ TIDAK BISA</td>';
                }
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</body>
</html>