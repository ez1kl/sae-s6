/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (aarch64)
--
-- Host: localhost    Database: sae_db
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Current Database: `sae_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sae_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;

USE `sae_db`;

--
-- Table structure for table `app_user`
--

DROP TABLE IF EXISTS `app_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_user`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `app_user` WRITE;
/*!40000 ALTER TABLE `app_user` DISABLE KEYS */;
INSERT INTO `app_user` VALUES
(29,'pons.sebastien@example.com','[\"ROLE_MEMBRE\"]','$2y$13$5IRl8CHtMCCkKGL4Jeax7eDLTWMHU4ILw.YyHAY5KjiOs/Y6beoGG'),
(30,'barbier.susanne@example.com','[\"ROLE_MEMBRE\"]','$2y$13$Lt1nBQhbIs0jegft38Hatuc7HgxtzEJy8B7EjoN9DSBzfPd5lgUTm'),
(31,'jcarlier@example.net','[\"ROLE_MEMBRE\"]','$2y$13$TVcRmoe3dNLidHuiRwAKOuSrEYftc0.rOWwcgfJAW.kZ9/h3yNXCS'),
(32,'humbert.matthieu@example.com','[\"ROLE_MEMBRE\"]','$2y$13$RqXtU.K80ulbzZdMrOnzv.m5O2T.vTgSFUn6OFbATKr1CMvfDLQK.'),
(33,'smarchand@example.net','[\"ROLE_MEMBRE\"]','$2y$13$PSzdBLTxsf60RVyQufrOFOC/Cfi9mhLOJg6bsCyfeylZbcF95cx96'),
(34,'delmas.lucy@example.org','[\"ROLE_MEMBRE\"]','$2y$13$.yawp213z5GYHjUKx.hbqeWrnqPUeQJrkKeqFApuqzih4DSgHxDTm'),
(35,'pierre.joubert@example.org','[\"ROLE_MEMBRE\"]','$2y$13$oEPTlu7yQLcQW5ApeP/onOk0lbx8bVqiCPyZE0P7puEVBc5R50nHW'),
(36,'roland.monnier@example.org','[\"ROLE_MEMBRE\"]','$2y$13$/0dWghcL0Mf/nQLC6aKQeO2GI90QfOS4XMM2l6PB.NY767qYgcE5S'),
(37,'zacharie90@example.com','[\"ROLE_MEMBRE\"]','$2y$13$Lc6C6IyywFmkznQm.L2a9OmMmH7XabwRHnSi1cFsTjWfnVfyNXB1m'),
(38,'yhumbert@example.org','[\"ROLE_MEMBRE\"]','$2y$13$9/drCczcUVy6kIKwnyBZHOEBESVJRbdv.ZCgTes0LgLyPKJmMzF2K'),
(39,'admin@admin.fr','[\"ROLE_RESPONSABLE\"]','$2y$13$/iJ3100Sa48V0z.WJ5dyKeSJMgemLNm8gd1rgM3zOu3s5yAjehXwm'),
(40,'biblio@biblio.fr','[\"ROLE_BIBLIOTHECAIRE\"]','$2y$13$4nNOyA.dMS72QA/MbVcDjuIg2BCeB/oIpds/geCL7/KTIdFMHDOEm');
/*!40000 ALTER TABLE `app_user` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `author`
--

DROP TABLE IF EXISTS `author`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `death_date` date DEFAULT NULL,
  `nationality` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `author`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `author` WRITE;
/*!40000 ALTER TABLE `author` DISABLE KEYS */;
INSERT INTO `author` VALUES
(12,'Hugo','Victor','1802-02-26','1885-05-22','Française','https://picsum.photos/seed/author-442/400/400','Figure majeure du romantisme français, Victor Hugo a marqué la littérature avec des romans sociaux et une poésie engagée.'),
(13,'Camus','Albert','1913-11-07','1960-01-04','Française','https://picsum.photos/seed/author-7750/400/400','Écrivain et philosophe de l\'absurde, Albert Camus explore la condition humaine, la révolte et la quête de sens.'),
(14,'Austen','Jane','1775-12-16','1817-07-18','Britannique','https://picsum.photos/seed/author-8195/400/400','Romancière anglaise incontournable, Jane Austen observe avec finesse les mœurs, les classes sociales et les relations amoureuses.'),
(15,'García Márquez','Gabriel','1927-03-06','2014-04-17','Colombienne','https://picsum.photos/seed/author-8237/400/400','Auteur emblématique du réalisme magique, Gabriel García Márquez mêle histoire, mémoire et imaginaire dans ses récits.'),
(16,'Murakami','Haruki','1949-01-12',NULL,'Japonaise','https://picsum.photos/seed/author-4209/400/400','Romancier contemporain japonais, Haruki Murakami construit des univers oniriques où solitude, musique et mystère se rencontrent.'),
(17,'Oui','Non','2026-03-19','2026-03-15','Française','google.com','azeaze');
/*!40000 ALTER TABLE `author` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `book`
--

DROP TABLE IF EXISTS `book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `release_year` int(11) NOT NULL,
  `language` varchar(10) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CBE5A331F675F31B` (`author_id`),
  CONSTRAINT `FK_CBE5A331F675F31B` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `book` WRITE;
/*!40000 ALTER TABLE `book` DISABLE KEYS */;
INSERT INTO `book` VALUES
(44,'Les Misérables',1862,'fr',12,'https://picsum.photos/299'),
(45,'Notre-Dame de Paris',1831,'fr',12,'https://picsum.photos/219'),
(46,'Les Contemplations',1856,'fr',12,'https://picsum.photos/329'),
(47,'L\'Étranger',1942,'fr',13,'https://picsum.photos/270'),
(48,'La Peste',1947,'fr',13,'https://picsum.photos/241'),
(49,'Le Mythe de Sisyphe',1942,'fr',13,'https://picsum.photos/205'),
(50,'Orgueil et Préjugés',1813,'en',14,'https://picsum.photos/247'),
(51,'Raison et Sentiments',1811,'en',14,'https://picsum.photos/234'),
(52,'Emma',1815,'en',14,'https://picsum.photos/291'),
(53,'Cent ans de solitude',1967,'es',15,'https://picsum.photos/211'),
(54,'L\'Amour aux temps du choléra',1985,'es',15,'https://picsum.photos/266'),
(55,'Chronique d\'une mort annoncée',1981,'es',15,'https://picsum.photos/388'),
(56,'Kafka sur le rivage',2002,'ja',16,'https://picsum.photos/308'),
(57,'1Q84',2009,'ja',16,'https://picsum.photos/213'),
(58,'La Ballade de l\'impossible',1987,'ja',16,'https://picsum.photos/225'),
(59,'Les Travailleurs de la mer',1866,'fr',12,'https://picsum.photos/265'),
(60,'La Chute',1956,'fr',13,'https://picsum.photos/211'),
(61,'Mansfield Park',1814,'en',14,'https://picsum.photos/388'),
(62,'Des feuilles mortes',1955,'es',15,'https://picsum.photos/393'),
(63,'Après le tremblement de terre',2000,'ja',16,'https://picsum.photos/223');
/*!40000 ALTER TABLE `book` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `book_category`
--

DROP TABLE IF EXISTS `book_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `book_category` (
  `book_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`,`category_id`),
  KEY `IDX_1FB30F9816A2B381` (`book_id`),
  KEY `IDX_1FB30F9812469DE2` (`category_id`),
  CONSTRAINT `FK_1FB30F9812469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_1FB30F9816A2B381` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_category`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `book_category` WRITE;
/*!40000 ALTER TABLE `book_category` DISABLE KEYS */;
INSERT INTO `book_category` VALUES
(44,12),
(45,12),
(46,15),
(47,12),
(47,14),
(48,12),
(49,14),
(50,12),
(50,16),
(51,12),
(51,16),
(52,12),
(52,16),
(53,12),
(53,16),
(54,12),
(54,16),
(55,12),
(55,16),
(56,12),
(56,13),
(56,16),
(57,12),
(57,13),
(57,16),
(58,12),
(58,16),
(59,12),
(60,12),
(60,14),
(61,12),
(61,16),
(62,12),
(62,16),
(63,13),
(63,16);
/*!40000 ALTER TABLE `book_category` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES
(12,'Roman','Œuvres de fiction narrative en prose.'),
(13,'Science-fiction','Récits basés sur des avancées scientifiques ou technologiques imaginaires.'),
(14,'Philosophie','Ouvrages traitant de questions fondamentales sur l\'existence et la connaissance.'),
(15,'Poésie','Œuvres littéraires en vers ou en prose poétique.'),
(16,'Littérature étrangère','Traductions d\'œuvres majeures de la littérature mondiale.');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES
('DoctrineMigrations\\Version20260311074944','2026-03-11 07:50:00',125),
('DoctrineMigrations\\Version20260311082530','2026-03-11 08:25:37',18),
('DoctrineMigrations\\Version20260311150657','2026-03-11 15:07:01',19),
('DoctrineMigrations\\Version20260313095426','2026-03-16 11:05:58',27),
('DoctrineMigrations\\Version20260316113024','2026-03-17 10:27:05',30);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `loan`
--

DROP TABLE IF EXISTS `loan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `book_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C5D30D0316A2B381` (`book_id`),
  KEY `IDX_C5D30D037597D3FE` (`member_id`),
  CONSTRAINT `FK_C5D30D0316A2B381` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`),
  CONSTRAINT `FK_C5D30D037597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `loan` WRITE;
