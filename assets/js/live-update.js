// File: assets/js/live-update.js

document.addEventListener('DOMContentLoaded', () => {
    // Cari elemen di halaman yang memiliki penanda untuk auto-update
    const liveUpdateElement = document.querySelector('[data-live-context]');

    // Jika elemen ditemukan di halaman ini, jalankan pengecekan
    if (liveUpdateElement) {
        const context = liveUpdateElement.dataset.liveContext;
        let currentTotal = liveUpdateElement.dataset.liveTotal;
        let lastUpdate = liveUpdateElement.dataset.liveLastUpdate;

        // Lakukan pengecekan setiap 10 detik (10000 milidetik)
        setInterval(() => {
            // Pastikan BASE_URL sudah didefinisikan (dari footer.php)
            if (typeof BASE_URL === 'undefined') {
                console.error('BASE_URL tidak terdefinisi. Auto-refresh tidak akan berjalan.');
                return;
            }

            fetch(`${BASE_URL}actions/cek_update.php?context=${context}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Cek apakah ada perubahan jumlah data ATAU waktu update terakhir
                    if (data.total && (data.total != currentTotal || data.last_update != lastUpdate)) {
                        console.log('Perubahan data terdeteksi! Me-refresh halaman...');
                        
                        // Beri efek fade out sebelum refresh agar terlihat mulus
                        liveUpdateElement.style.transition = 'opacity 0.5s';
                        liveUpdateElement.style.opacity = '0.5';
                        
                        // Tunggu sebentar lalu refresh
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    }
                })
                .catch(error => console.error('Gagal memeriksa update:', error));
        }, 10000);
    }
});