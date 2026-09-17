-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: ngekos
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
-- Table structure for table `admin_properti`
--

DROP TABLE IF EXISTS `admin_properti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_properti` (
  `admin_id` bigint unsigned NOT NULL,
  `properti_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`admin_id`,`properti_id`),
  KEY `admin_properti_properti_id_foreign` (`properti_id`),
  CONSTRAINT `admin_properti_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admin_properti_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_properti`
--

LOCK TABLES `admin_properti` WRITE;
/*!40000 ALTER TABLE `admin_properti` DISABLE KEYS */;
INSERT INTO `admin_properti` VALUES (4,1),(4,2);
/*!40000 ALTER TABLE `admin_properti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('ngekosin-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1788763799),('ngekosin-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1788763799;',1788763799),('ngekosin-cache-91032ad7bbcb6cf72875e8e8207dcfba80173f7c','i:1;',1788764805),('ngekosin-cache-91032ad7bbcb6cf72875e8e8207dcfba80173f7c:timer','i:1788764805;',1788764805),('ngekosin-cache-pengaturan.semua','a:7:{s:10:\"situs.nama\";s:9:\"Ngekos.in\";s:15:\"kos.jatuh_tempo\";s:5:\"akhir\";s:18:\"kos.denda_per_hari\";s:7:\"5000.00\";s:15:\"situs.deskripsi\";s:12:\"Platform kos\";s:11:\"situs.email\";s:14:\"halo@ngekos.in\";s:13:\"situs.telepon\";s:12:\"081234567890\";s:12:\"situs.alamat\";s:6:\"Sleman\";}',2103911914),('ngekosin-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:24:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"properti.lihat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:13:\"properti.buat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"properti.ubah\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"properti.hapus\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:21:\"properti.kelola-admin\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"kamar.lihat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:10:\"kamar.buat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:10:\"kamar.ubah\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"kamar.hapus\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:13:\"booking.lihat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"booking.buat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:12:\"booking.ubah\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:13:\"booking.batal\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:13:\"booking.hapus\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"penyewaan.lihat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:14:\"penyewaan.buat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"penyewaan.ubah\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"penyewaan.hapus\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:13:\"tagihan.lihat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:12:\"tagihan.ubah\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"pembayaran.lihat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:15:\"pembayaran.buat\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:4;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:21:\"pembayaran.verifikasi\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:18:\"konfigurasi.kelola\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:4:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:7:\"pemilik\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:8:\"anak_kos\";s:1:\"c\";s:3:\"web\";}}}',1788773261);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_pesans`
--

DROP TABLE IF EXISTS `chat_pesans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_pesans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `properti_id` bigint unsigned NOT NULL,
  `anak_kos_id` bigint unsigned NOT NULL,
  `pengirim_id` bigint unsigned NOT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dibaca_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_pesans_properti_id_anak_kos_id_created_at_index` (`properti_id`,`anak_kos_id`,`created_at`),
  KEY `chat_pesans_anak_kos_id_dibaca_pada_index` (`anak_kos_id`,`dibaca_pada`),
  KEY `chat_pesans_pengirim_id_dibaca_pada_index` (`pengirim_id`,`dibaca_pada`),
  CONSTRAINT `chat_pesans_anak_kos_id_foreign` FOREIGN KEY (`anak_kos_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_pesans_pengirim_id_foreign` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_pesans_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_pesans`
--

LOCK TABLES `chat_pesans` WRITE;
/*!40000 ALTER TABLE `chat_pesans` DISABLE KEYS */;
INSERT INTO `chat_pesans` VALUES (1,4,14,14,'halo pak ini kos nya bisa masuk mulai tanggal 1 september besok?','2026-08-24 01:35:40','2026-08-24 01:35:13','2026-08-24 01:35:40'),(2,4,14,11,'bisa mbak','2026-08-24 01:36:00','2026-08-24 01:35:56','2026-08-24 01:36:00'),(3,4,14,11,'kalau fiks tanggal segitu saya aturkan jadwal nya','2026-08-24 01:36:36','2026-08-24 01:36:32','2026-08-24 01:36:36'),(4,4,14,14,'iya pak','2026-08-24 01:37:07','2026-08-24 01:37:05','2026-08-24 01:37:07'),(5,4,14,14,'emang kamu bisa sembuhin semua luka lukaku','2026-08-24 03:09:34','2026-08-24 03:09:29','2026-08-24 03:09:34'),(6,4,14,11,'wong gor berharap e kok ra oleh','2026-08-24 04:11:44','2026-08-24 04:11:34','2026-08-24 04:11:44'),(7,7,16,16,'apakah masih kamar nya bu','2026-08-26 09:56:05','2026-08-26 09:55:21','2026-08-26 09:56:05'),(8,7,16,15,'masih dek mau  kamar yang mana','2026-08-26 09:56:21','2026-08-26 09:56:18','2026-08-26 09:56:21'),(9,4,9,9,'Halo, saya tertarik dengan kos Kos KOBAS. Apakah masih tersedia?','2026-09-03 11:46:45','2026-08-28 02:55:23','2026-09-03 11:46:45'),(12,7,9,9,'Halo, saya tertarik dengan kos Kos ceria. Apakah masih tersedia?',NULL,'2026-08-28 06:03:54','2026-08-28 06:03:54'),(13,2,9,9,'Halo, saya tertarik dengan kos Kos Budibud. Apakah masih tersedia?',NULL,'2026-08-28 07:02:14','2026-08-28 07:02:14'),(14,2,9,9,'Saya sudah memesan kamar B2 di Kos Budibud (Rp850.000/bulan) dan rencana masuk tanggal 10 Oktober 2026. Terima kasih.',NULL,'2026-08-28 07:02:42','2026-08-28 07:02:42'),(15,1,12,12,'Halo, saya tertarik dengan kos Kos Melati. Apakah masih tersedia?',NULL,'2026-08-31 08:57:32','2026-08-31 08:57:32'),(16,1,12,12,'Halo',NULL,'2026-08-31 08:57:48','2026-08-31 08:57:48'),(17,4,9,9,'Halo, saya tertarik dengan kos Kos KOBAS. Apakah masih tersedia?','2026-09-03 11:46:45','2026-09-01 07:58:21','2026-09-03 11:46:45'),(18,4,9,11,'masih mbak','2026-09-04 07:36:58','2026-09-03 11:46:59','2026-09-04 07:36:58'),(19,9,6,6,'Saya sudah memesan kamar A2 di Kos Ambatron (Rp700.000/bulan) dan rencana masuk tanggal 06 September 2026. Terima kasih.',NULL,'2026-09-06 09:32:58','2026-09-06 09:32:58'),(20,4,9,9,'Saya sudah memesan kamar Q1 di Kos KOBAS (Rp700.000/bulan) dan rencana masuk tanggal 03 Oktober 2026. Terima kasih.','2026-09-08 03:29:10','2026-09-08 03:28:47','2026-09-08 03:29:10'),(21,4,9,11,'baik terimakasih',NULL,'2026-09-08 03:29:30','2026-09-08 03:29:30'),(22,9,14,14,'Saya sudah memesan kamar M1 di Kos Ambatron (Rp700.000/bulan) untuk 1 bulan dan rencana masuk tanggal 08 September 2026. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.','2026-09-08 04:12:40','2026-09-08 04:10:48','2026-09-08 04:12:40'),(23,9,14,14,'Saya sudah memesan kamar A2 di Kos Ambatron (Rp700.000/bulan) untuk 2 bulan dan rencana masuk tanggal 10 September 2026. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.','2026-09-08 06:11:30','2026-09-08 06:10:09','2026-09-08 06:11:30'),(24,9,14,14,'Saya sudah membayar tagihan September 2026 di Kos Ambatron sebesar Rp700.000 via Tunai (Cash). Mohon diverifikasi. Terima kasih.','2026-09-08 06:11:30','2026-09-08 06:10:49','2026-09-08 06:11:30'),(25,9,14,20,'Pembayaran September 2026 sebesar Rp700.000 telah saya verifikasi. Terima kasih.',NULL,'2026-09-08 06:12:37','2026-09-08 06:12:37'),(26,6,12,12,'Saya sudah membayar tagihan Oktober 2026 di Kos Bu Tatik sebesar Rp650.000 via Tunai (Cash). Mohon diverifikasi. Terima kasih.',NULL,'2026-09-09 03:17:19','2026-09-09 03:17:19'),(27,6,12,12,'Saya sudah membayar tagihan November 2026 di Kos Bu Tatik sebesar Rp650.000 via Tunai (Cash). Mohon diverifikasi. Terima kasih.',NULL,'2026-09-09 03:17:30','2026-09-09 03:17:30'),(28,6,12,4,'Pembayaran November 2026 sebesar Rp650.000 telah saya verifikasi. Terima kasih.',NULL,'2026-09-09 03:18:03','2026-09-09 03:18:03'),(29,6,12,4,'Pembayaran Oktober 2026 sebesar Rp650.000 telah saya verifikasi. Terima kasih.',NULL,'2026-09-09 03:18:11','2026-09-09 03:18:11'),(30,9,12,12,'Saya sudah memesan kamar M1 di Kos Ambatron (Rp50.000/hari) untuk 5 hari dan rencana masuk tanggal 10 September 2026. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.','2026-09-09 03:49:39','2026-09-09 03:49:19','2026-09-09 03:49:39'),(31,9,12,20,'oke baik','2026-09-09 03:50:03','2026-09-09 03:49:53','2026-09-09 03:50:03'),(32,9,12,12,'Saya sudah membayar tagihan 10 September 2026 - 14 September 2026 di Kos Ambatron sebesar Rp250.000 via Tunai (Cash). Mohon diverifikasi. Terima kasih.',NULL,'2026-09-09 08:05:04','2026-09-09 08:05:04'),(33,9,12,20,'Pembayaran 10 September 2026 - 14 September 2026 sebesar Rp250.000 telah saya verifikasi. Terima kasih.','2026-09-09 08:06:01','2026-09-09 08:05:40','2026-09-09 08:06:01'),(34,9,21,21,'Saya sudah memesan kamar A2 di Kos Ambatron (Rp700.000/bulan) untuk 1 bulan dan rencana masuk tanggal 10 November 2026. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.','2026-09-09 08:19:26','2026-09-09 08:15:13','2026-09-09 08:19:26'),(35,9,21,21,'apakah ada kamar yang bisa buat 2 orang pak','2026-09-09 08:19:26','2026-09-09 08:19:16','2026-09-09 08:19:26'),(36,9,21,21,'Saya sudah membayar tagihan November 2026 di Kos Ambatron sebesar Rp700.000 via Transfer. Mohon diverifikasi. Terima kasih.',NULL,'2026-09-09 08:26:09','2026-09-09 08:26:09'),(37,4,22,22,'Saya sudah memesan kamar K2 di Kos KOBAS (Rp1.500.000/bulan) untuk 1 bulan dan rencana masuk tanggal 11 September 2026. Tagihan sewa akan otomatis dibuat untuk dibayar. Terima kasih.',NULL,'2026-09-11 02:09:16','2026-09-11 02:09:16'),(38,1,6,2,'Tagihan Agustus 2026 sudah TERLAMBAT 39 hari (jatuh tempo 06 Agustus 2026). Kos Kos Melati, kamar A2. Rincian: sewa Rp1.000.000 + denda Rp195.000 (39 hari x Rp5.000/hari). Total yang harus dibayar: Rp1.195.000 (harus pas, tidak boleh kurang). Bayar lewat dashboard (tab Tagihan Saya). Terima kasih.','2026-09-14 13:00:43','2026-09-14 12:56:46','2026-09-14 13:00:43'),(39,1,6,2,'Tagihan Juni 2026 sudah TERLAMBAT 76 hari (jatuh tempo 30 Juni 2026). Kos Kos Melati, kamar A2. Rincian: sewa Rp1.000.000 + denda Rp380.000 (76 hari x Rp5.000/hari). Total yang harus dibayar: Rp1.380.000 (harus pas, tidak boleh kurang). Bayar lewat dashboard (tab Tagihan Saya). Terima kasih.','2026-09-14 13:00:43','2026-09-14 12:56:51','2026-09-14 13:00:43'),(40,7,16,15,'Tagihan Agustus 2026 sudah TERLAMBAT 14 hari (jatuh tempo 31 Agustus 2026). Kos Kos ceria, kamar t2. Rincian: sewa Rp650.000 + denda Rp70.000 (14 hari x Rp5.000/hari). Total yang harus dibayar: Rp720.000 (harus pas, tidak boleh kurang). Bayar lewat dashboard (tab Tagihan Saya). Terima kasih.',NULL,'2026-09-14 12:56:51','2026-09-14 12:56:51');
/*!40000 ALTER TABLE `chat_pesans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `platform` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'android',
  `token` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_token_unique` (`token`),
  KEY `device_tokens_user_id_index` (`user_id`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_tokens`
--

LOCK TABLES `device_tokens` WRITE;
/*!40000 ALTER TABLE `device_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'default','{\"uuid\":\"feb1c505-ade3-403a-88a8-fe74ea104c6f\",\"displayName\":\"App\\\\Mail\\\\PengingatTagihanMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":19:{s:8:\\\"mailable\\\";O:29:\\\"App\\\\Mail\\\\PengingatTagihanMail\\\":9:{s:7:\\\"tagihan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Tagihan\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:5:{i:0;s:9:\\\"penyewaan\\\";i:1;s:15:\\\"penyewaan.kamar\\\";i:2;s:18:\\\"penyewaan.properti\\\";i:3;s:18:\\\"penyewaan.anggotas\\\";i:4;s:17:\\\"penyewaan.anakKos\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"jenis\\\";s:5:\\\"telat\\\";s:9:\\\"ringkasan\\\";a:6:{s:4:\\\"sewa\\\";d:1000000;s:5:\\\"denda\\\";d:195000;s:10:\\\"hari_telat\\\";i:39;s:14:\\\"denda_per_hari\\\";d:5000;s:5:\\\"total\\\";d:1195000;s:5:\\\"porsi\\\";d:1195000;}s:7:\\\"namaKos\\\";s:10:\\\"Kos Melati\\\";s:9:\\\"namaKamar\\\";s:2:\\\"A2\\\";s:10:\\\"isPatungan\\\";b:0;s:9:\\\"porsiSaya\\\";N;s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:17:\\\"anak1@ngekos.test\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:13:\\\"debounceOwner\\\";s:0:\\\"\\\";s:15:\\\"uniqueLockOwner\\\";s:0:\\\"\\\";s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1789390611,\"delay\":null}',0,NULL,1789390611,1789390611),(2,'default','{\"uuid\":\"681768d9-d637-4b99-8126-3ab9274cb5d4\",\"displayName\":\"App\\\\Mail\\\\PengingatTagihanMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":19:{s:8:\\\"mailable\\\";O:29:\\\"App\\\\Mail\\\\PengingatTagihanMail\\\":9:{s:7:\\\"tagihan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Tagihan\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:5:{i:0;s:9:\\\"penyewaan\\\";i:1;s:15:\\\"penyewaan.kamar\\\";i:2;s:18:\\\"penyewaan.properti\\\";i:3;s:18:\\\"penyewaan.anggotas\\\";i:4;s:17:\\\"penyewaan.anakKos\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"jenis\\\";s:5:\\\"telat\\\";s:9:\\\"ringkasan\\\";a:6:{s:4:\\\"sewa\\\";d:1000000;s:5:\\\"denda\\\";d:380000;s:10:\\\"hari_telat\\\";i:76;s:14:\\\"denda_per_hari\\\";d:5000;s:5:\\\"total\\\";d:1380000;s:5:\\\"porsi\\\";d:1380000;}s:7:\\\"namaKos\\\";s:10:\\\"Kos Melati\\\";s:9:\\\"namaKamar\\\";s:2:\\\"A2\\\";s:10:\\\"isPatungan\\\";b:0;s:9:\\\"porsiSaya\\\";N;s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:17:\\\"anak1@ngekos.test\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:13:\\\"debounceOwner\\\";s:0:\\\"\\\";s:15:\\\"uniqueLockOwner\\\";s:0:\\\"\\\";s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1789390611,\"delay\":null}',0,NULL,1789390611,1789390611),(3,'default','{\"uuid\":\"7642d3e6-1a83-4d78-b2d9-46f079b28c50\",\"displayName\":\"App\\\\Mail\\\\PengingatTagihanMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Mail\\\\SendQueuedMailable\",\"command\":\"O:34:\\\"Illuminate\\\\Mail\\\\SendQueuedMailable\\\":19:{s:8:\\\"mailable\\\";O:29:\\\"App\\\\Mail\\\\PengingatTagihanMail\\\":9:{s:7:\\\"tagihan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Tagihan\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:5:{i:0;s:9:\\\"penyewaan\\\";i:1;s:15:\\\"penyewaan.kamar\\\";i:2;s:18:\\\"penyewaan.properti\\\";i:3;s:18:\\\"penyewaan.anggotas\\\";i:4;s:17:\\\"penyewaan.anakKos\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:5:\\\"jenis\\\";s:5:\\\"telat\\\";s:9:\\\"ringkasan\\\";a:6:{s:4:\\\"sewa\\\";d:650000;s:5:\\\"denda\\\";d:70000;s:10:\\\"hari_telat\\\";i:14;s:14:\\\"denda_per_hari\\\";d:5000;s:5:\\\"total\\\";d:720000;s:5:\\\"porsi\\\";d:720000;}s:7:\\\"namaKos\\\";s:9:\\\"Kos ceria\\\";s:9:\\\"namaKamar\\\";s:2:\\\"t2\\\";s:10:\\\"isPatungan\\\";b:0;s:9:\\\"porsiSaya\\\";N;s:2:\\\"to\\\";a:1:{i:0;a:2:{s:4:\\\"name\\\";N;s:7:\\\"address\\\";s:16:\\\"vinsen@gmail.com\\\";}}s:6:\\\"mailer\\\";s:4:\\\"smtp\\\";}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:13:\\\"maxExceptions\\\";N;s:17:\\\"shouldBeEncrypted\\\";b:0;s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:13:\\\"debounceOwner\\\";s:0:\\\"\\\";s:15:\\\"uniqueLockOwner\\\";s:0:\\\"\\\";s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1789390612,\"delay\":null}',0,NULL,1789390612,1789390612);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kamar_fotos`
--

DROP TABLE IF EXISTS `kamar_fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kamar_fotos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kamar_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamar_fotos_kamar_id_urutan_index` (`kamar_id`,`urutan`),
  CONSTRAINT `kamar_fotos_kamar_id_foreign` FOREIGN KEY (`kamar_id`) REFERENCES `kamars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kamar_fotos`
--

LOCK TABLES `kamar_fotos` WRITE;
/*!40000 ALTER TABLE `kamar_fotos` DISABLE KEYS */;
INSERT INTO `kamar_fotos` VALUES (1,8,'kamar/mVjCOhOdEoNYrWV8AKdwJ9qXR3X7robNasmbqCxr.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(2,9,'kamar/2i2OUyrrF0sPXkzyWmRziMedwgvRfa9IjijDW2q5.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(3,10,'kamar/EuZfVEeLqrKuOkBSHvoTgvdqv4G1IM3Ikm0M6xGe.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(4,11,'kamar/p7ghooCjwhqKX8nXNEAiXsxdAijzhF5V0tFqQT2O.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(5,12,'kamar/PudFoJcQao6MJkFBveyBrbDRsEyaTe8FgOWBhcMx.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(6,16,'kamar/sPqi4uTOhkMYf3erP4DMycZoW9PNUcGdIH5jJXfX.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(7,17,'kamar/8Ql9NoKM4B5X4JYubOA0OHsylZqlEP3q2kvtrz1f.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(8,18,'kamar_galeri/KUeSNKOBoWvO1P3bIwJBr52xCOx6WJg4XU5VSUxH.jpg',0,1,'2026-09-11 01:19:32','2026-09-11 01:19:32'),(9,18,'kamar_galeri/XHetlqkYYvsvlF0KxjnOOoJJccrkiy7VBRC9bd4n.jpg',1,0,'2026-09-11 01:19:32','2026-09-11 01:19:32');
/*!40000 ALTER TABLE `kamar_fotos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kamars`
--

DROP TABLE IF EXISTS `kamars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kamars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `properti_id` bigint unsigned NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` smallint unsigned NOT NULL DEFAULT '1',
  `harga_sewa_bulanan` decimal(12,2) NOT NULL,
  `harga_sewa_harian` decimal(12,2) DEFAULT NULL,
  `harga_sewa_mingguan` decimal(12,2) DEFAULT NULL,
  `jenis_harga` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bulanan',
  `harga_asli` decimal(12,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tersedia',
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamars_properti_id_status_index` (`properti_id`,`status`),
  CONSTRAINT `kamars_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kamars`
--

LOCK TABLES `kamars` WRITE;
/*!40000 ALTER TABLE `kamars` DISABLE KEYS */;
INSERT INTO `kamars` VALUES (1,1,'A1',1,1000000.00,33000.00,NULL,'bulanan',1200000.00,'tersedia',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(2,1,'A2',1,1000000.00,33000.00,NULL,'bulanan',1200000.00,'terisi',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(3,1,'A3',2,1200000.00,40000.00,NULL,'bulanan',1440000.00,'tersedia',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(4,2,'B1',1,850000.00,28000.00,NULL,'bulanan',1020000.00,'terisi',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(5,2,'B2',1,850000.00,28000.00,NULL,'bulanan',1020000.00,'tersedia',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(6,3,'C1',1,1500000.00,50000.00,NULL,'bulanan',1800000.00,'tersedia',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(7,3,'C2',1,1500000.00,50000.00,NULL,'bulanan',1800000.00,'terisi',NULL,'2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(8,4,'K1',1,750000.00,25000.00,NULL,'bulanan',900000.00,'tersedia','kamar/mVjCOhOdEoNYrWV8AKdwJ9qXR3X7robNasmbqCxr.jpg','2026-08-21 08:04:48','2026-09-08 08:01:00',NULL),(9,6,'murai',1,650000.00,22000.00,NULL,'bulanan',780000.00,'terisi','kamar/2i2OUyrrF0sPXkzyWmRziMedwgvRfa9IjijDW2q5.jpg','2026-08-22 11:44:29','2026-09-08 08:01:00',NULL),(10,4,'W2',1,700000.00,23000.00,NULL,'bulanan',840000.00,'tersedia','kamar/EuZfVEeLqrKuOkBSHvoTgvdqv4G1IM3Ikm0M6xGe.jpg','2026-08-24 01:28:04','2026-09-08 08:01:00',NULL),(11,7,'r1',1,800000.00,27000.00,NULL,'bulanan',960000.00,'tersedia','kamar/p7ghooCjwhqKX8nXNEAiXsxdAijzhF5V0tFqQT2O.jpg','2026-08-26 07:12:33','2026-09-08 08:01:00',NULL),(12,7,'t2',1,650000.00,22000.00,NULL,'bulanan',780000.00,'terisi','kamar/PudFoJcQao6MJkFBveyBrbDRsEyaTe8FgOWBhcMx.jpg','2026-08-26 09:54:49','2026-09-08 08:01:00',NULL),(13,8,'D1',2,750000.00,NULL,NULL,'bulanan',NULL,'terisi',NULL,'2026-08-28 01:27:09','2026-08-28 01:27:09','2026-08-28 01:27:09'),(14,9,'M1',1,700000.00,50000.00,NULL,'bulanan',840000.00,'terisi',NULL,'2026-08-29 02:14:52','2026-09-09 03:49:19',NULL),(15,9,'A2',1,700000.00,23000.00,NULL,'bulanan',840000.00,'terisi',NULL,'2026-08-29 02:15:23','2026-09-09 08:15:13',NULL),(16,4,'Q1',1,700000.00,23000.00,NULL,'bulanan',840000.00,'terisi','kamar/sPqi4uTOhkMYf3erP4DMycZoW9PNUcGdIH5jJXfX.jpg','2026-09-08 03:01:14','2026-09-08 08:01:00',NULL),(17,9,'w4',2,15000000.00,100000.00,NULL,'bulanan',NULL,'tersedia','kamar/8Ql9NoKM4B5X4JYubOA0OHsylZqlEP3q2kvtrz1f.jpg','2026-09-09 08:17:57','2026-09-09 08:17:57',NULL),(18,4,'K2',2,1500000.00,100000.00,500000.00,'bulanan',2000000.00,'terisi','kamar_galeri/KUeSNKOBoWvO1P3bIwJBr52xCOx6WJg4XU5VSUxH.jpg','2026-09-11 01:19:32','2026-09-11 02:09:16',NULL);
/*!40000 ALTER TABLE `kamars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_20_072155_create_permission_tables',1),(5,'2026_08_20_080000_create_domain_tables',2),(6,'2026_08_20_170000_add_dashboard_indexes',3),(7,'2026_08_20_200000_add_foto_kota_to_domain_tables',4),(8,'2026_08_20_210000_create_pesan_bantuans_table',5),(9,'2026_08_20_220000_add_harga_jenis_harga_to_domain_tables',6),(10,'2026_08_21_000001_add_dinonaktifkan_pada_to_users_table',7),(11,'2026_08_21_010000_create_chat_pesans_table',8),(12,'2026_08_21_020000_add_rencana_tinggal_ke_bookings_dan_penyewaans',9),(13,'2026_08_21_030000_add_durasi_hari_ke_bookings',10),(14,'2026_08_24_000001_create_pengaturans_table',11),(15,'2026_08_24_000002_add_preferensi_notifikasi_to_users_table',11),(16,'2026_08_25_000001_add_avatar_to_users_table',12),(17,'2026_08_27_142735_create_personal_access_tokens_table',13),(18,'2026_08_28_113747_drop_orphaned_bookings_table',14),(19,'2026_08_29_000000_add_dibaca_pada_to_pesan_bantuans_table',15),(20,'2026_08_29_010000_create_device_tokens_table',16),(22,'2026_09_03_152057_add_latitude_longitude_to_propertis_table',17),(23,'2026_09_05_000001_create_properti_favorits_table',18),(24,'2026_09_05_000002_create_ulasans_table',18),(25,'2026_09_06_000000_create_pengeluarans_table',19),(26,'2026_09_07_000000_add_performance_indexes',20),(27,'2026_09_08_143745_add_harga_harian_dan_diskon_to_domain_tables',21),(28,'2026_09_10_000001_add_ktp_dan_mode_hunian_ke_penyewaans',22),(29,'2026_09_10_000002_create_penyewaan_anggotas_table',22),(30,'2026_09_10_000003_add_kwitansi_ke_pembayarans',22),(31,'2026_09_10_000004_create_galeri_fotos_tables',22),(32,'2026_09_10_000005_backfill_galeri_dari_foto_lama',22),(33,'2026_09_10_000006_add_harga_mingguan_to_domain_tables',23),(34,'2026_09_11_000001_create_tagihan_pengingat_table',24);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(2,'App\\Models\\User',3),(3,'App\\Models\\User',4),(4,'App\\Models\\User',6),(4,'App\\Models\\User',7),(4,'App\\Models\\User',8),(4,'App\\Models\\User',9),(4,'App\\Models\\User',10),(2,'App\\Models\\User',11),(4,'App\\Models\\User',12),(2,'App\\Models\\User',13),(4,'App\\Models\\User',14),(2,'App\\Models\\User',15),(4,'App\\Models\\User',16),(4,'App\\Models\\User',17),(4,'App\\Models\\User',18),(4,'App\\Models\\User',19),(2,'App\\Models\\User',20),(4,'App\\Models\\User',21),(4,'App\\Models\\User',22);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('admin.ngekos@gmail.com','$2y$12$UrKIGObIMB1rJI7MvPyC1euBdSpwIa5EXIuEs4AN9LjVWKKItPde6','2026-09-04 06:22:36'),('superadmin.ngekos@gmail.com','$2y$12$sX6P2N62a5W.qe0CinOB..EpZ5j2bjTZwtiiQPinopv2NDS471Ei.','2026-08-26 06:44:10');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembayarans`
--

DROP TABLE IF EXISTS `pembayarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tagihan_id` bigint unsigned NOT NULL,
  `anak_kos_id` bigint unsigned NOT NULL,
  `metode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'transfer',
  `jumlah` decimal(12,2) NOT NULL,
  `bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu_verifikasi',
  `nomor_kwitansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kwitansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diverifikasi_oleh` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembayarans_nomor_kwitansi_unique` (`nomor_kwitansi`),
  KEY `pembayarans_tagihan_id_foreign` (`tagihan_id`),
  KEY `pembayarans_diverifikasi_oleh_foreign` (`diverifikasi_oleh`),
  KEY `pembayarans_anak_kos_id_status_index` (`anak_kos_id`,`status`),
  KEY `pembayarans_status_verified_at_index` (`status`,`verified_at`),
  CONSTRAINT `pembayarans_anak_kos_id_foreign` FOREIGN KEY (`anak_kos_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembayarans_diverifikasi_oleh_foreign` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pembayarans_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayarans`
--

LOCK TABLES `pembayarans` WRITE;
/*!40000 ALTER TABLE `pembayarans` DISABLE KEYS */;
INSERT INTO `pembayarans` VALUES (1,1,6,'transfer',1000000.00,NULL,'diverifikasi',NULL,NULL,4,'2026-07-03 17:00:00','2026-08-20 08:44:30','2026-08-20 08:44:30'),(2,3,7,'transfer',850000.00,NULL,'diverifikasi',NULL,NULL,4,'2026-07-02 17:00:00','2026-08-20 08:44:30','2026-08-20 08:44:30'),(3,4,7,'transfer',850000.00,NULL,'diverifikasi',NULL,NULL,4,'2026-08-21 07:12:56','2026-08-20 08:44:30','2026-08-21 07:12:56'),(4,6,9,'transfer',750000.00,NULL,'diverifikasi',NULL,NULL,4,'2026-08-22 08:00:45','2026-08-22 07:15:57','2026-08-22 08:00:45'),(5,10,14,'transfer',700000.00,'bukti-pembayaran/YS32B90rwtABEACjIC4f7xQwGMtoRWoSlhItrnaB.jpg','diverifikasi',NULL,NULL,4,'2026-08-24 06:00:13','2026-08-24 05:58:49','2026-08-24 06:00:13'),(6,7,12,'transfer',650000.00,'bukti-pembayaran/DHsjg8nYTcvFCmdbxQ7H5JgMtiAfZRJSpFQmLuDD.jpg','diverifikasi',NULL,NULL,4,'2026-08-25 16:26:46','2026-08-25 16:25:29','2026-08-25 16:26:46'),(7,11,14,'transfer',700000.00,'bukti-pembayaran/3uBlTeajbAqWS88A7z04nZhpC0fRlI5u5OQW1zcE.jpg','diverifikasi',NULL,NULL,4,'2026-09-08 04:07:18','2026-09-08 04:05:24','2026-09-08 04:07:18'),(8,12,14,'transfer',700000.00,'bukti-pembayaran/h3xnJmlI2imTGCHPfFJKOryBnx64UTNdvNPrpc1R.jpg','diverifikasi',NULL,NULL,4,'2026-09-08 04:07:13','2026-09-08 04:06:00','2026-09-08 04:07:13'),(9,13,14,'transfer',700000.00,'bukti-pembayaran/aNwP8X4cAA1LORZDEZ6c9A6MitLjzRrbmlWdb7Hm.jpg','diverifikasi',NULL,NULL,4,'2026-09-08 04:07:08','2026-09-08 04:06:35','2026-09-08 04:07:08'),(10,14,14,'transfer',700000.00,'bukti-pembayaran/6aKChjRRePpYmDcKtfY9x14RRNFMlZ2m7Cz2hMjc.jpg','diverifikasi',NULL,NULL,4,'2026-09-08 04:08:37','2026-09-08 04:07:56','2026-09-08 04:08:37'),(11,15,14,'transfer',700000.00,'bukti-pembayaran/iiYarfd2pxmTWZQCrBRNOxxryIAzKmbJouQJE6fn.jpg','diverifikasi',NULL,NULL,4,'2026-09-08 04:08:33','2026-09-08 04:08:15','2026-09-08 04:08:33'),(12,18,14,'transfer',700000.00,'bukti-pembayaran/BU9E7P4SrmecvzmtbzyAwqfhA5VumtJ5053kGXlE.jpg','diverifikasi',NULL,NULL,4,'2026-09-08 04:14:01','2026-09-08 04:13:29','2026-09-08 04:14:01'),(13,19,14,'cash',700000.00,NULL,'diverifikasi',NULL,NULL,20,'2026-09-08 06:12:37','2026-09-08 06:10:49','2026-09-08 06:12:37'),(14,8,12,'cash',650000.00,NULL,'diverifikasi',NULL,NULL,4,'2026-09-09 03:18:11','2026-09-09 03:17:19','2026-09-09 03:18:11'),(15,9,12,'cash',650000.00,NULL,'diverifikasi',NULL,NULL,4,'2026-09-09 03:18:03','2026-09-09 03:17:30','2026-09-09 03:18:03'),(16,21,12,'cash',250000.00,NULL,'diverifikasi','KWT-202609-0001','kwitansi/KWT-202609-0001.pdf',20,'2026-09-09 08:05:40','2026-09-09 08:05:04','2026-09-10 07:05:31'),(17,22,21,'transfer',700000.00,'bukti-pembayaran/kmhzsVZBR5hTqBJ9JRb4fFppBEVJVQd1fF3EyQIy.jpg','menunggu_verifikasi',NULL,NULL,NULL,NULL,'2026-09-09 08:26:09','2026-09-09 08:26:09');
/*!40000 ALTER TABLE `pembayarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturans`
--

DROP TABLE IF EXISTS `pengaturans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kunci` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengaturans_kunci_unique` (`kunci`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturans`
--

LOCK TABLES `pengaturans` WRITE;
/*!40000 ALTER TABLE `pengaturans` DISABLE KEYS */;
INSERT INTO `pengaturans` VALUES (1,'situs.nama','Ngekos.in','2026-08-24 07:00:31','2026-08-24 07:00:31'),(2,'kos.jatuh_tempo','akhir','2026-08-24 07:00:31','2026-08-28 01:26:19'),(3,'kos.denda_per_hari','5000.00','2026-08-24 07:00:31','2026-08-28 01:26:20'),(4,'situs.deskripsi','Platform kos','2026-08-28 01:26:19','2026-08-28 01:26:19'),(5,'situs.email','halo@ngekos.in','2026-08-28 01:26:19','2026-08-28 01:26:19'),(6,'situs.telepon','081234567890','2026-08-28 01:26:19','2026-08-28 01:26:19'),(7,'situs.alamat','Sleman','2026-08-28 01:26:19','2026-08-28 01:26:19');
/*!40000 ALTER TABLE `pengaturans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengeluarans`
--

DROP TABLE IF EXISTS `pengeluarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengeluarans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `properti_id` bigint unsigned NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `jumlah` decimal(12,2) NOT NULL,
  `tanggal` date NOT NULL,
  `dibuat_oleh` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pengeluarans_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `pengeluarans_properti_id_tanggal_index` (`properti_id`,`tanggal`),
  KEY `pengeluarans_kategori_index` (`kategori`),
  CONSTRAINT `pengeluarans_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pengeluarans_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengeluarans`
--

LOCK TABLES `pengeluarans` WRITE;
/*!40000 ALTER TABLE `pengeluarans` DISABLE KEYS */;
INSERT INTO `pengeluarans` VALUES (1,9,'kebersihan','Kebersihan dan sampah',50000.00,'2026-09-08',20,'2026-09-08 04:15:47','2026-09-08 04:15:47');
/*!40000 ALTER TABLE `pengeluarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penyewaan_anggotas`
--

DROP TABLE IF EXISTS `penyewaan_anggotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penyewaan_anggotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `penyewaan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `porsi_persen` tinyint unsigned NOT NULL DEFAULT '50',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `ktp_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penyewaan_anggotas_penyewaan_id_user_id_unique` (`penyewaan_id`,`user_id`),
  KEY `penyewaan_anggotas_user_id_foreign` (`user_id`),
  KEY `penyewaan_anggotas_penyewaan_id_status_index` (`penyewaan_id`,`status`),
  CONSTRAINT `penyewaan_anggotas_penyewaan_id_foreign` FOREIGN KEY (`penyewaan_id`) REFERENCES `penyewaans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penyewaan_anggotas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penyewaan_anggotas`
--

LOCK TABLES `penyewaan_anggotas` WRITE;
/*!40000 ALTER TABLE `penyewaan_anggotas` DISABLE KEYS */;
INSERT INTO `penyewaan_anggotas` VALUES (1,18,14,50,'aktif','ktp/4nCA9B0weW39tmCcx190vfYgfgG09mVdGkpoyLb3.jpg',NULL,'2026-09-11 02:11:19','2026-09-11 02:11:19');
/*!40000 ALTER TABLE `penyewaan_anggotas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penyewaans`
--

DROP TABLE IF EXISTS `penyewaans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penyewaans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `anak_kos_id` bigint unsigned NOT NULL,
  `kamar_id` bigint unsigned NOT NULL,
  `properti_id` bigint unsigned NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `ktp_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode_hunian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tunggal',
  `permintaan_keluar_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penyewaans_anak_kos_id_foreign` (`anak_kos_id`),
  KEY `penyewaans_kamar_id_foreign` (`kamar_id`),
  KEY `penyewaans_properti_id_foreign` (`properti_id`),
  KEY `penyewaans_status_index` (`status`),
  CONSTRAINT `penyewaans_anak_kos_id_foreign` FOREIGN KEY (`anak_kos_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penyewaans_kamar_id_foreign` FOREIGN KEY (`kamar_id`) REFERENCES `kamars` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penyewaans_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penyewaans`
--

LOCK TABLES `penyewaans` WRITE;
/*!40000 ALTER TABLE `penyewaans` DISABLE KEYS */;
INSERT INTO `penyewaans` VALUES (1,6,2,1,'2026-06-01',NULL,'aktif','ktp/hahcrfJ7mqc1GlFdkfCSaENmHvoG6TOS6Yb4YIk3.png','tunggal',NULL,'2026-08-20 08:44:30','2026-09-14 13:01:11'),(2,7,4,2,'2026-07-01',NULL,'aktif',NULL,'tunggal',NULL,'2026-08-20 08:44:30','2026-08-20 08:44:30'),(3,8,7,3,'2026-02-01','2026-07-31','selesai',NULL,'tunggal',NULL,'2026-08-20 08:44:30','2026-08-20 08:44:30'),(4,9,8,4,'2026-08-22','2026-09-05','selesai',NULL,'tunggal','2026-09-04 09:59:50','2026-08-22 07:11:56','2026-09-04 20:00:43'),(5,12,9,6,'2026-09-01',NULL,'aktif',NULL,'tunggal',NULL,'2026-08-22 11:47:38','2026-08-22 11:47:38'),(6,14,10,4,'2026-09-01','2026-09-08','selesai',NULL,'tunggal',NULL,'2026-08-24 01:37:40','2026-09-08 04:09:46'),(7,16,12,7,'2026-08-26',NULL,'aktif',NULL,'tunggal',NULL,'2026-08-26 10:12:15','2026-08-26 10:12:15'),(11,9,5,2,'2026-10-10','2026-09-05','selesai',NULL,'tunggal','2026-09-04 09:55:59','2026-08-28 07:02:42','2026-09-04 20:00:35'),(13,9,16,4,'2026-10-03',NULL,'aktif',NULL,'tunggal',NULL,'2026-09-08 03:28:47','2026-09-08 03:28:47'),(14,14,14,9,'2026-09-08','2026-09-08','selesai',NULL,'tunggal',NULL,'2026-09-08 04:10:48','2026-09-08 06:09:06'),(15,14,15,9,'2026-09-10','2026-09-09','selesai',NULL,'tunggal',NULL,'2026-09-08 06:10:09','2026-09-09 07:59:36'),(16,12,14,9,'2026-09-10','2026-09-14','aktif',NULL,'tunggal',NULL,'2026-09-09 03:49:19','2026-09-09 03:49:19'),(17,21,15,9,'2026-11-10',NULL,'aktif',NULL,'tunggal',NULL,'2026-09-09 08:15:13','2026-09-09 08:15:13'),(18,22,18,4,'2026-09-11',NULL,'aktif','ktp/MieRLV2hdgozbmWZhPZ92ZqO5TWtVxaGN96DBmtw.jpg','patungan',NULL,'2026-09-11 02:09:16','2026-09-11 02:11:19');
/*!40000 ALTER TABLE `penyewaans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'properti.lihat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(2,'properti.buat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(3,'properti.ubah','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(4,'properti.hapus','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(5,'properti.kelola-admin','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(6,'kamar.lihat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(7,'kamar.buat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(8,'kamar.ubah','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(9,'kamar.hapus','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(10,'booking.lihat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(11,'booking.buat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(12,'booking.ubah','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(13,'booking.batal','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(14,'booking.hapus','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(15,'penyewaan.lihat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(16,'penyewaan.buat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(17,'penyewaan.ubah','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(18,'penyewaan.hapus','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(19,'tagihan.lihat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(20,'tagihan.ubah','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(21,'pembayaran.lihat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(22,'pembayaran.buat','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(23,'pembayaran.verifikasi','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(24,'konfigurasi.kelola','web','2026-08-20 00:26:19','2026-08-20 00:26:19');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',19,'mobile-token','3ea0f7311fdfd428fe3afb3615cca444f904b3ca9a1adfbd2f2213a2caa7eb4d','[\"*\"]','2026-08-27 08:56:14',NULL,'2026-08-27 07:51:28','2026-08-27 08:56:14'),(3,'App\\Models\\User',1,'mobile-token','1ad637ce22e776c0f1329298f10f00b6209119a0956fd1fb54ccf149a1b88c9c','[\"*\"]','2026-08-28 01:26:20',NULL,'2026-08-28 01:26:19','2026-08-28 01:26:20'),(4,'App\\Models\\User',1,'mobile-token','6d1cd7db08b7b5a3e1b41b18dc50f93864a802bb4b8c0dd2c696237786626a87','[\"*\"]','2026-08-28 01:26:27',NULL,'2026-08-28 01:26:27','2026-08-28 01:26:27'),(5,'App\\Models\\User',1,'mobile-token','675691aaaef16a84ced11c47c2a5c5c812ad57cf4c7e77bd9c28caf07264d2cb','[\"*\"]','2026-08-28 01:26:34',NULL,'2026-08-28 01:26:34','2026-08-28 01:26:34'),(6,'App\\Models\\User',2,'mobile-token','215e725b42a4718038a28040322e6c77dc5dbeca61517223187602566a42d2be','[\"*\"]','2026-08-28 01:26:42',NULL,'2026-08-28 01:26:42','2026-08-28 01:26:42'),(7,'App\\Models\\User',1,'mobile-token','3548978c78bd7e11b97ddca485fa8ea3ba537a12d3b0cdcf2499a4014b444568','[\"*\"]','2026-08-28 01:26:59',NULL,'2026-08-28 01:26:59','2026-08-28 01:26:59'),(8,'App\\Models\\User',2,'mobile-token','14f5806d8be4798c28d238a13cf5dacf605921f4ed71dae80608222a55a65a47','[\"*\"]','2026-08-28 01:27:09',NULL,'2026-08-28 01:27:09','2026-08-28 01:27:09'),(16,'App\\Models\\User',1,'mobile-token','cd774274bb1e217f704adf5afbf05a7bdfef5d58a78471edc9ffca69f8ff8788','[\"*\"]',NULL,NULL,'2026-08-28 02:45:16','2026-08-28 02:45:16'),(17,'App\\Models\\User',4,'mobile-token','78fad5f937083dc11a362d47f866e89ac00931d0ffc586483478bb4623d80f60','[\"*\"]',NULL,NULL,'2026-08-28 02:45:16','2026-08-28 02:45:16'),(18,'App\\Models\\User',1,'mobile-token','3b4c29658446a674744ad50e2746a94c6872b9b89d778c6e50865a7d331c279e','[\"*\"]',NULL,NULL,'2026-08-28 02:45:21','2026-08-28 02:45:21'),(20,'App\\Models\\User',2,'mobile-token','29aa2ab7716f06c4ff4e0fc46b7447b6d7b6a98a7cb336539dc2106638242a1a','[\"*\"]','2026-08-28 03:20:34',NULL,'2026-08-28 03:20:33','2026-08-28 03:20:34'),(21,'App\\Models\\User',9,'mobile-token','032a7f04a7c0387d87d71fba85b8a93af630ebeb19a9ba947a371a2a50e0b92b','[\"*\"]','2026-08-28 07:04:03',NULL,'2026-08-28 07:01:45','2026-08-28 07:04:03'),(23,'App\\Models\\User',2,'debug','8dc3a5ab7324b79255e53c47b857a97df45f7a1a85cecb02463690fba842df68','[\"*\"]','2026-08-29 01:57:21',NULL,'2026-08-29 01:55:48','2026-08-29 01:57:21'),(26,'App\\Models\\User',12,'mobile-token','f541f04e37b028a708cea1af3f340c3313ff9aefd396d7107e8a94daf65efc65','[\"*\"]','2026-08-29 06:56:20',NULL,'2026-08-29 06:50:25','2026-08-29 06:56:20'),(27,'App\\Models\\User',12,'mobile-token','66d04b23e8e2db8297cafd3e1dc0b851f92c35d9b0072aeb7209e50e22de010a','[\"*\"]','2026-08-31 08:58:21',NULL,'2026-08-29 06:58:18','2026-08-31 08:58:21'),(37,'App\\Models\\User',9,'mobile-token','b6f1ef0f9d8c92e35d1d888de12ceb6a1a37da3dc85e4934854d567934bcefd0','[\"*\"]',NULL,NULL,'2026-09-01 07:50:04','2026-09-01 07:50:04'),(38,'App\\Models\\User',9,'mobile-token','49f2c7037d7811e710d0dea807322c6c9bb47ef111f7d101fe7fa1cd8ff6551b','[\"*\"]',NULL,NULL,'2026-09-01 07:50:10','2026-09-01 07:50:10'),(40,'App\\Models\\User',11,'mobile-token','fd1b412bdd460772a4d3d494fadc9051008f3e110c99124d036451cead152d06','[\"*\"]','2026-09-02 11:06:50',NULL,'2026-09-02 11:06:38','2026-09-02 11:06:50'),(41,'App\\Models\\User',11,'mobile-token','6eedea886cc2bcba495a947f3f1cd9b6c3448288299725e209488d9de177d8d1','[\"*\"]','2026-09-03 07:19:13',NULL,'2026-09-03 07:18:05','2026-09-03 07:19:13'),(43,'App\\Models\\User',11,'mobile-token','07f9f2a7d29fc895e8215b4a0dc1c693439751f2cec8215c2e5f5e6842c49e89','[\"*\"]','2026-09-03 12:26:44',NULL,'2026-09-03 09:03:05','2026-09-03 12:26:44'),(44,'App\\Models\\User',11,'mobile-token','8054e860ce66862c8f7105efe49be78bab172c68b8380884a5e317b9562360f8','[\"*\"]','2026-09-03 13:00:58',NULL,'2026-09-03 13:00:57','2026-09-03 13:00:58'),(45,'App\\Models\\User',11,'mobile-token','fbc8789af74374f83487da0cac51196bc6ec748ca2341743b1411dd89ba520b5','[\"*\"]','2026-09-04 06:32:53',NULL,'2026-09-04 06:19:41','2026-09-04 06:32:53'),(46,'App\\Models\\User',9,'mobile-token','8d4f9660e49caa328dcb92ad448dbee4f657bdc39ba33b4b4da57532fda4f87f','[\"*\"]','2026-09-04 06:54:18',NULL,'2026-09-04 06:54:14','2026-09-04 06:54:18'),(47,'App\\Models\\User',9,'mobile-token','829b8e9861c32df9e7b7e6b48cd559648c752eab3c326d32311b8019f279db95','[\"*\"]','2026-09-04 07:36:58',NULL,'2026-09-04 07:36:11','2026-09-04 07:36:58'),(48,'App\\Models\\User',6,'mobile-token','9b452a1980aa176539324c719cd1e53ea8690a08198c0720827246c6985d32d6','[\"*\"]',NULL,NULL,'2026-09-04 08:29:51','2026-09-04 08:29:51'),(50,'App\\Models\\User',20,'mobile-token','fc4c1c5e56f973e1e04868ebf8d9c7b6d7f784fc3adb9d1f485bfbd3c302283c','[\"*\"]','2026-09-04 08:33:21',NULL,'2026-09-04 08:32:49','2026-09-04 08:33:21'),(51,'App\\Models\\User',20,'mobile-token','7ab79f4b4856c2bec84013dc39319ed33085cc43f2338c54d1e4feef18d5deb7','[\"*\"]','2026-09-04 08:35:53',NULL,'2026-09-04 08:34:27','2026-09-04 08:35:53'),(52,'App\\Models\\User',20,'mobile-token','8affaf7216a4802f49c1891a04fcf4f79f1b059685ca5707192eb93242ebe383','[\"*\"]','2026-09-04 09:03:47',NULL,'2026-09-04 09:03:32','2026-09-04 09:03:47'),(53,'App\\Models\\User',20,'mobile-token','4f8dbc945dbb38fbfd40ae9c203bbd0738f90bce4388dbb7823510c9a65467c3','[\"*\"]','2026-09-04 09:12:00',NULL,'2026-09-04 09:11:09','2026-09-04 09:12:00'),(54,'App\\Models\\User',20,'mobile-token','5e9114886480aa6eb848bf0bdac26e8fb4e146ecf307ca19abb53d04964351da','[\"*\"]','2026-09-04 09:12:53',NULL,'2026-09-04 09:12:52','2026-09-04 09:12:53'),(56,'App\\Models\\User',9,'mobile-token','0fa82e9cde871fd11ab6888ebb8d34aad87864d3eb0bb0508defea951f3831af','[\"*\"]','2026-09-04 09:35:38',NULL,'2026-09-04 09:32:00','2026-09-04 09:35:38'),(57,'App\\Models\\User',9,'mobile-token','a6fdca4efed1350eaa7e384d133d8b264a1036a57fdfa8da7177005ebc4f3e65','[\"*\"]','2026-09-04 09:59:52',NULL,'2026-09-04 09:55:37','2026-09-04 09:59:52'),(58,'App\\Models\\User',11,'mobile-token','e70aa2a291749cc3257e103d574e97666a352a3eb7b63e477246295e3a266e98','[\"*\"]','2026-09-04 12:30:43',NULL,'2026-09-04 11:43:10','2026-09-04 12:30:43'),(59,'App\\Models\\User',9,'mobile-token','6298e018a381008579c8bad848d891d049d8bda407c80502a90d331010efa17e','[\"*\"]','2026-09-04 12:35:24',NULL,'2026-09-04 12:35:00','2026-09-04 12:35:24'),(60,'App\\Models\\User',11,'mobile-token','ac0188664bd4dc14056e348827d352b76789030abfdd3eca65f7de8f12d5f99f','[\"*\"]','2026-09-04 13:16:01',NULL,'2026-09-04 13:15:15','2026-09-04 13:16:01'),(61,'App\\Models\\User',9,'mobile-token','800854fdb9af72a3e5f040be1d09e1ab3129775102f903d82d9d7b1bbab9eea5','[\"*\"]','2026-09-04 13:54:55',NULL,'2026-09-04 13:54:46','2026-09-04 13:54:55'),(62,'App\\Models\\User',9,'mobile-token','869499b88ef9a3e4a26d2243529ad9ba6397bb419465b8083cd2515ad28b0b7c','[\"*\"]','2026-09-04 18:56:40',NULL,'2026-09-04 18:56:02','2026-09-04 18:56:40'),(63,'App\\Models\\User',11,'mobile-token','14a972676eb8c379b293ba8db2d47f69d5ed2a77493fb5b1cc31e0bb6bbc4bc9','[\"*\"]','2026-09-05 12:22:23',NULL,'2026-09-05 12:20:59','2026-09-05 12:22:23'),(64,'App\\Models\\User',11,'mobile-token','3f67a3afeb42f752b3f1a403f370a9671a7edeb07643860c32853018b3369a9e','[\"*\"]','2026-09-06 12:27:47',NULL,'2026-09-06 03:12:34','2026-09-06 12:27:47'),(65,'App\\Models\\User',2,'mobile-token','5dc4d03b81c08dd6db3ef28e811cb2da52afe9da0d30efd4d3a72e44a5f97a65','[\"*\"]','2026-09-06 08:52:39',NULL,'2026-09-06 08:52:39','2026-09-06 08:52:39'),(66,'App\\Models\\User',6,'mobile-token','9e16016154716bea4e6c2230ea9fd6c52b9c819cde2d3845c1364c0f1691ac17','[\"*\"]','2026-09-06 08:52:40',NULL,'2026-09-06 08:52:40','2026-09-06 08:52:40'),(67,'App\\Models\\User',2,'mobile-token','e7b9b9b900271ed2e34ed38b792e34d7383efe4eb7a3ee17e8299249a8a4ff52','[\"*\"]','2026-09-06 09:27:41',NULL,'2026-09-06 09:27:40','2026-09-06 09:27:41'),(68,'App\\Models\\User',6,'mobile-token','1d2a5e5c1a47798cd36338ebbc573accb5296c2059cf32da814db84085ff8c8f','[\"*\"]','2026-09-06 09:27:42',NULL,'2026-09-06 09:27:40','2026-09-06 09:27:42'),(69,'App\\Models\\User',2,'mobile-token','cf88b83192bbffa77c3311b5b85ded4bc2ff386d6d5c2a4ee718aaa8abf3839e','[\"*\"]','2026-09-06 09:31:53',NULL,'2026-09-06 09:31:53','2026-09-06 09:31:53'),(70,'App\\Models\\User',6,'mobile-token','b28f93bc1deffd554a53ad89c330e977a1cd532fce1b336e6cfe1407e6a808e3','[\"*\"]','2026-09-06 09:31:54',NULL,'2026-09-06 09:31:53','2026-09-06 09:31:54'),(71,'App\\Models\\User',6,'mobile-token','b7c1f45f1c8e871f0f280528621785ffec0017aa01969a4352baa9ec77b6e5e5','[\"*\"]','2026-09-06 09:32:25',NULL,'2026-09-06 09:32:25','2026-09-06 09:32:25'),(72,'App\\Models\\User',2,'mobile-token','32f8c9be182157cd1adc27ab0d916609b77b26a5a7b07921a7d72f773eaf491a','[\"*\"]','2026-09-06 09:32:25',NULL,'2026-09-06 09:32:25','2026-09-06 09:32:25'),(73,'App\\Models\\User',6,'mobile-token','2fd02cee4af31ca70957b39c39106e71afc958d4481acfd3deabc2a01c7dd888','[\"*\"]',NULL,NULL,'2026-09-06 09:32:34','2026-09-06 09:32:34'),(74,'App\\Models\\User',2,'mobile-token','14a3496e01d3e4c8ad7b25fa5d4c412af6e62a8b8ae5de1ad1048d6e1d5b9be1','[\"*\"]',NULL,NULL,'2026-09-06 09:32:42','2026-09-06 09:32:42'),(75,'App\\Models\\User',6,'mobile-token','617aa5c29bec34cf510eb5cf7b3695e5ce75c5126953454b73c6bff90ba4381f','[\"*\"]',NULL,NULL,'2026-09-06 09:32:42','2026-09-06 09:32:42'),(76,'App\\Models\\User',6,'mobile-token','73153f9e6d08f47cd70fc0e1dd9785538363355624024c3a8adf389cc97881a3','[\"*\"]',NULL,NULL,'2026-09-06 09:32:48','2026-09-06 09:32:48'),(77,'App\\Models\\User',6,'mobile-token','3e8fa9206738523ee34a9b227acfe1be6f0af1e0ce50c5ec8e5da03f7e68bff6','[\"*\"]','2026-09-06 09:32:58',NULL,'2026-09-06 09:32:57','2026-09-06 09:32:58'),(78,'App\\Models\\User',2,'mobile-token','82a1b87f121fc0238769a57a16971734f80a689a232efe577ed8faf5d925a0fc','[\"*\"]',NULL,NULL,'2026-09-06 09:32:58','2026-09-06 09:32:58'),(79,'App\\Models\\User',11,'mobile-token','433dbcd811a83c4652141a85bc541c54203648941bb95ccafcdc77fb92efb552','[\"*\"]','2026-09-07 02:06:06',NULL,'2026-09-07 02:06:02','2026-09-07 02:06:06'),(80,'App\\Models\\User',11,'mobile-token','36757beee9315742a14bb9cf5c1c6cfc5fe8f051638cc2bd6090331c20cf3fbe','[\"*\"]','2026-09-07 03:23:45',NULL,'2026-09-07 03:23:09','2026-09-07 03:23:45'),(81,'App\\Models\\User',11,'mobile-token','871d41b24e2406bb390cd4c1e0da0d1c99899dd65a79b4e5e458d97efda0f4e8','[\"*\"]','2026-09-07 04:45:33',NULL,'2026-09-07 04:45:30','2026-09-07 04:45:33'),(82,'App\\Models\\User',11,'mobile-token','976fabf926e8c337cdb68e032e02201f2b63623a0f16fb3c7c66013bc1dffae0','[\"*\"]','2026-09-07 06:21:11',NULL,'2026-09-07 06:20:52','2026-09-07 06:21:11'),(83,'App\\Models\\User',11,'mobile-token','85ecfd6be13e5281ae02052b67b28dfba534bc2f4f2304fdf4f447659186d000','[\"*\"]','2026-09-07 06:50:26',NULL,'2026-09-07 06:49:01','2026-09-07 06:50:26');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesan_bantuans`
--

DROP TABLE IF EXISTS `pesan_bantuans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesan_bantuans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subjek` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baru',
  `balasan` text COLLATE utf8mb4_unicode_ci,
  `dibalas_oleh` bigint unsigned DEFAULT NULL,
  `dibalas_at` timestamp NULL DEFAULT NULL,
  `dibaca_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pesan_bantuans_user_id_foreign` (`user_id`),
  KEY `pesan_bantuans_dibalas_oleh_foreign` (`dibalas_oleh`),
  CONSTRAINT `pesan_bantuans_dibalas_oleh_foreign` FOREIGN KEY (`dibalas_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pesan_bantuans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesan_bantuans`
--

LOCK TABLES `pesan_bantuans` WRITE;
/*!40000 ALTER TABLE `pesan_bantuans` DISABLE KEYS */;
INSERT INTO `pesan_bantuans` VALUES (1,11,'Pardi','pardi23@gmail.com','Ingin menambahkan kos ','saya ingin menambahkan kos','baru',NULL,NULL,NULL,NULL,'2026-08-22 08:59:07','2026-08-22 08:59:07'),(2,6,'Anak Kos Rina','anak1@ngekos.test','Test API','Tes kirim dari smoke test','selesai','Baik, akan kami proses.',1,'2026-08-28 01:26:34','2026-08-29 07:38:59','2026-08-28 01:26:12','2026-08-29 07:38:59'),(3,6,'Rina','anak1@ngekos.test','tes','Pesan tes dari pengujian endpoint bantuan','baru',NULL,NULL,NULL,NULL,'2026-08-29 07:03:02','2026-08-29 07:03:02'),(4,6,'Rina','anak1@ngekos.test','tes ulang','Pesan valid untuk uji ulang setelah perbaikan','baru',NULL,NULL,NULL,NULL,'2026-08-29 07:11:22','2026-08-29 07:11:22'),(5,12,'Parto','parto32@gmail.com','soal data saya','apakah saya bisa lihat riwayat transaksi saya','selesai','bisa kak dengan premium nanti kakak bisa ekspor semua draf transaksi kakak',1,'2026-08-29 07:27:32','2026-08-31 06:42:14','2026-08-29 07:25:50','2026-08-31 06:42:14'),(7,12,'Parto','parto32@gmail.com','adalah pokoknya','halo pak saya ingin tau tentang kos budibud','baru',NULL,NULL,NULL,NULL,'2026-08-31 08:50:25','2026-08-31 08:50:25'),(8,11,'Pardi','pardi23@gmail.com','bingung','saya ingin kos saya ramai','selesai','berlangganan fitur premium pak jadi nanti kos bapak jadi masuk ke kos yang trending',4,'2026-09-04 06:24:56','2026-09-04 06:25:47','2026-09-04 06:20:39','2026-09-04 06:25:47'),(9,6,'Rina','anak1@ngekos.test','Tes','Pesan tes panjang cukup','baru',NULL,NULL,NULL,NULL,'2026-09-06 09:32:25','2026-09-06 09:32:25'),(10,NULL,'RAMA PUTRA PRAWARA','rama.putra24@students.utdi.ac.id','cara login','bingung login','baru',NULL,NULL,NULL,NULL,'2026-09-09 08:02:12','2026-09-09 08:02:12');
/*!40000 ALTER TABLE `pesan_bantuans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `properti_favorits`
--

DROP TABLE IF EXISTS `properti_favorits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properti_favorits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `properti_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `properti_favorits_user_id_properti_id_unique` (`user_id`,`properti_id`),
  KEY `properti_favorits_properti_id_foreign` (`properti_id`),
  CONSTRAINT `properti_favorits_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `properti_favorits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `properti_favorits`
--

LOCK TABLES `properti_favorits` WRITE;
/*!40000 ALTER TABLE `properti_favorits` DISABLE KEYS */;
/*!40000 ALTER TABLE `properti_favorits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `properti_fotos`
--

DROP TABLE IF EXISTS `properti_fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properti_fotos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `properti_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `properti_fotos_properti_id_urutan_index` (`properti_id`,`urutan`),
  CONSTRAINT `properti_fotos_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `properti_fotos`
--

LOCK TABLES `properti_fotos` WRITE;
/*!40000 ALTER TABLE `properti_fotos` DISABLE KEYS */;
INSERT INTO `properti_fotos` VALUES (1,1,'properti/kos-melati.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(2,2,'properti/kos-mawar.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(3,3,'properti/kos-anggrek.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(5,6,'properti/ypp35IlrKRt7Tg93VcHGT7pnAwlXybPqOcxOKJp0.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(6,7,'properti/0U2LOD4KZvSiZRKhvtTO4Bl7NjBj12b6WvONniVw.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(7,9,'properti/O8wB5Y2TJZQEbTE3zYFF6VAFdiWmCvZe4nKZuMpR.jpg',0,1,'2026-09-10 02:10:00','2026-09-10 02:10:00'),(10,4,'properti_galeri/DsSfymptVUdrtLH5xfrHQVvhPtbKHAd7wO8m2Ciw.jpg',0,1,'2026-09-10 07:19:42','2026-09-10 07:19:42'),(11,4,'properti_galeri/fpFRj59u9AZYYBC3Y2nqeIg42pEmOUNP2vTu0vN0.jpg',1,0,'2026-09-10 07:19:42','2026-09-10 07:19:42'),(12,4,'properti_galeri/HRAshF7v69O451BdPBgO3LmDiE1IdmDoDgO2C8G2.jpg',2,0,'2026-09-10 07:19:42','2026-09-10 07:19:42');
/*!40000 ALTER TABLE `properti_fotos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `propertis`
--

DROP TABLE IF EXISTS `propertis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `propertis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pemilik_id` bigint unsigned NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `kota` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `fasilitas` text COLLATE utf8mb4_unicode_ci,
  `aturan` text COLLATE utf8mb4_unicode_ci,
  `denda_per_hari` decimal(12,2) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `harga_harian` decimal(12,2) DEFAULT NULL,
  `harga_mingguan` decimal(12,2) DEFAULT NULL,
  `jenis_harga` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bulanan',
  `harga_asli` decimal(12,2) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `propertis_pemilik_id_status_index` (`pemilik_id`,`status`),
  KEY `propertis_status_kota_index` (`status`,`kota`),
  CONSTRAINT `propertis_pemilik_id_foreign` FOREIGN KEY (`pemilik_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `propertis`
--

LOCK TABLES `propertis` WRITE;
/*!40000 ALTER TABLE `propertis` DISABLE KEYS */;
INSERT INTO `propertis` VALUES (1,2,'Kos Melati','Jl. Melati No. 12, Bandung','Bandung',NULL,NULL,'Kos bersih dekat kampus, tersedia kamar AC.','WiFi, Kamar Mandi Dalam, Kasur, Lemari','Jam malam 23.00, dilarang membawa tamu menginap.',5000.00,1000000.00,33000.00,NULL,'bulanan',1200000.00,'aktif','properti/kos-melati.jpg','2026-08-20 08:44:30','2026-09-08 08:01:47',NULL),(2,2,'Kos Budibud','Jl. Pleret No. 8, Bantul','Bantul',NULL,NULL,'Kos murah dekat pasar, cocok untuk pekerja.','WiFi, Dapur Bersama, Parkir Motor','Dilarang membawa lawan jenis diatas jam 22.00.',5000.00,500000.00,17000.00,NULL,'bulanan',600000.00,'aktif','properti/kos-mawar.jpg','2026-08-20 08:44:30','2026-09-08 08:01:00',NULL),(3,3,'Kos Anggrek','Jl. Anggrek No. 3, Bandung','Bandung',NULL,NULL,'Kos eksklusif dengan kamar luas ber-AC.','AC, WiFi, Kulkas, Kamar Mandi Dalam','Bebas jam malam, dilarang bising.',10000.00,1500000.00,50000.00,NULL,'bulanan',1800000.00,'aktif','properti/kos-anggrek.jpg','2026-08-20 08:44:30','2026-09-08 08:01:47',NULL),(4,11,'Kos KOBAS','Gang arjuna no 30 condongcatur','Sleman',NULL,NULL,NULL,'WiFi, air, Tidak Termasuk Listrik, Kasur, Lemari, Kipas Angin','jam 22.00 tidak boleh ada perempuan',5000.00,750000.00,25000.00,NULL,'bulanan',900000.00,'aktif','properti_galeri/DsSfymptVUdrtLH5xfrHQVvhPtbKHAd7wO8m2Ciw.jpg','2026-08-21 08:03:05','2026-09-10 07:19:42',NULL),(6,13,'Kos Bu Tatik','Jalan janti no 34','Janti',NULL,NULL,NULL,'WiFi, Kulkas, air, Tidak Termasuk Listrik, Kasur, Lemari','dilarang membawa tamu lawan jenis di atas jam 22.00',NULL,650000.00,22000.00,NULL,'bulanan',780000.00,'aktif','properti/ypp35IlrKRt7Tg93VcHGT7pnAwlXybPqOcxOKJp0.jpg','2026-08-22 11:41:58','2026-09-08 08:01:01',NULL),(7,15,'Kos ceria','jalan Palagan no 90','Palagan',NULL,NULL,'bebas atau LV','Kamar Mandi Dalam, Kasur, kipas, Dapur Bersama','Bebas yang penting nyaman',NULL,800000.00,27000.00,NULL,'bulanan',960000.00,'aktif','properti/0U2LOD4KZvSiZRKhvtTO4Bl7NjBj12b6WvONniVw.jpg','2026-08-26 07:08:58','2026-09-08 08:01:01',NULL),(8,2,'Kos Uji Coba 2','Jl. Uji No 2','Sleman',NULL,NULL,NULL,'Wifi, AC',NULL,6000.00,750000.00,NULL,NULL,'bulanan',NULL,'aktif',NULL,'2026-08-28 01:27:09','2026-08-28 01:27:09','2026-08-28 01:27:09'),(9,20,'Kos Ambatron','jalan balero no 97','Ngawi',NULL,NULL,NULL,'WiFi, Kasur, Dapur, Parkir, Kamar Mandi Dalam, Air, Bantal, Lemari, Ruang Tamu, Parkir Motor & Sepeda, Parkir Mobil, Tamu, Lawan Jenis, Kloset Duduk, Meja, Ventilasi, Jendela, Tidak Termasuk Listrik, Akses','jam malam 23.00',0.00,700000.00,23000.00,NULL,'bulanan',840000.00,'aktif','properti/O8wB5Y2TJZQEbTE3zYFF6VAFdiWmCvZe4nKZuMpR.jpg','2026-08-29 01:49:13','2026-09-08 08:01:47',NULL);
/*!40000 ALTER TABLE `propertis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(22,2),(23,2),(6,3),(8,3),(10,3),(12,3),(15,3),(16,3),(17,3),(19,3),(20,3),(21,3),(23,3),(10,4),(11,4),(13,4),(15,4),(19,4),(21,4),(22,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(2,'pemilik','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(3,'admin','web','2026-08-20 00:26:19','2026-08-20 00:26:19'),(4,'anak_kos','web','2026-08-20 00:26:19','2026-08-20 00:26:19');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('6Dk5P1McRPVoE9Dg6tlVOi1DrRKED4FabHQPBUKQ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJsZ3hPSFFaM2swSmJLNDA4aFgzZ1EwSVluaWhYWjlhVDJWcEZnSzFFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788764262),('9Bz4lMVOowOYJM1Op8eU6w17U5vgw90ylcQ65QPU',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJiNUkzNVRLdFZrSzRGYTRTT0VjZVVGT0dtZVBmVzFBVktGcHI4SElVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAxXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788757192),('BMn6TXgdKnQpErmChvDoxA87yD0SWmuq370dxb8c',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJjYms3WjZoWDE1MGxUWVFnZWppczdxQlA4TVFpbDlIaTZsSnpYcjVMIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788763617),('hazPR2uLsbytQ8u6ZaUrSTdreJf2MOfCc3GL8gLT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJURERzVGNmOWZwZjFpVEhBUk5aNTFTenpUWGl5VnhxclBtUEk2U3RTIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9rb3MiLCJyb3V0ZSI6Imtvcy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788763617),('impLTlnlRkvAnSR57aEY2smctnvvw2mFAA8hHSE5',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiIxMEU5cjNRNGk0VlZKQnRUUEptWHhpVVFaRFM3UmZ5MVJ0dFNmQjJ3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788764251),('j2WHZFYVRjx0oAAQ3prTGPtKjzOCxwZtUsQSUboK',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiI0eVZoeE1SOU85UWh4dXhHQVNrSmFVelNHdzdzeEs4WWxmenJ2OHFrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788764279),('jDTlgZ2UFIMtYW8jPo3C3dmZswo4Hvk4fjZ9xcYb',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJFbXRua2lZT1pPcHlaMzIxNU9FYWU0dzZObkVXdno4NGhyelVlYk14IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788763617),('kcxCOSkWm1ivv9F1bgX2oYbP7Vbyb3VWuIjBzili',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJ3Z202eVQyNEU5VklVbXp5TWY5ZUcxbzJ6YUZKT1hBdG84TXFhelBmIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788763611),('l4UXdtiBjaVeQXZAJJND74u8iuleiv2mlh8xljtc',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJUQjdreHhkWlVkWkNNaHZPcTRIWTZ6TUhEblZIanlGbGhIeGVHeW9YIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788767632),('Pcjn2Ip66PPj1SlerC4ovCBvDFcsZS0126VorPx8',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJnZW5TTkd2SW0zNGZwWnJmUVVvQ2duWW5tVFlxU0dTOVhZQ2hScjJjIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3BlbWlsaWtcL3Jla2FwXC9wZGYifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9wZW1pbGlrXC9yZWthcFwvcGRmIiwicm91dGUiOiJwZW1pbGlrLnJla2FwLnBkZiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788766963),('pQEG3xLyPfhsBEVTsMGl44zKGMg1NwOQOUlmAFAH',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiIzbWNYRUNZNlNJYTBta1d6ZFhBTDR4TDIwMlE1elIyc1hKb2VyenZzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788764271),('R8rGv0XyLrLWRZhEbDNZrhUjwIjYKR724PioYJwJ',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJ6alE3YjRsczBLbGs4TmJFSnhYY1o1R3pwR0x1WmlhanY4RmJNUWZjIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2Rhc2hib2FyZFwvcGVtaWxpayJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2Rhc2hib2FyZFwvcGVtaWxpayIsInJvdXRlIjoiZGFzaGJvYXJkLnBlbWlsaWsifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788766963),('RcSmNQzcG6yJ5XdGUGjanhw3h8gI3tfUbMfVmVy4',20,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1','eyJfdG9rZW4iOiJIdjBMVzlSMEFHZFFkM1U1enBaa1ljS2VtT01JRnpQVGF0azY0dUh4IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2Rhc2hib2FyZFwvcGVtaWxpayIsInJvdXRlIjoiZGFzaGJvYXJkLnBlbWlsaWsifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjIwfQ==',1788764775),('sThcNb0l3oxUQBkMlzzwGhRm0IwvOAT6V2d9M6gN',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiIxMmlZek40ZEJRV0c5TXJIZWNROW1yek1nOHNRbnM5ajJtR21uQjZLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAxXC9sb2dpblwvcGVtaWxpayIsInJvdXRlIjoibG9naW4uZm9ybSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788757193),('U00iuarRpLDRkahqhq3pr7NN5cLYu2wnEker4EGZ',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168','eyJfdG9rZW4iOiJ3dnRvNTVhZWJSaFE4WFliVWpTRGxsbjZVbnRRU0xrQzlTcldLQ3dWIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAxXC9sb2dpblwvYW5ha19rb3MiLCJyb3V0ZSI6ImxvZ2luLmZvcm0ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788757193);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tagihan_pengingat`
--

DROP TABLE IF EXISTS `tagihan_pengingat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihan_pengingat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tagihan_id` bigint unsigned NOT NULL,
  `jenis` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_kirim` date NOT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tagihan_pengingat_tagihan_id_jenis_tanggal_kirim_unique` (`tagihan_id`,`jenis`,`tanggal_kirim`),
  KEY `tagihan_pengingat_tanggal_kirim_index` (`tanggal_kirim`),
  CONSTRAINT `tagihan_pengingat_tagihan_id_foreign` FOREIGN KEY (`tagihan_id`) REFERENCES `tagihans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tagihan_pengingat`
--

LOCK TABLES `tagihan_pengingat` WRITE;
/*!40000 ALTER TABLE `tagihan_pengingat` DISABLE KEYS */;
INSERT INTO `tagihan_pengingat` VALUES (1,2,'telat','2026-09-14','{\"denda\": 195000, \"total\": 1195000, \"selisih\": -39, \"hari_telat\": 39}','2026-09-14 12:56:51','2026-09-14 12:56:51'),(2,5,'telat','2026-09-14','{\"denda\": 380000, \"total\": 1380000, \"selisih\": -76, \"hari_telat\": 76}','2026-09-14 12:56:51','2026-09-14 12:56:51'),(3,16,'telat','2026-09-14','{\"denda\": 70000, \"total\": 720000, \"selisih\": -14, \"hari_telat\": 14}','2026-09-14 12:56:52','2026-09-14 12:56:52');
/*!40000 ALTER TABLE `tagihan_pengingat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tagihans`
--

DROP TABLE IF EXISTS `tagihans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tagihans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `penyewaan_id` bigint unsigned NOT NULL,
  `periode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `denda` decimal(12,2) NOT NULL DEFAULT '0.00',
  `jatuh_tempo` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_bayar',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tagihans_penyewaan_id_status_index` (`penyewaan_id`,`status`),
  CONSTRAINT `tagihans_penyewaan_id_foreign` FOREIGN KEY (`penyewaan_id`) REFERENCES `penyewaans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tagihans`
--

LOCK TABLES `tagihans` WRITE;
/*!40000 ALTER TABLE `tagihans` DISABLE KEYS */;
INSERT INTO `tagihans` VALUES (1,1,'Juli 2026',1000000.00,0.00,'2026-07-06','lunas','2026-08-20 08:44:30','2026-08-20 08:44:30'),(2,1,'Agustus 2026',1000000.00,195000.00,'2026-08-06','belum_bayar','2026-08-20 08:44:30','2026-09-14 12:56:46'),(3,2,'Juli 2026',850000.00,0.00,'2026-07-06','lunas','2026-08-20 08:44:30','2026-08-20 08:44:30'),(4,2,'Agustus 2026',850000.00,0.00,'2026-08-06','lunas','2026-08-20 08:44:30','2026-08-21 07:12:56'),(5,1,'Juni 2026',1000000.00,380000.00,'2026-06-30','belum_bayar','2026-08-21 12:57:58','2026-09-14 12:56:51'),(6,4,'Agustus 2026',750000.00,0.00,'2026-08-31','lunas','2026-08-22 07:11:56','2026-08-22 08:00:45'),(7,5,'September 2026',650000.00,0.00,'2026-09-30','lunas','2026-08-22 11:47:38','2026-08-25 16:26:46'),(8,5,'Oktober 2026',650000.00,0.00,'2026-10-31','lunas','2026-08-22 11:47:38','2026-09-09 03:18:11'),(9,5,'November 2026',650000.00,0.00,'2026-11-30','lunas','2026-08-22 11:47:38','2026-09-09 03:18:03'),(10,6,'September 2026',700000.00,0.00,'2026-09-30','lunas','2026-08-24 01:37:40','2026-08-24 06:00:13'),(11,6,'Oktober 2026',700000.00,0.00,'2026-10-31','lunas','2026-08-24 01:37:40','2026-09-08 04:07:18'),(12,6,'November 2026',700000.00,0.00,'2026-11-30','lunas','2026-08-24 01:37:40','2026-09-08 04:07:13'),(13,6,'Desember 2026',700000.00,0.00,'2026-12-31','lunas','2026-08-24 01:37:40','2026-09-08 04:07:09'),(14,6,'Januari 2027',700000.00,0.00,'2027-01-31','lunas','2026-08-24 01:37:40','2026-09-08 04:08:37'),(15,6,'Februari 2027',700000.00,0.00,'2027-02-28','lunas','2026-08-24 01:37:40','2026-09-08 04:08:33'),(16,7,'Agustus 2026',650000.00,70000.00,'2026-08-31','belum_bayar','2026-08-26 10:12:15','2026-09-14 12:56:51'),(17,7,'September 2026',650000.00,0.00,'2026-09-30','belum_bayar','2026-08-26 10:12:15','2026-08-26 10:12:15'),(18,14,'September 2026',700000.00,0.00,'2026-09-30','lunas','2026-09-08 04:10:48','2026-09-08 04:14:01'),(19,15,'September 2026',700000.00,0.00,'2026-09-30','lunas','2026-09-08 06:10:09','2026-09-08 06:12:37'),(20,15,'Oktober 2026',700000.00,0.00,'2026-10-31','belum_bayar','2026-09-08 06:10:09','2026-09-08 06:10:09'),(21,16,'10 September 2026 - 14 September 2026',250000.00,0.00,'2026-09-14','lunas','2026-09-09 03:49:19','2026-09-09 08:05:40'),(22,17,'November 2026',700000.00,0.00,'2026-11-30','belum_bayar','2026-09-09 08:15:13','2026-09-09 08:15:13'),(23,18,'September 2026',1500000.00,0.00,'2026-09-30','belum_bayar','2026-09-11 02:09:16','2026-09-11 02:09:16');
/*!40000 ALTER TABLE `tagihans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ulasans`
--

DROP TABLE IF EXISTS `ulasans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ulasans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `properti_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL DEFAULT '5',
  `komentar` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ulasans_user_id_properti_id_unique` (`user_id`,`properti_id`),
  KEY `ulasans_properti_id_foreign` (`properti_id`),
  CONSTRAINT `ulasans_properti_id_foreign` FOREIGN KEY (`properti_id`) REFERENCES `propertis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ulasans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ulasans`
--

LOCK TABLES `ulasans` WRITE;
/*!40000 ALTER TABLE `ulasans` DISABLE KEYS */;
/*!40000 ALTER TABLE `ulasans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `dinonaktifkan_pada` timestamp NULL DEFAULT NULL,
  `preferensi_notifikasi` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin Ngekos','superadmin.ngekos@gmail.com','081234567001',NULL,'2026-08-20 00:26:19','$2y$12$E.4jZCAFvAdhMcLbNg3T2u.8MSkzozciobTHtL4yRyqXQswF8eFUu',NULL,'2026-08-20 00:26:19','2026-09-05 12:09:34',NULL,NULL,'{\"chat_baru\": true, \"tagihan_baru\": true, \"pembayaran_diverifikasi\": false}'),(2,'Pemilik Budi','pemilik1@ngekos.test','081234567002',NULL,'2026-08-20 00:26:20','$2y$12$e9ua0/ZnqxXs0iwDFXmFOuE6kUj31T1zQWVb8b8.IurSA/ASRf12y',NULL,'2026-08-20 00:26:20','2026-08-20 00:26:20',NULL,NULL,NULL),(3,'Pemilik Siti','pemilik2@ngekos.test','081234567003',NULL,'2026-08-20 00:26:20','$2y$12$p6j8o3qf1BPSJbCCKyJbnuSuJgOSeY80.icWLYEDHi9YY6PKkVth.',NULL,'2026-08-20 00:26:20','2026-08-20 00:26:20',NULL,NULL,NULL),(4,'Admin Andi','admin.ngekos@gmail.com','081234567004',NULL,'2026-08-20 00:26:20','$2y$12$WW7ndv1nab6wgSi6UNc3keDrkcv/6R4OoikZICQV8lMJh/J/MX9fC',NULL,'2026-08-20 00:26:20','2026-08-28 02:31:44',NULL,NULL,NULL),(6,'Anak Kos Rina','anak1@ngekos.test','081234567006',NULL,'2026-08-20 00:26:21','$2y$12$z/7qbGJ6MlJw221FVPUpeO5aaKu7NR6w93aXC0mha6sjUK6W2UwzG','6op5EIQJsheiXIZ1KaXBf4AUNu35DJ9H61J4FUdNB18XTdk7wEOYX9nHP6An','2026-08-20 00:26:21','2026-09-04 08:30:13',NULL,NULL,NULL),(7,'Anak Kos Yoga','anak2@ngekos.test','081234567007',NULL,'2026-08-20 00:26:21','$2y$12$VvGkAton415A73ScFFee2.c6DMy2DrLbY3Ve9sRA54cdHNFeRqhba',NULL,'2026-08-20 00:26:21','2026-08-21 09:35:09',NULL,NULL,NULL),(8,'Anak Kos Maya','anak3@ngekos.test','081234567008',NULL,'2026-08-20 00:26:21','$2y$12$7it5a22GXbhSZ0xmHDsQm.QOpzfGk4U8dIJxF.h56OvMPkllWkuHO',NULL,'2026-08-20 00:26:21','2026-08-20 00:26:21',NULL,NULL,NULL),(9,'Febianti','febianti26@gmail.com','085758374175',NULL,NULL,'$2y$12$M2NHvYrNaVQuk2abLv9qzuYqs1mGajlenkIBDsp77kBUHnSNKGr.S',NULL,'2026-08-20 09:11:07','2026-08-20 09:11:07',NULL,NULL,NULL),(10,'diyanz','diyantoro225@gmail.com','085758374175',NULL,NULL,'$2y$12$2iNRnq/7gssvxhj07ejiC.l3V0MTc3NMFBL0c/WJ8tYk3EvfJ7IIO',NULL,'2026-08-20 12:52:01','2026-08-20 12:52:01',NULL,NULL,NULL),(11,'Pardi','pardi23@gmail.com','081234567001',NULL,NULL,'$2y$12$k7eIiHTUrzrEibtkY8wiFe2eSl.DF1N9l/hkQwegQmTq4mrA7g5Mi',NULL,'2026-08-20 13:39:12','2026-08-20 13:39:12',NULL,NULL,NULL),(12,'Parto','parto32@gmail.com','084576238743',NULL,NULL,'$2y$12$tjExn8DvElDYHveu7YH/VOGCoQ9NL7LN39N.HOzwGblGqUKRC1rn6',NULL,'2026-08-22 11:31:09','2026-08-22 11:31:09',NULL,NULL,NULL),(13,'Tatik','tatik09@gmail.com','097634512343',NULL,NULL,'$2y$12$pA.fOqof929Y0fovK0z.peQM5XljL2LdBAKh7qARnWBlrRmAj1HXO',NULL,'2026-08-22 11:37:59','2026-08-22 11:37:59',NULL,NULL,NULL),(14,'Cia','ciarotexa@gmail.com','085758374175',NULL,NULL,'$2y$12$9vlokv61qEJ3NhG4sMBNeOcjQ5R9sjN8b6oaDnrJdYU7iQt7ANLVe',NULL,'2026-08-24 01:33:08','2026-08-24 01:33:08',NULL,NULL,NULL),(15,'Sunarti','sunarti@gmail.com','086472051205',NULL,NULL,'$2y$12$1ZyrSQ8aI5x1N9lQQijNp.XgcZoK1XJJlMt.ziG.weWpZC62VIQ0.',NULL,'2026-08-26 06:58:48','2026-08-26 06:58:48',NULL,NULL,NULL),(16,'Vinsen','vinsen@gmail.com','08865321045',NULL,NULL,'$2y$12$yLpjUg/SUd8JsduC4PtIsOQ8iLYqEDT.9LTsq.uAYKTwfMcRVhV/C',NULL,'2026-08-26 07:29:39','2026-08-26 07:29:39',NULL,NULL,NULL),(17,'Diyan Toro','diyantoro114@gmail.com','086472051205',NULL,NULL,'$2y$12$CXUPDUNjK4V/F7M34DDlNOHBPgaUYJeAxYT8RWskW8DxG1r5p9vQO',NULL,'2026-08-26 16:15:57','2026-08-26 16:15:57',NULL,NULL,NULL),(18,'Kentong','kentong21@gmail.com','084576238743',NULL,NULL,'$2y$12$QLW2HlLOwMuiljD0uOIy4unfSnf/DIk6OnDyC9MIgBxtJ0/9SlRrO',NULL,'2026-08-27 07:35:05','2026-08-27 07:35:05',NULL,NULL,NULL),(19,'Diyan','diyan14@gamil.com',NULL,NULL,NULL,'$2y$12$7fX1TYBhrxRGFXyRLca...0L.3x/ODsrsVNwwmJ72JvJ6F/bHL8s6',NULL,'2026-08-27 07:51:27','2026-08-27 07:51:27',NULL,NULL,NULL),(20,'Rahul','rahulkece@gmail.com','0987554321',NULL,NULL,'$2y$12$XmH3oPFA9LdbOpYCWd.oMONVjdmxctkdmqamZenR7EFf4YVjbMuYC',NULL,'2026-08-28 07:06:20','2026-08-28 07:06:20',NULL,NULL,NULL),(21,'Rama','ramaputra@gmail.com','08765432123',NULL,NULL,'$2y$12$N0bC.8NDHGTngPib.cRcnes4ExOam.4YA/AsNlK/kS/L0m2LPDgEW',NULL,'2026-09-09 08:13:47','2026-09-09 08:13:47',NULL,NULL,NULL),(22,'Dyanz','dyanz@gmail.com','085721543216',NULL,NULL,'$2y$12$dsFEE0.79c4CpN0.hf4n2Oh86xD1Nw9KAsy9pJcMzu5lSdZTWUyuG',NULL,'2026-09-11 01:31:14','2026-09-11 01:31:14',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'ngekos'
--

--
-- Dumping routines for database 'ngekos'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15 19:53:53
