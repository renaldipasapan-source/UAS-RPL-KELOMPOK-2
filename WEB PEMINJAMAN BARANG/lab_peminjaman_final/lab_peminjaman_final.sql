-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for lab_peminjaman_final
DROP DATABASE IF EXISTS `lab_peminjaman_final`;
CREATE DATABASE IF NOT EXISTS `lab_peminjaman_final` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `lab_peminjaman_final`;

-- Dumping structure for table lab_peminjaman_final.barang
DROP TABLE IF EXISTS `barang`;
CREATE TABLE IF NOT EXISTS `barang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `namaBarang` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `SN` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Serial Number',
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` int DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `status_barang` enum('Tersedia','Dipakai','Rusak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tersedia',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SN` (`SN`),
  KEY `fk_barang_type` (`id_type`),
  CONSTRAINT `fk_barang_type` FOREIGN KEY (`id_type`) REFERENCES `typebarang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.barang: ~55 rows (approximately)
INSERT INTO `barang` (`id`, `namaBarang`, `SN`, `gambar`, `id_type`, `qty`, `status_barang`) VALUES
	(1, 'Keypade Module', 'SN-PRY-001', NULL, 2, 5, 'Tersedia'),
	(2, 'Routerboard Caplite', 'SN-RC-001', NULL, 1, 5, 'Tersedia'),
	(3, 'Module RTC', 'SN-MRTC-001', NULL, 2, 8, 'Tersedia'),
	(4, 'Modul 4x4', 'SN-MMM-001', NULL, 2, 10, 'Tersedia'),
	(5, 'Routerboard RB951', 'SN-RR-001', NULL, 1, 8, 'Tersedia'),
	(6, 'SDK (Software Development Kit)', 'SN-SDK-001', NULL, 1, 1, 'Tersedia'),
	(7, 'Micro Controller Platform', 'SN-MCP-001', NULL, 1, 2, 'Tersedia'),
	(8, 'Raspberry Pi 3 model B+', 'SN-RPM-001', NULL, 1, 3, 'Tersedia'),
	(9, 'Water Sensor', 'SN-WSR-001', NULL, 2, 7, 'Tersedia'),
	(10, 'Sensor Ultrasonik', 'SN-SUK-001', NULL, 2, 8, 'Tersedia'),
	(11, 'Tag RFID Gantungan Kunci', 'SN-TRGK-001', NULL, 2, 10, 'Tersedia'),
	(12, 'Modul RFID RC 522', 'SN-MRR-001', NULL, 1, 9, 'Tersedia'),
	(13, 'Modul 7 Segment', 'SN-MST-001', NULL, 2, 4, 'Tersedia'),
	(14, 'Modul Stepper Motor', 'SN-MSM-001', NULL, 2, 9, 'Tersedia'),
	(15, 'Stepper Motor', 'SN-SMR-001', NULL, 2, 9, 'Tersedia'),
	(16, 'Sensor Gerak PIR', 'SN-SGP-001', NULL, 2, 15, 'Tersedia'),
	(17, 'Remote', 'SN-RMT-001', NULL, 2, 10, 'Tersedia'),
	(18, 'Controller', 'SN-CTLR-001', NULL, 2, 8, 'Tersedia'),
	(19, 'Tower Pro (Servo)', 'SN-TPS-001', NULL, 2, 7, 'Tersedia'),
	(20, 'Bread Board', 'SN-BBD-001', NULL, 3, 11, 'Tersedia'),
	(21, 'Slot Baterai', 'SN-SBI-001', NULL, 3, 29, 'Tersedia'),
	(22, 'Adaptor', 'SN-ADR-001', NULL, 3, 5, 'Tersedia'),
	(23, 'AC/DC Adaptor', 'SN-ADC-001', NULL, 3, 5, 'Tersedia'),
	(24, 'LCD', 'SN-LCD-7', NULL, 3, 7, 'Tersedia'),
	(25, 'LCD + Module', 'SN-LCDP-001', NULL, 3, 3, 'Tersedia'),
	(26, 'LCD Monitor', 'SN-LCDM-001', NULL, 3, 1, 'Tersedia'),
	(27, 'HDTV', 'SN-HDTV-001', NULL, 3, 1, 'Tersedia'),
	(28, 'USB B', 'SN-USBB-001', NULL, 3, 1, 'Tersedia'),
	(29, 'Network Adapter TP-Link', 'SN-NATL-001', NULL, 4, 3, 'Tersedia'),
	(30, 'Cisco Wireless VPN Firewall', 'SN-CWVF-001', NULL, 4, 2, 'Tersedia'),
	(31, 'Aruba 300 Series Campus Access Point', 'SN-ACAP-001', NULL, 4, 2, 'Tersedia'),
	(32, 'Cisco 2600', 'SN-CC26-001', NULL, 4, 2, 'Tersedia'),
	(33, 'Catalys 2950', 'SN-CTLS-001', NULL, 4, 1, 'Tersedia'),
	(34, 'Cable Cross', 'SN-CCRS', NULL, 4, 1, 'Tersedia'),
	(35, 'Commscope Cable CAT6, 4P, UTP, 24AWG, 75C, CM, BL, 305M', 'SN-CCC6-001', NULL, 4, 2, 'Tersedia'),
	(36, 'Belden', 'SN-BLDN-001', NULL, 4, 1, 'Tersedia'),
	(37, 'Accessories Box Keystudio', 'SN-ABK', NULL, 5, 4, 'Tersedia'),
	(38, 'Key Studio Mini Tank Robot', 'SN-KMTR-001', NULL, 5, 2, 'Tersedia'),
	(39, 'Keystudio Smart Small Turtle Robot', 'SN-KSTR-001', NULL, 5, 1, 'Tersedia'),
	(40, '4wd Bluetooth Multi-Functional Car Kit', 'SN-BMFC-001', NULL, 5, 1, 'Tersedia'),
	(41, 'Starter Kit A-02', 'SN-SKA-001', NULL, 5, 1, 'Tersedia'),
	(42, 'Tutorial Pack Innovate Electronic', 'SN-TPIE-001', NULL, 5, 4, 'Tersedia'),
	(43, 'Soldering Stand', 'SN-SDGS-001', NULL, 5, 4, 'Tersedia'),
	(44, 'Network Cable Tester', 'SN-NCT-001', NULL, 5, 4, 'Tersedia'),
	(45, 'Dual Modular Crimping Tool', 'SN-DMCT-001', NULL, 5, 9, 'Tersedia'),
	(46, 'Sanfix Professional Tool Kit', 'SN-SPTK-001', NULL, 5, 5, 'Tersedia'),
	(47, 'Logitech', 'SN-LTH-001', NULL, 5, 1, 'Tersedia'),
	(48, 'Drone', 'SN-DRN-001', NULL, 5, 1, 'Tersedia'),
	(49, 'TEAC FD 55BR (Floppy Disk Drive)', 'SN-TFBR-001', NULL, 6, 1, 'Tersedia'),
	(50, 'Cromemco ZPU No. 40824', 'SN-CZPU-001', NULL, 6, 1, 'Tersedia'),
	(51, 'Cromemco 16FDC', 'SN-CFDC-001', NULL, 6, 1, 'Tersedia'),
	(52, 'Cromemco 64KZ', 'SN-CKZ-001', NULL, 6, 1, 'Tersedia'),
	(53, 'Cromemco Pri', 'SN-CPRI-001', NULL, 6, 1, 'Tersedia'),
	(54, 'Micoms XL-6 Turbo', 'SN-MXLT-001', NULL, 6, 1, 'Tersedia'),
	(55, 'HSC-K 02', 'SN-HSCK-001', NULL, 6, 1, 'Tersedia');

-- Dumping structure for table lab_peminjaman_final.form_peminjamanbarang
DROP TABLE IF EXISTS `form_peminjamanbarang`;
CREATE TABLE IF NOT EXISTS `form_peminjamanbarang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_barang` int NOT NULL,
  `nama` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_identitas` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_identitas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `tgl_fix` date DEFAULT NULL COMMENT 'Tanggal kembali aktual',
  `keterangan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `buktiFoto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_approval` enum('Waiting','Approved','Deny') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Waiting',
  `qty` int NOT NULL DEFAULT '1',
  `keterangan_serahterima` text COLLATE utf8mb4_unicode_ci,
  `foto_serahterima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kondisi` enum('Baik','Rusak Ringan','Rusak Berat') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_serahterima` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fpb_user` (`id_user`),
  KEY `fk_fpb_barang` (`id_barang`),
  CONSTRAINT `fk_fpb_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fpb_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.form_peminjamanbarang: ~0 rows (approximately)

-- Dumping structure for table lab_peminjaman_final.form_peminjamanruangan
DROP TABLE IF EXISTS `form_peminjamanruangan`;
CREATE TABLE IF NOT EXISTS `form_peminjamanruangan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_ruangan` int NOT NULL,
  `nama` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_identitas` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_identitas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wkt_pinjam` datetime NOT NULL,
  `wkt_kembali` datetime NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `buktiFoto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_approval` enum('Waiting','Approved','Deny') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Waiting',
  `keterangan_serahterima` text COLLATE utf8mb4_unicode_ci,
  `foto_serahterima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kondisi` enum('Baik','Kotor','Ada Kerusakan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_serahterima` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fpr_user` (`id_user`),
  KEY `fk_fpr_ruangan` (`id_ruangan`),
  CONSTRAINT `fk_fpr_ruangan` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fpr_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.form_peminjamanruangan: ~0 rows (approximately)

-- Dumping structure for table lab_peminjaman_final.form_pengaduanmasalah
DROP TABLE IF EXISTS `form_pengaduanmasalah`;
CREATE TABLE IF NOT EXISTS `form_pengaduanmasalah` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `tipe_peminjaman` enum('Barang','Ruangan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_form_peminjamanBarang` int DEFAULT NULL,
  `id_form_peminjamanRuangan` int DEFAULT NULL,
  `deskripsi_masalah` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat_prioritas` int NOT NULL DEFAULT '1' COMMENT '1=Rendah 2=Sedang 3=Tinggi',
  `status_pengaduan` enum('Waiting','Approved','Deny') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Waiting',
  `tgl_pengaduan` date NOT NULL,
  `foto_pengaduan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_resolusi` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fpm_user` (`id_user`),
  KEY `fk_fpm_barang` (`id_form_peminjamanBarang`),
  KEY `fk_fpm_ruangan` (`id_form_peminjamanRuangan`),
  CONSTRAINT `fk_fpm_barang` FOREIGN KEY (`id_form_peminjamanBarang`) REFERENCES `form_peminjamanbarang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fpm_ruangan` FOREIGN KEY (`id_form_peminjamanRuangan`) REFERENCES `form_peminjamanruangan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fpm_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.form_pengaduanmasalah: ~0 rows (approximately)

-- Dumping structure for table lab_peminjaman_final.ruangan
DROP TABLE IF EXISTS `ruangan`;
CREATE TABLE IF NOT EXISTS `ruangan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `namaRuangan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `SN` int NOT NULL COMMENT 'Nomor/Kode Ruangan',
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_ruangan` enum('Tersedia','Dipakai','Rusak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tersedia',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SN` (`SN`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.ruangan: ~3 rows (approximately)
INSERT INTO `ruangan` (`id`, `namaRuangan`, `SN`, `gambar`, `status_ruangan`) VALUES
	(1, 'Lab Jaringan P2', 202, NULL, 'Tersedia'),
	(2, 'Lab Programming P2', 203, NULL, 'Tersedia'),
	(3, 'Lab Mobile L10', 1007, NULL, 'Tersedia');

-- Dumping structure for table lab_peminjaman_final.typebarang
DROP TABLE IF EXISTS `typebarang`;
CREATE TABLE IF NOT EXISTS `typebarang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.typebarang: ~6 rows (approximately)
INSERT INTO `typebarang` (`id`, `nama`) VALUES
	(1, 'Development Board, Mikrokontroler & Komputer'),
	(2, 'Modul, Sensor & Input/Output'),
	(3, 'Komponen Dasar, Display & Daya'),
	(4, 'Jaringan'),
	(5, 'Perkakas, Robotik & Kit Pembelajaran'),
	(6, 'Perangkat Vintage (Khusus)');

-- Dumping structure for table lab_peminjaman_final.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_identitas` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'NIM / NIP / NIK',
  `nomor_identitas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NULL untuk role peminjam',
  `role` enum('kaprodi','admin','peminjam') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'peminjam',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_identitas` (`nomor_identitas`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table lab_peminjaman_final.users: ~7 rows (approximately)
INSERT INTO `users` (`id`, `nama`, `jenis_identitas`, `nomor_identitas`, `password`, `role`, `created_at`, `updated_at`) VALUES
	(1, 'Ary Budy Warsito', 'NIP', '198501012010011001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kaprodi', '2026-06-09 14:33:04', '2026-06-22 21:15:53'),
	(2, 'Desta Althaaf Yudhistira', 'NIM', '20255520016', '$2y$12$lheaMShrEnJEjhII2nVIMuVALjolyyK4HUrv1N1/FEDPRSbl1Zoo.', 'admin', '2026-06-09 14:33:04', '2026-06-22 21:16:50'),
	(3, 'Peminjam', 'NIM', '123', NULL, 'peminjam', '2026-06-09 14:33:04', '2026-06-22 21:15:38'),
	(8, 'Renaldi Pasapan', 'NIM', '20255520009', '$2y$12$CWNXXsVHBYXExN/CPNSYauq4DBnAGy9MU2BetLzDzqjo/jxWAQrl2', 'admin', '2026-06-22 21:16:19', '2026-06-22 21:16:19'),
	(9, 'Theopilus Conary Chang', 'NIM', '20255520017', '$2y$12$XKFgF5r09sCzhrtfqKBIr.lhOe5VZHbdrfxGUXyQQQOnBE6bj54aO', 'admin', '2026-06-22 21:17:48', '2026-06-22 21:17:48'),
	(10, 'Reza Wijaya', 'NIM', '20255520004', '$2y$12$uct2bWUrec4ojzcN178ZUuHbyIMEplLBZmOWQqC3ujbJ5qzpvAdfq', 'admin', '2026-06-22 21:18:55', '2026-06-22 21:18:55'),
	(11, 'Admin', 'NIP', '199203152015012002', '$2y$12$KLUA0pZXDuWsB/GVVgZ6keKXKigw1csB/8JwWdpsW3R9ANSW2HqF6', 'admin', '2026-06-23 10:51:22', '2026-06-23 10:51:22');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
