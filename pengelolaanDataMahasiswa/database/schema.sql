-- =============================================================================
-- Skema Database Kasus 1 — Pengelolaan Data Mahasiswa (MVC)
-- Kompatibel dengan database `akademik1` pada root repository (`../akademik1.sql`)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `akademik1`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `akademik1`;

CREATE TABLE IF NOT EXISTS `mahasiswa` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nim` VARCHAR(15) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nama` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `prodi` VARCHAR(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    `angkatan` YEAR NOT NULL DEFAULT 2026,
    `ipk` DECIMAL(3,2) DEFAULT '0.00',
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`nim`),
    UNIQUE KEY `uq_mahasiswa_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Catatan jika database `akademik1` sudah diimpor sebelumnya dari `../akademik1.sql`:
-- Jalankan perintah di bawah bila tim memilih form minimalis (hanya NIM & Nama)
-- agar kolom tambahan memiliki nilai default:
--
-- ALTER TABLE `mahasiswa`
--     MODIFY `email` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
--     MODIFY `prodi` VARCHAR(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
--     MODIFY `angkatan` YEAR NOT NULL DEFAULT 2026;
-- -----------------------------------------------------------------------------

-- Data awal contoh (sesuai visualisasi Kasus 1 Slide 44, aman dijalankan berulang)
INSERT IGNORE INTO `mahasiswa` (`nim`, `nama`, `email`, `prodi`, `angkatan`, `ipk`, `deleted_at`) VALUES
('231001', 'Alya', 'alya@kampus.ac.id', 'Teknik Informatika', 2023, 3.70, NULL),
('231002', 'Bima', 'bima@kampus.ac.id', 'Teknik Informatika', 2023, 3.55, NULL),
('231003', 'Citra Lestari', 'citra.lestari@kampus.ac.id', 'Sistem Informasi', 2023, 3.85, NULL);
