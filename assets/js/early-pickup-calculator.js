// File: assets/js/early-pickup-calculator.js

document.addEventListener('DOMContentLoaded', function() {
    // Cari semua elemen yang dibutuhkan dari form
    const tglMulaiBaruInput = document.getElementById('tgl_mulai_baru');
    const tglSelesaiElement = document.getElementById('tgl_selesai');
    const hargaHarianElement = document.getElementById('harga_harian');
    const kalkulasiBox = document.getElementById('kalkulasi-biaya');
    const biayaBaruText = document.getElementById('biaya-baru');

    // Pastikan semua elemen ada sebelum menjalankan kalkulator
    if (!tglMulaiBaruInput || !tglSelesaiElement || !hargaHarianElement || !kalkulasiBox || !biayaBaruText) {
        return; 
    }

    const tglSelesai = new Date(tglSelesaiElement.value);
    const hargaHarian = parseFloat(hargaHarianElement.value);

    // Tambahkan event listener saat tanggal diubah
    tglMulaiBaruInput.addEventListener('change', function() {
        const tglMulaiBaru = new Date(this.value);

        // Lakukan kalkulasi hanya jika tanggal valid
        if (tglMulaiBaru && tglSelesai && tglMulaiBaru < tglSelesai) {
            const diffTime = Math.abs(tglSelesai - tglMulaiBaru);
            const diffHours = diffTime / (1000 * 60 * 60); // Hitung selisih dalam jam
            
            // Bulatkan ke atas ke hari berikutnya jika ada kelebihan jam
            const diffDays = Math.ceil(diffHours / 24); 

            const totalBiayaBaru = diffDays * hargaHarian;

            // Tampilkan hasil dengan format mata uang Rupiah
            biayaBaruText.textContent = new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR',
                minimumFractionDigits: 0 
            }).format(totalBiayaBaru);

            kalkulasiBox.style.display = 'block';
        } else {
            // Sembunyikan box jika tanggal tidak valid
            kalkulasiBox.style.display = 'none';
        }
    });
});