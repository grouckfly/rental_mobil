// File: assets/js/live-update.js (Versi AJAX Partial Update)

document.addEventListener('DOMContentLoaded', () => {
    const liveUpdateElement = document.querySelector('[data-live-context]');

    if (liveUpdateElement) {
        let lastUpdate = liveUpdateElement.dataset.liveLastUpdate;
        const context = liveUpdateElement.dataset.liveContext;

        setInterval(() => {
            if (typeof BASE_URL === 'undefined') return;

            // 1. Cek apakah ada update
            fetch(`${BASE_URL}actions/cek_update.php?context=${context}`)
                .then(response => response.json())
                .then(data => {
                    // 2. Jika ada update baru, minta konten baru
                    if (data && data.last_update && data.last_update !== lastUpdate) {
                        console.log('Perubahan terdeteksi! Memuat ulang konten...');
                        
                        // Tentukan konten apa yang harus diminta
                        let contentContext = '';
                        if (context === 'admin_pemesanan') {
                            contentContext = 'admin_history_table';
                        }
                        // Tambahkan if lain untuk context lain (misal: admin_mobil)

                        if (contentContext) {
                            fetch(`${BASE_URL}actions/get_content.php?context=${contentContext}`)
                                .then(response => response.text())
                                .then(html => {
                                    // 3. Ganti konten lama dengan konten baru
                                    liveUpdateElement.innerHTML = html;
                                    // 4. Perbarui waktu update terakhir
                                    lastUpdate = data.last_update;
                                    console.log('Konten berhasil diperbarui.');
                                });
                        }
                    }
                })
                .catch(error => console.error('Gagal memeriksa update:', error));
        }, 15000); // Cek setiap 15 detik
    }
});