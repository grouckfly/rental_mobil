// File: assets/js/notification-handler.js

document.addEventListener('DOMContentLoaded', () => {
    // Ambil parameter dari URL saat ini
    const urlParams = new URLSearchParams(window.location.search);
    const statusType = urlParams.get('status_type');
    const statusMsg = urlParams.get('status_msg');

    // Cek apakah ada pesan notifikasi di URL
    if (statusType && statusMsg) {
        // Panggil fungsi showToast (dari script.js) untuk menampilkan notifikasi
        showToast(statusMsg, statusType);

        // HAPUS parameter dari URL agar tidak muncul lagi saat refresh
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
    }
});