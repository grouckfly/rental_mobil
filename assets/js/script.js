// File: assets/js/script.js

document.addEventListener('DOMContentLoaded', () => {

    // Logika untuk Menu Mobile (Hamburger Menu)
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', () => {
            // Menambahkan atau menghapus class 'nav-open' pada navigasi utama
            mainNav.classList.toggle('nav-open');
        });
    }

    // Menutup menu jika user mengklik di luar area menu
    document.addEventListener('click', (event) => {
        if (mainNav && mainNav.classList.contains('nav-open')) {
            const isClickInsideNav = mainNav.contains(event.target);
            const isClickOnToggle = mobileMenuToggle.contains(event.target);

            if (!isClickInsideNav && !isClickOnToggle) {
                mainNav.classList.remove('nav-open');
            }
        }
    });

});