// File: assets/js/dark-mode.js

document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const currentTheme = localStorage.getItem('theme');

    // 1. Periksa preferensi tema dari localStorage saat halaman dimuat
    if (currentTheme === 'dark') {
        // Jika tema gelap tersimpan, terapkan class 'dark-mode'
        document.body.classList.add('dark-mode');
    }

    // 2. Tambahkan event listener ke tombol toggle
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            // Toggle class 'dark-mode' pada body
            document.body.classList.toggle('dark-mode');

            // 3. Simpan preferensi tema yang baru ke localStorage
            let theme = 'light'; // Tema default
            if (document.body.classList.contains('dark-mode')) {
                theme = 'dark';
            }
            localStorage.setItem('theme', theme);
        });
    }
});