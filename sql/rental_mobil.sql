-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2025 at 08:03 PM
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
  `status` enum('Tersedia','Disewa','Perawatan','Tidak Aktif') NOT NULL DEFAULT 'Tersedia',
  `gambar_mobil` varchar(255) DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `kelas_mobil` enum('Low level','Mid level','High level','Luxury') DEFAULT 'Mid level',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mobil`
--

INSERT INTO `mobil` (`id_mobil`, `plat_nomor`, `merk`, `model`, `tahun`, `jenis_mobil`, `harga_sewa_harian`, `denda_per_hari`, `status`, `gambar_mobil`, `spesifikasi`, `kelas_mobil`, `updated_at`) VALUES
(3, 'W 4 NI', 'Ford', 'Everest Titanium', '2024', 'SUV', 650000.00, 50000.00, 'Tersedia', '68737c74e045a3.41489030.jpg', 'America aaaaa', 'Mid level', '2025-07-17 08:29:22'),
(4, 'M 45 DA', 'Mazda', 'CX-5', '2024', 'SUV', 700000.00, 70000.00, 'Tidak Aktif', '68737fcdb2bf74.16132469.jpg', 'Mazda vrummm', 'Mid level', '2025-07-19 08:00:26'),
(18, 'B 1101 TY', 'Toyota', 'Avanza G', '2023', 'MPV', 400000.00, 50000.00, 'Tersedia', '687bb636314b54.12310854.jpg', '7 Seater, Bensin, Otomatis', 'Low level', '2025-07-19 15:14:14'),
(19, 'B 1102 TY', 'Toyota', 'Kijang Innova Zenix', '2024', 'MPV', 750000.00, 100000.00, 'Tersedia', 'innova.jpg', '7 Seater, Hybrid, Otomatis, Captain Seat', 'Mid level', '2025-07-19 08:28:15'),
(20, 'B 1103 TY', 'Toyota', 'Fortuner VRZ', '2022', 'SUV', 900000.00, 150000.00, 'Tersedia', '687bb66a762136.20231195.jpg', '7 Seater, Diesel, 4x2, Otomatis', 'High level', '2025-07-19 15:14:50'),
(21, 'B 1104 TY', 'Toyota', 'Alphard G', '2023', 'MPV Premium', 1500000.00, 200000.00, 'Tersedia', 'alphard.jpg', '6 Seater, Bensin, Pilot Seat', 'Luxury', '2025-07-19 08:28:15'),
(22, 'B 1105 TY', 'Toyota', 'Yaris Cross', '2024', 'SUV Compact', 550000.00, 60000.00, 'Tersedia', 'yaris.jpg', '5 Seater, Hybrid, Otomatis', 'Mid level', '2025-07-19 08:48:06'),
(23, 'D 1201 DH', 'Daihatsu', 'Xenia R', '2023', 'MPV', 350000.00, 50000.00, 'Tersedia', 'xenia.jpg', '7 Seater, Bensin, Manual', 'Low level', '2025-07-19 08:28:15'),
(24, 'D 1202 DH', 'Daihatsu', 'Terios R', '2024', 'SUV', 420000.00, 55000.00, 'Tersedia', 'terios.jpg', '7 Seater, Bensin, Otomatis', 'Mid level', '2025-07-19 08:28:15'),
(25, 'D 1203 DH', 'Daihatsu', 'Rocky', '2023', 'SUV Compact', 430000.00, 60000.00, 'Tersedia', 'rocky.jpg', '5 Seater, Bensin Turbo, Otomatis', 'Mid level', '2025-07-19 08:28:15'),
(26, 'F 1301 HN', 'Honda', 'Brio Satya', '2024', 'LCGC', 275000.00, 40000.00, 'Tersedia', '687bb65e88f2c2.62750985.jpg', '5 Seater, Bensin, Manual', 'Low level', '2025-07-19 17:47:49'),
(27, 'F 1302 HN', 'Honda', 'HR-V SE', '2023', 'SUV', 650000.00, 80000.00, 'Tersedia', 'hrv.jpg', '5 Seater, Otomatis, Panoramic Sunroof', 'Mid level', '2025-07-19 08:28:15'),
(28, 'F 1303 HN', 'Honda', 'CR-V Hybrid', '2024', 'SUV', 950000.00, 120000.00, 'Tersedia', 'crv.jpg', '7 Seater, Hybrid, Otomatis, Honda Sensing', 'High level', '2025-07-19 08:47:22'),
(29, 'F 1304 HN', 'Honda', 'Civic RS', '2023', 'Sedan', 800000.00, 100000.00, 'Tersedia', 'civic.jpg', '5 Seater, Bensin Turbo, Otomatis', 'High level', '2025-07-19 08:28:15'),
(30, 'F 1305 HN', 'Honda', 'Accord RS', '2024', 'Sedan', 1000000.00, 150000.00, 'Tersedia', 'accord.jpg', '5 Seater, Hybrid, Otomatis', 'High level', '2025-07-19 08:28:15'),
(31, 'B 1401 MZ', 'Mazda', 'CX-5', '2023', 'SUV', 800000.00, 110000.00, 'Tersedia', 'cx5.jpg', '5 Seater, Bensin, Premium Interior', 'High level', '2025-07-19 08:28:15'),
(32, 'B 1402 MZ', 'Mazda', '2 Sedan', '2024', 'Sedan', 500000.00, 60000.00, 'Tersedia', 'mazda2.jpg', '5 Seater, Bensin, Otomatis', 'Mid level', '2025-07-19 08:28:15'),
(33, 'B 1501 SZ', 'Suzuki', 'Ertiga Hybrid', '2024', 'MPV', 450000.00, 60000.00, 'Tersedia', 'ertiga.jpg', '7 Seater, Mild Hybrid, Otomatis', 'Mid level', '2025-07-19 08:28:15'),
(34, 'B 1502 SZ', 'Suzuki', 'Grand Vitara', '2024', 'SUV', 550000.00, 65000.00, 'Tersedia', 'vitara.jpg', '5 Seater, Hybrid, Otomatis', 'Mid level', '2025-07-19 08:28:15'),
(35, 'B 1601 IS', 'Isuzu', 'MU-X', '2022', 'SUV', 700000.00, 90000.00, 'Tersedia', 'mux.jpg', '7 Seater, Diesel, 4x4, Tangguh', 'High level', '2025-07-19 08:28:15'),
(36, 'B 1701 SB', 'Subaru', 'Crosstrek', '2024', 'SUV Crossover', 750000.00, 100000.00, 'Tersedia', 'crosstrek.jpg', '5 Seater, Bensin, Symmetrical AWD', 'High level', '2025-07-19 08:28:15'),
(37, 'B 1801 NS', 'Nissan', 'Kicks e-Power', '2023', 'SUV Hybrid', 600000.00, 70000.00, 'Tersedia', 'kicks.jpg', '5 Seater, Hybrid, One-Pedal Operation', 'Mid level', '2025-07-19 08:28:15'),
(38, 'B 1802 NS', 'Nissan', 'Terra VL', '2022', 'SUV', 750000.00, 95000.00, 'Tersedia', 'terra.jpg', '7 Seater, Diesel, 4x4', 'High level', '2025-07-19 08:28:15'),
(39, 'B 1901 MT', 'Mitsubishi', 'Pajero Sport Dakar', '2023', 'SUV', 950000.00, 150000.00, 'Tersedia', '687bb677e43d05.71645899.jpg', '7 Seater, Diesel, Otomatis, Sunroof', 'High level', '2025-07-19 15:15:03'),
(40, 'B 1902 MT', 'Mitsubishi', 'Xforce Ultimate', '2024', 'SUV Compact', 500000.00, 60000.00, 'Tersedia', 'xforce.jpg', '5 Seater, Bensin, Audio by Yamaha', 'Mid level', '2025-07-19 08:28:15'),
(41, 'B 1903 MT', 'Mitsubishi', 'Triton', '2023', 'Double Cabin', 800000.00, 100000.00, 'Tidak Aktif', 'triton.jpg', '5 Seater, Diesel, 4x4', 'High level', '2025-07-19 08:28:15'),
(42, 'B 2001 LX', 'Lexus', 'RX 350h', '2024', 'SUV', 2000000.00, 250000.00, 'Tersedia', 'lexus_rx.jpg', '5 Seater, Hybrid, Premium Comfort', 'Luxury', '2025-07-19 08:28:15'),
(43, 'B 2002 LX', 'Lexus', 'LM 350h', '2024', 'MPV Premium', 3500000.00, 400000.00, 'Tersedia', 'lexus_lm.jpg', '4 Seater, Hybrid, VIP Lounge', 'Luxury', '2025-07-19 08:28:15'),
(44, 'B 3001 BW', 'BMW', '330i M Sport', '2023', 'Sedan', 1300000.00, 180000.00, 'Tersedia', 'bmw3.jpg', '5 Seater, Bensin, Sporty Handling', 'High level', '2025-07-19 08:28:15'),
(45, 'B 3002 BW', 'BMW', 'X1 sDrive18i', '2024', 'SUV', 1000000.00, 150000.00, 'Tersedia', 'bmwx1.jpg', '5 Seater, Bensin, Compact Luxury', 'High level', '2025-07-19 08:28:15'),
(46, 'B 3003 BW', 'BMW', 'i7', '2024', 'EV Sedan', 4000000.00, 500000.00, 'Tersedia', 'bmwi7.jpg', '5 Seater, Listrik, Theatre Screen', 'Luxury', '2025-07-19 08:28:15'),
(47, 'B 4001 MB', 'Mercedes Benz', 'C200', '2023', 'Sedan', 1100000.00, 170000.00, 'Tersedia', 'mb_c200.jpg', '5 Seater, Bensin, Elegant Design', 'High level', '2025-07-19 08:28:15'),
(48, 'B 4002 MB', 'Mercedes Benz', 'GLC 300', '2024', 'SUV', 1800000.00, 220000.00, 'Tersedia', 'mb_glc.jpg', '5 Seater, Bensin, MBUX Infotainment', 'Luxury', '2025-07-19 08:47:30'),
(49, 'B 4003 MB', 'Mercedes Benz', 'V-Class', '2022', 'MPV Premium', 2200000.00, 280000.00, 'Tersedia', 'mb_v.jpg', '7 Seater, Diesel, Business Lounge', 'Luxury', '2025-07-19 08:28:15'),
(50, 'B 5001 VW', 'Volkswagen', 'T-Cross', '2023', 'SUV Compact', 600000.00, 70000.00, 'Tersedia', 'vw_tcross.jpg', '5 Seater, Bensin Turbo, European Build', 'Mid level', '2025-07-19 08:28:15'),
(51, 'B 6001 AD', 'Audi', 'Q3', '2023', 'SUV', 1000000.00, 150000.00, 'Tersedia', 'audi_q3.jpg', '5 Seater, Bensin, Virtual Cockpit', 'High level', '2025-07-19 08:28:15'),
(52, 'B 7001 PG', 'Peugeot', '2008', '2023', 'SUV', 650000.00, 75000.00, 'Tersedia', 'peugeot2008.jpg', '5 Seater, Bensin, Stylish French Design', 'Mid level', '2025-07-19 08:28:15'),
(53, 'B 8001 VL', 'Volvo', 'XC60 Recharge', '2024', 'SUV', 2100000.00, 250000.00, 'Tersedia', 'volvo_xc60.jpg', '5 Seater, Plug-in Hybrid, Scandinavian Safety', 'Luxury', '2025-07-19 08:28:15'),
(54, 'B 9001 MN', 'MINI', 'Cooper S 3-Door', '2024', 'Hatchback', 900000.00, 120000.00, 'Tersedia', 'mini.jpg', '4 Seater, Bensin, Fun to Drive', 'High level', '2025-07-19 08:28:15'),
(55, 'B 1 LMBO', 'Lamborghini', 'Huracan Tecnica', '2023', 'Supercar', 15000000.00, 3000000.00, 'Tersedia', 'huracan.jpg', '2 Seater, Bensin V10, High Performance', 'Luxury', '2025-07-19 08:28:15'),
(56, 'B 2 FRRI', 'Ferrari', '296 GTB', '2024', 'Supercar', 18000000.00, 3500000.00, 'Tersedia', 'ferrari296.jpg', '2 Seater, Hybrid V6, Italian Icon', 'Luxury', '2025-07-19 08:28:15'),
(57, 'B 111 FO', 'Ford', 'Everest Titanium', '2023', 'SUV', 900000.00, 120000.00, 'Tersedia', 'everest.jpg', '7 Seater, Diesel Bi-Turbo, 4x4', 'High level', '2025-07-19 08:28:15'),
(58, 'B 222 FO', 'Ford', 'Ranger Raptor', '2024', 'Double Cabin', 1200000.00, 150000.00, 'Tersedia', 'raptor.jpg', '5 Seater, Diesel, Off-road Performance', 'High level', '2025-07-19 08:28:15'),
(59, 'B 333 CH', 'Chevrolet', 'Trax', '2024', 'SUV Compact', 500000.00, 60000.00, 'Tersedia', 'trax.jpg', '5 Seater, Bensin, Modern Design', 'Mid level', '2025-07-19 08:28:15'),
(60, 'B 444 JP', 'Jeep', 'Wrangler Rubicon', '2022', 'SUV Off-road', 1500000.00, 200000.00, 'Tersedia', 'wrangler.jpg', '4 Seater, Bensin, Open Roof', 'Luxury', '2025-07-19 08:47:39'),
(61, 'B 555 JP', 'Jeep', 'Grand Cherokee', '2023', 'SUV', 2300000.00, 280000.00, 'Tersedia', 'cherokee.jpg', '7 Seater, Bensin, American Luxury', 'Luxury', '2025-07-19 08:28:15'),
(62, 'B 666 TS', 'Tesla', 'Model Y', '2023', 'EV SUV', 1500000.00, 200000.00, 'Tersedia', 'tesla_y.jpg', '5 Seater, Listrik, Full Self-Driving', 'Luxury', '2025-07-19 08:28:15'),
(63, 'B 777 DG', 'Dodge', 'RAM 1500', '2022', 'Pickup Truck', 1800000.00, 220000.00, 'Tidak Aktif', 'ram1500.jpg', '5 Seater, Bensin V8 HEMI, Heavy Duty', 'High level', '2025-07-19 08:28:15'),
(64, 'B 101 CN', 'Wuling', 'Air EV', '2023', 'EV City Car', 300000.00, 40000.00, 'Tersedia', 'air_ev.jpg', '4 Seater, Listrik, Compact', 'Low level', '2025-07-19 08:28:15'),
(65, 'B 202 CN', 'Wuling', 'Almaz RS Pro', '2024', 'SUV', 550000.00, 65000.00, 'Tersedia', 'almaz.jpg', '7 Seater, Bensin Turbo, WISE, Sunroof', 'Mid level', '2025-07-19 08:47:49'),
(66, 'B 303 CN', 'Chery', 'Omoda 5', '2023', 'SUV Crossover', 500000.00, 60000.00, 'Tersedia', 'omoda5.jpg', '5 Seater, Bensin Turbo, Futuristic Design', 'Mid level', '2025-07-19 08:28:15'),
(67, 'B 404 CN', 'Chery', 'Tiggo 8 Pro', '2023', 'SUV', 650000.00, 75000.00, 'Tersedia', 'tiggo8.jpg', '7 Seater, Bensin, Premium Features', 'High level', '2025-07-19 08:28:15'),
(68, 'B 505 CN', 'BYD', 'Dolphin', '2024', 'EV Hatchback', 450000.00, 55000.00, 'Tersedia', 'dolphin.jpg', '5 Seater, Listrik, Blade Battery', 'Mid level', '2025-07-19 08:28:15'),
(69, 'B 606 CN', 'BYD', 'Seal', '2024', 'EV Sedan', 800000.00, 100000.00, 'Tersedia', 'seal.jpg', '5 Seater, Listrik, AWD Performance', 'High level', '2025-07-19 08:28:15'),
(70, 'B 707 CN', 'DFSK', 'Glory i-Auto', '2022', 'SUV', 450000.00, 55000.00, 'Tersedia', 'glory.jpg', '7 Seater, Bensin, Voice Command', 'Mid level', '2025-07-19 08:48:20'),
(71, 'B 808 CN', 'Neta', 'V', '2024', 'EV Crossover', 400000.00, 50000.00, 'Tersedia', 'neta_v.jpg', '5 Seater, Listrik, Affordable EV', 'Low level', '2025-07-19 08:28:15'),
(72, 'B 909 CN', 'GWM', 'Tank 500', '2024', 'SUV', 1200000.00, 150000.00, 'Tersedia', 'tank500.jpg', '7 Seater, Hybrid, Rugged Luxury', 'Luxury', '2025-07-19 08:28:15'),
(73, 'B 0 SS', 'Rolls Royce', 'Ghost', '2020', 'Luxury Sedan', 12000000.00, 5000000.00, 'Tersedia', '687bc23f4a49c2.44486513.jpg', 'Engine and Performance:\r\nEngine: 6.7-liter twin-turbocharged V12.\r\nHorsepower: 563 hp (Black Badge models have 592 hp).\r\nTorque: 627 lb-ft.\r\nTransmission: Eight-speed automatic.\r\nDrivetrain: All-wheel drive.\r\nTop Speed: 250 km/h (155 mph).\r\n0-60 mph: Approximately 4.6 seconds (as claimed by Rolls-Royce).\r\nFuel Consumption: Approximately 7 km/L (according to one source, but this may vary based on driving conditions). \r\n\r\nOther Notable Features:\r\nAdaptive suspension with a road-scanning camera: Helps anticipate and compensate for road imperfections. \r\nAll-wheel steering: Improves maneuverability. \r\nLuxurious interior: Features high-quality materials, advanced technology, and ample space. \r\nRear-seat entertainment system: Supports multiple streaming devices. \r\nOptional Champagne cooler: Can be set to different temperatures for vintage and non-vintage bottles. \r\nBespoke audio system: 18-speaker, 1300-watt system with magnesium-ceramic speaker cones. \r\nInfotainment system: Features a large touchscreen with in-dash navigation and support for Apple CarPlay and Android Auto.', 'Luxury', '2025-07-19 16:30:27');

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
(1, 4, '2025-07-20 00:42:54', 275000.00, 'Transfer Bank', '687bd91e76a9b5.62795549.jpg', 'Diverifikasi', NULL, 1);

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
  `rating_pengguna` int(1) DEFAULT NULL,
  `review_pelanggan` text DEFAULT NULL,
  `catatan_admin` varchar(255) DEFAULT NULL,
  `waktu_pengambilan` datetime DEFAULT NULL,
  `waktu_pengembalian` datetime DEFAULT NULL,
  `tgl_mulai_diajukan` datetime DEFAULT NULL,
  `tanggal_pemesanan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `kode_pemesanan`, `id_pengguna`, `id_mobil`, `tanggal_mulai`, `tanggal_selesai`, `tanggal_pengembalian`, `total_biaya`, `total_denda`, `total_biaya_diajukan`, `status_pemesanan`, `alasan_pembatalan`, `rekening_pembatalan`, `rating_pengguna`, `review_pelanggan`, `catatan_admin`, `waktu_pengambilan`, `waktu_pengembalian`, `tgl_mulai_diajukan`, `tanggal_pemesanan`) VALUES
