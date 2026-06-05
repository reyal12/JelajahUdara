-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: jelajahudara
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bandara`
--

DROP TABLE IF EXISTS `bandara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bandara` (
  `id_bandara` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_bandara` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_bandara` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `wilayah` enum('Barat','Timur') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Barat',
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_bandara`),
  UNIQUE KEY `uq_kode_bandara` (`kode_bandara`),
  KEY `idx_kota` (`kota`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bandara`
--

LOCK TABLES `bandara` WRITE;
/*!40000 ALTER TABLE `bandara` DISABLE KEYS */;
INSERT INTO `bandara` VALUES (1,'Bandar Udara Internasional Soekarno-Hatta','Jakarta','CGK','2024-01-01 08:00:00','Barat','aktif'),(3,'Bandar Udara Internasional Radin Inten II','Lampung','TKG','2024-01-01 08:00:00','Barat','aktif'),(5,'Bandar Udara Internasional Sultan Mahmud Badaruddin II','Palembang','PLM','2024-01-01 08:00:00','Barat','aktif'),(7,'Bandar Udara Internasional Sultan Hasanuddin','Makassar','UPG','2024-01-01 08:00:00','Barat','aktif'),(9,'Bandar Udara Internasional Pattimura','Ambon','AMQ','2024-01-01 08:00:00','Barat','aktif'),(11,'Bandar Udara Internasional Sentani','Jayapura','DJJ','2024-01-01 08:00:00','Barat','aktif'),(13,'Bandar Udara Internasional Ngurah Rai','Denpasar','DPS','2024-01-01 08:00:00','Barat','aktif'),(15,'Bandar Udara Internasional Juanda','Surabaya','SUB','2024-01-01 08:00:00','Barat','aktif'),(17,'Bandar Udara Internasional Sultan Syarif Kasim II','Pekanbaru','PKU','2024-01-01 08:00:00','Barat','aktif'),(19,'Bandar Udara Internasional Kualanamu','Medan','KNO','2024-01-01 08:00:00','Barat','aktif');
/*!40000 ALTER TABLE `bandara` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_pemesanan`
--

DROP TABLE IF EXISTS `log_pemesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_pemesanan` (
  `id_log` int unsigned NOT NULL AUTO_INCREMENT,
  `id_pemesanan` int unsigned NOT NULL,
  `aktivitas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_log` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_log_pemesanan` (`id_pemesanan`),
  CONSTRAINT `fk_log_pemesanan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_pemesanan`
--

LOCK TABLES `log_pemesanan` WRITE;
/*!40000 ALTER TABLE `log_pemesanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_pemesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maskapai`
--

DROP TABLE IF EXISTS `maskapai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maskapai` (
  `id_maskapai` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_maskapai` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_maskapai` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_maskapai`),
  UNIQUE KEY `uq_kode_maskapai` (`kode_maskapai`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maskapai`
--

LOCK TABLES `maskapai` WRITE;
/*!40000 ALTER TABLE `maskapai` DISABLE KEYS */;
INSERT INTO `maskapai` VALUES (1,'Garuda Indonesia','GIA','2024-01-01 08:00:00','aktif'),(3,'Lion Air','LNI','2024-01-01 08:00:00','aktif'),(5,'Citilink','CTV','2024-01-01 08:00:00','aktif'),(7,'Batik Air','BTK','2024-01-01 08:00:00','aktif'),(9,'Sriwijaya Air','SJY','2024-01-01 08:00:00','aktif');
/*!40000 ALTER TABLE `maskapai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran` (
  `id_pembayaran` int unsigned NOT NULL AUTO_INCREMENT,
  `id_pemesanan` int unsigned NOT NULL,
  `metode_pembayaran` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_bayar` decimal(14,2) NOT NULL,
  `tanggal_bayar` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_pembayaran` enum('menunggu','lunas','gagal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  PRIMARY KEY (`id_pembayaran`),
  UNIQUE KEY `uq_pembayaran_pemesanan` (`id_pemesanan`),
  CONSTRAINT `fk_pembayaran_pemesanan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemesanan`
--

DROP TABLE IF EXISTS `pemesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pemesanan` (
  `id_pemesanan` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_booking` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int unsigned NOT NULL,
  `id_penerbangan` int unsigned NOT NULL,
  `tanggal_pesan` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jumlah_tiket` tinyint unsigned NOT NULL DEFAULT '1',
  `total_harga` decimal(14,2) NOT NULL,
  `status_pemesanan` enum('pending','dikonfirmasi','dibatalkan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_pemesanan`),
  UNIQUE KEY `uq_kode_booking` (`kode_booking`),
  KEY `idx_pemesanan_user` (`id_user`),
  KEY `idx_pemesanan_penerbangan` (`id_penerbangan`),
  CONSTRAINT `fk_pemesanan_penerbangan` FOREIGN KEY (`id_penerbangan`) REFERENCES `penerbangan` (`id_penerbangan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pemesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemesanan`
--

LOCK TABLES `pemesanan` WRITE;
/*!40000 ALTER TABLE `pemesanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pemesanan` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_pemesanan_insert` AFTER INSERT ON `pemesanan` FOR EACH ROW BEGIN
                  INSERT INTO log_pemesanan (id_pemesanan, aktivitas, waktu_log)
                  VALUES (NEW.id_pemesanan, CONCAT('Pemesanan baru dibuat. Kode: ', NEW.kode_booking, ', User ID: ', NEW.id_user, ', Tiket: ', NEW.jumlah_tiket), NOW());
                END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `penerbangan`
--

DROP TABLE IF EXISTS `penerbangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penerbangan` (
  `id_penerbangan` int unsigned NOT NULL AUTO_INCREMENT,
  `id_maskapai` int unsigned NOT NULL,
  `asal_bandara` int unsigned NOT NULL,
  `tujuan_bandara` int unsigned NOT NULL,
  `tanggal_berangkat` date NOT NULL,
  `jam_berangkat` time NOT NULL,
  `jam_tiba` time NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `kursi_tersedia` smallint unsigned NOT NULL DEFAULT '0',
  `status_penerbangan` enum('aktif','dibatalkan','selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  PRIMARY KEY (`id_penerbangan`),
  KEY `idx_tanggal` (`tanggal_berangkat`),
  KEY `idx_asal` (`asal_bandara`),
  KEY `idx_tujuan` (`tujuan_bandara`),
  KEY `fk_penerbangan_maskapai` (`id_maskapai`),
  CONSTRAINT `fk_penerbangan_asal` FOREIGN KEY (`asal_bandara`) REFERENCES `bandara` (`id_bandara`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_penerbangan_maskapai` FOREIGN KEY (`id_maskapai`) REFERENCES `maskapai` (`id_maskapai`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_penerbangan_tujuan` FOREIGN KEY (`tujuan_bandara`) REFERENCES `bandara` (`id_bandara`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penerbangan`
--

LOCK TABLES `penerbangan` WRITE;
/*!40000 ALTER TABLE `penerbangan` DISABLE KEYS */;
INSERT INTO `penerbangan` VALUES (41,7,13,1,'2026-06-19','19:45:00','18:21:00',600000.00,84,'aktif'),(42,5,1,19,'2026-06-15','02:11:00','11:11:00',2099999.00,119,'aktif'),(43,7,9,15,'2026-06-30','10:00:00','11:30:00',2000000.00,150,'aktif');
/*!40000 ALTER TABLE `penerbangan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `penerbangan_barat`
--

DROP TABLE IF EXISTS `penerbangan_barat`;
/*!50001 DROP VIEW IF EXISTS `penerbangan_barat`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `penerbangan_barat` AS SELECT 
 1 AS `id_penerbangan`,
 1 AS `id_maskapai`,
 1 AS `nama_maskapai`,
 1 AS `kode_maskapai`,
 1 AS `asal_bandara`,
 1 AS `nama_bandara_asal`,
 1 AS `kode_bandara_asal`,
 1 AS `kota_asal`,
 1 AS `tujuan_bandara`,
 1 AS `nama_bandara_tujuan`,
 1 AS `kode_bandara_tujuan`,
 1 AS `kota_tujuan`,
 1 AS `tanggal_berangkat`,
 1 AS `jam_berangkat`,
 1 AS `jam_tiba`,
 1 AS `harga`,
 1 AS `kursi_tersedia`,
 1 AS `status_penerbangan`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `penerbangan_timur`
--

DROP TABLE IF EXISTS `penerbangan_timur`;
/*!50001 DROP VIEW IF EXISTS `penerbangan_timur`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `penerbangan_timur` AS SELECT 
 1 AS `id_penerbangan`,
 1 AS `id_maskapai`,
 1 AS `nama_maskapai`,
 1 AS `kode_maskapai`,
 1 AS `asal_bandara`,
 1 AS `nama_bandara_asal`,
 1 AS `kode_bandara_asal`,
 1 AS `kota_asal`,
 1 AS `tujuan_bandara`,
 1 AS `nama_bandara_tujuan`,
 1 AS `kode_bandara_tujuan`,
 1 AS `kota_tujuan`,
 1 AS `tanggal_berangkat`,
 1 AS `jam_berangkat`,
 1 AS `jam_tiba`,
 1 AS `harga`,
 1 AS `kursi_tersedia`,
 1 AS `status_penerbangan`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','customer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Sistem','admin@jelajahudara.id','$2y$10$TkNYU8k7VYGKoeOLND5U5O9KfV7o523qdtaLA6.anv6JQawjDqJQq','admin','2024-01-01 08:00:00','Admin Sistem'),(3,'Budi Santoso','budi@gmail.com','60870c861ef925eabf8529188a250abe2a3796a2f93a0de8bb93a28fb81fed2b','customer','2024-01-10 09:30:00','Budi Santoso'),(5,'Sari Dewi','sari@yahoo.com','9899aca0d639088b8bde899f1967524894c2a6573b3be46bcfcde042ffd32617','customer','2024-01-12 11:00:00','Sari Dewi'),(7,'Andi Pratama','andi@outlook.com','8fe3c1b9826563bec4aaf57e321690f465a01acfaef571541538217db466897d','customer','2024-01-15 14:00:00','Andi Pratama'),(9,'Rina Lestari','rina@gmail.com','c53b86625799d77a940a7a31520af3c50f9c63ae07eaafa7c469df1338d54cf3','customer','2024-01-20 16:45:00','Rina Lestari');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vw_jadwal_penerbangan`
--

DROP TABLE IF EXISTS `vw_jadwal_penerbangan`;
/*!50001 DROP VIEW IF EXISTS `vw_jadwal_penerbangan`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_jadwal_penerbangan` AS SELECT 
 1 AS `id_penerbangan`,
 1 AS `id_maskapai`,
 1 AS `nama_maskapai`,
 1 AS `kode_maskapai`,
 1 AS `asal_bandara`,
 1 AS `nama_bandara_asal`,
 1 AS `kode_bandara_asal`,
 1 AS `kota_asal`,
 1 AS `tujuan_bandara`,
 1 AS `nama_bandara_tujuan`,
 1 AS `kode_bandara_tujuan`,
 1 AS `kota_tujuan`,
 1 AS `tanggal_berangkat`,
 1 AS `jam_berangkat`,
 1 AS `jam_tiba`,
 1 AS `harga`,
 1 AS `kursi_tersedia`,
 1 AS `status_penerbangan`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `penerbangan_barat`
--

/*!50001 DROP VIEW IF EXISTS `penerbangan_barat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `penerbangan_barat` AS select `vw_jadwal_penerbangan`.`id_penerbangan` AS `id_penerbangan`,`vw_jadwal_penerbangan`.`id_maskapai` AS `id_maskapai`,`vw_jadwal_penerbangan`.`nama_maskapai` AS `nama_maskapai`,`vw_jadwal_penerbangan`.`kode_maskapai` AS `kode_maskapai`,`vw_jadwal_penerbangan`.`asal_bandara` AS `asal_bandara`,`vw_jadwal_penerbangan`.`nama_bandara_asal` AS `nama_bandara_asal`,`vw_jadwal_penerbangan`.`kode_bandara_asal` AS `kode_bandara_asal`,`vw_jadwal_penerbangan`.`kota_asal` AS `kota_asal`,`vw_jadwal_penerbangan`.`tujuan_bandara` AS `tujuan_bandara`,`vw_jadwal_penerbangan`.`nama_bandara_tujuan` AS `nama_bandara_tujuan`,`vw_jadwal_penerbangan`.`kode_bandara_tujuan` AS `kode_bandara_tujuan`,`vw_jadwal_penerbangan`.`kota_tujuan` AS `kota_tujuan`,`vw_jadwal_penerbangan`.`tanggal_berangkat` AS `tanggal_berangkat`,`vw_jadwal_penerbangan`.`jam_berangkat` AS `jam_berangkat`,`vw_jadwal_penerbangan`.`jam_tiba` AS `jam_tiba`,`vw_jadwal_penerbangan`.`harga` AS `harga`,`vw_jadwal_penerbangan`.`kursi_tersedia` AS `kursi_tersedia`,`vw_jadwal_penerbangan`.`status_penerbangan` AS `status_penerbangan` from `vw_jadwal_penerbangan` where (`vw_jadwal_penerbangan`.`kota_asal` in ('Jakarta','Lampung','Palembang')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `penerbangan_timur`
--

/*!50001 DROP VIEW IF EXISTS `penerbangan_timur`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `penerbangan_timur` AS select `vw_jadwal_penerbangan`.`id_penerbangan` AS `id_penerbangan`,`vw_jadwal_penerbangan`.`id_maskapai` AS `id_maskapai`,`vw_jadwal_penerbangan`.`nama_maskapai` AS `nama_maskapai`,`vw_jadwal_penerbangan`.`kode_maskapai` AS `kode_maskapai`,`vw_jadwal_penerbangan`.`asal_bandara` AS `asal_bandara`,`vw_jadwal_penerbangan`.`nama_bandara_asal` AS `nama_bandara_asal`,`vw_jadwal_penerbangan`.`kode_bandara_asal` AS `kode_bandara_asal`,`vw_jadwal_penerbangan`.`kota_asal` AS `kota_asal`,`vw_jadwal_penerbangan`.`tujuan_bandara` AS `tujuan_bandara`,`vw_jadwal_penerbangan`.`nama_bandara_tujuan` AS `nama_bandara_tujuan`,`vw_jadwal_penerbangan`.`kode_bandara_tujuan` AS `kode_bandara_tujuan`,`vw_jadwal_penerbangan`.`kota_tujuan` AS `kota_tujuan`,`vw_jadwal_penerbangan`.`tanggal_berangkat` AS `tanggal_berangkat`,`vw_jadwal_penerbangan`.`jam_berangkat` AS `jam_berangkat`,`vw_jadwal_penerbangan`.`jam_tiba` AS `jam_tiba`,`vw_jadwal_penerbangan`.`harga` AS `harga`,`vw_jadwal_penerbangan`.`kursi_tersedia` AS `kursi_tersedia`,`vw_jadwal_penerbangan`.`status_penerbangan` AS `status_penerbangan` from `vw_jadwal_penerbangan` where (`vw_jadwal_penerbangan`.`kota_asal` in ('Makassar','Ambon','Jayapura')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vw_jadwal_penerbangan`
--

/*!50001 DROP VIEW IF EXISTS `vw_jadwal_penerbangan`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_jadwal_penerbangan` AS select `p`.`id_penerbangan` AS `id_penerbangan`,`p`.`id_maskapai` AS `id_maskapai`,`m`.`nama_maskapai` AS `nama_maskapai`,`m`.`kode_maskapai` AS `kode_maskapai`,`p`.`asal_bandara` AS `asal_bandara`,`b_asal`.`nama_bandara` AS `nama_bandara_asal`,`b_asal`.`kode_bandara` AS `kode_bandara_asal`,`b_asal`.`kota` AS `kota_asal`,`p`.`tujuan_bandara` AS `tujuan_bandara`,`b_tuj`.`nama_bandara` AS `nama_bandara_tujuan`,`b_tuj`.`kode_bandara` AS `kode_bandara_tujuan`,`b_tuj`.`kota` AS `kota_tujuan`,`p`.`tanggal_berangkat` AS `tanggal_berangkat`,`p`.`jam_berangkat` AS `jam_berangkat`,`p`.`jam_tiba` AS `jam_tiba`,`p`.`harga` AS `harga`,`p`.`kursi_tersedia` AS `kursi_tersedia`,`p`.`status_penerbangan` AS `status_penerbangan` from (((`penerbangan` `p` join `maskapai` `m` on((`p`.`id_maskapai` = `m`.`id_maskapai`))) join `bandara` `b_asal` on((`p`.`asal_bandara` = `b_asal`.`id_bandara`))) join `bandara` `b_tuj` on((`p`.`tujuan_bandara` = `b_tuj`.`id_bandara`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 13:00:42
