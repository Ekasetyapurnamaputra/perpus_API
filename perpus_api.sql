-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2026 at 08:53 AM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpus_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `penerbit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `kode_buku`, `judul`, `penulis`, `kategori`, `tahun_terbit`, `penerbit`) VALUES
(2, 'BK002', 'Bumi', 'Tere Liye', 'Novel', '2014', 'Gramedia'),
(3, 'BK003', 'Pemrograman PHP', 'Abdul Kadir', 'Teknologi', '2020', 'Andi'),
(4, 'BK004', 'Belajar HTML dan CSS', 'Jubilee Enterprise', 'Teknologi', '2021', 'Elex Media'),
(5, 'BK005', 'Dasar-Dasar JavaScript', 'Wahana Komputer', 'Teknologi', '2022', 'Andi'),
(6, 'BK006', 'Bulan', 'Tere Liye', 'Novel', '2015', 'Gramedia'),
(7, 'BK007', 'Matahari', 'Tere Liye', 'Novel', '2016', 'Gramedia'),
(8, 'BK008', 'Bintang', 'Tere Liye', 'Novel', '2017', 'Gramedia'),
(9, 'BK009', 'Ceros dan Batozar', 'Tere Liye', 'Novel', '2018', 'Gramedia'),
(10, 'BK010', 'Komet Minor', 'Tere Liye', 'Novel', '2019', 'Gramedia'),
(11, 'BK011', 'Algoritma dan Pemrograman', 'Rinaldi Munir', 'Teknologi', '2016', 'Informatika'),
(12, 'BK012', 'Basis Data Modern', 'Fathansyah', 'Teknologi', '2018', 'Informatika'),
(13, 'BK013', 'Negeri 5 Menara', 'Ahmad Fuadi', 'Novel', '2009', 'Gramedia'),
(14, 'BK014', 'Filosofi Teras', 'Henry Manampiring', 'Pengembangan Diri', '2018', 'Kompas'),
(15, 'BK015', 'Atomic Habits', 'James Clear', 'Pengembangan Diri', '2019', 'Gramedia'),
(16, 'BK016', 'Sapiens', 'Yuval Noah Harari', 'Sejarah', '2011', 'Kepustakaan Populer Gramedia'),
(17, 'BK017', 'Homo Deus', 'Yuval Noah Harari', 'Sejarah', '2015', 'Kepustakaan Populer Gramedia'),
(18, 'BK018', 'Pulang', 'Leila S. Chudori', 'Novel', '2012', 'Kepustakaan Populer Gramedia'),
(19, 'BK019', 'Laut Bercerita', 'Leila S. Chudori', 'Novel', '2017', 'Kepustakaan Populer Gramedia'),
(20, 'BK020', 'Belajar Python untuk Pemula', 'Budi Raharjo', 'Teknologi', '2023', 'Informatika'),
(21, 'BK021', 'Dunia Bawah', 'Andi Widodo', 'Novel', '2024', 'Ronald siswanto');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `batas_pengembalian` date NOT NULL,
  `tanggal_kembali` datetime DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
  `catatan` varchar(255) DEFAULT NULL,
  `dibuat_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `kode_anggota` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `telepon` varchar(30) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `dibuat_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `kode_anggota`, `nama`, `telepon`, `email`, `alamat`, `status`, `dibuat_at`, `diperbarui_at`) VALUES
(2, 'AG001', 'Eka Setya Purnama Putra', '0812264355913', 'ekasetyapurnamaputra@gmail.com', 'Klanan , Grogol , Sawoo , Ponorogo', 'aktif', '2026-09-24 05:34:36', '2026-09-24 05:34:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_buku` (`kode_buku`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loans_member` (`member_id`),
  ADD KEY `idx_loans_book` (`book_id`),
  ADD KEY `idx_loans_status` (`status`),
  ADD KEY `idx_loans_due` (`batas_pengembalian`),
  ADD KEY `fk_loans_admin` (`admin_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_members_kode` (`kode_anggota`),
  ADD KEY `idx_members_nama` (`nama`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_loans_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_loans_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_loans_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
