<?php
// File: actions/export/pdf.php
require_once '../../vendor/autoload.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model FROM pemesanan p JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna JOIN mobil m ON p.id_mobil = m.id_mobil WHERE DATE(p.tanggal_pemesanan) BETWEEN ? AND ? ORDER BY p.tanggal_pemesanan DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tgl_awal, $tgl_akhir]);
$data = $stmt->fetchAll();

$html = "
<html><head><style>
    body { font-family: 'Helvetica', sans-serif; font-size: 10px; }
    .header { text-align: center; margin-bottom: 20px; }
    h1 { margin: 0; } p { margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background-color: #f2f2f2; }
</style></head><body>
    <div class='header'>
        <h1>Laporan Transaksi</h1>
        <p>Periode: ".date('d F Y', strtotime($tgl_awal))." - ".date('d F Y', strtotime($tgl_akhir))."</p>
    </div>
    <table><thead><tr><th>Kode</th><th>Tanggal</th><th>Pelanggan</th><th>Mobil</th><th>Total</th><th>Denda</th><th>Status</th></tr></thead><tbody>";

foreach ($data as $item) {
    $html .= "<tr>
                <td>{$item['kode_pemesanan']}</td>
                <td>".date('d-m-Y', strtotime($item['tanggal_pemesanan']))."</td>
                <td>{$item['nama_lengkap']}</td>
                <td>{$item['merk']} {$item['model']}</td>
                <td>".format_rupiah($item['total_biaya'])."</td>
                <td>".format_rupiah($item['total_denda'])."</td>
                <td>{$item['status_pemesanan']}</td>
            </tr>";
}
$html .= "</tbody></table></body></html>";

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'laporan-transaksi-' . $tgl_awal . '-sd-' . $tgl_akhir . '.pdf';
$dompdf->stream($filename, ["Attachment" => 0]); // Set Attachment ke 1 untuk langsung download