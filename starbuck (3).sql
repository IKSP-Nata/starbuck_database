-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 18, 2026 at 03:18 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `starbuck`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail_transaksi` int NOT NULL,
  `id_kasir` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `id_nota` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_transaksi` int DEFAULT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kuantitas` int DEFAULT NULL,
  `kuantitas_harga` decimal(12,2) DEFAULT NULL,
  `catatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail_transaksi`, `id_kasir`, `id_produk`, `id_nota`, `id_transaksi`, `kategori`, `kuantitas`, `kuantitas_harga`, `catatan`, `harga`) VALUES
(1, 1002, 1, 'Chk 2639', 1, 'Minuman', 1, 123765.00, NULL, 123765.00),
(2, 1002, 2, 'Chk 2639', 1, 'Minuman', 2, 24930.00, NULL, 12465.00),
(3, 1002, 3, 'Chk 2639', 1, 'Minuman', 1, 105957.00, NULL, 100000.00),
(4, 1002, 4, 'Chk 2639', 1, 'Tambahan', 1, 14246.00, NULL, 14246.00),
(5, 1002, 5, 'Chk 2639', 1, 'Makanan', 1, 57876.00, NULL, 57876.00),
(6, 1005, 2, 'Chk 2640', 2, 'Minuman', 3, 37395.00, NULL, 12465.00),
(7, 1005, 6, 'Chk 2640', 2, 'Minuman', 1, 30000.00, NULL, 30000.00),
(8, 1005, 7, 'Chk 2640', 2, 'Minuman', 5, 135000.00, NULL, 27000.00),
(9, NULL, 7, NULL, 3, NULL, 3, NULL, NULL, 27000.00),
(10, NULL, 7, NULL, 4, NULL, 2, NULL, NULL, 27000.00),
(11, NULL, 7, NULL, 5, NULL, 2, NULL, NULL, 27000.00),
(12, NULL, 2, NULL, 6, NULL, 6, NULL, NULL, 12465.00),
(13, NULL, 7, NULL, 7, NULL, 4, NULL, NULL, 27000.00),
(14, NULL, 5, NULL, 8, NULL, 2, NULL, NULL, 57876.00),
(15, NULL, 7, NULL, 9, NULL, 2, NULL, NULL, 27000.00),
(16, 1002, 1, NULL, 10, 'Minuman', 2, 247530.00, 'Ice, Normal', 123765.00),
(17, 1006, 3, NULL, 11, 'Minuman', 1, 105957.00, 'Hot, Less sugar', 105957.00),
(18, 1006, 6, NULL, 12, 'Minuman', 2, 60000.00, 'Ice, Normal', 30000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kasir`
--

CREATE TABLE `kasir` (
  `id_kasir` int NOT NULL,
  `nama_kasir` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_telp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kasir`
--

INSERT INTO `kasir` (`id_kasir`, `nama_kasir`, `no_telp`, `alamat`, `email`) VALUES
(1002, 'Olivia', '08912313131231', 'Jalan Bypass Ngurah Rai', 'Oliv@gmail.com'),
(1003, 'Dilla', '08912313131232', 'Jalan Pulau Sailus', 'dilla21@gmail.com'),
(1004, 'Nitha', '08912313131233', 'Jalan Taman Pancing', 'nit24@yahoo.com'),
(1005, 'Ade', '08912313131234', 'Jalan Danau Tempe', 'ade788@email.com'),
(1006, 'Magnum', '08912313131235', 'Jalan Monang Maning', 'mag@gmail.com'),
(1007, 'Davin', '08124012382948', 'Jl. apa123', 'rpl3@email.com');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga` decimal(12,0) DEFAULT NULL,
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Minuman',
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga`, `kategori`, `gambar`) VALUES
(1, 'Venti Mocha Latte', 123765, 'Minuman', '1794847825_kopi.jpg'),
(2, 'Oat Milk', 12465, 'Tambahan', '1794847913_1134062749950066196.jpg'),
(3, 'Grande Caramel Macchiato', 105957, 'Minuman', '1794848055_402016704261893073.jpg'),
(4, 'Extra Shot', 14246, 'Tambahan', '1794848046_Espresso_Shot_PNG___Classic_Coffee_Photography_for_Cafe_Menus___Branding.jpg'),
(5, 'Tall Pike Place Roast', 57876, 'Minuman', '1794848090_Coffee_Peanut_Butter_Smoothie.jpg'),
(6, 'Matcha Latte', 30000, 'Minuman', '1794847960_Dirty_Matcha_Latte___Creamy_Matcha_Meets_Bold_Espresso_-_Etsy.jpg'),
(7, 'Americano', 27000, 'Minuman', '1794848083________________.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `subtotal` decimal(12,0) DEFAULT NULL,
  `pajak` decimal(12,0) DEFAULT NULL,
  `total` decimal(12,0) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `subtotal`, `pajak`, `total`, `tanggal`, `waktu`) VALUES
(1, 326774, 35945, 362719, '2026-03-18', '10:52:41'),
(2, 202395, 22263, 224658, '2026-03-19', '12:45:59'),
(3, 81000, 8910, 89910, '2026-11-16', '01:12:29'),
(4, 54000, 5940, 59940, '2026-11-16', '01:12:46'),
(5, 54000, 5940, 59940, '2026-11-17', '00:38:43'),
(6, 74790, 8227, 83017, '2026-11-17', '00:39:12'),
(7, 108000, 11880, 119880, '2026-11-17', '01:06:29'),
(8, 115752, 12733, 128485, '2026-11-17', '11:55:26'),
(9, 54000, 5940, 59940, '2026-11-18', '00:29:37'),
(10, 247530, 27228, 274758, '2026-11-18', '09:28:54'),
(11, 105957, 11655, 117612, '2026-11-18', '11:01:50'),
(12, 60000, 6600, 66600, '2026-11-18', '11:05:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail_transaksi`),
  ADD KEY `fk_detail_kasir` (`id_kasir`),
  ADD KEY `id_produk` (`id_produk`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_kasir`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kasir`
--
ALTER TABLE `kasir`
  MODIFY `id_kasir` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1008;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `fk_detail_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `kasir` (`id_kasir`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
