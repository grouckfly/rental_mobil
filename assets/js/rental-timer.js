// File: assets/js/rental-timer.js (Versi Cerdas)

document.addEventListener('DOMContentLoaded', function() {
    const timerElement = document.getElementById('countdown-timer');
    
    if (timerElement) {
        const endTime = new Date(timerElement.dataset.endTime).getTime();
        
        const interval = setInterval(function() {
            const now = new Date().getTime();
            const distance = endTime - now;
            
            // Jika waktu sudah habis
            if (distance < 0) {
                clearInterval(interval);
                
                // ========================================================
                // LOGIKA BARU: Cek tindakan apa yang harus dilakukan
                // ========================================================
                const actionOnExpire = timerElement.dataset.actionOnExpire;

                if (actionOnExpire === 'redirect') {
                    // Jika ini adalah timer pembayaran, lakukan redirect
                    timerElement.innerHTML = "Waktu pembayaran telah habis. Mengalihkan...";
                    window.location.href = BASE_URL + 'pelanggan/dashboard.php?status=payment_expired';
                } else {
                    // Jika ini adalah timer sewa, cukup tampilkan pesan
                    timerElement.innerHTML = "Waktu sewa telah berakhir.";
                    timerElement.style.color = "red";
                }
                return;
            }
            
            // Kalkulasi sisa waktu (tetap sama)
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Tampilkan hasilnya
            let output = '';
            if (days > 0) {
                output = `${days} hari ${hours} jam ${minutes} menit`;
            } else {
                output = `${hours} jam ${minutes} menit ${seconds} detik`;
            }
            timerElement.innerHTML = output;

        }, 1000);
    }
});