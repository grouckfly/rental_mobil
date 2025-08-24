<?php
// File: admin/laporan.php (Versi Disempurnakan)

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_auth(['Admin', 'Karyawan']);
$page_title = 'Laporan & Analisa';
require_once '../includes/header.php';

// Atur filter tanggal, default untuk bulan berjalan
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t');

// Inisialisasi array untuk hasil laporan
$mobil_populer = [];
$mobil_profit = [];
$pelanggan_top = [];
$total_pendapatan_periode = 0;
$total_transaksi_periode = 0;

try {
    // Satu query besar untuk mengambil semua data transaksi yang relevan
    $stmt = $pdo->prepare("
        SELECT 
            p.id_mobil, p.id_pengguna,
            m.merk, m.model, m.plat_nomor,
            u.nama_lengkap, u.email,
            (p.total_biaya + p.total_denda) AS pendapatan_per_pesanan
        FROM pemesanan p
        JOIN mobil m ON p.id_mobil = m.id_mobil
        JOIN pengguna u ON p.id_pengguna = u.id_pengguna
        WHERE p.status_pemesanan = 'Selesai' 
          AND DATE(p.tanggal_pemesanan) BETWEEN ? AND ?
    ");
    $stmt->execute([$tgl_awal, $tgl_akhir]);
    $all_transactions = $stmt->fetchAll();

    // Olah data menggunakan PHP
    $mobil_stats = [];
    $pelanggan_stats = [];
    $total_transaksi_periode = count($all_transactions);

    foreach ($all_transactions as $trx) {
        $total_pendapatan_periode += $trx['pendapatan_per_pesanan'];
        $mobil_id = $trx['id_mobil'];
        $pelanggan_id = $trx['id_pengguna'];

        // Agregasi data mobil
        if (!isset($mobil_stats[$mobil_id])) {
            $mobil_stats[$mobil_id] = [
                'nama' => $trx['merk'] . ' ' . $trx['model'],
                'plat_nomor' => $trx['plat_nomor'],
                'jumlah_sewa' => 0,
                'total_pendapatan' => 0
            ];
        }
        $mobil_stats[$mobil_id]['jumlah_sewa']++;
        $mobil_stats[$mobil_id]['total_pendapatan'] += $trx['pendapatan_per_pesanan'];

        // Agregasi data pelanggan
        if (!isset($pelanggan_stats[$pelanggan_id])) {
            $pelanggan_stats[$pelanggan_id] = ['nama' => $trx['nama_lengkap'], 'email' => $trx['email'], 'jumlah_sewa' => 0, 'total_belanja' => 0];
        }
        $pelanggan_stats[$pelanggan_id]['jumlah_sewa']++;
        $pelanggan_stats[$pelanggan_id]['total_belanja'] += $trx['pendapatan_per_pesanan'];
    }

    // Urutkan dan potong hasil agregasi
    uasort($mobil_stats, fn($a, $b) => $b['jumlah_sewa'] <=> $a['jumlah_sewa']);
    $mobil_populer = array_slice($mobil_stats, 0, 10, true);

    uasort($mobil_stats, fn($a, $b) => $b['total_pendapatan'] <=> $a['total_pendapatan']);
    $mobil_profit = array_slice($mobil_stats, 0, 10, true);

    uasort($pelanggan_stats, fn($a, $b) => $b['total_belanja'] <=> $a['total_belanja']);
    $pelanggan_top = array_slice($pelanggan_stats, 0, 10, true);
} catch (PDOException $e) { /* Tangani error */
}
?>

<div class="page-header">
    <h1>Laporan & Analisa</h1>
    <p>Analisis kinerja bisnis Anda berdasarkan rentang tanggal yang dipilih.</p>
</div>

<div class="filter-container">
    <form action="" method="GET" class="filter-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="form-group"><label>Dari Tanggal</label><input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
        <div class="form-group"><label>Sampai Tanggal</label><input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
        <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
    </form>
</div>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Total Pendapatan</h3>
        <p class="widget-data price"><?= format_rupiah($total_pendapatan_periode) ?></p>
        <div class="widget-details"><span>Dalam periode yang dipilih</span></div>
    </div>
    <div class="widget">
        <h3>Total Transaksi</h3>
        <p class="widget-data"><?= $total_transaksi_periode ?></p>
        <div class="widget-details"><span>Transaksi yang selesai</span></div>
    </div>
</div>

<div class="report-grid">
    <div class="table-container">
        <h2>Top 10 Mobil Terpopuler</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mobil</th>
                    <th>Plat Nomor</th>
                    <th>Jumlah Disewa</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mobil_populer)): ?><tr>
                        <td colspan="3">Tidak ada data.</td>
                    </tr><?php else: $i = 1;
                            foreach ($mobil_populer as $item): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($item['nama']) ?></td>
                            <td><?= htmlspecialchars($item['plat_nomor']) ?></td>
                            <td><?= $item['jumlah_sewa'] ?> kali</td>
                        </tr>
                <?php endforeach;
                        endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h2>Top 10 Mobil Paling Menguntungkan</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Mobil</th>
                    <th>Plat Nomor</th>
                    <th>Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mobil_profit)): ?><tr>
                        <td colspan="3">Tidak ada data.</td>
                    </tr><?php else: $i = 1;
                            foreach ($mobil_profit as $item): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($item['nama']) ?></td>
                            <td><?= htmlspecialchars($item['plat_nomor']) ?></td>
                            <td><?= format_rupiah($item['total_pendapatan']) ?></td>
                        </tr>
                <?php endforeach;
                        endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container full-width">
        <h2>Top 10 Pelanggan Terbaik</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>Jumlah Sewa</th>
                    <th>Total Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pelanggan_top)): ?><tr>
                        <td colspan="5">Tidak ada data.</td>
                    </tr><?php else: $i = 1;
                            foreach ($pelanggan_top as $item): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($item['nama']) ?></td>
                            <td><?= htmlspecialchars($item['email']) ?></td>
                            <td><?= $item['jumlah_sewa'] ?> kali</td>
                            <td><?= format_rupiah($item['total_belanja']) ?></td>
                        </tr>
                <?php endforeach;
                        endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>