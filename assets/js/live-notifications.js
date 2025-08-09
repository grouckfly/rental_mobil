// File: assets/js/live-notifications.js

document.addEventListener('DOMContentLoaded', () => {
    const notifIcon = document.querySelector('.notification-icon');

    // Jalankan hanya jika ikon notifikasi ada di halaman (artinya pengguna sudah login)
    if (notifIcon) {
        const checkMessages = () => {
            // Pastikan BASE_URL sudah ada
            if (typeof BASE_URL === 'undefined') return;

            fetch(`${BASE_URL}actions/cek_update.php?context=cek_pesan_baru`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.unread_count !== undefined) {
                        const count = parseInt(data.unread_count, 10);
                        let badge = notifIcon.querySelector('#pesan-badge');

                        if (count > 0) {
                            if (!badge) {
                                // Jika badge belum ada, buat baru
                                badge = document.createElement('span');
                                badge.className = 'badge';
                                badge.id = 'pesan-badge';
                                notifIcon.appendChild(badge);
                            }
                            // Perbarui jumlahnya
                            badge.textContent = count;
                        } else {
                            // Jika jumlahnya 0, hapus badge
                            if (badge) {
                                badge.remove();
                            }
                        }
                    }
                })
                .catch(error => console.error('Gagal memeriksa notifikasi pesan:', error));
        };

        // Lakukan pengecekan setiap 5 detik
        setInterval(checkMessages, 5000);
    }
});