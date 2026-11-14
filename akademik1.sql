-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 14, 2026 at 03:48 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `akademik1`
--

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `nidn` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`nidn`, `nama`, `email`, `no_hp`) VALUES
('0001028501', 'Prof. Dr. Gabby Chynthia, S.T., M.T', 'gabby.chynthia@kampus.ac.id', '081234567890'),
('0001028502', 'Harits Forza, S.T., M.T', 'haritss@kampus.ac.id', '081234567891'),
('0001028503', 'Dr. Gabriel Lisar., S.Psi., M.Psi', 'gabriels@kampus.ac.id', '081234567892'),
('0001028504', 'Carl Bertady, S.E., M.S', 'carllber@kampus.ac.id', '081234567893');

-- --------------------------------------------------------

--
-- Table structure for table `krs`
--

CREATE TABLE `krs` (
  `id` bigint UNSIGNED NOT NULL,
  `nim` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_mk` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` tinyint UNSIGNED NOT NULL,
  `tahun_ajaran` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_huruf` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_krs` enum('aktif','batal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `krs`
--

INSERT INTO `krs` (`id`, `nim`, `kode_mk`, `semester`, `tahun_ajaran`, `nilai_huruf`, `status_krs`) VALUES
(1, '2026001', 'IF201', 3, '2026/2027', 'A', 'aktif'),
(2, '2026002', 'IF202', 3, '2026/2027', 'A', 'aktif'),
(3, '2026003', 'IF203', 3, '2026/2027', 'A', 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int NOT NULL,
  `nim` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `angkatan` year NOT NULL,
  `ipk` decimal(3,2) DEFAULT '0.00',
  `deleted_at` datetime DEFAULT NULL
) ;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `nama`, `email`, `prodi`, `angkatan`, `ipk`, `deleted_at`) VALUES
(1, '2026001', 'Andi Pratama', 'andi@kampus.ac.id', 'Teknik Informatika', 2026, '3.75', NULL),
(2, '2026002', 'Bagas Wahyu', 'bagas@kampus.ac.id', 'Teknik Informatika', 2026, '3.54', NULL),
(33, '2026003', 'Citra Maharani', 'citra.maharani@kampus.ac.id', 'Teknik Informatika', 2026, '3.60', NULL),
(3, '211007', 'Eka Saputra', 'eka.saputra@gmail.com', 'Teknik Informatika', 2021, '3.75', NULL),
(4, '211011', 'Hana Safitri', 'hana.safitri@gmail.com', 'Teknik Informatika', 2021, '3.54', NULL),
(5, '211015', 'Lukman Hakim', 'lukman.hakim@gmail.com', 'Teknik Industri', 2021, '2.89', NULL),
(6, '211019', 'Putra Ramadhan', 'putra.ramadhan@gmail.com', 'Teknik Mesin', 2021, '3.42', NULL),
(7, '211023', 'Taufik Hidayat', 'taufik.hidayat@gmail.com', 'Teknik Mesin', 2021, '3.26', NULL),
(8, '211027', 'Yoga Pranata', 'yoga.pranata@gmail.com', 'Teknik Elektro', 2021, '3.37', NULL),
(9, '211031', 'Fitri Rahmawati', 'fitri.rahmawati@gmail.com', 'Manajemen', 2021, '3.51', NULL),
(10, '221005', 'Dian Pratama', 'dian.pratama@gmail.com', 'Teknik Informatika', 2022, '3.62', NULL),
(11, '221009', 'Citra Lestari', 'citra.lestari@gmail.com', 'Teknik Informatika', 2022, '3.88', NULL),
(12, '221013', 'Joko Santoso', 'joko.santoso@gmail.com', 'Teknik Industri', 2022, '3.05', NULL),
(13, '221017', 'Nanda Firmansyah', 'nanda.firmansyah@gmail.com', 'Teknik Industri', 2022, '3.20', NULL),
(14, '221021', 'Rizky Maulana', 'rizky.maulana@gmail.com', 'Teknik Mesin', 2022, '2.97', NULL),
(15, '221025', 'Vina Melati', 'vina.melati@gmail.com', 'Teknik Elektro', 2022, '3.92', NULL),
(16, '221029', 'Bayu Anggara', 'bayu.anggara@gmail.com', 'Sistem Informasi', 2022, '3.13', NULL),
(17, '221033', 'Ilham Kurniawan', 'ilham.kurniawan@gmail.com', 'Manajemen', 2022, '3.40', NULL),
(18, '231004', 'Candra Wijaya', 'candra.wijaya@gmail.com', 'Teknik Informatika', 2023, '3.45', NULL),
(19, '231008', 'Fajar Nugroho', 'fajar.nugroho@gmail.com', 'Teknik Informatika', 2023, '3.10', NULL),
(20, '231012', 'Indra Kusuma', 'indra.kusuma@gmail.com', 'Teknik Industri', 2023, '3.33', NULL),
(21, '231016', 'Maya Anggraini', 'maya.anggraini@gmail.com', 'Teknik Industri', 2023, '3.71', NULL),
(22, '231020', 'Qori Handayani', 'qori.handayani@gmail.com', 'Teknik Mesin', 2023, '3.58', NULL),
(23, '231024', 'Umar Fadhil', 'umar.fadhil@gmail.com', 'Teknik Elektro', 2023, '3.49', NULL),
(24, '231028', 'Zahra Aulia', 'zahra.aulia@gmail.com', 'Sistem Informasi', 2023, '3.85', NULL),
(25, '231032', 'Galih Prakoso', 'galih.prakoso@gmail.com', 'Manajemen', 2023, '2.92', NULL),
(26, '241006', 'Cahya Ramadhan', 'cahya.ramadhan@gmail.com', 'Teknik Informatika', 2024, '3.28', NULL),
(27, '241010', 'Gilang Permana', 'gilang.permana@gmail.com', 'Teknik Informatika', 2024, '2.95', NULL),
(28, '241014', 'Kania Dewi', 'kania.dewi@gmail.com', 'Teknik Industri', 2024, '3.67', NULL),
(29, '241018', 'Oka Mahendra', 'oka.mahendra@gmail.com', 'Teknik Mesin', 2024, '3.15', NULL),
(30, '241022', 'Sari Wulandari', 'sari.wulandari@gmail.com', 'Teknik Mesin', 2024, '3.81', NULL),
(31, '241026', 'Wawan Setiawan', 'wawan.setiawan@gmail.com', 'Teknik Elektro', 2024, '3.08', NULL),
(32, '241030', 'Dewi Anjani', 'dewi.anjani@gmail.com', 'Sistem Informasi', 2024, '3.64', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `kode_mk` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_mk` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sks` tinyint UNSIGNED NOT NULL,
  `nidn` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`kode_mk`, `nama_mk`, `sks`, `nidn`) VALUES
('IF201', 'Pemrograman Berbasis Web', 3, '0001028501'),
('IF202', 'Aljabar Linear', 3, '0001028502'),
('IF203', 'Logaritma', 1, '0001028503');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`nidn`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `krs`
--
ALTER TABLE `krs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_krs` (`nim`,`kode_mk`,`semester`,`tahun_ajaran`),
  ADD KEY `fk_krs_mk` (`kode_mk`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`kode_mk`),
  ADD KEY `fk_mk_dosen` (`nidn`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `krs`
--
ALTER TABLE `krs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `krs`
--
ALTER TABLE `krs`
  ADD CONSTRAINT `fk_krs_mahasiswa` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`),
  ADD CONSTRAINT `fk_krs_mata_kuliah` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_mk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
