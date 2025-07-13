/*
 * File: admin/js/admin.js
 * Deskripsi: Skrip khusus untuk fungsionalitas di panel admin.
*/

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi semua fungsi setelah halaman dimuat
    initializeTableSearch();
    initializeDashboardChart();
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
    if (!chartCanvas) return; // Hanya berjalan jika ada elemen canvas

    // Mengambil data dari atribut data-* di elemen canvas
    const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
    const values = JSON.parse(chartCanvas.dataset.values || '[]');
    
    const ctx = chartCanvas.getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Pemesanan',
                data: values,
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}