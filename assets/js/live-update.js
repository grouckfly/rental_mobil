// File: assets/js/live-update.js (Versi Cerdas dengan Deteksi Interaksi)

document.addEventListener('DOMContentLoaded', () => {
    const liveUpdateElement = document.querySelector('[data-live-context]');

    if (liveUpdateElement) {
        // 1. Buat variabel penanda apakah pengguna sedang berinteraksi dengan form
        let isUserInteracting = false;

        // 2. Tambahkan pendeteksi interaksi pada semua input di dalam form filter
        const filterFormInputs = document.querySelectorAll('.filter-form input, .filter-form select');
        filterFormInputs.forEach(input => {
            // Jika pengguna mulai mengetik/memilih, set penanda ke true
            input.addEventListener('focus', () => {
                isUserInteracting = true;
                console.log('User sedang mengetik, auto-refresh ditunda.');
            });
            // Jika pengguna selesai dan klik di tempat lain, set penanda ke false
            input.addEventListener('blur', () => {
                isUserInteracting = false;
                console.log('User selesai mengetik, auto-refresh dilanjutkan.');
            });
        });

        const context = liveUpdateElement.dataset.liveContext;
        const id = liveUpdateElement.dataset.liveId || null;
        
        let currentTotal = liveUpdateElement.dataset.liveTotal;
        let currentStatus = liveUpdateElement.dataset.liveStatus;
        let lastUpdate = liveUpdateElement.dataset.liveLastUpdate;

        setInterval(() => {
            // 3. Tambahkan kondisi: JANGAN lakukan pengecekan jika pengguna sedang berinteraksi
            if (isUserInteracting) {
                return; // Lewati siklus pengecekan ini
            }

            if (typeof BASE_URL === 'undefined') return;

            let fetchUrl = `${BASE_URL}actions/cek_update.php?context=${context}`;
            if (id) {
                fetchUrl += `&id=${id}`;
            }

            fetch(fetchUrl)
                .then(response => response.json())
                .then(data => {
                    if (!data) return;

                    let needsRefresh = false;
                    
                    if (context === 'detail_pemesanan') {
                        if (data.status_pemesanan && (data.status_pemesanan !== currentStatus || data.updated_at !== lastUpdate)) {
                            needsRefresh = true;
                        }
                    } else {
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
        }, 15000);
    }
});