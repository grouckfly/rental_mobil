<?php
// File: admin/_template_history_table.php
// File ini hanya berisi HTML untuk tabel riwayat
?>
<table>
    <thead>
        <tr>
            <th>Kode</th>
            <?php if (in_array($_SESSION['role'], ['Admin', 'Karyawan'])): ?><th>Pelanggan</th><?php endif; ?>
            <th>Mobil</th>
            <th>Tanggal</th>
            <th>Total Bayar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($histories)): ?>
            <?php
            $filtered_histories = array_filter($histories, function ($history) {
                return in_array($history['status_pemesanan'], ['Selesai', 'Dibatalkan']);
            });
            ?>
            <?php if (!empty($filtered_histories)): ?>
                <?php foreach ($filtered_histories as $history): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($history['kode_pemesanan']) ?></strong></td>
                        <?php if (in_array($_SESSION['role'], ['Admin', 'Karyawan'])): ?><td><?= htmlspecialchars($history['nama_lengkap']) ?></td><?php endif; ?>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= date('d M Y', strtotime($history['tanggal_pemesanan'])) ?></td>
                        <td><?= format_rupiah($history['total_biaya'] + $history['total_denda']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td><a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Tidak ada riwayat dengan status "Selesai" atau "Dibatalkan" yang ditemukan.</td>
                </tr>
            <?php endif; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">Tidak ada riwayat yang ditemukan sesuai kriteria.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>