// File: assets/js/rental-timer.js

document.addEventListener('DOMContentLoaded', function() {
    // Cari elemen div yang akan dijadikan timer
    const timerElement = document.getElementById('countdown-timer');
    
    // Cek apakah elemen timer ada di halaman ini
    if (timerElement) {
        // Ambil waktu selesai sewa dari atribut data-*
        const endTime = new Date(timerElement.dataset.endTime).getTime();
        
        // Perbarui countdown setiap 1 detik
        const interval = setInterval(function() {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            // Jika waktu sewa sudah habis
            if (distance < 0) {
                clearInterval(interval);
                timerElement.innerHTML = "Waktu sewa telah berakhir.";
                timerElement.style.color = "red";
                return;
            }
            
            // Kalkulasi sisa waktu
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Tampilkan hasilnya di dalam elemen
            timerElement.innerHTML = `${days} hari ${hours} jam ${minutes} menit ${seconds} detik`;
        }, 1000);
    }
});