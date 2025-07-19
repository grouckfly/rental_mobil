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

    initializeStarRatings();
    initializeSearchableSelect();

});

/**
 * Fungsi untuk menampilkan notifikasi toast.
 * @param {string} message - Pesan yang akan ditampilkan.
 * @param {string} type - Tipe notifikasi ('success' atau 'error').
 */
function showToast(message, type = 'success') {
    // 1. Buat elemen div baru untuk toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;

    // 2. Tambahkan toast ke dalam body
    document.body.appendChild(toast);

    // 3. Tampilkan toast dengan animasi
    setTimeout(() => {
        toast.classList.add('show');
    }, 100); // Sedikit delay agar transisi CSS berjalan

    // 4. Sembunyikan dan hapus toast setelah 5 detik
    setTimeout(() => {
        toast.classList.remove('show');
        // Hapus elemen dari DOM setelah animasi fade out selesai
        setTimeout(() => {
            toast.remove();
        }, 500); // Waktu ini harus cocok dengan durasi transisi di CSS
    }, 2500);
}


/**
 * Fungsi baru untuk menampilkan rating bintang secara dinamis.
 */
function initializeStarRatings() {
    // Cari semua elemen dengan class .star-rating
    const starRatingElements = document.querySelectorAll('.star-rating');
    
    // Lakukan loop untuk setiap elemen yang ditemukan
    starRatingElements.forEach(starElement => {
        // Ambil nilai rating dari atribut data-rating
        const rating = parseFloat(starElement.dataset.rating) || 0;
        
        // Hitung persentase lebar bintang kuning
        const percentage = (rating / 5) * 100;

        // Set variabel CSS --rating-percent pada elemen
        starElement.style.setProperty('--rating-percent', percentage + '%');
    });
}

function initializeSearchableSelect() {
    // Gunakan jQuery untuk menargetkan elemen
    const $selectElement = $('#filter-mobil');
    
    if ($selectElement.length) {
        $selectElement.select2({
            placeholder: 'Ketik untuk mencari mobil...',
            allowClear: true,
            ajax: {
                url: BASE_URL + 'actions/mobil/cari_mobil.php',
                dataType: 'json',
                delay: 50,
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            dropdownCssClass: "select2-dropdown-scrollable"
        });
    }
}