/*!40000 ALTER TABLE `loan` DISABLE KEYS */;
INSERT INTO `loan` VALUES
(50,'2025-11-19 02:37:54','2025-12-03 02:37:54','2025-12-06 02:37:54',44,28),
(51,'2025-10-23 02:25:00','2025-11-06 02:25:00','2025-11-04 02:25:00',45,31),
(52,'2025-11-04 16:59:53','2025-11-18 16:59:53','2025-11-24 16:59:53',46,32),
(53,'2025-10-30 15:39:28','2025-11-13 15:39:28','2025-11-18 15:39:28',47,33),
(54,'2025-11-06 13:00:00','2025-11-20 13:00:00','2025-11-25 13:00:00',48,32),
(55,'2025-11-14 04:17:47','2025-11-28 04:17:47','2025-12-03 04:17:47',49,33),
(56,'2025-11-08 13:29:05','2025-11-22 13:29:05','2025-11-23 13:29:05',50,27),
(57,'2026-03-14 07:55:38','2026-03-28 07:55:38',NULL,51,26),
(58,'2026-03-15 00:07:47','2026-03-29 00:07:47',NULL,52,32),
(59,'2026-03-14 05:33:08','2026-03-28 05:33:08',NULL,53,29),
(60,'2026-03-09 09:51:18','2026-03-23 09:51:18',NULL,54,35),
(61,'2026-03-16 10:08:05','2026-03-30 10:08:05',NULL,55,26),
(62,'2026-01-19 09:56:15','2026-02-02 09:56:15',NULL,56,32),
(63,'2026-02-05 07:02:41','2026-02-19 07:02:41',NULL,57,35),
(64,'2026-02-02 21:49:01','2026-02-16 21:49:01',NULL,58,33);
/*!40000 ALTER TABLE `loan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membership_date` date NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `suspended` tinyint(4) NOT NULL DEFAULT 0,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_70E4FA78A76ED395` (`user_id`),
  CONSTRAINT `FK_70E4FA78A76ED395` FOREIGN KEY (`user_id`) REFERENCES `app_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
INSERT INTO `member` VALUES
(26,'2024-05-23','Perrot','Martine','2007-09-10','+33 7 30 16 59 26','80, place Masson\n67007 LedouxVille',29,0,'https://picsum.photos/seed/member-211/400/400'),
(27,'2025-01-20','Voisin','Luc','2000-08-18','0699826533','79, place Meunier\n73149 Fouquet-la-Forêt',30,0,'https://picsum.photos/seed/member-8736/400/400'),
(28,'2024-06-25','Millet','Bertrand','1984-10-03','+33 8 12 68 85 62','960, rue de Normand\n19493 Klein-la-Forêt',31,0,'https://picsum.photos/seed/member-8298/400/400'),
(29,'2026-03-12','Gay','Marc','1973-09-06','02 60 86 21 64','77, impasse Schneider\n35543 Bodin',32,0,'https://picsum.photos/seed/member-6609/400/400'),
(30,'2025-03-04','Brunel','Charlotte','1983-12-18','01 74 92 15 23','39, place de Julien\n12428 Potier',33,0,'https://picsum.photos/seed/member-4256/400/400'),
(31,'2024-03-16','Jourdan','Lorraine','1980-07-13','01 81 67 96 31','987, place de Royer\n36572 Descamps',34,0,'https://picsum.photos/seed/member-3151/400/400'),
(32,'2026-01-13','Dubois','Guy','1995-03-14','01 60 38 65 87','34, rue de Hardy\n65826 Marechal',35,0,'https://picsum.photos/seed/member-7457/400/400'),
(33,'2025-06-21','Fernandez','Marcel','1968-09-06','+33 3 79 61 37 86','22, place Laurent\n80402 Letellier',36,0,'https://picsum.photos/seed/member-5702/400/400'),
(34,'2023-12-24','Caron','Lucy','2002-07-05','05 30 89 22 77','41, avenue Antoine Schmitt\n18914 Colas-sur-Hoareau',37,1,'https://picsum.photos/seed/member-5134/400/400'),
(35,'2024-03-07','Bazin','Geneviève','1988-10-24','0747226583','3, impasse de Blot\n67429 Payet',38,1,'https://picsum.photos/seed/member-9846/400/400');
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `book_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_42C8495516A2B381` (`book_id`),
  KEY `IDX_42C849557597D3FE` (`member_id`),
  CONSTRAINT `FK_42C8495516A2B381` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_42C849557597D3FE` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
INSERT INTO `reservation` VALUES
(32,'2026-03-18 12:20:29',59,26),
(33,'2026-03-18 12:20:29',60,27),
(34,'2026-03-18 12:20:29',61,28),
(35,'2026-03-18 12:24:23',44,26);
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping events for database 'sae_db'
--

--
-- Dumping routines for database 'sae_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-18 13:40:53
