<?php
// File: actions/export/pdf.php (Versi Final dengan Filter Lengkap)

require_once '../../vendor/autoload.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ==========================================================
// 1. Ambil semua parameter filter dari URL
// ==========================================================
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';
$id_mobil = isset($_GET['id_mobil']) ? (int)$_GET['id_mobil'] : 0;
$kelas_mobil = $_GET['kelas_mobil'] ?? '';
$jenis_mobil = $_GET['jenis'] ?? '';
$nama_pelanggan = $_GET['nama_pelanggan'] ?? '';
$search_query = $_GET['q'] ?? '';

// ==========================================================
// 2. Bangun query dinamis yang SAMA PERSIS seperti di history.php
// ==========================================================
$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model, m.plat_nomor FROM pemesanan p 
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna 
        JOIN mobil m ON p.id_mobil = m.id_mobil WHERE 1=1";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (p.kode_pemesanan LIKE :q OR pg.nama_lengkap LIKE :q OR m.merk LIKE :q OR m.model LIKE :q)";
    $params[':q'] = "%$search_query%";
}
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql .= " AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
}
if (!empty($status_filter)) {
    $sql .= " AND p.status_pemesanan = ?";
    $params[] = $status_filter;
}
if ($id_mobil > 0) {
    $sql .= " AND p.id_mobil = ?";
    $params[] = $id_mobil;
}
if (!empty($kelas_mobil)) {
    $sql .= " AND m.kelas_mobil = ?";
    $params[] = $kelas_mobil;
}
if (!empty($jenis_mobil)) {
    $sql .= " AND m.jenis_mobil = ?";
    $params[] = $jenis_mobil;
}
if (!empty($nama_pelanggan)) {
    $sql .= " AND pg.nama_lengkap LIKE ?";
    $params[] = "%$nama_pelanggan%";
}
$sql .= " ORDER BY p.tanggal_pemesanan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// 3. Buat judul periode yang dinamis
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $periode_text = "Periode: " . date('d F Y', strtotime($tgl_awal)) . " - " . date('d F Y', strtotime($tgl_akhir));
} else {
    $periode_text = "Semua Riwayat Transaksi";
}

// 4. Buat konten HTML untuk PDF
$html = "
<html><head><style>
    body { font-family: 'Helvetica', sans-serif; font-size: 9px; }
    .header { text-align: center; margin-bottom: 20px; }
    h1 { margin: 0; } p { margin: 5px 0; font-size: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    tfoot tr td { font-weight: bold; background-color: #f2f2f2; }
    .text-right { text-align: right; }
</style></head><body>
    <div class='header'>
        <h1>Laporan Transaksi</h1>
        <p>{$periode_text}</p>
    </div>
    <table>
        <thead><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Mobil</th><th>Plat</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>";

$total_pendapatan = 0;
if (empty($data)) {
    $html .= "<tr><td colspan='7' style='text-align:center;'>Tidak ada data yang ditemukan.</td></tr>";
} else {
    foreach ($data as $item) {
        $total_item = $item['total_biaya'] + $item['total_denda'];
        $total_pendapatan += $total_item;
        $html .= "<tr>
                    <td>{$item['kode_pemesanan']}</td>
                    <td>" . date('d-m-Y', strtotime($item['tanggal_pemesanan'])) . "</td>
                    <td>" . htmlspecialchars($item['nama_lengkap']) . "</td>
                    <td>" . htmlspecialchars($item['merk'] . ' ' . $item['model']) . "</td>
                    <td>" . htmlspecialchars($item['plat_nomor']) . "</td>
                    <td class='text-right'>" . format_rupiah($total_item) . "</td>
                    <td>{$item['status_pemesanan']}</td>
                </tr>";
    }
}

$html .= "
        </tbody>
        <tfoot>
            <tr>
                <td colspan='5' class='text-right'>TOTAL KESELURUHAN</td>
                <td colspan='2' class='text-right'>" . format_rupiah($total_pendapatan) . "</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>";

// 5. Render PDF dengan Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'laporan-transaksi-' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ["Attachment" => 0]);
