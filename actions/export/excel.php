<?php
// File: actions/export/excel.php
require_once '../../vendor/autoload.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil semua parameter filter dari URL
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');
$status = $_GET['status'] ?? '';
$nama_pelanggan = $_GET['nama_pelanggan'] ?? '';
$kode_pesanan = $_GET['kode_pesanan'] ?? '';
$mobil = $_GET['mobil'] ?? '';

// Bangun query dinamis (SAMA PERSIS SEPERTI DI history.php ADMIN)
$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model FROM pemesanan p JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna JOIN mobil m ON p.id_mobil = m.id_mobil WHERE 1=1";
$params = [];
$sql .= " AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?";
$params[] = $tgl_awal; $params[] = $tgl_akhir;
if (!empty($status)) { $sql .= " AND p.status_pemesanan = ?"; $params[] = $status; }
if (!empty($nama_pelanggan)) { $sql .= " AND pg.nama_lengkap LIKE ?"; $params[] = "%$nama_pelanggan%"; }
if (!empty($kode_pesanan)) { $sql .= " AND p.kode_pemesanan LIKE ?"; $params[] = "%$kode_pesanan%"; }
if (!empty($mobil)) { $sql .= " AND (m.merk LIKE ? OR m.model LIKE ?)"; $params[] = "%$mobil%"; $params[] = "%$mobil%"; }
$sql .= " ORDER BY p.tanggal_pemesanan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Transaksi');

$sheet->setCellValue('A1', 'LAPORAN TRANSAKSI RENTAL MOBIL');
$sheet->mergeCells('A1:G1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tgl_awal)) . ' - ' . date('d/m/Y', strtotime($tgl_akhir)));
$sheet->mergeCells('A2:G2');

$sheet->setCellValue('A4', 'KODE');
$sheet->setCellValue('B4', 'TGL PESAN');
$sheet->setCellValue('C4', 'PELANGGAN');
$sheet->setCellValue('D4', 'MOBIL');
$sheet->setCellValue('E4', 'TOTAL BIAYA');
$sheet->setCellValue('F4', 'DENDA');
$sheet->setCellValue('G4', 'STATUS');
$sheet->getStyle('A4:G4')->getFont()->setBold(true);

$row = 5;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $row, $item['kode_pemesanan']);
    $sheet->setCellValue('B' . $row, date('Y-m-d', strtotime($item['tanggal_pemesanan'])));
    $sheet->setCellValue('C' . $row, $item['nama_lengkap']);
    $sheet->setCellValue('D' . $row, $item['merk'] . ' ' . $item['model']);
    $sheet->setCellValue('E' . $row, $item['total_biaya']);
    $sheet->setCellValue('F' . $row, $item['total_denda']);
    $sheet->setCellValue('G' . $row, $item['status_pemesanan']);
    $row++;
}

foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$filename = 'laporan-transaksi-' . $tgl_awal . '-sd-' . $tgl_akhir . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;