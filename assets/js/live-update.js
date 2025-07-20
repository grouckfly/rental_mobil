// File: assets/js/live-update.js (Versi dengan Pengecekan Detail)

document.addEventListener('DOMContentLoaded', () => {
    const liveUpdateElement = document.querySelector('[data-live-context]');

    if (liveUpdateElement) {
        const context = liveUpdateElement.dataset.liveContext;
        const id = liveUpdateElement.dataset.liveId || null; // Ambil ID jika ada
        
        // Data awal dari halaman
        let currentTotal = liveUpdateElement.dataset.liveTotal;
        let currentStatus = liveUpdateElement.dataset.liveStatus; // Status awal
        let lastUpdate = liveUpdateElement.dataset.liveLastUpdate;

        setInterval(() => {
            if (typeof BASE_URL === 'undefined') return;

            // Bangun URL dengan ID jika ada
            let fetchUrl = `${BASE_URL}actions/cek_update.php?context=${context}`;
            if (id) {
                fetchUrl += `&id=${id}`;
            }

            fetch(fetchUrl)
                .then(response => response.json())
                .then(data => {
                    if (!data) return;

                    let needsRefresh = false;

                    // Logika untuk halaman detail (membandingkan status)
                    if (context === 'detail_pemesanan') {
                        if (data.status_pemesanan && (data.status_pemesanan !== currentStatus || data.updated_at !== lastUpdate)) {
                            needsRefresh = true;
                        }
                    } 
                    // Logika untuk halaman daftar (membandingkan total)
                    else {
                        if (data.total && (data.total != currentTotal || data.last_update != lastUpdate)) {
                            needsRefresh = true;
                        }
                    }

                    if (needsRefresh) {
                        console.log('Perubahan data terdeteksi! Me-refresh halaman...');
                        liveUpdateElement.style.transition = 'opacity 0.5s';
                        liveUpdateElement.style.opacity = '0.5';
                        setTimeout(() => { location.reload(); }, 500);
                    }
                })
                .catch(error => console.error('Gagal memeriksa update:', error));
        }, 5000); // Cek setiap 5 detik agar lebih responsif
    }
});