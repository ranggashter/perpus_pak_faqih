-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: perpus_api
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_buku` varchar(20) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_buku` (`kode_buku`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES (1,'BK001','Laskar Pelangi','Andrea Hirata','Novel',2005,'Bentang Pustaka','aa654da298bba6b9a42489189d30df3d.jpg','2026-09-24 03:31:33'),(2,'BK002','Bumi','Tere Liye','Novel',2014,'Gramedia','4251435a3f495f460d88a508665a004b.jpg','2026-09-24 03:31:33'),(3,'BK003','Pemrograman PHP','Abdul Kadir','Teknologi',2020,'Andi','20c4c26a3943aa417e618817687c32be.jpg','2026-09-24 03:31:33'),(4,'BK004','Belajar HTML dan CSS','Jubilee Enterprise','Teknologi',2021,'Elex Media',NULL,'2026-09-24 03:31:33'),(5,'BK005','Dasar-Dasar JavaScript','Wahana Komputer','Teknologi',2022,'Andi',NULL,'2026-09-24 03:31:33'),(6,'BK006','Filosofi Teras','Henry Manampiring','Pengembangan Diri',2018,'Kompas',NULL,'2026-09-24 03:31:33'),(7,'BK007','Atomic Habits','James Clear','Pengembangan Diri',2019,'Gramedia',NULL,'2026-09-24 03:31:33'),(8,'BK008','Clean Code','Robert C. Martin','Teknologi',2008,'Prentice Hall',NULL,'2026-09-24 03:31:33'),(9,'BK009','Negeri 5 Menara','Ahmad Fuadi','Novel',2009,'Gramedia','33f1cb0ca89f4e84c452a26d84f7ba2c.jpg','2026-09-24 03:31:33'),(10,'BK010','Sapiens','Yuval Noah Harari','Sejarah',2011,'Kepustakaan Populer Gramedia','de2b47e5280c035a2ad6719f31ed5dce.jpg','2026-09-24 03:31:33'),(11,'BK-111111','Laskar Pelangii','rangga','Sejarah',2026,'Gramedia','3d962ee776e86a8bfcd00805fb948e68.jpg','2026-09-24 06:19:37'),(12,'BK-111119','Laskar Pelangii','ranggajhbjhn','Sejarah',2026,'Gramedia','2c135f7aa4e69e799de1cbf2739a9600.jpg','2026-09-24 06:21:01');
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas') NOT NULL DEFAULT 'petugas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin','$2y$10$rAzco9NjE6uqcacD/8DTzuKaIYHhJzrNrmUtqv6Db8CN4M/BBbGwS','admin','2026-09-24 03:31:33'),(2,'Petugas Satu','petugas','petugas123','petugas','2026-09-24 03:31:33');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24 13:56:37
