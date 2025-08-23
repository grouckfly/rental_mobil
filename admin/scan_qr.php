<?php
// File: karyawan/scan_qr.php
// Memanggil config untuk mendapatkan konstanta BASE_URL
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Scan QR Code Pemesanan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" href="assets/img/etc/Copyright.png">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .scanner-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            width: 90%;
            max-width: 500px;
        }

        #qr-reader {
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        #qr-reader-results {
            margin-top: 20px;
            font-size: 1.2rem;
            font-weight: bold;
            word-wrap: break-word;
        }

        .back-link {
            margin-top: 20px;
            display: inline-block;
        }

        .confirmation-dialog {
            text-align: left;
            border: 2px solid var(--warning-color);
            padding: 20px;
            border-radius: 5px;
            background: #fffaf0;
        }

        .confirmation-dialog p {
            margin-bottom: 15px;
        }

        .confirmation-dialog .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <div class="scanner-container">
        <div id="scanner-wrapper">
            <h1>Arahkan Kamera ke QR Code</h1>
            <div id="qr-reader"></div>
        </div>
        <div id="qr-reader-results"></div>
        <a href="dashboard.php" class="btn btn-secondary back-link" id="back-to-dashboard">Kembali ke Dashboard</a>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Karena file ini .php, kita bisa mencetak variabel BASE_URL dengan aman
            const BASE_URL = '<?= BASE_URL ?>';
            const resultContainer = document.getElementById('qr-reader-results');
            const scannerWrapper = document.getElementById('scanner-wrapper');
            let processing = false;

            function onScanSuccess(decodedText, decodedResult) {
                if (processing) return;
                processing = true;
                resultContainer.innerHTML = `Memproses kode: <strong>${decodedText}</strong>...`;

                // Sekarang fetch menggunakan BASE_URL yang sudah diproses dengan benar oleh PHP
                fetch(BASE_URL + 'proses_scan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `kode=${decodedText}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.action === 'confirm_early_pickup') {
                            html5QrcodeScanner.clear();
                            scannerWrapper.style.display = 'none';
                            displayConfirmationDialog(data);
                        } else {
                            // PERBAIKAN: Tampilkan informasi debug jika ada
                            html5QrcodeScanner.clear();
                            const color = data.success ? 'green' : 'red';
                            let html = `Status: <strong style="color:${color};">${data.message}</strong>`;

                            if (data.debug) {
                                html += `<div style="text-align:left; font-size:0.8rem; margin-top:15px; padding:10px; background:#eee; border-radius:5px; color: #333;">
                                <strong>Info Debug:</strong><br>
                                Waktu Server: ${data.debug.waktu_server_sekarang}<br>
                                Jadwal Ambil: ${data.debug.jadwal_mulai_pemesanan}<br>
                                <strong>Kesimpulan: ${data.debug.kesimpulan}</strong>
                             </div>`;
                            }
                            resultContainer.innerHTML = html;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function displayConfirmationDialog(data) {
                resultContainer.innerHTML = `
                    <div class="confirmation-dialog">
                        <h4>Konfirmasi Pengambilan Lebih Cepat</h4>
                        <p>Jadwal asli: <strong>${data.jadwal_asli}</strong>.</p>
                        <p>Toleransi Pengambilan Cepat: <strong>1 Jam Lebih Awal</strong>.</p>
                        <p>Total biaya baru menjadi sekitar <strong>${data.biaya_baru}</strong>. Apakah pelanggan setuju?</p>
                        <div class="actions">
                            <button id="cancel-early-btn" class="btn btn-secondary">Tidak, Batal</button>
                            <button id="confirm-early-btn" data-id="${data.id_pemesanan}" class="btn btn-success">Ya, Konfirmasi & Mulai Sewa</button>
                        </div>
                    </div>
                `;

                document.getElementById('confirm-early-btn').addEventListener('click', function() {
                    this.disabled = true;
                    this.textContent = 'Memproses...';
                    const pemesananId = this.dataset.id;
                    fetch(BASE_URL + 'actions/pemesanan/mulai_sewa_cepat.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `id_pemesanan=${pemesananId}`
                        })
                        .then(res => res.json())
                        .then(finalData => {
                            const color = finalData.success ? 'green' : 'red';
                            resultContainer.innerHTML = `Status: <strong style="color:${color};">${finalData.message}</strong>`;
                        });
                });
                document.getElementById('cancel-early-btn').addEventListener('click', () => location.reload());
            }

            let html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            }, false);
            html5QrcodeScanner.render(onScanSuccess);
        });
    </script>
</body>

</html>