(1, 'BOOK-U5H7K', 3, 4, '2025-07-18 00:00:00', '2025-07-20 00:00:00', NULL, 1400000.00, 0.00, NULL, 'Dibatalkan', 'Testing', '19y823194y2 an wawan', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-13 12:04:04'),
(2, 'BOOK-WVDHT', 3, 3, '2025-07-13 00:00:00', '2025-07-14 00:00:00', NULL, 650000.00, 150000.00, NULL, 'Selesai', NULL, NULL, 5, 'Gacor lek sound horeg, DUm tak dum tak ngunu le', NULL, '2025-07-13 21:50:14', '2025-07-16 10:53:01', NULL, '2025-07-13 12:50:09'),
(3, 'BOOK-T40O5', 3, 3, '2025-07-17 00:00:00', '2025-07-18 00:00:00', NULL, 650000.00, 0.00, NULL, 'Dibatalkan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-16 04:02:40'),
(4, 'BOOK-CSSGP', 6, 26, '2025-07-20 00:00:00', '2025-07-21 00:00:00', NULL, 275000.00, 0.00, NULL, 'Selesai', NULL, NULL, 5, 'Peh Lancar Jaya Bolo', NULL, '2025-07-20 00:46:32', '2025-07-20 00:47:49', NULL, '2025-07-19 17:37:20');

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
  MODIFY `id_mobil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
