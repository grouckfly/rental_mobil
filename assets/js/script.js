// File: assets/js/script.js (Versi Final & Lengkap)

document.addEventListener("DOMContentLoaded", () => {
  // Panggil semua fungsi inisialisasi setelah halaman dimuat
  initializeMainMenuToggle(); // <-- FUNGSI YANG HILANG DITAMBAHKAN
  initializeSidebarToggle();
  initializeStarRatings();
  initializeSearchableSelect();
  initializeSpecToggle();
});

/**
 * Fungsi untuk menu navigasi utama (publik) di layar kecil
 */
function initializeMainMenuToggle() {
  const mainMenuToggle = document.querySelector(".mobile-menu-toggle");
  const mainNav = document.querySelector(".main-nav");
  if (mainMenuToggle && mainNav) {
    mainMenuToggle.addEventListener("click", () => {
      mainNav.classList.toggle("nav-open");
    });
  }
}

/**
 * Fungsi untuk membuka/menutup sidebar di dashboard
 */
function initializeSidebarToggle() {
  const sidebarToggleBtn = document.getElementById("sidebar-toggle-btn");
  const pageWrapper = document.querySelector(".page-wrapper");
  const overlay = document.querySelector(".sidebar-overlay");

  // Kondisi ini akan berhasil jika ketiga elemen ditemukan di HTML
  if (sidebarToggleBtn && pageWrapper && overlay) {
    // Event saat tombol hamburger sidebar di-klik
    sidebarToggleBtn.addEventListener("click", () => {
      pageWrapper.classList.toggle("sidebar-open");
    });

    // Event saat area overlay di-klik untuk menutup
    overlay.addEventListener("click", () => {
      pageWrapper.classList.remove("sidebar-open");
    });
  }
}

/**
 * Fungsi untuk menampilkan rating bintang secara dinamis
 */
function initializeStarRatings() {
  const starRatingElements = document.querySelectorAll(".star-rating");
  starRatingElements.forEach((starElement) => {
    const rating = parseFloat(starElement.dataset.rating) || 0;
    const percentage = (rating / 5) * 100;
    starElement.style.setProperty("--rating-percent", percentage + "%");
  });
}

/**
 * Fungsi untuk dropdown yang bisa dicari menggunakan Select2
 */
function initializeSearchableSelect() {
  // Pastikan jQuery dan Select2 sudah dimuat
  if (
    typeof jQuery !== "undefined" &&
    typeof jQuery.fn.select2 !== "undefined"
  ) {
    const $selectElement = $("#filter-mobil");
    if ($selectElement.length) {
      $selectElement.select2({
        placeholder: "Ketik untuk mencari mobil...",
        allowClear: true,
        ajax: {
          url: BASE_URL + "actions/mobil/cari_mobil.php",
          dataType: "json",
          delay: 250, // PERBAIKAN: Delay diperpanjang agar lebih efisien
          processResults: function (data) {
            return { results: data.results };
          },
          cache: true,
        },
      });
    }
  }
}

/**
 * Fungsi untuk menampilkan notifikasi toast
 */
function showToast(message, type = "success") {
  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
  }, 100);

  // PERBAIKAN: Durasi notifikasi diperpanjang menjadi 4 detik
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => {
      toast.remove();
    }, 500);
  }, 4000);
}

/**
 * Fungsi untuk menangani tombol "Lihat Selengkapnya" pada spesifikasi
 */
function initializeSpecToggle() {
    const toggleBtn = document.getElementById('toggle-spec-btn');
    const specContent = document.getElementById('spec-content');

    if (toggleBtn && specContent) {
        // Cek apakah tinggi asli konten (scrollHeight) lebih besar dari tinggi yang terlihat (clientHeight)
        // Ini cara akurat untuk tahu apakah teksnya terpotong
        if (specContent.scrollHeight <= specContent.clientHeight) {
            // Jika kontennya pendek dan tidak terpotong, sembunyikan tombol
            toggleBtn.style.display = 'none';
        } else {
            // Jika kontennya panjang, pasang event listener
            toggleBtn.addEventListener('click', () => {
                specContent.classList.toggle('expanded');
                specContent.classList.toggle('collapsed');

                if (specContent.classList.contains('expanded')) {
                    toggleBtn.textContent = 'Sembunyikan';
                } else {
                    toggleBtn.textContent = 'Lihat Selengkapnya';
                }
            });
        }
    }
}