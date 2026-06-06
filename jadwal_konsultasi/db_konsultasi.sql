-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 8.4.3 - MySQL Community Server - GPL
-- OS Server:                    Win64
-- HeidiSQL Versi:               12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Membuang struktur basisdata untuk db_konsultasi
CREATE DATABASE IF NOT EXISTS `db_konsultasi` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_konsultasi`;

-- membuang struktur untuk table db_konsultasi.dosen
CREATE TABLE IF NOT EXISTS `dosen` (
  `id_dosen` int NOT NULL AUTO_INCREMENT,
  `nip` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '827ccb0eea8a706c4c34a16891f84e7b',
  `nama_dosen` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori_keahlian` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_dosen`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pengeluaran data tidak dipilih.

-- membuang struktur untuk table db_konsultasi.jadwal
CREATE TABLE IF NOT EXISTS `jadwal` (
  `id_jadwal` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_dosen` int NOT NULL,
  `tgl_konsultasi` date NOT NULL,
  `keperluan` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak') COLLATE utf8mb4_general_ci DEFAULT 'Menunggu',
  PRIMARY KEY (`id_jadwal`),
  KEY `id_user` (`id_user`),
  KEY `id_dosen` (`id_dosen`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pengeluaran data tidak dipilih.

-- membuang struktur untuk table db_konsultasi.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `npm` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_mhs` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','mahasiswa') COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prodi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fakultas` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pengeluaran data tidak dipilih.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
