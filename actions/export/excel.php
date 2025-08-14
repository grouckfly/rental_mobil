<?php
// File: actions/export/excel.php (Versi Final Disempurnakan)

// Memuat library dari Composer dan file konfigurasi
require_once '../../vendor/autoload.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Menggunakan class dari PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// 1. Ambil semua parameter filter dari URL
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';
$id_mobil = isset($_GET['id_mobil']) ? (int)$_GET['id_mobil'] : 0;
$kelas_mobil = $_GET['kelas_mobil'] ?? '';
$jenis_mobil = $_GET['jenis'] ?? '';
$nama_pelanggan = $_GET['nama_pelanggan'] ?? '';
$search_query = $_GET['q'] ?? '';

// 2. Bangun query dinamis
$sql = "SELECT p.*, pg.nama_lengkap, m.merk, m.model, m.plat_nomor FROM pemesanan p 
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna 
        JOIN mobil m ON p.id_mobil = m.id_mobil WHERE 1=1";
$params = [];
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql .= " AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
}
if (empty($tgl_awal) && empty($tgl_akhir)) {
    $tgl_awal = date('Y-m-d');
    $tgl_akhir = date('Y-m-d');
    $is_default_date = true;
}
if (!empty($status_filter)) {
    $sql .= " AND p.status_pemesanan = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY p.tanggal_pemesanan DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Buat objek Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Transaksi');

// --- Judul Laporan ---
$sheet->setCellValue('A1', 'LAPORAN TRANSAKSI RENTAL MOBIL');
$sheet->mergeCells('A1:K1'); // Sesuaikan dengan jumlah kolom
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
if ($is_default_date) {
    $periode_text = 'Tanggal: ' . date('d F Y', strtotime($tgl_awal));
} else {
    $periode_text = 'Periode: ' . date('d F Y', strtotime($tgl_awal)) . ' - ' . date('d F Y', strtotime($tgl_akhir));
}
$sheet->setCellValue('A2', $periode_text);
$sheet->mergeCells('A2:K2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- Styling untuk Header Tabel ---
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007bff']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]]
];
$sheet->getStyle('A4:K4')->applyFromArray($headerStyle);

// --- Menulis Header Tabel ---
$sheet->setCellValue('A4', 'KODE');
$sheet->setCellValue('B4', 'TGL PESAN');
$sheet->setCellValue('C4', 'PELANGGAN');
$sheet->setCellValue('D4', 'MOBIL');
$sheet->setCellValue('E4', 'PLAT NOMOR');
$sheet->setCellValue('F4', 'MULAI SEWA');
$sheet->setCellValue('G4', 'SELESAI SEWA');
$sheet->setCellValue('H4', 'BIAYA SEWA');
$sheet->setCellValue('I4', 'DENDA');
$sheet->setCellValue('J4', 'TOTAL PENDAPATAN');
$sheet->setCellValue('K4', 'STATUS');

// --- Menulis Data ke dalam Baris ---
$row = 5;
$total_pendapatan = 0;
foreach ($data as $item) {
    $total_item = $item['total_biaya'] + $item['total_denda'];
    $total_pendapatan += $total_item;

    $sheet->setCellValue('A' . $row, $item['kode_pemesanan']);
    $sheet->setCellValue('B' . $row, date('d-m-Y', strtotime($item['tanggal_pemesanan'])));
    $sheet->setCellValue('C' . $row, $item['nama_lengkap']);
    $sheet->setCellValue('D' . $row, $item['merk'] . ' ' . $item['model']);
    $sheet->setCellValue('E' . $row, $item['plat_nomor']);
    $sheet->setCellValue('F' . $row, date('d-m-Y H:i', strtotime($item['tanggal_mulai'])));
    $sheet->setCellValue('G' . $row, date('d-m-Y H:i', strtotime($item['tanggal_selesai'])));
    $sheet->setCellValue('H' . $row, $item['total_biaya']);
    $sheet->setCellValue('I' . $row, $item['total_denda']);
    $sheet->setCellValue('J' . $row, $total_item);
    $sheet->setCellValue('K' . $row, $item['status_pemesanan']);
    $row++;
}

// --- Menambahkan Baris Total di Akhir ---
$summaryRow = $row + 1;
$sheet->setCellValue('I' . $summaryRow, 'TOTAL KESELURUHAN');
$sheet->mergeCells('I' . $summaryRow . ':J' . $summaryRow);
$sheet->getStyle('I' . $summaryRow)->getFont()->setBold(true);
$sheet->getStyle('I' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->setCellValue('K' . $summaryRow, $total_pendapatan);
$sheet->getStyle('K' . $summaryRow)->getFont()->setBold(true);


// --- Styling Tambahan ---
// Format angka menjadi Rupiah
$currencyFormat = '"Rp " #,##0';
$sheet->getStyle('H5:J' . $row)->getNumberFormat()->setFormatCode($currencyFormat);
$sheet->getStyle('K' . $summaryRow)->getNumberFormat()->setFormatCode($currencyFormat);

// Atur lebar kolom otomatis
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Tambahkan border ke seluruh tabel data
$borderStyle = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]];
$sheet->getStyle('A4:K' . ($row - 1))->applyFromArray($borderStyle);
$sheet->getStyle('I' . $summaryRow . ':K' . $summaryRow)->applyFromArray($borderStyle);

// Bekukan baris header (Freeze Pane)
$sheet->freezePane('A5');

// --- Mengirim File ke Browser ---
$filename = 'laporan-transaksi-' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
