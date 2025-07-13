// File: assets/js/qr-generator.js

document.addEventListener('DOMContentLoaded', function() {
    // Cari elemen div yang akan diubah menjadi QR Code
    const qrCodeElement = document.getElementById('qrcode');
    
    // Cek apakah elemen tersebut ada di halaman ini
    if (qrCodeElement) {
        // Ambil data (kode pemesanan) dari atribut 'data-kode'
        const kodePemesanan = qrCodeElement.dataset.kode;

        // Pastikan ada data kode sebelum membuat QR Code
        if (kodePemesanan) {
            // Hapus isi div jika ada (untuk mencegah duplikasi)
            qrCodeElement.innerHTML = "";
            
            // Buat QR Code baru menggunakan library qrcode.js
            new QRCode(qrCodeElement, {
                text: kodePemesanan,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    }
});