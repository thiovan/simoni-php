-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.33 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for simoni_db
CREATE DATABASE IF NOT EXISTS `simoni_db` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `simoni_db`;

-- Dumping structure for table simoni_db.complaints
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` varchar(50) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `officer_id` int(11) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `description` text,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket` (`ticket`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Dumping data for table simoni_db.complaints: 6 rows
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;

-- Dumping structure for table simoni_db.complaint_images
CREATE TABLE IF NOT EXISTS `complaint_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` int(11) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table simoni_db.complaint_images: ~2 rows (approximately)
/*!40000 ALTER TABLE `complaint_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaint_images` ENABLE KEYS */;

-- Dumping structure for table simoni_db.histories
CREATE TABLE IF NOT EXISTS `histories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table simoni_db.histories: ~17 rows (approximately)
/*!40000 ALTER TABLE `histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `histories` ENABLE KEYS */;

-- Dumping structure for table simoni_db.history_images
CREATE TABLE IF NOT EXISTS `history_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `history_id` int(11) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table simoni_db.history_images: ~2 rows (approximately)
/*!40000 ALTER TABLE `history_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `history_images` ENABLE KEYS */;

-- Dumping structure for table simoni_db.statuses
CREATE TABLE IF NOT EXISTS `statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- Dumping data for table simoni_db.statuses: 6 rows
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
INSERT INTO `statuses` (`id`, `name`, `order`) VALUES
	(1, 'menunggu verifikasi', 0),
	(2, 'ditolak', 0),
	(3, 'verifikasi', 1),
	(4, 'disposisi', 2),
	(5, 'tindak lanjut', 3),
	(6, 'selesai', 4);
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;

-- Dumping structure for table simoni_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `role` enum('admin','officer') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- Dumping data for table simoni_db.users: ~6 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `fullname`, `role`, `password`) VALUES
	(1, 'admin', 'Admin', 'admin', '$2y$10$D7g6b9nyBXFhcu.vZBP7fO0H34LaeMMq5uv89OLa0KwId24PnHW/W'),
	(2, 'pembangunan', 'Kasi Pembangunan', 'officer', '$2y$10$D7g6b9nyBXFhcu.vZBP7fO0H34LaeMMq5uv89OLa0KwId24PnHW/W'),
	(3, 'pemerintahan', 'Kasi Pemerintahan', 'officer', '$2y$10$D7g6b9nyBXFhcu.vZBP7fO0H34LaeMMq5uv89OLa0KwId24PnHW/W'),
	(4, 'pelayanan', 'Kasi Pelayanan Publik', 'officer', '$2y$10$D7g6b9nyBXFhcu.vZBP7fO0H34LaeMMq5uv89OLa0KwId24PnHW/W'),
	(5, 'trantib', 'Ketenteraman Dan Ketertiban Umum', 'officer', '$2y$10$D7g6b9nyBXFhcu.vZBP7fO0H34LaeMMq5uv89OLa0KwId24PnHW/W'),
	(6, 'reksos', 'Perekonomian Dan Kesejahteraan Sosial', 'officer', '$2y$10$D7g6b9nyBXFhcu.vZBP7fO0H34LaeMMq5uv89OLa0KwId24PnHW/W');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
