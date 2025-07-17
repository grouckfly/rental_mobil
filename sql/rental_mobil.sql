-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2025 at 10:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rental_mobil`
--

-- --------------------------------------------------------

--
-- Table structure for table `mobil`
--

CREATE TABLE `mobil` (
  `id_mobil` int(11) NOT NULL,
  `plat_nomor` varchar(10) NOT NULL,
  `merk` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `tahun` year(4) NOT NULL,
  `jenis_mobil` varchar(50) DEFAULT NULL,
  `harga_sewa_harian` decimal(10,2) NOT NULL,
  `denda_per_hari` decimal(10,2) NOT NULL,
  `status` enum('Tersedia','Disewa','Perawatan') NOT NULL DEFAULT 'Tersedia',
  `gambar_mobil` varchar(255) DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mobil`
--

INSERT INTO `mobil` (`id_mobil`, `plat_nomor`, `merk`, `model`, `tahun`, `jenis_mobil`, `harga_sewa_harian`, `denda_per_hari`, `status`, `gambar_mobil`, `spesifikasi`, `updated_at`) VALUES
(3, 'W 4 NI', 'Ford', 'Everest Titanium', '2024', 'SUV', 650000.00, 50000.00, 'Tersedia', '68737c74e045a3.41489030.jpg', 'America aaaaa', '2025-07-17 08:29:22'),
(4, 'M 45 DA', 'Mazda', 'CX-5', '2024', 'SUV', 700000.00, 70000.00, 'Tersedia', '68737fcdb2bf74.16132469.jpg', 'Mazda vrummm', '2025-07-13 09:43:41');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pemesanan` int(11) NOT NULL,
  `tanggal_bayar` datetime NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status_pembayaran` enum('Menunggu Verifikasi','Diverifikasi','Ditolak') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `id_karyawan_verif` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pemesanan`, `tanggal_bayar`, `jumlah_bayar`, `metode_pembayaran`, `bukti_pembayaran`, `status_pembayaran`, `keterangan`, `id_karyawan_verif`) VALUES
(1, 1, '2025-07-13 19:24:37', 1400000.00, 'Transfer Bank', '6873a58507e960.39260916.jpg', '', NULL, NULL),
(2, 2, '2025-07-13 19:51:10', 650000.00, 'Transfer Bank', '6873abbe6540b4.80897099.jpg', 'Diverifikasi', NULL, 2),
(3, 3, '2025-07-16 11:02:58', 650000.00, 'Transfer Bank', '68772472a9fc68.30276027.jpg', 'Diverifikasi', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `kode_pemesanan` varchar(10) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_mobil` int(11) NOT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `tanggal_pengembalian` datetime DEFAULT NULL,
  `total_biaya` decimal(12,2) NOT NULL,
  `total_denda` decimal(12,2) DEFAULT 0.00,
  `total_biaya_diajukan` decimal(12,2) DEFAULT NULL,
  `status_pemesanan` enum('Menunggu Pembayaran','Dikonfirmasi','Berjalan','Selesai','Dibatalkan','Pengajuan Pembatalan','Pengajuan Ambil Cepat','Menunggu Pembayaran Denda','Pengajuan Ditolak') NOT NULL,
  `alasan_pembatalan` text DEFAULT NULL,
  `rekening_pembatalan` varchar(50) DEFAULT NULL,
  `catatan_admin` varchar(255) DEFAULT NULL,
  `waktu_pengambilan` datetime DEFAULT NULL,
  `waktu_pengembalian` datetime DEFAULT NULL,
  `tgl_mulai_diajukan` datetime DEFAULT NULL,
  `tanggal_pemesanan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `kode_pemesanan`, `id_pengguna`, `id_mobil`, `tanggal_mulai`, `tanggal_selesai`, `tanggal_pengembalian`, `total_biaya`, `total_denda`, `total_biaya_diajukan`, `status_pemesanan`, `alasan_pembatalan`, `rekening_pembatalan`, `catatan_admin`, `waktu_pengambilan`, `waktu_pengembalian`, `tgl_mulai_diajukan`, `tanggal_pemesanan`) VALUES
(1, 'BOOK-U5H7K', 3, 4, '2025-07-18 00:00:00', '2025-07-20 00:00:00', NULL, 1400000.00, 0.00, NULL, 'Dibatalkan', 'Testing', '19y823194y2 an wawan', NULL, NULL, NULL, NULL, '2025-07-13 12:04:04'),
(2, 'BOOK-WVDHT', 3, 3, '2025-07-13 00:00:00', '2025-07-14 00:00:00', NULL, 650000.00, 150000.00, NULL, 'Selesai', NULL, NULL, NULL, '2025-07-13 21:50:14', '2025-07-16 10:53:01', NULL, '2025-07-13 12:50:09'),
(3, 'BOOK-T40O5', 3, 3, '2025-07-17 00:00:00', '2025-07-18 00:00:00', NULL, 650000.00, 0.00, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-16 04:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('Admin','Karyawan','Pelanggan') NOT NULL DEFAULT 'Pelanggan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `username`, `password`, `nama_lengkap`, `email`, `no_telp`, `alamat`, `role`, `created_at`) VALUES
(1, 'admin1', '$2y$10$XVska1TxhC3PoqFFdG662u7z92jXpCt2R0sFFHUjENdojZqnLeiNi', 'admin', 'admin@email.com', '081234567890', 'yo ndak tau ler', 'Admin', '2025-07-13 07:17:20'),
(2, 'karyawan1', '$2y$10$Vp9b4.pFgY9isyEpByHjP.14LjyI3Keq6CT.kyrdTrHXHElneO9TS', 'karyawan', 'karyawan@email.com', '080987654321', NULL, 'Karyawan', '2025-07-13 07:19:46'),
(3, 'pelanggan1', '$2y$10$OMOPWI8SLcN/UPQ0ssoLZ.qc9dH2XPVu4w.AV9W7vsfMFMdl7MmTq', 'pelanggan', 'pelanggan@email.com', '088765123490', NULL, 'Pelanggan', '2025-07-13 07:20:33'),
(6, 'pelanggan2', '$2y$10$mI98taoejY2Z31EOxRVbwesOcRYAcWgp6ptv.vqQbhjxomQF3XBUS', 'pelanggan2', 'pelanggan2@email.com', '089012368173', NULL, 'Pelanggan', '2025-07-13 11:52:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mobil`
--
ALTER TABLE `mobil`
  ADD PRIMARY KEY (`id_mobil`),
  ADD UNIQUE KEY `plat_nomor` (`plat_nomor`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_pemesanan` (`id_pemesanan`),
  ADD KEY `fk_pembayaran_karyawan` (`id_karyawan_verif`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD UNIQUE KEY `kode_pemesanan` (`kode_pemesanan`),
  ADD KEY `fk_pemesanan_pengguna` (`id_pengguna`),
  ADD KEY `fk_pemesanan_mobil` (`id_mobil`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mobil`
--
ALTER TABLE `mobil`
  MODIFY `id_mobil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_karyawan` FOREIGN KEY (`id_karyawan_verif`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pembayaran_pemesanan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `fk_pemesanan_mobil` FOREIGN KEY (`id_mobil`) REFERENCES `mobil` (`id_mobil`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pemesanan_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
