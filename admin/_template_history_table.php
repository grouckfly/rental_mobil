<?php
// File: admin/_template_history_table.php
// File ini hanya berisi HTML untuk tabel
?>
<table>
        <thead>
            <tr>
                <th>Kode</th><?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?><th>Pelanggan</th><?php endif; ?><th>Mobil</th>
                <th>Tanggal</th>
                <th>Total Bayar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($histories)): foreach ($histories as $history): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($history['kode_pemesanan']) ?></strong></td>
                        <?php if (in_array($role_session, ['Admin', 'Karyawan'])): ?><td><?= htmlspecialchars($history['nama_lengkap']) ?></td><?php endif; ?>
                        <td><?= htmlspecialchars($history['merk'] . ' ' . $history['model']) ?></td>
                        <td><?= date('d M Y', strtotime($history['tanggal_pemesanan'])) ?></td>
                        <td><?= format_rupiah($history['total_biaya'] + $history['total_denda']) ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $history['status_pemesanan'])) ?>"><?= htmlspecialchars($history['status_pemesanan']) ?></span></td>
                        <td><a href="<?= BASE_URL ?>actions/pemesanan/detail.php?id=<?= $history['id_pemesanan'] ?>" class="btn btn-info btn-sm">Lihat Detail</a></td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="7">Tidak ada riwayat yang ditemukan sesuai kriteria.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>