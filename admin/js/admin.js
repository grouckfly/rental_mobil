/*
 * File: admin/js/admin.js
 * Deskripsi: Skrip khusus untuk fungsionalitas di panel admin.
*/

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi semua fungsi setelah halaman dimuat
    initializeTableSearch();
    initializeDashboardChart();
    initializeFilterToggle();
});

/**
 * Fungsi untuk menambahkan fungsionalitas pencarian pada tabel.
 */
function initializeTableSearch() {
    const searchInput = document.getElementById('table-search');
    if (!searchInput) return;

    // Cari .table-container yang paling dekat setelah .search-container
    const tableContainer = searchInput.closest('div').nextElementSibling;
    if (!tableContainer || !tableContainer.classList.contains('table-container')) return;
    
    const tableRows = tableContainer.querySelectorAll('tbody tr');

    searchInput.addEventListener('keyup', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        tableRows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(searchTerm) ? '' : 'none';
        });
    });
}

/**
 * Fungsi untuk membuat grafik di halaman dashboard admin menggunakan Chart.js.
 */
function initializeDashboardChart() {
    const chartCanvas = document.getElementById('dashboardChart');
    if (!chartCanvas) return;

    const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
    const dataJumlah = JSON.parse(chartCanvas.dataset.valuesJumlah || '[]');
    const dataPendapatan = JSON.parse(chartCanvas.dataset.valuesPendapatan || '[]');
    
    const ctx = chartCanvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'bar', // Tipe grafik utama adalah bar
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Jumlah Pesanan',
                    data: dataJumlah,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Biru
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y' // Sumbu Y kiri untuk jumlah
                },
                {
                    label: 'Pendapatan (Rp)',
                    data: dataPendapatan,
                    type: 'line', // Tipe dataset ini adalah garis
                    borderColor: 'rgba(75, 192, 192, 1)', // Hijau
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1' // Sumbu Y kanan untuk Rupiah
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { // Sumbu Y kiri (Jumlah)
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        stepSize: 5
                    }
                },
                y1: { // Sumbu Y kanan (Pendapatan)
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false, // Hanya tampilkan garis grid dari sumbu Y kiri
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
}

/**
 * Fungsi untuk menampilkan/menyembunyikan filter lanjutan
 */
function initializeFilterToggle() {
    const toggleBtn = document.getElementById('toggle-filter-btn');
    const filterContainer = document.getElementById('advanced-filter-container');

    if (toggleBtn && filterContainer) {
        toggleBtn.addEventListener('click', () => {
            // Cek apakah filter sedang tersembunyi
            if (filterContainer.style.display === 'none') {
                // Tampilkan filter
                filterContainer.style.display = 'block';
                toggleBtn.textContent = 'Sembunyikan Filter';
                toggleBtn.classList.remove('btn-secondary');
                toggleBtn.classList.add('btn-primary');
            } else {
                // Sembunyikan filter
                filterContainer.style.display = 'none';
                toggleBtn.textContent = 'Filter Lanjutan';
                toggleBtn.classList.remove('btn-primary');
                toggleBtn.classList.add('btn-secondary');
            }
        });
    }
}