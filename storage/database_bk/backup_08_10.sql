-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: a31_factory
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `approval_histories`
--

DROP TABLE IF EXISTS `approval_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `approvable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approvable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int NOT NULL DEFAULT '1',
  `workflow_status_before` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workflow_status_after` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_histories_approvable_type_approvable_id_index` (`approvable_type`,`approvable_id`),
  KEY `approval_histories_user_id_index` (`user_id`),
  KEY `approval_histories_action_index` (`action`),
  KEY `approval_histories_level_index` (`level`),
  KEY `approval_histories_created_at_index` (`created_at`),
  CONSTRAINT `approval_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_histories`
--

LOCK TABLES `approval_histories` WRITE;
/*!40000 ALTER TABLE `approval_histories` DISABLE KEYS */;
INSERT INTO `approval_histories` VALUES (1,'Modules\\PersonnelReport\\Models\\EmployeeLeave',5,1,'approved',1,NULL,'approved_by_director',NULL,NULL,'\"[]\"','2025-10-01 02:24:46','2025-10-01 02:24:46'),(2,'Modules\\PersonnelReport\\Models\\EmployeeLeave',4,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 02:27:34','2025-10-01 02:27:34'),(3,'Modules\\PersonnelReport\\Models\\EmployeeLeave',4,1,'rejected',1,NULL,'rejected',NULL,'123456','\"[]\"','2025-10-01 02:41:22','2025-10-01 02:41:22'),(4,'Modules\\PersonnelReport\\Models\\EmployeeLeave',6,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 02:48:42','2025-10-01 02:48:42'),(5,'Modules\\PersonnelReport\\Models\\EmployeeLeave',7,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 02:51:22','2025-10-01 02:51:22'),(6,'Modules\\PersonnelReport\\Models\\EmployeeLeave',8,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 03:39:07','2025-10-01 03:39:07'),(7,'Modules\\PersonnelReport\\Models\\EmployeeLeave',9,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 03:44:58','2025-10-01 03:44:58'),(8,'Modules\\PersonnelReport\\Models\\EmployeeLeave',10,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 06:38:30','2025-10-01 06:38:30'),(9,'Modules\\PersonnelReport\\Models\\EmployeeLeave',11,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-01 06:45:07','2025-10-01 06:45:07'),(10,'Modules\\VehicleRegistration\\Models\\VehicleRegistration',32,1,'approved',1,NULL,'approved',NULL,NULL,'\"[]\"','2025-10-01 06:59:40','2025-10-01 06:59:40'),(11,'Modules\\VehicleRegistration\\Models\\VehicleRegistration',33,1,'approved',1,NULL,'approved',NULL,NULL,'\"[]\"','2025-10-01 07:13:07','2025-10-01 07:13:07'),(12,'Modules\\VehicleRegistration\\Models\\VehicleRegistration',31,1,'approved',1,NULL,'approved',NULL,NULL,'\"[]\"','2025-10-03 02:14:59','2025-10-03 02:14:59'),(13,'Modules\\PersonnelReport\\Models\\EmployeeLeave',12,1,'approved',1,NULL,'approved_by_approver',NULL,NULL,'\"[]\"','2025-10-08 00:10:05','2025-10-08 00:10:05'),(14,'Modules\\VehicleRegistration\\Models\\VehicleRegistration',30,1,'rejected',1,NULL,'rejected',NULL,'Dung nghi nua','\"[]\"','2025-10-08 01:35:10','2025-10-08 01:35:10');
/*!40000 ALTER TABLE `approval_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `old_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` enum('Electronic','Furniture','Gear') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Good','Fine','Bad','Damaged') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_service` tinyint(1) NOT NULL DEFAULT '1',
  `is_gpr` tinyint(1) NOT NULL DEFAULT '1',
  `real_price` int DEFAULT NULL,
  `expected_price` int DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `acquisition_type` enum('Directed','Founded','Transferred') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `funded_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bulk_messages`
--

DROP TABLE IF EXISTS `bulk_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bulk_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `numbers` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bulk_messages`
--

LOCK TABLES `bulk_messages` WRITE;
/*!40000 ALTER TABLE `bulk_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `bulk_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:103:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"dashboard.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:12;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:9:\"user.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"user.view.own\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:20:\"user.view.department\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:17:\"user.view.company\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:13:\"user.view.all\";s:1:\"c\";s:3:\"web\";}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:11:\"user.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:9:\"user.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"user.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:9:\"role.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:11:\"role.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:9:\"role.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:11:\"role.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:15:\"permission.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:17:\"permission.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"permission.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:17:\"permission.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:15:\"department.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:18;a:3:{s:1:\"a\";i:19;s:1:\"b\";s:19:\"department.view.own\";s:1:\"c\";s:3:\"web\";}i:19;a:3:{s:1:\"a\";i:20;s:1:\"b\";s:26:\"department.view.department\";s:1:\"c\";s:3:\"web\";}i:20;a:3:{s:1:\"a\";i:21;s:1:\"b\";s:23:\"department.view.company\";s:1:\"c\";s:3:\"web\";}i:21;a:3:{s:1:\"a\";i:22;s:1:\"b\";s:19:\"department.view.all\";s:1:\"c\";s:3:\"web\";}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:17:\"department.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:15:\"department.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:17:\"department.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:18:\"department.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:13:\"employee.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:27;a:3:{s:1:\"a\";i:28;s:1:\"b\";s:17:\"employee.view.own\";s:1:\"c\";s:3:\"web\";}i:28;a:3:{s:1:\"a\";i:29;s:1:\"b\";s:24:\"employee.view.department\";s:1:\"c\";s:3:\"web\";}i:29;a:3:{s:1:\"a\";i:30;s:1:\"b\";s:21:\"employee.view.company\";s:1:\"c\";s:3:\"web\";}i:30;a:3:{s:1:\"a\";i:31;s:1:\"b\";s:17:\"employee.view.all\";s:1:\"c\";s:3:\"web\";}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:15:\"employee.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:13:\"employee.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:15:\"employee.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:16:\"employee.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:11:\"report.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:36;a:3:{s:1:\"a\";i:37;s:1:\"b\";s:15:\"report.view.own\";s:1:\"c\";s:3:\"web\";}i:37;a:3:{s:1:\"a\";i:38;s:1:\"b\";s:22:\"report.view.department\";s:1:\"c\";s:3:\"web\";}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:19:\"report.view.company\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:12;}}i:39;a:3:{s:1:\"a\";i:40;s:1:\"b\";s:15:\"report.view.all\";s:1:\"c\";s:3:\"web\";}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:13:\"report.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:11:\"report.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:13:\"report.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:14:\"report.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:12;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:10:\"leave.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:45;a:3:{s:1:\"a\";i:46;s:1:\"b\";s:14:\"leave.view.own\";s:1:\"c\";s:3:\"web\";}i:46;a:3:{s:1:\"a\";i:47;s:1:\"b\";s:21:\"leave.view.department\";s:1:\"c\";s:3:\"web\";}i:47;a:3:{s:1:\"a\";i:48;s:1:\"b\";s:18:\"leave.view.company\";s:1:\"c\";s:3:\"web\";}i:48;a:3:{s:1:\"a\";i:49;s:1:\"b\";s:14:\"leave.view.all\";s:1:\"c\";s:3:\"web\";}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:12:\"leave.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:10:\"leave.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:12:\"leave.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:13:\"leave.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:12;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:25:\"vehicle_registration.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:12;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:27:\"vehicle_registration.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:12;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:25:\"vehicle_registration.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:12;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:27:\"vehicle_registration.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:12;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:28:\"vehicle_registration.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:12;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:27:\"vehicle_registration.assign\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:12;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:12:\"profile.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:12;}}i:60;a:3:{s:1:\"a\";i:61;s:1:\"b\";s:14:\"profile.create\";s:1:\"c\";s:3:\"web\";}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:12:\"profile.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:12;}}i:62;a:3:{s:1:\"a\";i:63;s:1:\"b\";s:14:\"profile.delete\";s:1:\"c\";s:3:\"web\";}i:63;a:3:{s:1:\"a\";i:64;s:1:\"b\";s:10:\"view-users\";s:1:\"c\";s:3:\"web\";}i:64;a:3:{s:1:\"a\";i:65;s:1:\"b\";s:12:\"create-users\";s:1:\"c\";s:3:\"web\";}i:65;a:3:{s:1:\"a\";i:66;s:1:\"b\";s:10:\"edit-users\";s:1:\"c\";s:3:\"web\";}i:66;a:3:{s:1:\"a\";i:67;s:1:\"b\";s:12:\"delete-users\";s:1:\"c\";s:3:\"web\";}i:67;a:3:{s:1:\"a\";i:68;s:1:\"b\";s:10:\"view-roles\";s:1:\"c\";s:3:\"web\";}i:68;a:3:{s:1:\"a\";i:69;s:1:\"b\";s:12:\"create-roles\";s:1:\"c\";s:3:\"web\";}i:69;a:3:{s:1:\"a\";i:70;s:1:\"b\";s:10:\"edit-roles\";s:1:\"c\";s:3:\"web\";}i:70;a:3:{s:1:\"a\";i:71;s:1:\"b\";s:12:\"delete-roles\";s:1:\"c\";s:3:\"web\";}i:71;a:3:{s:1:\"a\";i:72;s:1:\"b\";s:16:\"view-permissions\";s:1:\"c\";s:3:\"web\";}i:72;a:3:{s:1:\"a\";i:73;s:1:\"b\";s:18:\"create-permissions\";s:1:\"c\";s:3:\"web\";}i:73;a:3:{s:1:\"a\";i:74;s:1:\"b\";s:16:\"edit-permissions\";s:1:\"c\";s:3:\"web\";}i:74;a:3:{s:1:\"a\";i:75;s:1:\"b\";s:18:\"delete-permissions\";s:1:\"c\";s:3:\"web\";}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:27:\"vehicle_registration.reject\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:12;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:33:\"vehicle_registration.download_pdf\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:12;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:36:\"vehicle_registration.check_signature\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:12;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:22:\"record_management.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:12;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:26:\"record_management.view.own\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:5;i:2;i:12;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:33:\"record_management.view.department\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:30:\"record_management.view.company\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:12;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:26:\"record_management.view.all\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:12;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:24:\"record_management.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:22:\"record_management.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:12;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:24:\"record_management.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:12;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:25:\"record_management.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:12;}}i:87;a:3:{s:1:\"a\";i:88;s:1:\"b\";s:22:\"record_management.view\";s:1:\"c\";s:8:\"backpack\";}i:88;a:3:{s:1:\"a\";i:89;s:1:\"b\";s:24:\"record_management.create\";s:1:\"c\";s:8:\"backpack\";}i:89;a:3:{s:1:\"a\";i:90;s:1:\"b\";s:22:\"record_management.edit\";s:1:\"c\";s:8:\"backpack\";}i:90;a:3:{s:1:\"a\";i:91;s:1:\"b\";s:24:\"record_management.delete\";s:1:\"c\";s:8:\"backpack\";}i:91;a:3:{s:1:\"a\";i:92;s:1:\"b\";s:21:\"salary_up_record.view\";s:1:\"c\";s:8:\"backpack\";}i:92;a:3:{s:1:\"a\";i:93;s:1:\"b\";s:23:\"salary_up_record.create\";s:1:\"c\";s:8:\"backpack\";}i:93;a:3:{s:1:\"a\";i:94;s:1:\"b\";s:21:\"salary_up_record.edit\";s:1:\"c\";s:8:\"backpack\";}i:94;a:3:{s:1:\"a\";i:95;s:1:\"b\";s:23:\"salary_up_record.delete\";s:1:\"c\";s:8:\"backpack\";}i:95;a:3:{s:1:\"a\";i:96;s:1:\"b\";s:21:\"salary_up_record.view\";s:1:\"c\";s:3:\"web\";}i:96;a:3:{s:1:\"a\";i:97;s:1:\"b\";s:23:\"salary_up_record.create\";s:1:\"c\";s:3:\"web\";}i:97;a:3:{s:1:\"a\";i:98;s:1:\"b\";s:21:\"salary_up_record.edit\";s:1:\"c\";s:3:\"web\";}i:98;a:3:{s:1:\"a\";i:99;s:1:\"b\";s:23:\"salary_up_record.delete\";s:1:\"c\";s:3:\"web\";}i:99;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:24:\"so_dieu_dong_record.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:100;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:26:\"so_dieu_dong_record.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:101;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:26:\"so_dieu_dong_record.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:102;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:26:\"so_dieu_dong_record.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:6:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:16:\"Ban Giám đốc\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"Trưởng phòng\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:26:\"Đội trưởng đội xe\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:11:\"Nhân viên\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:12;s:1:\"b\";s:4:\"test\";s:1:\"c\";s:3:\"web\";}}}',1759978882);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_sub_category`
--

DROP TABLE IF EXISTS `category_sub_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_sub_category` (
  `category_id` bigint unsigned NOT NULL,
  `sub_category_id` bigint unsigned NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`category_id`,`sub_category_id`),
  KEY `category_sub_category_sub_category_id_foreign` (`sub_category_id`),
  CONSTRAINT `category_sub_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `category_sub_category_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_sub_category`
--

LOCK TABLES `category_sub_category` WRITE;
/*!40000 ALTER TABLE `category_sub_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_sub_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `changelogs`
--

DROP TABLE IF EXISTS `changelogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `changelogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changelogs`
--

LOCK TABLES `changelogs` WRITE;
/*!40000 ALTER TABLE `changelogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `changelogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_personnel_reports`
--

DROP TABLE IF EXISTS `daily_personnel_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_personnel_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned NOT NULL,
  `report_date` date NOT NULL,
  `total_employees` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `absent_count` int NOT NULL DEFAULT '0',
  `on_leave_count` int NOT NULL DEFAULT '0',
  `sick_count` int NOT NULL DEFAULT '0',
  `annual_leave_count` int NOT NULL DEFAULT '0',
  `personal_leave_count` int NOT NULL DEFAULT '0',
  `military_leave_count` int NOT NULL DEFAULT '0',
  `other_leave_count` int NOT NULL DEFAULT '0',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_personnel_reports_department_id_report_date_unique` (`department_id`,`report_date`),
  KEY `daily_personnel_reports_report_date_index` (`report_date`),
  CONSTRAINT `daily_personnel_reports_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_personnel_reports`
--

LOCK TABLES `daily_personnel_reports` WRITE;
/*!40000 ALTER TABLE `daily_personnel_reports` DISABLE KEYS */;
INSERT INTO `daily_personnel_reports` VALUES (1,1,'2025-09-25',46,40,2,0,0,0,0,0,2,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(2,2,'2025-09-25',29,24,0,5,1,2,0,0,0,'system','system','2025-09-25 09:16:31','2025-09-25 10:09:48'),(3,3,'2025-09-25',45,30,2,1,1,2,0,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(4,4,'2025-09-25',36,19,4,7,1,2,2,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(5,5,'2025-09-25',41,16,5,7,2,0,2,1,2,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(6,6,'2025-09-25',19,17,0,2,0,1,2,0,1,'system','system','2025-09-25 09:16:31','2025-09-25 10:09:48'),(7,7,'2025-09-25',30,11,4,4,1,1,1,1,0,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(8,8,'2025-09-25',23,22,0,1,3,0,2,1,2,'system','system','2025-09-25 09:16:31','2025-09-25 10:09:48'),(9,9,'2025-09-25',22,22,3,2,1,1,0,1,0,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(10,10,'2025-09-25',27,27,0,0,3,2,2,0,1,'system','system','2025-09-25 09:16:31','2025-09-25 10:09:48'),(11,11,'2025-09-25',50,42,1,4,0,0,2,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(12,12,'2025-09-25',44,33,4,8,2,2,1,0,2,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(13,13,'2025-09-25',23,20,4,0,1,1,1,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(14,14,'2025-09-25',17,10,0,7,2,2,0,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 10:09:48'),(15,15,'2025-09-25',32,18,5,0,0,0,0,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(16,16,'2025-09-25',49,9,5,3,3,0,2,1,1,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(17,17,'2025-09-25',41,11,0,1,3,0,0,1,2,'system','system','2025-09-25 09:16:31','2025-09-25 09:16:31'),(18,18,'2025-09-25',28,24,0,4,1,0,2,0,0,'system','system','2025-09-25 09:16:31','2025-09-25 10:09:48');
/*!40000 ALTER TABLE `daily_personnel_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `level` int NOT NULL DEFAULT '0',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'department',
  `is_managerial` tinyint(1) NOT NULL DEFAULT '0',
  `permissions_override` json DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departments_parent_id_level_index` (`parent_id`,`level`),
  CONSTRAINT `departments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,NULL,0,'division',1,NULL,'BAN GIÁM ĐỐC','System','System',NULL,'2025-09-03 04:47:33','2025-09-29 02:36:03',NULL),(2,1,1,'department',1,NULL,'Phòng Kế hoạch','System','System',NULL,'2025-09-03 04:47:33','2025-09-29 02:36:04',NULL),(3,1,1,'department',1,NULL,'Ban Chính trị','System','System',NULL,'2025-09-03 04:47:35','2025-09-29 02:36:04',NULL),(4,1,1,'department',1,NULL,'Phòng Kỹ thuật','System','System',NULL,'2025-09-03 04:47:36','2025-09-29 02:36:04',NULL),(5,1,1,'department',1,NULL,'Phòng Cơ điện','System','System',NULL,'2025-09-03 04:47:37','2025-09-29 02:36:04',NULL),(6,1,1,'department',1,NULL,'Phòng Vật tư','System','System',NULL,'2025-09-03 04:47:38','2025-09-29 02:36:04',NULL),(7,1,1,'department',1,NULL,'Phòng kiểm tra chất lượng','System','System',NULL,'2025-09-03 04:47:39','2025-09-29 02:36:04',NULL),(8,1,1,'department',1,NULL,'Phòng Tài chính','System','System',NULL,'2025-09-03 04:47:39','2025-09-29 02:36:04',NULL),(9,1,1,'department',1,NULL,'Phòng Hành chính-Hậu cần','System','System',NULL,'2025-09-03 04:47:40','2025-09-29 02:36:04',NULL),(10,1,1,'department',1,NULL,'PX1: Đài điều khiển','System','System',NULL,'2025-09-03 04:47:41','2025-09-29 02:36:04',NULL),(11,1,1,'department',1,NULL,'PX2: BỆ PHÓNG','System','System',NULL,'2025-09-03 04:47:43','2025-09-29 02:36:04',NULL),(12,1,1,'department',1,NULL,'PX3: SC XE ĐẶC CHỦNG','System','System',NULL,'2025-09-03 04:47:43','2025-09-29 02:36:04',NULL),(13,1,1,'department',1,NULL,'PX4: CƠ KHÍ','System','System',NULL,'2025-09-03 04:47:44','2025-09-29 02:36:04',NULL),(14,1,1,'department',1,NULL,'PX5: KÍP, ĐẠN TÊN LỬA','System','System',NULL,'2025-09-03 04:47:45','2025-09-29 02:36:04',NULL),(15,1,1,'department',1,NULL,'PX6: XE MÁY-TNĐ','System','System',NULL,'2025-09-03 04:47:47','2025-09-29 02:36:04',NULL),(16,1,1,'department',1,NULL,'PX7:  ĐO LƯỜNG','System','System',NULL,'2025-09-03 04:47:48','2025-09-29 02:36:04',NULL),(17,1,1,'department',1,NULL,'PX8: ĐỘNG CƠ-BIẾN THẾ','System','System',NULL,'2025-09-03 04:47:48','2025-09-29 02:36:04',NULL),(18,1,1,'department',1,NULL,'PX 9: HÓA NGHIỆM PHỤC HỒI \"O, G\"','System','System',NULL,'2025-09-03 04:47:49','2025-09-29 02:36:04',NULL);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discounts`
--

DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `rate` int NOT NULL,
  `date` date NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_auto` tinyint(1) NOT NULL DEFAULT '0',
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `batch` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discounts_employee_id_foreign` (`employee_id`),
  CONSTRAINT `discounts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discounts`
--

LOCK TABLES `discounts` WRITE;
/*!40000 ALTER TABLE `discounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_leave`
--

DROP TABLE IF EXISTS `employee_leave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_leave` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `start_at` time DEFAULT NULL,
  `end_at` time DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `workflow_status` enum('pending','approved_by_approver','approved_by_director','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by_approver` bigint unsigned DEFAULT NULL,
  `approved_at_approver` timestamp NULL DEFAULT NULL,
  `approver_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approver_signature_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by_director` bigint unsigned DEFAULT NULL,
  `approved_at_director` timestamp NULL DEFAULT NULL,
  `director_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `director_signature_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `digital_signature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `signed_pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_certificate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewer_id` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `is_authorized` tinyint(1) NOT NULL DEFAULT '0',
  `is_checked` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'system',
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'system',
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_leave_approved_by_foreign` (`approved_by`),
  KEY `employee_leave_reviewer_id_foreign` (`reviewer_id`),
  KEY `employee_leave_employee_id_foreign` (`employee_id`),
  KEY `employee_leave_approved_by_approver_foreign` (`approved_by_approver`),
  KEY `employee_leave_approved_by_director_foreign` (`approved_by_director`),
  CONSTRAINT `employee_leave_approved_by_approver_foreign` FOREIGN KEY (`approved_by_approver`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_leave_approved_by_director_foreign` FOREIGN KEY (`approved_by_director`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_leave_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_leave_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `employee_leave_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_leave`
--

LOCK TABLES `employee_leave` WRITE;
/*!40000 ALTER TABLE `employee_leave` DISABLE KEYS */;
INSERT INTO `employee_leave` VALUES (1,19,'2025-10-08','2025-10-12',NULL,NULL,'Đi công tác trên Quân chủng','Hà Nộ','business','pending','pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'system','system',NULL,'2025-10-08 03:00:01','2025-10-08 03:00:01',NULL);
/*!40000 ALTER TABLE `employee_leave` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `enlist_date` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rank_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `quit_date` date DEFAULT NULL,
  `CCCD` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` tinyint DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_leave_allowed` int NOT NULL DEFAULT '0',
  `annual_leave_balance` int NOT NULL DEFAULT '12' COMMENT 'Số ngày nghỉ phép còn lại trong năm',
  `annual_leave_total` int NOT NULL DEFAULT '12' COMMENT 'Tổng số ngày nghỉ phép trong năm',
  `annual_leave_used` int NOT NULL DEFAULT '0' COMMENT 'Số ngày nghỉ phép đã sử dụng',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `profile_photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_mobile_number_unique` (`phone`),
  UNIQUE KEY `employees_national_number_unique` (`CCCD`),
  KEY `employees_position_id_foreign` (`position_id`),
  KEY `employees_department_id_foreign` (`department_id`),
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'Phạm Đức Giang','1973-09-14','09/1991','4//',1,1,NULL,'pdgiang','0900000001',1,'hh1b linh dam, hoang ma',0,12,12,0,1,NULL,'System','Phòng Kế hoạch',NULL,'2025-09-05 13:23:25','2025-09-26 00:36:08',NULL),(2,'Hà Tiến Thụy','1975-01-01','12/2003','4//',2,1,NULL,'htthuy','0900000002',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(3,'Cao Anh Hùng','1974-08-06','09/1991','4//',3,1,NULL,'cahung','0900000003',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(4,'Bùi Tân Chinh','1979-12-04','09/2003','3//',3,1,NULL,'btchinh','0900000004',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-25 08:41:00',NULL),(5,'Nguyễn Văn Bảy','1972-09-20','03/1991','3//',3,1,NULL,'nvbay','0900000005',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(6,'Phạm Ngọc Sơn','1967-10-22','08/1985','4//',4,1,NULL,'pnson','0900000006',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(7,'Nguyễn Đình Sự','1986-09-16','09/2005','2//',5,2,NULL,'ndsu','0900000007',1,'',0,12,12,0,1,NULL,'System','Phòng Kế hoạch',NULL,'2025-09-05 13:23:25','2025-09-18 08:13:43',NULL),(8,'Phạm Tiến Long','1977-05-30','09/1996','3//',6,2,NULL,'ptlong','0900000008',1,'',0,9,12,6,1,NULL,'System','Ban Giám Đốc',NULL,'2025-09-05 13:23:25','2025-09-15 15:19:19',NULL),(9,'Đặng Đình Quỳnh','1983-09-18','09/2004','2//',7,2,NULL,'ddquynh','0900000009',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(10,'Lục Viết Hợp','1983-01-01','--','1//',7,2,NULL,'lvhop','0900000010',1,'',0,9,12,6,1,NULL,'System','Ban Giám Đốc',NULL,'2025-09-05 13:23:25','2025-09-15 15:31:46',NULL),(11,'Trần Đình Tài','1968-11-20','02/1986','3//CN',8,2,NULL,'tdtai','0900000011',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(12,'Trịnh Thị Thuý Hà','1982-09-03','02/2001','2//',9,2,NULL,'tttha','0900000012',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(13,'Trịnh Văn Cương','1993-08-23','08/2011','4/',9,2,NULL,'tvcuong','0900000013',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:25','2025-09-05 13:23:25',NULL),(14,'Nguyễn T Thu Hà','1995-08-29','07/2024','2/',9,2,NULL,'nttha','0900000014',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(15,'Vũ Thành Trung','1980-02-24','02/1998','1//CN',10,2,NULL,'vttrung','0900000015',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(16,'Phạm Thị Thuý','1976-10-15','12/2003','1//CN',11,2,NULL,'ptthuy','0900000016',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(17,'Phạm Thị Trà','1975-06-02','03/1999','1//CN',12,2,NULL,'pttra','0900000017',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(18,'Vũ Thanh Hà','1987-08-12','02/2006','1//CN',13,2,NULL,'vtha','0900000018',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(19,'Nguyễn Địch Linh','1990-07-18','09/2008','1//CN',14,2,NULL,'ndlinh','0900000019',1,'',0,0,12,32,1,NULL,'System','Nguyễn Đình Sự',NULL,'2025-09-05 13:23:26','2025-09-29 02:40:32',NULL),(20,'Tạ Quốc Bảo','1997-09-06','02/2017','2/CN',15,2,NULL,'tqbao','0900000020',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(21,'Trần Ngọc Liễu','1985-05-14','10/2011','1//CN',10,2,NULL,'tnlieu','0900000021',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(22,'Nguyễn T Thu Thanh','1974-04-28','09/1991','2//CN',13,2,NULL,'nttthanh','0900000022',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(23,'Trần Hữu Ngọc','1985-12-16','02/2005','1//CN',16,2,NULL,'thngoc','0900000023',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(24,'Nguyễn Minh Thanh','1973-07-10','02/1992','2//CN',17,2,NULL,'nmthanh','0900000024',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(25,'Nông Tiến Tân','1993-09-22','09/2011','4/CN',17,2,NULL,'nttan','0900000025',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(26,'Nguyễn Trọng Toàn','1975-10-01','02/1994','1//CN',17,2,NULL,'nttoan','0900000026',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(27,'Phạm Văn Bảy','1974-05-05','03/1993','1//CN',18,2,NULL,'pvbay','0900000027',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(28,'Phạm Văn Tặng','1978-03-27','03/1999','1//CN',19,2,NULL,'pvtang','0900000028',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(29,'Bùi Thanh Quân','1979-01-15','03/1999','4/CN',19,2,NULL,'btquan','0900000029',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(30,'Vũ Hữu Hải','1980-09-30','02/2001','4/CN',19,2,NULL,'vhhai','0900000030',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(31,'Lê Ngọc Duy','1990-01-31','02/2009','3/CN',19,2,NULL,'lnduy','0900000031',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:26','2025-09-05 13:23:26',NULL),(32,'Nguyễn Văn Thắng','1973-08-01','09/2006','1//CN',19,2,NULL,'nvthang','0900000032',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(33,'Nguyễn Tiến Cường','1986-07-04','03/2013','1//CN',19,2,NULL,'ntcuong','0900000033',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(34,'Hoàng Văn Tình','1979-08-01','03/1999','1//CN',19,2,NULL,'hvtinh','0900000034',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(35,'Hoàng Anh Đức','2004-10-31','02/2023','1/CN',19,2,NULL,'haduc','0900000035',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(36,'Hoàng Bảo Chung','1995-11-23','09/2014','3/CN',19,2,NULL,'hbchung','0900000036',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(37,'Phạm Thị Thu Hương','1974-09-22','12/2003','3//',20,3,NULL,'ptthuong','0900000037',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(38,'Phan Minh Nghĩa','1984-07-20','02/2004','1//',21,3,NULL,'pmnghia','0900000038',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(39,'Nguyễn Trung Kiên','1978-01-01','09/2003','2//',22,3,NULL,'ntkien','0900000039',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(40,'Nguyễn Văn Thắng','1987-01-02','09/2006','1//',22,3,NULL,'nvthang87','0900000040',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-29 02:40:32',NULL),(41,'Đặng Trọng Chánh','1994-01-01','--','3/',22,3,NULL,'dtchanh','0900000041',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(42,'Nguyễn Minh Hiếu','1999-10-17','09/2017','3/',22,3,NULL,'nmhieu','0900000042',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(43,'Bùi Thị Nhật Lệ','1997-03-29','02/2016','1/',23,3,NULL,'btnle','0900000043',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(44,'Nguyễn Văn Ngà','1983-09-05','09/2002','2//',5,4,NULL,'nvnga','0900000044',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(45,'Nguyễn Trung Kiên','1985-10-16','09/2003','2//',24,4,NULL,'ntkien85','0900000045',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(46,'Phạm Duy Thái','1993-11-17','08/2011','4/',24,4,NULL,'pdthai','0900000046',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(47,'Lê Quý Vũ','1983-06-24','09/2001','2//',25,4,NULL,'lqvu','0900000047',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:27','2025-09-05 13:23:27',NULL),(48,'Nguyên Hữu Ngọc','1991-11-09','09/2009','1//',26,4,NULL,'nhngoc','0900000048',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(49,'Lại Hoàng Hà','1988-09-12','09/2006','1//',27,4,NULL,'lhha','0900000049',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(50,'Dương Thế Vinh','1993-07-22','08/2011','4/',27,4,NULL,'dtvinh','0900000050',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(51,'Đỗ Văn Quân','1992-01-01','--','4/',27,4,NULL,'dvquan','0900000051',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(52,'Nguyễn Văn Bình','1992-10-30','09/2011','4/',27,4,NULL,'nvbinh','0900000052',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(53,'Bùi Công Đoài','1988-09-25','09/2006','1//',27,4,NULL,'bcdoai','0900000053',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(54,'Đặng Hùng','1983-06-23','09/2003','1//',27,4,NULL,'dhung','0900000054',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(55,'Ngô Văn Hiển','1986-12-10','09/2004','2//',27,4,NULL,'nvhien','0900000055',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(56,'Đỗ Văn Linh','1994-03-25','09/2012','4/',27,4,NULL,'dvlinh','0900000056',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(57,'Hoàng Công Thành','1992-12-04','09/2009','4/',27,4,NULL,'hcthanh','0900000057',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(58,'Văn Sỹ Lực','1997-05-07','09/2015','3/',27,4,NULL,'vsluc','0900000058',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(59,'Nguyễn Trần Đức','1995-04-27','09/2013','4/',27,4,NULL,'ntduc','0900000059',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(60,'Lê Minh Vượng','1999-01-14','09/2017','3/',27,4,NULL,'lmvuong','0900000060',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(61,'Tạ Văn Hoàng','1993-01-01','--','4/',27,4,NULL,'tvhoang','0900000061',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(62,'Phạm Thị Phương','1980-10-30','11/1998','1//CN',28,4,NULL,'ptphuong','0900000062',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(63,'Lê Thị Vân','1975-06-02','02/2001','1//CN',28,4,NULL,'ltvan','0900000063',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(64,'Trần Xuân Trường','1987-06-21','03/2015','1//CN',29,4,NULL,'txtruong','0900000064',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:28','2025-09-05 13:23:28',NULL),(65,'Nguyễn Đình Tuấn','1986-11-19','02/2006','1//CN',29,4,NULL,'ndtuan','0900000065',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(66,'Trần Ngọc Dũng','1987-12-08','09/2006','1//',5,5,NULL,'tndung','0900000066',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(67,'Nguyễn Anh Tuấn','1974-02-10','02/2006','3//CN',30,5,NULL,'natuan','0900000067',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(68,'Nguyễn Xuân Dũng','1974-10-06','02/1992','1//CN',31,5,NULL,'nxdung','0900000068',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(69,'Ngô Viết Toản','1973-08-12','02/1994','CNQP',32,5,NULL,'nvtoan','0900000069',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(70,'Hoàng Minh Ánh','1973-12-22','02/1993','CNQP',32,5,NULL,'hmanh','0900000070',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(71,'Nguyễn Văn Luỹ','1984-10-25','09/2002','2//',5,6,NULL,'nvluy','0900000071',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(72,'Trần Bá Trường','1991-12-17','09/2010','4/',33,6,NULL,'tbtruong','0900000072',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(73,'Bùi Văn Phong','1985-10-04','10/2003','1//CN',15,6,NULL,'bvphong','0900000073',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(74,'Mai Văn Thuy','1967-05-28','02/1986','3//CN',34,6,NULL,'mvthuy','0900000074',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(75,'Nguyễn Văn Cường','1975-05-01','02/1998','2//CN',34,6,NULL,'nvcuong','0900000075',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(76,'Phan Thị Thu Hường','1983-09-21','09/2009','2//CN',15,6,NULL,'ptthuong83','0900000076',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(77,'Trịnh Thu Huyền','1982-11-27','12/2003','1//CN',34,6,NULL,'tthuyen','0900000077',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(78,'Đặng Thị Huệ','1993-05-08','03/2015','4/CN',15,6,NULL,'dthue','0900000078',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:29','2025-09-05 13:23:29',NULL),(79,'Đinh Tiến Dũng','1976-07-13','03/1997','1//CN',35,6,NULL,'dtdung','0900000079',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(80,'Đoàn Thị Sự','1971-12-12','03/1999','1//CN',35,6,NULL,'dtsu','0900000080',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(81,'Phạm Thị Thu Hà','1976-12-15','11/1998','1//CN',35,6,NULL,'pttha','0900000081',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(82,'Đinh Thị Tâm','1979-05-05','12/2003','4/CN',35,6,NULL,'dttam','0900000082',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(83,'Nguyễn Thị Hiền','1992-08-02','12/2020','2/CN',15,6,NULL,'nthien','0900000083',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(84,'Đinh Quang Điềm','1985-11-02','09/2003','2//',5,7,NULL,'dqdiem','0900000084',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(85,'Huỳnh Thái Tân','1967-06-16','03/1986','3//CN',36,7,NULL,'httan','0900000085',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(86,'Mai Trường Giang','1974-01-02','02/1992','3//CN',37,7,NULL,'mtgiang','0900000086',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(87,'Nguyễn Việt Dũng','1976-06-26','02/1995','2//CN',37,7,NULL,'nvdung','0900000087',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(88,'Nguyễn Xuân Quý','1977-02-12','03/1996','1//CN',36,7,NULL,'nxquy','0900000088',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(89,'Nguyễn Xuân Bách','1975-05-31','02/1994','2//CN',38,7,NULL,'nxbach','0900000089',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(90,'Nguyễn Ngọc Quý','1983-10-06','02/2003','1//CN',39,7,NULL,'nnquy','0900000090',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(91,'Thái Thị Hà','1981-07-08','09/2010','2//CN',40,7,NULL,'ttha','0900000091',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(92,'Nguyễn Văn Bách','1974-07-15','02/1992','1//CN',41,7,NULL,'nvbach','0900000092',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(93,'Nguyễn Văn Cường','1979-07-17','02/1986','1//',5,8,NULL,'nvcuong79','0900000093',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(94,'Nguyễn Văn Phú','1988-08-14','03/2007','4/CN',42,8,NULL,'nvphu','0900000094',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(95,'Phạm Thị Kiều Ân','1982-06-13','12/2003','1//CN',43,8,NULL,'ptkan','0900000095',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:30','2025-09-05 13:23:30',NULL),(96,'Nguyễn Thị Thuý','1987-07-05','03/2013','1//CN',42,8,NULL,'ntthuy','0900000096',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(97,'Dương Thị Mơ','1990-10-19','03/2015','1//CN',42,8,NULL,'dtmo','0900000097',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(98,'Nguyễn Thị Hằng','1995-06-24','02/2016','4/CN',42,8,NULL,'nthang','0900000098',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(99,'Phạm T Thu Hương','1983-07-16','12/2003','1//CN',43,8,NULL,'ptthuong832','0900000099',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(100,'Chử Quang Anh','1980-02-10','03/1999','2//',5,9,NULL,'cqanh','0900000100',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(101,'Đào Văn Tiến','1973-08-31','09/1991','3//',44,9,NULL,'dvtien','0900000101',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(102,'Trần Đình Tám','1979-07-30','03/1999','2//CN',45,9,NULL,'tdtam','0900000102',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(103,'Nguyễn Quỳnh Trang','1981-04-02','02/2001','2//CN',46,9,NULL,'nqtrang','0900000103',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(104,'Lê Mạnh Hà','1990-08-13','02/2012','4/CN',47,9,NULL,'lmha','0900000104',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(105,'Nguyễn Thị Anh','1990-08-24','03/2015','2/CN',48,9,NULL,'ntanh','0900000105',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(106,'Đỗ Đức Toàn','1984-10-21','03/2013','1//CN',49,9,NULL,'ddtoan','0900000106',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(107,'Triệu T Hoài Phương','1987-11-13','02/1994','2/CN',49,9,NULL,'tthphuong','0900000107',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(108,'Trịnh Bá Thuận','1966-08-16','09/1983','1//CN',45,9,NULL,'tbthuan','0900000108',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(109,'Đặng Quốc Sỹ','1980-01-20','02/2001','4/CN',45,9,NULL,'dqsy','0900000109',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(110,'Phạm Lan Phương','1994-05-14','09/2016','2/CN',49,9,NULL,'plphuong','0900000110',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(111,'Giang Chí Dũng','1998-01-18','12/2022','2/CN',45,9,NULL,'gcdung','0900000111',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(112,'Nguyễn Thị Huyền','1990-10-28','03/2013','CNQP',49,9,NULL,'nthuyen','0900000112',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:31','2025-09-05 13:23:31',NULL),(113,'Nguyễn T Phương Chi','1981-06-17','03/2015','1//CN',50,9,NULL,'ntpchi','0900000113',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(114,'Phạm Thị Vân Anh','1992-03-28','02/2016','3/CN',50,9,NULL,'ptvanh','0900000114',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(115,'Trần Thị Tuyến','1989-06-20','03/2014','4/CN',50,9,NULL,'tttuyen','0900000115',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(116,'Bùi Đức Anh','1993-10-31','03/2015','3/CN',50,9,NULL,'bdanh','0900000116',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(117,'Vũ Thị Kim Ngân','1989-08-27','03/1999','2/CN\n3/CN',50,9,NULL,'vtkngan','0900000117',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(118,'Nguyễn Thu Huyền','1982-09-20','02/2001','3/CN',51,9,NULL,'nthuyen82','0900000118',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(119,'Trần Trọng Đại','1985-12-04','08/2004','1//',52,10,NULL,'ttdai','0900000119',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(120,'Lưu Hoàng Văn','1992-05-10','09/2009','4/',53,10,NULL,'lhvan','0900000120',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(121,'Đồng Xuân Dũng','1999-01-01','--','3/',33,10,NULL,'dxdung','0900000121',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(122,'Trương Thanh Tú','2001-01-01','--','2/',33,10,NULL,'tttu','0900000122',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(123,'Dương T Phương Loan','1977-08-29','09/1983','1//CN',54,10,NULL,'dtploan','0900000123',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(124,'Nguyễn Hữu Thanh','1983-09-02','10/2002','1//CN',55,10,NULL,'nhthanh','0900000124',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(125,'Nguyễn Thị Tuyền','1983-02-13','02/2012','2/CN',56,10,NULL,'nttuyen','0900000125',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(126,'Lê Thị Thuý Hằng','1985-03-24','12/2022','3/CN',56,10,NULL,'ltthang','0900000126',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(127,'Nguyễn T Thuý Bình','1984-10-30','02/1995','4/CN',57,10,NULL,'nttbinh','0900000127',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(128,'Đặng Thị Kim Dung','1981-06-05','09/2016','1/CN',56,10,NULL,'dtkdung','0900000128',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(129,'Dương Thị Thân Thương','1989-06-29','03/2014','CNQP',56,10,NULL,'dttthuong','0900000129',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:32','2025-09-05 13:23:32',NULL),(130,'Phạm Thị Trang Nhung','1979-09-27','02/2012','CNQP',56,10,NULL,'pttnhung','0900000130',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(131,'Trần Thị Chuyên','1990-10-20','03/2014','4/CN',56,10,NULL,'ttchuyen','0900000131',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(132,'Phạm Khắc Hùng','1985-10-12','02/2004','1//CN',58,10,NULL,'pkhung','0900000132',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(133,'Nguyễn Mạnh Hùng','1974-05-01','02/1995','2//CN',59,10,NULL,'nmhung','0900000133',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(134,'Vũ Mạnh Tú','1985-06-26','02/2004','1//CN',59,10,NULL,'vmtu','0900000134',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(135,'Bùi Anh Tuấn','1983-03-11','02/2004','1//CN',59,10,NULL,'batuan','0900000135',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(136,'Nguyễn Văn Thụ','1988-11-03','02/2012','4/CN',59,10,NULL,'nvthu','0900000136',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(137,'Đặng Văn Phố','1974-01-01','02/1992','2//CN',59,10,NULL,'dvpho','0900000137',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(138,'Nguyễn Xuân Trường','1982-02-25','02/2001','1//CN',59,10,NULL,'nxtruong','0900000138',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(139,'Hà Thanh Trung','1973-10-20','02/1992','3//CN',60,10,NULL,'httrung','0900000139',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(140,'Nguyễn Văn Huyên','1982-08-20','03/2002','1//CN',61,10,NULL,'nvhuyen','0900000140',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(141,'Nguyễn Gia Mạnh','1985-06-25','02/2005','1//CN',61,10,NULL,'ngmanh','0900000141',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(142,'Đỗ Hồng Sơn','2001-12-27','02/2020','1/CN',61,10,NULL,'dhson','0900000142',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(143,'Nguyễn Tuấn Hiệp','1974-06-04','10/1995','1//CN',61,10,NULL,'nthiep','0900000143',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(144,'Vũ Mạnh Cương','1978-05-09','12/2003','4/CN',62,10,NULL,'vmcuong','0900000144',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(145,'Lê Trọng Quỳnh','1978-12-16','12/2003','1//CN',63,10,NULL,'ltquynh','0900000145',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(146,'Đặng Viết Công','1983-09-12','12/2003','4/CN',64,10,NULL,'dvcong','0900000146',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(147,'Nguyễn Tiến Dũng','1996-09-22','03/2015','2/CN',64,10,NULL,'ntdung','0900000147',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:33','2025-09-05 13:23:33',NULL),(148,'Nguyễn Hồng Anh','1981-05-04','09/1999','2//',52,11,NULL,'nhanh','0900000148',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(149,'Trần Đức Tấn','1987-07-11','09/2005','1//',65,11,NULL,'tdtan','0900000149',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(150,'Hoàng Anh Dũng','2000-08-21','09/2018','2/',33,11,NULL,'hadung','0900000150',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(151,'Nguyễn Mai Hương','1983-09-01','12/2003','1//CN',54,11,NULL,'nmhuong','0900000151',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(152,'Hoàng Văn Tiến','1978-05-16','02/1998','1//CN',66,11,NULL,'hvtien','0900000152',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(153,'Nguyễn Xuân Thụ','1989-03-23','10/2007','4/CN',67,11,NULL,'nxthu','0900000153',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(154,'Hà Nguyễn Tuấn Anh','1998-12-02','08/2024','CNQP',67,11,NULL,'hntanh','0900000154',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(155,'Đinh Viết Trường','1992-08-28','09/2010','4/CN',67,11,NULL,'dvtruong','0900000155',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(156,'Phan Thanh Quang','1981-05-23','02/2000','1//CN',68,11,NULL,'ptquang','0900000156',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(157,'Nguyễn Tiến Nam','1981-10-09','02/2000','4/CN',69,11,NULL,'ntnam','0900000157',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(158,'Nguyễn Huy Thắng','1983-01-01','02/2004','3/CN',69,11,NULL,'nhthang','0900000158',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:34','2025-09-05 13:23:34',NULL),(159,'Trần Hồng Công','1989-10-21','09/2016','2/CN',69,11,NULL,'thcong','0900000159',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(160,'An Văn Trực','1983-07-09','09/2001','3//',52,12,NULL,'avtruc','0900000160',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(161,'Phạm Quỳnh Trang','1987-10-28','02/2012','3/CN',70,12,NULL,'pqtrang','0900000161',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(162,'Ngô Thị Sơn','1970-09-26','06/1998','2//CN',54,12,NULL,'ntson','0900000162',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(163,'Nguyễn Anh Tuấn','1987-03-12','02/2006','1//CN',71,12,NULL,'natuan87','0900000163',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(164,'Trần Ngọc Phú','1983-12-18','03/2002','1//CN',72,12,NULL,'tnphu','0900000164',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(165,'Nguyễn Tuấn Long','1983-04-15','10/1995','4/CN',73,12,NULL,'ntlong','0900000165',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(166,'Nguyễn Đức Anh','1993-06-08','12/2022','1/CN',73,12,NULL,'ndanh','0900000166',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(167,'Nguyễn Phú Hùng','1995-01-06','12/2023','2/CN',73,12,NULL,'nphung','0900000167',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(168,'Nguyễn Anh Đạt','1995-06-25','03/2014','3/CN',73,12,NULL,'nadat','0900000168',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(169,'Trịnh Trọng Cường','1975-01-15','02/1994','1//CN',74,12,NULL,'ttcuong','0900000169',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(170,'Cấn Xuân Khánh','1991-02-14','09/2010','4/',52,13,NULL,'cxkhanh','0900000170',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(171,'Vũ Thị Hiền','1988-02-08','02/2012','4/CN',54,13,NULL,'vthien','0900000171',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(172,'Phan Văn Đăng','1974-05-05','02/1995','CNQP',75,13,NULL,'pvdang','0900000172',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(173,'Bùi Mạnh Hùng','1982-10-30','02/2016','1//CN',76,13,NULL,'bmhung','0900000173',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(174,'Trần Văn Thành','1974-08-16','02/1994','1//CN',77,13,NULL,'tvthanh','0900000174',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:35','2025-09-05 13:23:35',NULL),(175,'Vũ Trịnh Giang','1992-09-02','02/2016','2/CN',77,13,NULL,'vtgiang','0900000175',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(176,'Nguyễn Tuấn Long','1976-07-13','02/1994','1//CN',78,13,NULL,'ntlong76','0900000176',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(177,'Vũ Huy Phương','1986-09-25','03/2014','1//CN',79,13,NULL,'vhphuong','0900000177',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(178,'Vũ Hải Dương','1991-05-16','02/2016','4/CN',79,13,NULL,'vhduong','0900000178',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(179,'Trịnh Thành Chung','1986-08-01','09/2016','2/CN',79,13,NULL,'ttchung','0900000179',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(180,'Nguyễn Diên Quang','1981-11-20','03/2002','1//CN',80,13,NULL,'ndquang','0900000180',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(181,'Mai Thị Phượng','1982-08-03','02/2012','2/CN',81,13,NULL,'mtphuong','0900000181',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(182,'Bùi Thị Hồng Thu','1988-10-03','02/2016','2/CN',81,13,NULL,'bththu','0900000182',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(183,'Đặng Văn Tường','1970-11-01','02/1992','1//CN',82,13,NULL,'dvtuong','0900000183',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(184,'Trần Hồng Tú','1981-01-11','03/1999','4/CN',83,13,NULL,'thtu','0900000184',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(185,'Lê Trọng Quý','1989-12-07','09/2016','1/CN',84,13,NULL,'ltquy','0900000185',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(186,'Đỗ Trung Kiên','1994-01-19','12/2023','1/CN',85,13,NULL,'dtkien','0900000186',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(187,'Chu Lê Tuấn Anh','1998-11-16','08/2024','CNQP',85,13,NULL,'cltanh','0900000187',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(188,'Hoàng Văn Thắng','1995-10-02','02/2014','3/CN',85,13,NULL,'hvthang','0900000188',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(189,'Nguyễn Thành Long','1977-01-02','10/1995','2//',52,14,NULL,'ntlong77','0900000189',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(190,'Bùi Trường Giang','1987-04-23','09/2005','2//',65,14,NULL,'btgiang','0900000190',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(191,'Nguyễn Hải Sơn','1990-08-10','09/2008','4/',27,14,NULL,'nhson','0900000191',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:36','2025-09-05 13:23:36',NULL),(192,'Nguyễn T Lan Anh','1979-09-07','12/2003','1//CN',54,14,NULL,'ntlanh','0900000192',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(193,'Tống Cao Cường','1986-11-20','10/2004','1//CN',86,14,NULL,'tccuong','0900000193',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(194,'Nguyễn Hữu Tâm','1983-07-22','12/2003','4/CN',87,14,NULL,'nhtam','0900000194',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(195,'Hồ Thị Hiền','1986-06-05','02/2012','CNQP',87,14,NULL,'hthien','0900000195',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(196,'Nguyễn T Phương Thảo','1993-06-22','02/1993','CNQP',87,14,NULL,'ntpthao','0900000196',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(197,'Bùi Văn Huy','1983-10-15','02/2003','1//CN',88,14,NULL,'bvhuy','0900000197',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(198,'Phan Văn Sáng','1978-12-21','12/2003','4/CN',89,14,NULL,'pvsang','0900000198',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(199,'Hữu Thị Thuý','1981-05-17','12/2003','4/CN',89,14,NULL,'htthuy81','0900000199',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(200,'Hà Minh Nho','1974-08-20','03/1997','1//CN',90,14,NULL,'hmnho','0900000200',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(201,'Nguyễn Văn Đồng','1983-09-20','03/2002','1//CN',91,14,NULL,'nvdong','0900000201',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(202,'Trần T Kim Oanh','1982-09-12','12/2003','4/CN',91,14,NULL,'ttkoanh','0900000202',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(203,'Bùi Thị Huệ','1991-10-22','02/2016','2/CN',91,14,NULL,'bthue','0900000203',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(204,'Bùi Đức Cảnh','1991-12-24','09/2009','4/CN',91,14,NULL,'bdcanh','0900000204',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(205,'Trần Đức Minh','1984-08-26','10/2004','1//CN',92,14,NULL,'tdminh','0900000205',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(206,'Vũ Đình Tùng','1986-05-21','10/2005','1//CN',93,14,NULL,'vdtung','0900000206',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(207,'Trần Đình Tùng','2005-12-30','12/2023','1/CN',93,14,NULL,'tdtung','0900000207',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:37','2025-09-05 13:23:37',NULL),(208,'Đào Thị Thu Huyền','1985-07-12','02/2012','2/CN',93,14,NULL,'dtthuyen','0900000208',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(209,'Nguyễn Văn Quyết','1988-08-27','02/2012','2/CN',94,14,NULL,'nvquyet','0900000209',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(210,'Nguyễn Thị Thu','1991-09-01','08/2024','CNQP',95,14,NULL,'ntthu','0900000210',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(211,'Trần T Ngọc Anh','1979-08-29','04/2008','3/CN',95,14,NULL,'ttnanh','0900000211',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(212,'Đỗ Văn Hưng','1984-09-03','12/2006','2//',52,15,NULL,'dvhung','0900000212',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(213,'Nguyễn Thị Tân Miền','1980-01-06','02/1998','2//CN',54,15,NULL,'nttmien','0900000213',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(214,'Nguyễn Ngọc Khánh','1983-09-04','02/2003','1//CN',96,15,NULL,'nnkhanh','0900000214',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(215,'Nguyễn Dự Đáng','1986-09-24','03/2008','4/CN',97,15,NULL,'nddang','0900000215',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(216,'Lê Văn Hội','1992-03-21','09/2016','2/CN',97,15,NULL,'lvhoi','0900000216',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(217,'Nguyễn Kim Biển','1984-01-29','02/2003','1//CN',98,15,NULL,'nkbien','0900000217',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(218,'Trần Mạnh Kiều','1982-02-03','02/2001','1//CN',99,15,NULL,'tmkieu','0900000218',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(219,'Dương Bá Quyền','1990-12-12','02/2012','1//CN',100,15,NULL,'dbquyen','0900000219',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(220,'Nguyễn Thị Tươi','1988-07-21','03/2013','2/CN',101,15,NULL,'nttuoi','0900000220',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(221,'Bùi T Khánh Thuỳ','1990-12-28','03/2014','2/CN',101,15,NULL,'btkthuy','0900000221',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(222,'Hà Chí Quang','1973-07-20','02/1993','2//CN',102,15,NULL,'hcquang','0900000222',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(223,'Võ Văn Tới','1985-11-09','03/2007','4/CN',103,15,NULL,'vvtoi','0900000223',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(224,'Nguyễn Quang Hùng','1998-08-16','08/2024','CNQP',103,15,NULL,'nqhung','0900000224',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:38','2025-09-05 13:23:38',NULL),(225,'Nguyễn Quyết Tiến','1994-06-09','09/2016','2/CN',103,15,NULL,'nqtien','0900000225',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(226,'Tạ Hồng Đăng','1985-05-07','09/2003','2//',52,16,NULL,'thdang','0900000226',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(227,'Nguyễn Thị Hoàn','1983-04-26','02/2012','4/CN',54,16,NULL,'nthoan','0900000227',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(228,'Nguyễn Sơn Đông','1980-10-13','02/2000','1//CN',104,16,NULL,'nsdong','0900000228',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(229,'Nguyễn Hải Tiến','1984-05-10','02/2004','1//CN',105,16,NULL,'nhtien','0900000229',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(230,'Trần Việt Trung','1990-12-28','12/2022','3/CN',105,16,NULL,'tvtrung','0900000230',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(231,'Trần Thị Việt Hồng','1982-08-08','12/2003','1//CN',106,16,NULL,'ttvhong','0900000231',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(232,'Vũ Ngọc Quỳnh','1983-10-07','10/2002','1//CN',70,16,NULL,'vnquynh','0900000232',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(233,'Thái Thị Âu','1987-05-29','02/2012','2/CN',70,16,NULL,'ttau','0900000233',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(234,'Nguyễn Thuỳ Linh','1984-10-20','02/2012','1//CN',70,16,NULL,'ntlinh','0900000234',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(235,'Nguyễn Thị Mai','1994-07-18','12/2022','CNQP',70,16,NULL,'ntmai','0900000235',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(236,'Hoàng Văn Thành','1983-06-08','09/2001','2//',52,17,NULL,'hvthanh','0900000236',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(237,'Vũ Thị Liên','1982-12-15','04/2005','1//CN',54,17,NULL,'vtlien','0900000237',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(238,'Khuất Duy Mạnh','1982-08-30','02/2003','1//CN',107,17,NULL,'kdmanh','0900000238',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:39','2025-09-05 13:23:39',NULL),(239,'Nguyễn Thị Duyên','1992-02-16','12/2023','2/CN',108,17,NULL,'ntduyen','0900000239',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(240,'Nông Thị Thuý','1987-02-12','03/2013','2/CN',108,17,NULL,'ntthuy87','0900000240',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(241,'Đinh Thị Thành','1988-01-15','03/2015','2/CN',108,17,NULL,'dtthanh','0900000241',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(242,'Lương T Thanh Loan','1986-11-13','08/2024','CNQP',108,17,NULL,'lttloan','0900000242',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(243,'Phan Thanh Trường','1975-08-18','01/1997','1//CN',109,17,NULL,'pttruong','0900000243',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(244,'Mai Hồng Sơn','1971-01-02','02/1992','1//CN',110,17,NULL,'mhson','0900000244',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(245,'Nguyễn Thái Bình','1981-03-11','02/2000','4/CN',111,17,NULL,'ntbinh','0900000245',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(246,'Nguyễn Thanh Bình','1972-07-20','02/1995','CNQP',111,17,NULL,'ntbinh72','0900000246',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(247,'Trần Ngọc Quang','1983-09-09','03/2002','4/CN',111,17,NULL,'tnquang','0900000247',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(248,'Phạm Trường Giang','1976-03-08','10/1994','3//',52,18,NULL,'ptgiang','0900000248',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(249,'Nguyễn Thị Thảo','1975-10-17','02/1993','2//CN',54,18,NULL,'ntthao','0900000249',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(250,'Bùi Văn Khởi','1965-08-13','03/1999','CNQP',112,18,NULL,'bvkhoi','0900000250',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL),(251,'Cao Văn Tuyển','1985-04-24','10/2003','1//CN',112,18,NULL,'cvtuyen','0900000251',1,'',0,12,12,0,1,NULL,'System','System',NULL,'2025-09-05 13:23:40','2025-09-05 13:23:40',NULL);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
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
-- Table structure for table `fingerprints`
--

DROP TABLE IF EXISTS `fingerprints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fingerprints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `log` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `is_checked` tinyint(1) NOT NULL DEFAULT '0',
  `excuse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fingerprints_employee_id_foreign` (`employee_id`),
  CONSTRAINT `fingerprints_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fingerprints`
--

LOCK TABLES `fingerprints` WRITE;
/*!40000 ALTER TABLE `fingerprints` DISABLE KEYS */;
/*!40000 ALTER TABLE `fingerprints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holidays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `note` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ext` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `total` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imports`
--

LOCK TABLES `imports` WRITE;
/*!40000 ALTER TABLE `imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `imports` ENABLE KEYS */;
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
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `progress` int unsigned NOT NULL DEFAULT '0',
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `leave_type` enum('business','attendance','study','leave','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_instantly` tinyint(1) NOT NULL,
  `is_accumulative` tinyint(1) NOT NULL,
  `discount_rate` int NOT NULL,
  `days_limit` int NOT NULL,
  `minutes_limit` int NOT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
INSERT INTO `leaves` VALUES (1,'Nghỉ Ốm',0,0,0,30,0,'Nghỉ ốm theo quy định','System','System',NULL,'2025-09-03 12:58:46','2025-09-03 12:58:46',NULL),(2,'Nghỉ Việc cá nhân',0,0,0,15,0,'Nghỉ việc cá nhân theo quy định','System','System',NULL,'2025-09-03 12:58:46','2025-09-03 12:58:46',NULL);
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned DEFAULT NULL,
  `text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_employee_id_foreign` (`employee_id`),
  CONSTRAINT `messages_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (64,'2013_11_01_131410_create_contracts_table',1),(65,'2013_11_01_132154_create_employees_table',1),(66,'2014_10_12_000000_create_users_table',1),(67,'2014_10_12_100000_create_password_reset_tokens_table',1),(68,'2014_10_12_100000_create_password_resets_table',1),(69,'2014_10_12_200000_add_two_factor_columns_to_users_table',1),(70,'2019_08_19_000000_create_failed_jobs_table',1),(71,'2019_12_14_000001_create_personal_access_tokens_table',1),(72,'2023_10_19_112718_create_sessions_table',1),(73,'2023_11_02_105426_create_settings_table',1),(74,'2023_11_02_114106_create_leaves_table',1),(75,'2023_11_02_114945_create_positions_table',1),(76,'2023_11_02_120331_create_holidays_table',1),(77,'2023_11_02_121209_create_centers_table',1),(78,'2023_11_02_122959_create_center_holiday_table',1),(79,'2023_11_02_123831_create_fingerprints_table',1),(80,'2023_11_02_124645_create_discounts_table',1),(81,'2023_11_02_133032_create_departments_table',1),(82,'2023_11_02_133459_create_employee_leave_table',1),(83,'2023_11_02_140207_create_timelines_table',1),(84,'2023_11_10_162228_create_messages_table',1),(85,'2023_11_26_092207_create_notifications_table',1),(86,'2023_12_01_195938_create_jobs_table',1),(87,'2023_12_01_205218_create_imports_table',1),(88,'2024_04_16_105426_create_changelogs_table',1),(89,'2024_04_16_111956_create_permission_tables',1),(90,'2024_04_30_115612_create_assets_table',1),(91,'2024_05_05_134550_create_categories_table',1),(92,'2024_05_05_134557_create_sub_categories_table',1),(93,'2024_05_06_113204_create_category_sub_category_table',1),(94,'2024_07_10_100000_create_transitions_table',1),(95,'2025_05_20_092754_create_message_bulks_table',1),(97,'2025_09_03_053455_add_is_sequent_to_timelines_table',2),(98,'2025_09_03_054400_restructure_employees_table',3),(99,'2025_09_03_120052_update_employee_leaves_table_for_approval_workflow',4),(100,'2025_09_04_050454_add_signature_path_to_users_table',5),(101,'2025_09_04_112514_add_cccd_birth_enlist_to_users_table',6),(102,'2025_09_04_125256_add_leave_balance_to_employees_table',7),(103,'2025_09_04_133132_add_leave_tracking_fields_to_employees_table',8),(104,'2025_09_04_032652_rename_email_to_username_in_users_table',9),(105,'2025_09_04_062951_create_vehicles_table',9),(106,'2025_09_04_062953_create_vehicle_registrations_table',9),(107,'2025_09_04_122957_change_employee_id_to_varchar_in_employee_leave_table',9),(108,'2025_09_08_221932_add_signed_pdf_path_to_employee_leave_table',10),(109,'2025_09_08_224228_change_digital_signature_to_longtext_in_employee_leave_table',10),(110,'2025_09_08_232845_add_template_pdf_path_to_employee_leave_table',10),(111,'2025_09_15_163257_update_vehicle_registrations_for_new_workflow',11),(112,'2025_09_25_080606_create_cache_table_manual',12),(113,'2025_09_25_084400_add_department_id_to_users_table',13),(114,'2025_09_25_085550_update_employee_leave_table_add_location_and_leave_type',14),(115,'2025_09_25_090601_fix_employee_leave_created_by_default_value',15),(116,'2025_09_25_035848_create_daily_personnel_reports_table',16),(118,'2025_09_26_004920_add_approval_workflow_to_employee_leave_table',17),(119,'2025_09_26_011329_add_signed_pdf_path_to_employee_leave_table',18),(120,'2025_09_29_023041_add_department_hierarchy_fields',19),(121,'2025_09_29_023058_add_user_hierarchy_fields',20),(122,'2025_09_30_001730_add_datetime_fields_to_vehicle_registrations_table',21),(123,'2025_09_30_002002_make_old_datetime_fields_nullable_in_vehicle_registrations',22),(124,'2025_09_30_013312_add_signed_pdf_path_to_vehicle_registrations_table',23),(125,'2025_09_30_092707_add_certificate_fields_to_users_table',24),(126,'2025_10_01_000001_create_approval_histories_table',25),(127,'2025_10_01_065657_update_workflow_status_enum_in_vehicle_registrations_table',26),(128,'2025_10_02_073626_create_record_types_table',27),(129,'2025_10_02_073631_create_record_entries_table',27),(132,'2025_10_02_085611_create_salary_up_records_table',28),(133,'2025_10_07_081712_create_records_so_dieu_dong_table',29);
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
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `model_has_permissions` VALUES (1,'App\\Models\\User',40),(54,'App\\Models\\User',40),(55,'App\\Models\\User',40),(56,'App\\Models\\User',40),(57,'App\\Models\\User',40),(58,'App\\Models\\User',40),(59,'App\\Models\\User',40),(76,'App\\Models\\User',40),(77,'App\\Models\\User',40),(78,'App\\Models\\User',40);
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
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(4,'App\\Models\\User',3),(3,'App\\Models\\User',4),(3,'App\\Models\\User',5),(3,'App\\Models\\User',6),(3,'App\\Models\\User',7),(3,'App\\Models\\User',8),(3,'App\\Models\\User',9),(3,'App\\Models\\User',10),(3,'App\\Models\\User',11),(3,'App\\Models\\User',12),(3,'App\\Models\\User',13),(3,'App\\Models\\User',14),(3,'App\\Models\\User',15),(3,'App\\Models\\User',16),(3,'App\\Models\\User',17),(3,'App\\Models\\User',18),(3,'App\\Models\\User',19),(3,'App\\Models\\User',20),(2,'App\\Models\\User',21),(2,'App\\Models\\User',22),(2,'App\\Models\\User',23),(2,'App\\Models\\User',24),(2,'App\\Models\\User',25),(2,'App\\Models\\User',26),(2,'App\\Models\\User',27),(3,'App\\Models\\User',28),(5,'App\\Models\\User',29),(5,'App\\Models\\User',30),(5,'App\\Models\\User',31),(5,'App\\Models\\User',32),(5,'App\\Models\\User',33),(5,'App\\Models\\User',34),(5,'App\\Models\\User',35),(5,'App\\Models\\User',36),(5,'App\\Models\\User',37),(5,'App\\Models\\User',38),(5,'App\\Models\\User',39),(12,'App\\Models\\User',40),(5,'App\\Models\\User',41),(5,'App\\Models\\User',42),(5,'App\\Models\\User',43),(5,'App\\Models\\User',44),(5,'App\\Models\\User',45),(5,'App\\Models\\User',46),(5,'App\\Models\\User',47),(5,'App\\Models\\User',48),(5,'App\\Models\\User',49),(5,'App\\Models\\User',50),(5,'App\\Models\\User',51),(5,'App\\Models\\User',52),(5,'App\\Models\\User',53),(5,'App\\Models\\User',54),(5,'App\\Models\\User',55),(5,'App\\Models\\User',56),(5,'App\\Models\\User',57),(5,'App\\Models\\User',58),(5,'App\\Models\\User',59),(5,'App\\Models\\User',60),(5,'App\\Models\\User',61),(5,'App\\Models\\User',62),(5,'App\\Models\\User',63),(5,'App\\Models\\User',64),(5,'App\\Models\\User',65),(5,'App\\Models\\User',66),(5,'App\\Models\\User',67),(5,'App\\Models\\User',68),(5,'App\\Models\\User',69),(5,'App\\Models\\User',70),(5,'App\\Models\\User',71),(5,'App\\Models\\User',72),(5,'App\\Models\\User',73),(5,'App\\Models\\User',74),(5,'App\\Models\\User',75),(5,'App\\Models\\User',76),(5,'App\\Models\\User',77),(5,'App\\Models\\User',78),(5,'App\\Models\\User',79),(5,'App\\Models\\User',80),(5,'App\\Models\\User',81),(5,'App\\Models\\User',82),(5,'App\\Models\\User',83),(5,'App\\Models\\User',84),(5,'App\\Models\\User',85),(5,'App\\Models\\User',86),(5,'App\\Models\\User',87),(5,'App\\Models\\User',88),(5,'App\\Models\\User',89),(5,'App\\Models\\User',90),(5,'App\\Models\\User',91),(5,'App\\Models\\User',92),(5,'App\\Models\\User',93),(5,'App\\Models\\User',94),(5,'App\\Models\\User',95),(5,'App\\Models\\User',96),(5,'App\\Models\\User',97),(5,'App\\Models\\User',98),(5,'App\\Models\\User',99),(5,'App\\Models\\User',100),(5,'App\\Models\\User',101),(5,'App\\Models\\User',102),(5,'App\\Models\\User',103),(5,'App\\Models\\User',104),(5,'App\\Models\\User',105),(5,'App\\Models\\User',106),(5,'App\\Models\\User',107),(5,'App\\Models\\User',108),(5,'App\\Models\\User',109),(5,'App\\Models\\User',110),(5,'App\\Models\\User',111),(5,'App\\Models\\User',112),(5,'App\\Models\\User',113),(5,'App\\Models\\User',114),(5,'App\\Models\\User',115),(5,'App\\Models\\User',116),(5,'App\\Models\\User',117),(5,'App\\Models\\User',118),(5,'App\\Models\\User',119),(5,'App\\Models\\User',120),(5,'App\\Models\\User',121),(5,'App\\Models\\User',122),(5,'App\\Models\\User',123),(5,'App\\Models\\User',124),(5,'App\\Models\\User',125),(5,'App\\Models\\User',126),(5,'App\\Models\\User',127),(5,'App\\Models\\User',128),(5,'App\\Models\\User',129),(5,'App\\Models\\User',130),(5,'App\\Models\\User',131),(5,'App\\Models\\User',132),(5,'App\\Models\\User',133),(5,'App\\Models\\User',134),(5,'App\\Models\\User',135),(5,'App\\Models\\User',136),(5,'App\\Models\\User',137),(5,'App\\Models\\User',138),(5,'App\\Models\\User',139),(5,'App\\Models\\User',140),(5,'App\\Models\\User',141),(5,'App\\Models\\User',142),(5,'App\\Models\\User',143),(5,'App\\Models\\User',144),(5,'App\\Models\\User',145),(5,'App\\Models\\User',146),(5,'App\\Models\\User',147),(5,'App\\Models\\User',148),(5,'App\\Models\\User',149),(5,'App\\Models\\User',150),(5,'App\\Models\\User',151),(5,'App\\Models\\User',152),(5,'App\\Models\\User',153),(5,'App\\Models\\User',154),(5,'App\\Models\\User',155),(5,'App\\Models\\User',156),(5,'App\\Models\\User',157),(5,'App\\Models\\User',158),(5,'App\\Models\\User',159),(5,'App\\Models\\User',160),(5,'App\\Models\\User',161),(5,'App\\Models\\User',162),(5,'App\\Models\\User',163),(5,'App\\Models\\User',164),(5,'App\\Models\\User',165),(5,'App\\Models\\User',166),(5,'App\\Models\\User',167),(5,'App\\Models\\User',168),(5,'App\\Models\\User',169),(5,'App\\Models\\User',170),(5,'App\\Models\\User',171),(5,'App\\Models\\User',172),(5,'App\\Models\\User',173),(5,'App\\Models\\User',174),(5,'App\\Models\\User',175),(5,'App\\Models\\User',176),(5,'App\\Models\\User',177),(5,'App\\Models\\User',178),(5,'App\\Models\\User',179),(5,'App\\Models\\User',180),(5,'App\\Models\\User',181),(5,'App\\Models\\User',182),(5,'App\\Models\\User',183),(5,'App\\Models\\User',184),(5,'App\\Models\\User',185),(5,'App\\Models\\User',186),(5,'App\\Models\\User',187),(5,'App\\Models\\User',188),(5,'App\\Models\\User',189),(5,'App\\Models\\User',190),(5,'App\\Models\\User',191),(5,'App\\Models\\User',192),(5,'App\\Models\\User',193),(5,'App\\Models\\User',194),(5,'App\\Models\\User',195),(5,'App\\Models\\User',196),(5,'App\\Models\\User',197),(5,'App\\Models\\User',198),(5,'App\\Models\\User',199),(5,'App\\Models\\User',200),(5,'App\\Models\\User',201),(5,'App\\Models\\User',202),(5,'App\\Models\\User',203),(5,'App\\Models\\User',204),(5,'App\\Models\\User',205),(5,'App\\Models\\User',206),(5,'App\\Models\\User',207),(5,'App\\Models\\User',208),(5,'App\\Models\\User',209),(5,'App\\Models\\User',210),(5,'App\\Models\\User',211),(5,'App\\Models\\User',212),(5,'App\\Models\\User',213),(5,'App\\Models\\User',214),(5,'App\\Models\\User',215),(5,'App\\Models\\User',216),(5,'App\\Models\\User',217),(5,'App\\Models\\User',218),(5,'App\\Models\\User',219),(5,'App\\Models\\User',220),(5,'App\\Models\\User',221),(5,'App\\Models\\User',222),(5,'App\\Models\\User',223),(5,'App\\Models\\User',224),(5,'App\\Models\\User',225),(5,'App\\Models\\User',226),(5,'App\\Models\\User',227),(5,'App\\Models\\User',228),(5,'App\\Models\\User',229),(5,'App\\Models\\User',230),(5,'App\\Models\\User',231),(5,'App\\Models\\User',232),(5,'App\\Models\\User',233),(5,'App\\Models\\User',234),(5,'App\\Models\\User',235),(5,'App\\Models\\User',236),(5,'App\\Models\\User',237),(5,'App\\Models\\User',238),(5,'App\\Models\\User',239),(5,'App\\Models\\User',240),(5,'App\\Models\\User',241),(5,'App\\Models\\User',242),(5,'App\\Models\\User',243),(5,'App\\Models\\User',244),(5,'App\\Models\\User',245),(5,'App\\Models\\User',246),(5,'App\\Models\\User',247),(5,'App\\Models\\User',248),(5,'App\\Models\\User',249),(5,'App\\Models\\User',250),(5,'App\\Models\\User',251),(5,'App\\Models\\User',252),(5,'App\\Models\\User',253),(5,'App\\Models\\User',254),(5,'App\\Models\\User',255);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.view','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(2,'user.view','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(3,'user.view.own','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(4,'user.view.department','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(5,'user.view.company','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(6,'user.view.all','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(7,'user.create','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(8,'user.edit','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(9,'user.delete','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(10,'role.view','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(11,'role.create','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(12,'role.edit','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(13,'role.delete','web','2025-09-30 00:30:58','2025-09-30 00:30:58'),(14,'permission.view','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(15,'permission.create','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(16,'permission.edit','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(17,'permission.delete','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(18,'department.view','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(19,'department.view.own','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(20,'department.view.department','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(21,'department.view.company','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(22,'department.view.all','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(23,'department.create','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(24,'department.edit','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(25,'department.delete','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(26,'department.approve','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(27,'employee.view','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(28,'employee.view.own','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(29,'employee.view.department','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(30,'employee.view.company','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(31,'employee.view.all','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(32,'employee.create','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(33,'employee.edit','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(34,'employee.delete','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(35,'employee.approve','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(36,'report.view','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(37,'report.view.own','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(38,'report.view.department','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(39,'report.view.company','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(40,'report.view.all','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(41,'report.create','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(42,'report.edit','web','2025-09-30 00:30:59','2025-09-30 00:30:59'),(43,'report.delete','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(44,'report.approve','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(45,'leave.view','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(46,'leave.view.own','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(47,'leave.view.department','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(48,'leave.view.company','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(49,'leave.view.all','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(50,'leave.create','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(51,'leave.edit','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(52,'leave.delete','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(53,'leave.approve','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(54,'vehicle_registration.view','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(55,'vehicle_registration.create','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(56,'vehicle_registration.edit','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(57,'vehicle_registration.delete','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(58,'vehicle_registration.approve','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(59,'vehicle_registration.assign','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(60,'profile.view','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(61,'profile.create','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(62,'profile.edit','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(63,'profile.delete','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(64,'view-users','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(65,'create-users','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(66,'edit-users','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(67,'delete-users','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(68,'view-roles','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(69,'create-roles','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(70,'edit-roles','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(71,'delete-roles','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(72,'view-permissions','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(73,'create-permissions','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(74,'edit-permissions','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(75,'delete-permissions','web','2025-09-30 08:11:36','2025-09-30 08:11:36'),(76,'vehicle_registration.reject','web','2025-10-01 00:08:38','2025-10-01 00:08:38'),(77,'vehicle_registration.download_pdf','web','2025-10-01 00:08:38','2025-10-01 00:08:38'),(78,'vehicle_registration.check_signature','web','2025-10-01 00:08:38','2025-10-01 00:08:38'),(79,'record_management.view','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(80,'record_management.view.own','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(81,'record_management.view.department','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(82,'record_management.view.company','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(83,'record_management.view.all','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(84,'record_management.create','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(85,'record_management.edit','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(86,'record_management.delete','web','2025-10-02 08:14:33','2025-10-02 08:14:33'),(87,'record_management.approve','web','2025-10-02 08:14:34','2025-10-02 08:14:34'),(88,'record_management.view','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(89,'record_management.create','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(90,'record_management.edit','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(91,'record_management.delete','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(92,'salary_up_record.view','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(93,'salary_up_record.create','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(94,'salary_up_record.edit','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(95,'salary_up_record.delete','backpack','2025-10-02 08:58:28','2025-10-02 08:58:28'),(96,'salary_up_record.view','web','2025-10-02 08:58:46','2025-10-02 08:58:46'),(97,'salary_up_record.create','web','2025-10-02 08:58:46','2025-10-02 08:58:46'),(98,'salary_up_record.edit','web','2025-10-02 08:58:46','2025-10-02 08:58:46'),(99,'salary_up_record.delete','web','2025-10-02 08:58:46','2025-10-02 08:58:46'),(101,'so_dieu_dong_record.view','web','2025-10-07 08:19:57','2025-10-07 08:19:57'),(102,'so_dieu_dong_record.create','web','2025-10-07 08:19:57','2025-10-07 08:19:57'),(103,'so_dieu_dong_record.update','web','2025-10-07 08:19:57','2025-10-07 08:19:57'),(104,'so_dieu_dong_record.delete','web','2025-10-07 08:19:58','2025-10-07 08:19:58');
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
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vacancies_count` int NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'Giám đốc',1,'System','System',NULL,'2025-09-03 04:47:33','2025-09-03 04:47:33',NULL),(2,'Chính uỷ',1,'System','System',NULL,'2025-09-03 04:47:33','2025-09-03 04:47:33',NULL),(3,'P.Giám đốc',1,'System','System',NULL,'2025-09-03 04:47:33','2025-09-03 04:47:33',NULL),(4,'Chờ hưu từ 01/11/24',1,'System','System',NULL,'2025-09-03 04:47:33','2025-09-03 04:47:33',NULL),(5,'Trưởng phòng',1,'System','System',NULL,'2025-09-03 04:47:33','2025-09-03 04:47:33',NULL),(6,'P. Trưởng phòng',1,'System','System',NULL,'2025-09-03 04:47:33','2025-09-03 04:47:33',NULL),(7,'TL Quân sự',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(8,'TL Quân lực',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(9,'TL Kế hoạch',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(10,'NV Văn thư, bảo mật',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(11,'NV Thông tin',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(12,'NV lao động tiền lương',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(13,'NV điều độ SX',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(14,'NV Quân lực',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(15,'NV Thống kê',1,'System','System',NULL,'2025-09-03 04:47:34','2025-09-03 04:47:34',NULL),(16,'TT Tổ bảo vệ-PCCC',1,'System','System',NULL,'2025-09-03 04:47:35','2025-09-03 04:47:35',NULL),(17,'NV bảo vệ',1,'System','System',NULL,'2025-09-03 04:47:35','2025-09-03 04:47:35',NULL),(18,'Đội trưởng đội xe',1,'System','System',NULL,'2025-09-03 04:47:35','2025-09-03 04:47:35',NULL),(19,'Lái xe',1,'System','System',NULL,'2025-09-03 04:47:35','2025-09-03 04:47:35',NULL),(20,'Trưởng Ban',1,'System','System',NULL,'2025-09-03 04:47:35','2025-09-03 04:47:35',NULL),(21,'Phó Trưởng Ban',1,'System','System',NULL,'2025-09-03 04:47:35','2025-09-03 04:47:35',NULL),(22,'Trợ lý Chính trị',1,'System','System',NULL,'2025-09-03 04:47:36','2025-09-03 04:47:36',NULL),(23,'NV Chính trị',1,'System','System',NULL,'2025-09-03 04:47:36','2025-09-03 04:47:36',NULL),(24,'P Trưởng phòng',1,'System','System',NULL,'2025-09-03 04:47:36','2025-09-03 04:47:36',NULL),(25,'Trạm trưởng Spyder',1,'System','System',NULL,'2025-09-03 04:47:36','2025-09-03 04:47:36',NULL),(26,'P.Trạm trưởng Spyder',1,'System','System',NULL,'2025-09-03 04:47:36','2025-09-03 04:47:36',NULL),(27,'Trợ lý KT',1,'System','System',NULL,'2025-09-03 04:47:36','2025-09-03 04:47:36',NULL),(28,'NV thư viện KT',1,'System','System',NULL,'2025-09-03 04:47:37','2025-09-03 04:47:37',NULL),(29,'NV kỹ thuật',1,'System','System',NULL,'2025-09-03 04:47:37','2025-09-03 04:47:37',NULL),(30,'NV Cơ điện',1,'System','System',NULL,'2025-09-03 04:47:37','2025-09-03 04:47:37',NULL),(31,'Tổ trưởng',1,'System','System',NULL,'2025-09-03 04:47:37','2025-09-03 04:47:37',NULL),(32,'Thợ cơ điện',1,'System','System',NULL,'2025-09-03 04:47:38','2025-09-03 04:47:38',NULL),(33,'TL Kỹ thuật',1,'System','System',NULL,'2025-09-03 04:47:38','2025-09-03 04:47:38',NULL),(34,'NV Tiếp liệu',1,'System','System',NULL,'2025-09-03 04:47:38','2025-09-03 04:47:38',NULL),(35,'Thủ kho VTKT',1,'System','System',NULL,'2025-09-03 04:47:38','2025-09-03 04:47:38',NULL),(36,'NV KCS Bệ phóng',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(37,'NV KCS Đài Điều khiển',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(38,'NV KCS kíp đạn',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(39,'NV KCS Xe máy-TNĐ',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(40,'NV KCS',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(41,'NV KCS Cơ khí',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(42,'NV kế toán',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(43,'NV thủ quỹ',1,'System','System',NULL,'2025-09-03 04:47:39','2025-09-03 04:47:39',NULL),(44,'Phó Trưởng phòng',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(45,'NV Doanh trại',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(46,'NV Quản lý',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(47,'NV nấu ăn, tiếp phẩm',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(48,'NV Nhà khách',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(49,'NV nấu ăn',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(50,'Y sỹ',1,'System','System',NULL,'2025-09-03 04:47:40','2025-09-03 04:47:40',NULL),(51,'Y tá, nấu ăn',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(52,'Quản đốc',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(53,'Phó Quản đốc',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(54,'NV điều độ',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(55,'TT Tổ lắp ráp, SC khối',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(56,'Thợ lắp ráp, SC khối',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(57,'Thợ lắp ráp,  SC khối',1,'System','System',NULL,'2025-09-03 04:47:41','2025-09-03 04:47:41',NULL),(58,'TT Tổ SC Đài ĐK S-75M',1,'System','System',NULL,'2025-09-03 04:47:42','2025-09-03 04:47:42',NULL),(59,'Thợ SC Đài ĐK S-75M',1,'System','System',NULL,'2025-09-03 04:47:42','2025-09-03 04:47:42',NULL),(60,'TT Tổ Đài điều khiển S-125M',1,'System','System',NULL,'2025-09-03 04:47:42','2025-09-03 04:47:42',NULL),(61,'Thợ Đài điều khiển S-125M',1,'System','System',NULL,'2025-09-03 04:47:42','2025-09-03 04:47:42',NULL),(62,'TT Tổ SC xe AKKOR',1,'System','System',NULL,'2025-09-03 04:47:42','2025-09-03 04:47:42',NULL),(63,'TT Tổ SC cơ khí an ten',1,'System','System',NULL,'2025-09-03 04:47:42','2025-09-03 04:47:42',NULL),(64,'Thợ SC cơ khí an ten',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(65,'Phó QĐ',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(66,'TT Tổ Thợ SC điện bệ phóng',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(67,'Thợ SC điện bệ phóng',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(68,'TT Tổ SC cơ khí bệ phóng',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(69,'Thợ SC cơ khí bệ phóng',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(70,'Thợ SC vôn kế, đồng hồ',1,'System','System',NULL,'2025-09-03 04:47:43','2025-09-03 04:47:43',NULL),(71,'TT Tổ SC xe khí nén',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(72,'TT Tổ SC xe nạp chất \"O, G\"',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(73,'Thợ xe nạp chất \"O, G\"',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(74,'TT Tổ SC dây chuyền dKT',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(75,'TT Tổ cơ khí nguội',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(76,'TT Tổ cơ khí cắt gọt',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(77,'Thợ cơ khí cắt gọt',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(78,'TT Tổ SC gia công cơ khí',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(79,'Thợ gia công cơ khí',1,'System','System',NULL,'2025-09-03 04:47:44','2025-09-03 04:47:44',NULL),(80,'TT Tổ ép nhựa, mạ',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(81,'Thợ ép nhựa, mạ',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(82,'Thợ mộc',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(83,'TT Tổ mộc',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(84,'TT Tổ SC gầm vỏ',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(85,'Thợ gầm vỏ',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(86,'TT Tổ SC Đạn TL S-75M',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(87,'Thợ SC Đạn TL S-75M',1,'System','System',NULL,'2025-09-03 04:47:45','2025-09-03 04:47:45',NULL),(88,'TT Tổ SC Đạn TL S-125M',1,'System','System',NULL,'2025-09-03 04:47:46','2025-09-03 04:47:46',NULL),(89,'Thợ SC Đạn TL S-125M',1,'System','System',NULL,'2025-09-03 04:47:46','2025-09-03 04:47:46',NULL),(90,'TT Tổ SC kíp S-75M',1,'System','System',NULL,'2025-09-03 04:47:46','2025-09-03 04:47:46',NULL),(91,'Thợ SC kíp S-75M',1,'System','System',NULL,'2025-09-03 04:47:46','2025-09-03 04:47:46',NULL),(92,'TT Tổ SC kíp S-125M',1,'System','System',NULL,'2025-09-03 04:47:46','2025-09-03 04:47:46',NULL),(93,'Thợ SC kíp S-125M',1,'System','System',NULL,'2025-09-03 04:47:46','2025-09-03 04:47:46',NULL),(94,'TT Tổ sơn, bao gói',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(95,'Thợ sơn bao gói',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(96,'TT Tổ SC trạm nguồn',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(97,'Thợ SC trạm ngồn',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(98,'TT tổ SC động cơ',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(99,'Thợ SC động cơ',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(100,'TT Tổ SC điện xe máy',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(101,'Thợ SC điện xe máy',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(102,'TT Tổ SC ô tô',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(103,'Thợ SC ô tô',1,'System','System',NULL,'2025-09-03 04:47:47','2025-09-03 04:47:47',NULL),(104,'TT Tổ vô tuyến hiện sóng',1,'System','System',NULL,'2025-09-03 04:47:48','2025-09-03 04:47:48',NULL),(105,'Thợ vô tuyến hiện sóng',1,'System','System',NULL,'2025-09-03 04:47:48','2025-09-03 04:47:48',NULL),(106,'TT Tổ SC vôn kế, đồng hồ',1,'System','System',NULL,'2025-09-03 04:47:48','2025-09-03 04:47:48',NULL),(107,'TT Tổ SC động cơ EMU, MI',1,'System','System',NULL,'2025-09-03 04:47:48','2025-09-03 04:47:48',NULL),(108,'Thợ SC động cơ EMU, MI',1,'System','System',NULL,'2025-09-03 04:47:48','2025-09-03 04:47:48',NULL),(109,'TT Tổ SC Biến thế, tẩm sấy',1,'System','System',NULL,'2025-09-03 04:47:49','2025-09-03 04:47:49',NULL),(110,'TT Tổ Sơn tổng hợp',1,'System','System',NULL,'2025-09-03 04:47:49','2025-09-03 04:47:49',NULL),(111,'Thợ sơn tổng hợp',1,'System','System',NULL,'2025-09-03 04:47:49','2025-09-03 04:47:49',NULL),(112,'Thợ hóa nghiệm\nnhiên liệu \"O, G\"',1,'System','System',NULL,'2025-09-03 04:47:49','2025-09-03 04:47:49',NULL),(113,'1',2,'Nguyễn Đình Sự','Nguyễn Đình Sự','Nguyễn Đình Sự','2025-09-08 09:45:03','2025-09-08 09:45:44','2025-09-08 09:45:44');
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_entries`
--

DROP TABLE IF EXISTS `record_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_type_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `data` json NOT NULL,
  `year` int DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_entries_record_type_id_year_index` (`record_type_id`,`year`),
  KEY `record_entries_employee_id_index` (`employee_id`),
  CONSTRAINT `record_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `record_entries_record_type_id_foreign` FOREIGN KEY (`record_type_id`) REFERENCES `record_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_entries`
--

LOCK TABLES `record_entries` WRITE;
/*!40000 ALTER TABLE `record_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `record_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `record_types`
--

DROP TABLE IF EXISTS `record_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `record_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fields_config` json NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `department_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_types_slug_unique` (`slug`),
  KEY `record_types_department_id_foreign` (`department_id`),
  CONSTRAINT `record_types_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `record_types`
--

LOCK TABLES `record_types` WRITE;
/*!40000 ALTER TABLE `record_types` DISABLE KEYS */;
INSERT INTO `record_types` VALUES (1,'Sổ đăng ký điều động nội bộ','internal-transfer','Quản lý việc điều động nhân viên giữa các đơn vị trong công ty','{\"fields\": [{\"name\": \"serial_number\", \"type\": \"number\", \"label\": \"Số TT\", \"order\": 1, \"required\": true}, {\"name\": \"from_department_id\", \"type\": \"department_select\", \"label\": \"Đơn vị đi\", \"order\": 2, \"required\": true}, {\"name\": \"to_department_id\", \"type\": \"department_select\", \"label\": \"Đơn vị đến\", \"order\": 3, \"required\": true}, {\"name\": \"decision_number\", \"type\": \"text\", \"label\": \"Số QĐ\", \"order\": 4, \"required\": true}, {\"name\": \"contract_date\", \"type\": \"date\", \"label\": \"Ngày ký hợp đồng\", \"order\": 5, \"required\": true}, {\"name\": \"arrival_date\", \"type\": \"date\", \"label\": \"Ngày có mặt\", \"order\": 6, \"required\": true}, {\"name\": \"notes\", \"type\": \"textarea\", \"label\": \"Ghi chú\", \"order\": 7, \"required\": false}]}',1,2,'2025-10-02 08:13:48','2025-10-02 08:13:48'),(2,'Sổ danh sách quân nhân','military-personnel','Danh sách chi tiết thông tin quân nhân theo năm','{\"fields\": [{\"name\": \"serial_number\", \"type\": \"number\", \"label\": \"Số TT\", \"order\": 1, \"required\": true}, {\"name\": \"group_type\", \"type\": \"select\", \"label\": \"Loại nhóm\", \"order\": 2, \"options\": [\"TC\", \"SC\", \"CC1\", \"CC2\"], \"required\": true}, {\"name\": \"rank_level\", \"type\": \"number\", \"label\": \"Bậc L\", \"order\": 3, \"required\": true}, {\"name\": \"coefficient\", \"type\": \"decimal\", \"label\": \"Hệ số\", \"order\": 4, \"required\": true}, {\"name\": \"seniority_percentage\", \"type\": \"decimal\", \"label\": \"% TN VK BL\", \"order\": 5, \"required\": false}, {\"name\": \"salary_coefficient\", \"type\": \"decimal\", \"label\": \"Hệ số BL\", \"order\": 6, \"required\": false}, {\"name\": \"received_date\", \"type\": \"date\", \"label\": \"Tháng năm nhận\", \"order\": 7, \"required\": true}, {\"name\": \"unit\", \"type\": \"text\", \"label\": \"Đơn vị\", \"order\": 8, \"required\": true}, {\"name\": \"notes\", \"type\": \"textarea\", \"label\": \"Ghi chú\", \"order\": 9, \"required\": false}]}',1,2,'2025-10-02 08:13:48','2025-10-02 08:13:48'),(3,'Sổ đăng ký vi phạm kỷ luật','discipline-violation','Quản lý các trường hợp vi phạm kỷ luật của nhân viên','{\"fields\": [{\"name\": \"serial_number\", \"type\": \"number\", \"label\": \"Số tự thứ\", \"order\": 1, \"required\": true}, {\"name\": \"discipline_content\", \"type\": \"textarea\", \"label\": \"Nội dung kỷ luật\", \"order\": 2, \"required\": true}, {\"name\": \"discipline_type\", \"type\": \"select\", \"label\": \"Hình thức kỷ luật\", \"order\": 3, \"options\": [\"Khiển trách\", \"Cảnh cáo\", \"Hạ bậc lương\", \"Sa thải\"], \"required\": true}, {\"name\": \"decision_number\", \"type\": \"text\", \"label\": \"Số QĐ ngày ký\", \"order\": 4, \"required\": true}, {\"name\": \"decision_date\", \"type\": \"date\", \"label\": \"Ngày ký QĐ\", \"order\": 5, \"required\": true}, {\"name\": \"signed_by\", \"type\": \"text\", \"label\": \"Người ký kỷ luật\", \"order\": 6, \"required\": true}, {\"name\": \"notes\", \"type\": \"textarea\", \"label\": \"Ghi chú\", \"order\": 7, \"required\": false}]}',1,2,'2025-10-02 08:13:48','2025-10-02 08:13:48'),(4,'Sổ đăng ký nâng lương, nâng loại, chuyển nhóm','salary-adjustment','Quản lý các trường hợp nâng lương, nâng loại, chuyển nhóm và tăng % phụ cấp thâm niên','{\"fields\": [{\"name\": \"group_type\", \"type\": \"select\", \"label\": \"Loại nhóm (MS)\", \"order\": 1, \"options\": [\"TC\", \"SC\", \"CC1\", \"CC2\"], \"required\": true}, {\"name\": \"rank_level\", \"type\": \"number\", \"label\": \"Bậc L\", \"order\": 2, \"required\": true}, {\"name\": \"coefficient\", \"type\": \"decimal\", \"label\": \"Hệ số\", \"order\": 3, \"required\": true}, {\"name\": \"seniority_percentage\", \"type\": \"decimal\", \"label\": \"% TN VK\", \"order\": 4, \"required\": false}, {\"name\": \"salary_coefficient\", \"type\": \"decimal\", \"label\": \"Hệ số BL\", \"order\": 5, \"required\": false}, {\"name\": \"promotion_type\", \"type\": \"text\", \"label\": \"Thăng quân hàm QNCN\", \"order\": 6, \"required\": false}, {\"name\": \"received_date\", \"type\": \"date\", \"label\": \"Tháng năm nhận\", \"order\": 7, \"required\": true}, {\"name\": \"unit\", \"type\": \"text\", \"label\": \"Đơn vị (Phòng, Ban, PX)\", \"order\": 8, \"required\": true}, {\"name\": \"notes\", \"type\": \"textarea\", \"label\": \"Ghi chú\", \"order\": 9, \"required\": false}]}',1,2,'2025-10-02 08:13:48','2025-10-02 08:13:48'),(5,'Sổ nâng lương','so-nang-luong','Sổ theo dõi nâng lương, nâng loại, chuyển nhóm','{\"fields\": [{\"name\": \"ho_ten\", \"type\": \"text\", \"label\": \"Họ và tên\", \"required\": true}, {\"name\": \"nhap_ngu\", \"type\": \"date\", \"label\": \"Nhập ngũ (TĐ)\", \"required\": false}, {\"name\": \"chuc_vu\", \"type\": \"text\", \"label\": \"Chức vụ (CNQS)\", \"required\": false}, {\"name\": \"luong_hien_loai_nhom\", \"type\": \"text\", \"label\": \"Lương hiện - Loại nhóm (MS)\", \"required\": false}, {\"name\": \"luong_hien_bac\", \"type\": \"number\", \"label\": \"Lương hiện - Bậc L\", \"required\": false}, {\"name\": \"luong_hien_he_so\", \"type\": \"text\", \"label\": \"Lương hiện - Hệ số\", \"required\": false}, {\"name\": \"luong_hien_phan_tram\", \"type\": \"number\", \"label\": \"Lương hiện - % TN VK\", \"required\": false}, {\"name\": \"luong_hien_he_so_bl\", \"type\": \"text\", \"label\": \"Lương hiện - Hệ số BL\", \"required\": false}, {\"name\": \"luong_hien_quan_ham\", \"type\": \"text\", \"label\": \"Lương hiện - Quân hàm QN\", \"required\": false}, {\"name\": \"luong_hien_thang\", \"type\": \"text\", \"label\": \"Lương hiện - Tháng nhận bổ nhiệm\", \"required\": false}, {\"name\": \"luong_moi_loai_nhom\", \"type\": \"text\", \"label\": \"Xếp lương mới - Loại nhóm (MS)\", \"required\": false}, {\"name\": \"luong_moi_bac\", \"type\": \"number\", \"label\": \"Xếp lương mới - Bậc L\", \"required\": false}, {\"name\": \"luong_moi_he_so\", \"type\": \"text\", \"label\": \"Xếp lương mới - Hệ số\", \"required\": false}, {\"name\": \"luong_moi_phan_tram\", \"type\": \"number\", \"label\": \"Xếp lương mới - % TN VK\", \"required\": false}, {\"name\": \"luong_moi_he_so_bl\", \"type\": \"text\", \"label\": \"Xếp lương mới - Hệ số BL\", \"required\": false}, {\"name\": \"luong_moi_thang_qd\", \"type\": \"text\", \"label\": \"Xếp lương mới - Tháng quân đội hưởng\", \"required\": false}, {\"name\": \"luong_moi_thang_nhan\", \"type\": \"text\", \"label\": \"Xếp lương mới - Tháng năm nhận QNCN\", \"required\": false}, {\"name\": \"don_vi\", \"type\": \"text\", \"label\": \"Đơn vị (Phòng, Ban, PX)\", \"required\": false}, {\"name\": \"ghi_chu\", \"type\": \"textarea\", \"label\": \"Ghi chú\", \"required\": false}]}',1,1,'2025-10-02 08:39:28','2025-10-02 08:39:28');
/*!40000 ALTER TABLE `record_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `records_quan_nhan`
--

DROP TABLE IF EXISTS `records_quan_nhan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `records_quan_nhan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `ho_ten_thuong_dung` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Họ tên thường dùng',
  `so_hieu_quan_nhan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số hiệu quân nhân',
  `so_the_QN` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số thẻ quân nhân',
  `cap_bac` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_nhan_cap` date DEFAULT NULL,
  `ngay_cap_cc` date DEFAULT NULL COMMENT 'Ngày cấp Chứng minh, thẻ, CC',
  `cnqs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bac_ky_thuat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tai_ngu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_chuyen_qncn` date DEFAULT NULL,
  `ngay_chuyen_cnv` date DEFAULT NULL,
  `luong_nhom_ngach_bac` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lương: nhóm ngạch bậc',
  `ngay_vao_doan` date DEFAULT NULL,
  `ngay_vao_dang` date DEFAULT NULL,
  `ngay_chinh_thuc` date DEFAULT NULL COMMENT 'Ngày chính thức Đảng',
  `tp_gia_dinh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Thành phần gia đình',
  `tp_ban_than` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Thành phần bản thân',
  `dan_toc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ton_giao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `van_hoa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Trình độ văn hóa',
  `ngoai_ngu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suc_khoe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hang_thuong_tru` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `khu_vuc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `khen_thuong` text COLLATE utf8mb4_unicode_ci,
  `ky_luat` text COLLATE utf8mb4_unicode_ci,
  `ten_truong` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên trường',
  `cap_hoc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cấp học: ĐH, CĐ, TC...',
  `nganh_hoc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thoi_gian_hoc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VD: 2018-2022',
  `nguon_quan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sinh quân/Trở quân',
  `bao_tin` text COLLATE utf8mb4_unicode_ci COMMENT 'Khi cần báo tin cho ai? ở đâu?',
  `ho_ten_cha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_ten_me` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ho_ten_vo_chong` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `may_con` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ghi_chu` text COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `records_quan_nhan`
--

LOCK TABLES `records_quan_nhan` WRITE;
/*!40000 ALTER TABLE `records_quan_nhan` DISABLE KEYS */;
INSERT INTO `records_quan_nhan` VALUES (1,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(2,2,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(3,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(4,4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(5,5,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(6,6,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(7,7,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(8,8,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(9,9,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(10,10,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(11,11,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(12,12,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(13,13,2,'ll','111','222','daiuy','4123-02-01','4567-03-12','42422','424','224','5124-07-04','4545-03-12','2242','1990-06-03','2000-06-03','1234-03-12','FAFA','ÂFAFA','FAFAF','FAFAF','À','ÂFA','FAFAF','ÀAF','FAFAF','ttt','FAFAFAFAF','ÂFAF','FAFA','ÂFA','FAFAF','FAFA','GAADADAD','1212','122','tttt','3','!#123',NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:20:03',NULL),(14,14,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(15,15,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(16,16,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(17,17,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(18,18,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(19,19,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(20,20,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(21,21,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(22,22,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(23,23,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(24,24,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(25,25,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(26,26,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(27,27,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(28,28,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(29,29,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(30,30,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(31,31,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(32,32,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(33,33,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(34,34,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(35,35,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(36,36,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(37,37,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(38,38,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(39,39,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(40,40,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(41,41,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(42,42,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(43,43,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(44,44,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(45,45,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(46,46,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(47,47,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(48,48,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(49,49,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(50,50,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(51,51,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(52,52,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(53,53,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(54,54,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(55,55,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(56,56,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(57,57,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(58,58,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:51','2025-10-07 02:15:51',NULL),(59,59,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(60,60,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(61,61,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(62,62,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(63,63,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(64,64,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(65,65,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(66,66,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(67,67,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(68,68,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(69,69,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(70,70,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(71,71,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(72,72,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(73,73,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(74,74,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(75,75,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(76,76,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(77,77,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(78,78,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(79,79,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(80,80,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(81,81,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(82,82,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(83,83,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(84,84,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(85,85,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(86,86,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(87,87,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(88,88,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(89,89,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(90,90,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(91,91,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(92,92,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(93,93,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(94,94,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(95,95,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(96,96,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(97,97,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(98,98,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(99,99,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(100,100,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(101,101,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(102,102,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(103,103,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(104,104,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(105,105,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(106,106,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(107,107,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(108,108,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(109,109,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(110,110,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(111,111,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(112,112,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(113,113,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(114,114,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(115,115,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(116,116,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(117,117,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(118,118,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(119,119,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(120,120,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(121,121,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(122,122,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(123,123,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(124,124,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(125,125,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(126,126,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(127,127,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(128,128,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(129,129,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(130,130,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(131,131,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(132,132,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(133,133,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(134,134,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(135,135,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(136,136,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:52','2025-10-07 02:15:52',NULL),(137,137,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(138,138,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(139,139,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(140,140,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(141,141,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(142,142,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(143,143,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(144,144,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(145,145,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(146,146,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(147,147,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(148,148,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(149,149,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(150,150,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(151,151,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(152,152,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(153,153,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(154,154,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(155,155,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(156,156,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(157,157,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(158,158,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(159,159,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(160,160,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(161,161,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(162,162,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(163,163,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(164,164,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(165,165,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(166,166,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(167,167,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(168,168,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(169,169,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(170,170,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(171,171,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(172,172,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(173,173,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(174,174,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(175,175,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(176,176,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(177,177,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(178,178,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(179,179,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(180,180,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(181,181,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(182,182,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(183,183,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(184,184,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(185,185,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(186,186,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(187,187,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(188,188,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(189,189,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(190,190,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(191,191,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(192,192,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(193,193,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(194,194,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(195,195,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(196,196,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(197,197,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(198,198,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(199,199,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(200,200,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(201,201,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(202,202,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(203,203,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(204,204,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(205,205,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(206,206,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(207,207,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(208,208,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(209,209,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(210,210,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(211,211,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(212,212,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(213,213,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(214,214,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(215,215,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(216,216,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(217,217,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(218,218,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(219,219,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(220,220,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(221,221,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(222,222,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(223,223,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(224,224,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(225,225,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(226,226,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(227,227,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:53','2025-10-07 02:15:53',NULL),(228,228,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(229,229,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(230,230,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(231,231,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(232,232,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(233,233,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(234,234,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(235,235,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(236,236,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(237,237,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(238,238,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(239,239,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(240,240,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(241,241,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(242,242,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(243,243,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(244,244,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(245,245,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(246,246,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(247,247,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(248,248,18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(249,249,18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(250,250,18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL),(251,251,18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-07 02:15:54','2025-10-07 02:15:54',NULL);
/*!40000 ALTER TABLE `records_quan_nhan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `records_so_dieu_dong`
--

DROP TABLE IF EXISTS `records_so_dieu_dong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `records_so_dieu_dong` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL COMMENT 'ID nhân viên',
  `department_id` bigint unsigned NOT NULL COMMENT 'ID phòng ban',
  `nhap_ngu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nhập ngũ (mm/yyyy)',
  `chuc_vu_cnqc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chức vụ CNQC',
  `so_quyet_dinh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số quyết định',
  `ngay_quyet_dinh` date DEFAULT NULL COMMENT 'Ngày quyết định',
  `nguoi_ky` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Người ký',
  `chuc_vu_nguoi_ky` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chức vụ người ký',
  `ly_do_dieu_dong` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lý do điều động',
  `tu_don_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Từ đơn vị',
  `den_don_vi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đến đơn vị',
  `chuc_vu_cu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chức vụ cũ',
  `chuc_vu_moi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chức vụ mới',
  `ngay_hieu_luc` date DEFAULT NULL COMMENT 'Ngày hiệu lực',
  `ghi_chu` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `records_so_dieu_dong_department_id_foreign` (`department_id`),
  KEY `records_so_dieu_dong_employee_id_department_id_index` (`employee_id`,`department_id`),
  KEY `records_so_dieu_dong_ngay_quyet_dinh_index` (`ngay_quyet_dinh`),
  CONSTRAINT `records_so_dieu_dong_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `records_so_dieu_dong_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `records_so_dieu_dong`
--

LOCK TABLES `records_so_dieu_dong` WRITE;
/*!40000 ALTER TABLE `records_so_dieu_dong` DISABLE KEYS */;
INSERT INTO `records_so_dieu_dong` VALUES (1,9,2,NULL,NULL,'GG','2025-06-03',NULL,NULL,NULL,'GG','GG',NULL,NULL,'2026-06-03',NULL,'2025-10-08 00:12:49','2025-10-08 00:12:49');
/*!40000 ALTER TABLE `records_so_dieu_dong` ENABLE KEYS */;
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(23,1),(24,1),(25,1),(26,1),(27,1),(32,1),(33,1),(34,1),(35,1),(36,1),(39,1),(41,1),(42,1),(43,1),(44,1),(45,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(62,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(101,1),(102,1),(103,1),(104,1),(1,2),(8,2),(18,2),(23,2),(24,2),(25,2),(26,2),(27,2),(32,2),(33,2),(34,2),(35,2),(44,2),(53,2),(54,2),(55,2),(56,2),(57,2),(58,2),(59,2),(60,2),(62,2),(76,2),(77,2),(78,2),(1,3),(18,3),(24,3),(27,3),(33,3),(36,3),(41,3),(42,3),(43,3),(44,3),(45,3),(50,3),(51,3),(52,3),(53,3),(54,3),(60,3),(62,3),(81,3),(84,3),(85,3),(1,4),(54,4),(55,4),(56,4),(59,4),(60,4),(62,4),(76,4),(77,4),(1,5),(60,5),(80,5),(1,12),(36,12),(39,12),(41,12),(42,12),(43,12),(44,12),(45,12),(50,12),(51,12),(52,12),(53,12),(54,12),(55,12),(56,12),(57,12),(58,12),(59,12),(60,12),(62,12),(76,12),(77,12),(78,12),(79,12),(80,12),(81,12),(82,12),(83,12),(84,12),(85,12),(86,12),(87,12);
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
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','web','2025-09-30 00:31:00','2025-09-30 00:31:00'),(2,'Ban Giám đốc','web','2025-09-30 00:31:00','2025-10-01 00:44:28'),(3,'Trưởng phòng','web','2025-09-30 00:31:00','2025-10-01 00:43:48'),(4,'Đội trưởng đội xe','web','2025-09-30 00:31:00','2025-10-01 00:43:32'),(5,'Nhân viên','web','2025-09-30 00:31:00','2025-10-01 00:43:10'),(12,'test','web','2025-10-02 01:19:51','2025-10-02 01:19:51');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_up_records`
--

DROP TABLE IF EXISTS `salary_up_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_up_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `year` int NOT NULL DEFAULT '2025',
  `ho_ten` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nhap_ngu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nhập ngũ (TĐ) - Format: mm/yyyy',
  `chuc_vu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Chức vụ (CNQS)',
  `luong_hien_loai_nhom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Loại nhóm (MS)',
  `luong_hien_bac` int DEFAULT NULL COMMENT 'Bậc L',
  `luong_hien_he_so` decimal(5,2) DEFAULT NULL COMMENT 'Hệ số',
  `luong_hien_phan_tram_tn_vk` decimal(5,2) DEFAULT NULL COMMENT '% TN VK',
  `luong_hien_he_so_bl` decimal(5,2) DEFAULT NULL COMMENT 'Hệ số BL',
  `luong_hien_quan_ham` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Quân hàm QN',
  `luong_hien_thang_nhan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tháng nhận bổ nhiệm',
  `luong_moi_loai_nhom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Loại nhóm (MS)',
  `luong_moi_bac` int DEFAULT NULL COMMENT 'Bậc L',
  `luong_moi_he_so` decimal(5,2) DEFAULT NULL COMMENT 'Hệ số',
  `luong_moi_phan_tram_tn_vk` decimal(5,2) DEFAULT NULL COMMENT '% TN VK',
  `luong_moi_he_so_bl` decimal(5,2) DEFAULT NULL COMMENT 'Hệ số BL',
  `luong_moi_thang_qd_huong` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tháng quân đội hưởng',
  `luong_moi_thang_nam_nhan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tháng năm nhận QNCN',
  `don_vi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đơn vị (Phòng, Ban, PX)',
  `ghi_chu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_up_records_year_index` (`year`),
  KEY `salary_up_records_employee_id_index` (`employee_id`),
  KEY `salary_up_records_department_id_index` (`department_id`),
  KEY `salary_up_records_year_department_id_index` (`year`,`department_id`),
  CONSTRAINT `salary_up_records_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_up_records_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_up_records`
--

LOCK TABLES `salary_up_records` WRITE;
/*!40000 ALTER TABLE `salary_up_records` DISABLE KEYS */;
INSERT INTO `salary_up_records` VALUES (1,7,2,2025,'Nguyễn Đình Sự','09/2005','Trưởng phòng','1',1,1.00,1.00,1.00,'2//','1','2',2,2.00,2.00,2.00,'2//','2',NULL,'123',NULL,NULL,'2025-10-03 02:16:27','2025-10-03 02:16:27',NULL);
/*!40000 ALTER TABLE `salary_up_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `sessions` VALUES ('DSvfGsCytXxQaGzhZwxrL3PHu7LSusaUDkrdqjOf',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiY3pnWGFpeFdxUmlFczhqVHpUZ3F6eHkybjdVdUNUUU1HQkx2aDdYSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sZWF2ZS1yZXF1ZXN0LzEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTU6ImxvZ2luX2JhY2twYWNrXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDtzOjIyOiJwYXNzd29yZF9oYXNoX2JhY2twYWNrIjtzOjYwOiIkMnkkMTIkc3J5NjZUdXR1WlBMTkIyeWtYeFpjT01YWXZYaVFseTNzTVl4Wi8wckJFNlpEVUwxV2w2TkciO30=',1759892448),('NXhKBKuO6cyFfMNuHmIHzzom09fwrip8CnnAC6NB',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSUdGMlgxVVI2MjhERGozV2F6bEFScE1QVkMxbjdGMEpxWW1OOTVrRyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sZWF2ZS1yZXF1ZXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1NToibG9naW5fYmFja3BhY2tfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MjI6InBhc3N3b3JkX2hhc2hfYmFja3BhY2siO3M6NjA6IiQyeSQxMiRZVEV6SEd1QXYvUWQvdUh1c0ExQTNlWWliNEF6VGphNWZ1RDRueDlGY0JhNTZhNFFRbkZWLiI7fQ==',1759892663);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sms_api_sender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms_api_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms_api_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_categories`
--

LOCK TABLES `sub_categories` WRITE;
/*!40000 ALTER TABLE `sub_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `sub_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timelines`
--

DROP TABLE IF EXISTS `timelines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timelines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `center_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `position_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_sequent` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timelines_center_id_foreign` (`center_id`),
  KEY `timelines_department_id_foreign` (`department_id`),
  KEY `timelines_position_id_foreign` (`position_id`),
  KEY `timelines_employee_id_foreign` (`employee_id`),
  CONSTRAINT `timelines_center_id_foreign` FOREIGN KEY (`center_id`) REFERENCES `centers` (`id`),
  CONSTRAINT `timelines_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `timelines_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `timelines_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timelines`
--

LOCK TABLES `timelines` WRITE;
/*!40000 ALTER TABLE `timelines` DISABLE KEYS */;
/*!40000 ALTER TABLE `timelines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transitions`
--

DROP TABLE IF EXISTS `transitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `handed_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `center_document_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transitions_center_document_number_unique` (`center_document_number`),
  KEY `transitions_asset_id_foreign` (`asset_id`),
  KEY `transitions_employee_id_foreign` (`employee_id`),
  CONSTRAINT `transitions_asset_id_foreign` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  CONSTRAINT `transitions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transitions`
--

LOCK TABLES `transitions` WRITE;
/*!40000 ALTER TABLE `transitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_photo_path` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_pin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'PIN riêng cho chữ ký số của user',
  `certificate_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn đến file certificate .pfx riêng của user',
  `department_id` bigint unsigned DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_department_head` tinyint(1) NOT NULL DEFAULT '0',
  `department_permissions` json DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_employee_id_foreign` (`username`),
  KEY `users_department_id_is_department_head_index` (`department_id`,`is_department_head`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Admin','admin','admin@a31factory.com','$2y$12$YTEzHGuAv/Qd/uHusA1A3eYib4AzTja5fuD4nx9FcBa56a4QQnFV.','WKIAjdRJ5bIggC59fRI8zzUOwAkhjspuQCYj4nkB84oqBeekLy9c3XoHXAGw','profile-photos/avatar_1_1759821705.jpg',NULL,'123456',NULL,NULL,'admin',1,NULL,'System','System Administrator',NULL,'2025-09-15 16:02:02','2025-10-07 07:21:47',NULL),(2,NULL,'Ban Giám Đốc','bangiamdoc','bangiamdoc@a31factory.com','$2y$12$sUZoAdckY/0iSM3wOos4F.LI013c9DjXSzFd1Jo1gYs64IZGprlC.','M3EbCGd1n4gFDI8TisIZYU95lWL3AJcLyrLtZoIKgCRGjTVzdViD1bNaxRGZ',NULL,NULL,NULL,NULL,1,'director',1,NULL,'System','Ban Giám Đốc',NULL,'2025-09-15 16:02:02','2025-09-30 01:47:28',NULL),(3,NULL,'Đội trưởng đội xe','doixe','doixe@a31factory.com','$2y$12$kRUNSQEqOJZC3vEe2NzFseT1qXQUKY3M0IfjTW2o2uV3fwvt62ET.','bwIONcAGjZv6qnc09AEQC14wRzswrxrrmkwXtsXvcJlBTd0IE9KrvqKiPlCR',NULL,NULL,NULL,NULL,NULL,'supervisor',0,NULL,'System','Đội trưởng đội xe',NULL,'2025-09-15 16:02:02','2025-09-29 07:54:02',NULL),(4,NULL,'Phòng Kế hoạch','phongkehoach','phongkehoach@a31factory.com','$2y$12$sry66TutuZPLNB2ykXxZcOMXYvXiQly3sMYxZ/0rBE6ZDUL1Wl6NG','6ytMrfxnTlH4NNItitXPcMGM7wAM4OVZv424rOTkO4VDd94jvETD2sDWmQpR',NULL,NULL,NULL,NULL,2,'manager',1,NULL,'System','Phòng Kế hoạch',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(5,NULL,'Phòng Vật tư','phongvattu','phongvattu@a31factory.com','$2y$10$PhqlCsIUidTzX0yDng77X.KxOTpFp7EfE8kimmqFKFK45kGE0cjc2',NULL,NULL,NULL,NULL,NULL,3,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(6,NULL,'Phòng Kỹ thuật','phongkythuat','phongkythuat@a31factory.com','$2y$10$ronlsPYeNjMtLzglbKvi4u5Op3TNA6xVYPXdFp1bhRVSp9uME.QVS',NULL,NULL,NULL,NULL,NULL,4,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(7,NULL,'Phòng Cơ điện','phongcodien','phongcodien@a31factory.com','$2y$10$heS6Pg0slthB4ATzHxYiY.sf9z8Mt2bAuCjOwzUjn1Yr06J8EO9Vu',NULL,NULL,NULL,NULL,NULL,5,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(8,NULL,'Phòng kiểm tra chất lượng','phongkiemtra','phongkiemtra@a31factory.com','$2y$10$ZF6eNCJczgec.osNhXmrXO8fMPEflrYGC35c/gHJl0BkocXxBovy.',NULL,NULL,NULL,NULL,NULL,6,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(9,NULL,'Phòng Tài chính','phongtaichinh','phongtaichinh@a31factory.com','$2y$10$PNoY1yThAlWmCpN8Sl9NseU2P2ilc03zrFncAhCP1fJB6tIddXOam',NULL,NULL,NULL,NULL,NULL,7,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(10,NULL,'Phòng Hành chính-Hậu cần','phonghanhchinh','phonghanhchinh@a31factory.com','$2y$10$jyNuF9QSh1zCfWnqg.0CLuQXob1rqt7a.hag/PPTnHdZV1udPEnfC',NULL,NULL,NULL,NULL,NULL,8,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(11,NULL,'Ban Chính trị','banchinhtrị','banchinhtrị@a31factory.com','$2y$10$GfcjMmvqNDZv2klMyyoRK.0eC75SWnPLW6IJcT6vpnefkyXc3CIAC',NULL,NULL,NULL,NULL,NULL,9,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(12,NULL,'PX1: Đài điều khiển','px1','px1@a31factory.com','$2y$12$u1azkG1h483gBkjLMvfSDuhzcZe5aEIuhTwJ8Y5FSZ0Fg3lDv0j/S',NULL,NULL,NULL,NULL,NULL,10,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 07:51:38',NULL),(13,NULL,'PX2: BỆ PHÓNG','px2','px2@a31factory.com','$2y$10$JCCjbfzvat13PrDNrl4iVO.ehO3PyiawE3FvnozSKp75lwbSKMfxm',NULL,NULL,NULL,NULL,NULL,11,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(14,NULL,'PX3: SC XE ĐẶC CHỦNG','px3','px3@a31factory.com','$2y$10$0okjfL8Q5VwykWa8XkZ1Vu7VWHnn.XaNJDfX1HaRhUqsb2mQE6YL6',NULL,NULL,NULL,NULL,NULL,12,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(15,NULL,'PX4: CƠ KHÍ','px4','px4@a31factory.com','$2y$10$MKUhTMevQgG6SJA/2hcZIe0zlu3RyhuCdWtH1eolJYqLCmsvht/QO',NULL,NULL,NULL,NULL,NULL,13,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(16,NULL,'PX5: KÍP, ĐẠN TÊN LỬA','px5','px5@a31factory.com','$2y$10$As0TF9mV25Qv/vQgnaAiB.5Evy8skZv.4IDHgwiCC4kfRP5d2AJbK',NULL,NULL,NULL,NULL,NULL,14,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(17,NULL,'PX6: XE MÁY-TNĐ','px6','px6@a31factory.com','$2y$10$SfoUWGNg2jHHgJlsdEb19u9mF.rY6w.8BYMzxRU2QS0.LsVP9S7QO',NULL,NULL,NULL,NULL,NULL,15,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:02','2025-09-29 02:58:26',NULL),(18,NULL,'PX7: ĐO LƯỜNG','px7','px7@a31factory.com','$2y$10$CQjTstDeruUPGTqv7KV3IOiF4aqcmZxrbVIORDqoRDUC4eQX.4VNa',NULL,NULL,NULL,NULL,NULL,16,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:03','2025-09-29 02:58:26',NULL),(19,NULL,'PX8: ĐỘNG CƠ-BIẾN THẾ','px8','px8@a31factory.com','$2y$12$4aOU44fhDD/PNMfBmLm9m.W0vAtfFvC2TQhq18CkTuxXnD0N/P6u6',NULL,NULL,NULL,NULL,NULL,17,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:03','2025-09-29 02:58:26',NULL),(20,NULL,'PX 9: HÓA NGHIỆM PHỤC HỒI \"O, G\"','px9','px9@a31factory.com','$2y$12$SWCl8pBL2u3h8MiLzQxlJu7k75CXPx2oX4vLbnaIWUGsJkZBbtC0a',NULL,NULL,NULL,NULL,NULL,18,'manager',1,NULL,'System','System',NULL,'2025-09-15 16:02:03','2025-09-29 02:58:26',NULL),(21,NULL,'Phê duyệt','pheduyet','pheduyet@a31.com','$2y$12$ZKY78OovTH1kmbLmTmt5d.EQQHAh3VTkrFrnjFQ7hCKxABtL2.8BS',NULL,'profile-photos/avatar_21_1758852363.png','signatures/signature_21_1758852820.png',NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 00:49:04','2025-09-29 02:36:03',NULL),(22,1,'Phạm Đức Giang','pdgiang','pdgiang@a31.com.vn','$2y$12$izApoPVowWyatYa7d8bX7u85RkIF98LdYszsWnHilXMMdlN3WKthu',NULL,NULL,NULL,NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 01:33:22','2025-09-29 02:36:03',NULL),(23,2,'Hà Tiến Thụy','htthuy','htthuy@a31.com.vn','$2y$12$.UxQ0C8skGQadI1mwPhRMuTPJeRz9wjFYobWt7OwdK.jRoX8MlVPu',NULL,NULL,NULL,NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 01:33:22','2025-09-29 02:36:04',NULL),(24,3,'Cao Anh Hùng','cahung','cahung@a31.com.vn','$2y$12$ZZCj3muBVvm.HJmdXqk1Fex8MAMtWQK0clIkQL1KQ4s.UcSj2OcLW',NULL,NULL,NULL,NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 01:33:22','2025-09-29 02:36:04',NULL),(25,4,'Bùi Tân Chinh','btchinh','btchinh@a31.com.vn','$2y$12$3HFMbeTEBa9wAJ0N.WnTeO2osMvn7yDcqPZuW3WR8RJaerNbQ3cLO',NULL,NULL,NULL,NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 01:33:23','2025-09-29 02:36:04',NULL),(26,5,'Nguyễn Văn Bảy','nvbay','nvbay@a31.com.vn','$2y$12$EFa7kfIm6PmZ3jEx6mbH1.9YdOxajfn74zXVEMblx7F8nUagNZT4q',NULL,NULL,NULL,NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 01:33:23','2025-09-29 02:36:04',NULL),(27,6,'Phạm Ngọc Sơn','pnson','pnson@a31.com.vn','$2y$12$Lv0eKTSKnvOb3dc.Uq10s.6IeXoMa7L0WksHHaMv3R8aNX4v1r9mi',NULL,NULL,NULL,NULL,NULL,1,'manager',1,NULL,NULL,NULL,NULL,'2025-09-26 01:33:23','2025-09-29 02:36:04',NULL),(28,7,'Nguyễn Đình Sự','ndsu','ndsu@a31.com.vn','$2y$12$6c9mC472eBsPY7VvH/Upz.4eKEK6CjtLNC2XWd3/aBuIXhDETlIdS',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:23','2025-09-29 02:36:04',NULL),(29,8,'Phạm Tiến Long','ptlong','ptlong@a31.com.vn','$2y$12$69DscwsSnVUEr2hxXsRaNOEnm7guXLIFmCKQ1TflYZZdiT6lNDgsm',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:24','2025-09-29 02:36:04',NULL),(30,9,'Đặng Đình Quỳnh','ddquynh','ddquynh@a31.com.vn','$2y$12$vuQCD1vCP5dWpUplwJ7U4OZf5hc0cPCX6S2u.8iUlDmUtEr9fv.Zu',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:24','2025-09-29 02:36:04',NULL),(31,10,'Lục Viết Hợp','lvhop','lvhop@a31.com.vn','$2y$12$vhXdNjvG/kQVF4r7ZM/StOi9Vqse1WAvVniDi4lNuaCxwkg4xiORy',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:24','2025-09-29 02:36:04',NULL),(32,11,'Trần Đình Tài','tdtai','tdtai@a31.com.vn','$2y$12$3Q8dJniDzcz96RJy4z8oweUTXBFh1CerPHfCkT2OQxDCo.1zKURz.',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:24','2025-09-29 02:36:04',NULL),(33,12,'Trịnh Thị Thuý Hà','ttha','ttha@a31.com.vn','$2y$12$zh1K8ip57W340CqgbgDAseywKgNoSwFtadQs91aDtJ8pE973fgKmu',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:24','2025-09-29 02:36:04',NULL),(34,13,'Trịnh Văn Cương','tvcuong','tvcuong@a31.com.vn','$2y$12$XDVQICtFacVEE.6pgHnLPuRox1q0zX8G1u0vu37zh3BwMQ98YUwYC',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:25','2025-09-29 02:36:04',NULL),(35,14,'Nguyễn T Thu Hà','ntha','ntha@a31.com.vn','$2y$12$9zSzZL2bQBSWNmeFTEhm6OioQryTUMs7x9afm/evvFF1Yhnsg3S/2',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:25','2025-09-29 02:36:04',NULL),(36,15,'Vũ Thành Trung','vttrung','vttrung@a31.com.vn','$2y$12$YmYpujaCcx4lFCpYDPYPgecWlcPHenvRLyPRWOSeXn2z6ohdOBLpe',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:25','2025-09-29 02:36:04',NULL),(37,16,'Phạm Thị Thuý','ptthuy','ptthuy@a31.com.vn','$2y$12$UdwXp.vJh1z2dggGBIDOtODnwCHrRCnDsdItlffHyoWbwF.1dFBEC',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:25','2025-09-29 02:36:04',NULL),(38,17,'Phạm Thị Trà','pttra','pttra@a31.com.vn','$2y$12$/KZtohBi/dyU73vOAAbrZuKmOvKZuCqgy9UZAepZneR3VT75KFHtS',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:25','2025-09-29 02:36:04',NULL),(39,18,'Vũ Thanh Hà','vtha','vtha@a31.com.vn','$2y$12$YeZ.GMj/CiFtDSIcq.gMcecOaFVo/a3hoIN02V9N9213qoPBWMrg.',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:26','2025-09-29 02:36:04',NULL),(40,19,'Nguyễn Địch Linh','ndlinh','ndlinh@a31.com.vn','$2y$12$I4zMJMu698RV5Ma2NEtgYuZpipTedA/n8XgEaQ08U2A9q6ZZKdIli',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:26','2025-09-29 03:44:10',NULL),(41,20,'Tạ Quốc Bảo','tqbao','tqbao@a31.com.vn','$2y$12$zy7479nRfPwzPutoKK/lkuUGtzWl7Vi5bUDUFzPayOfFjIPHQpzwO',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:26','2025-09-29 02:36:05',NULL),(42,21,'Trần Ngọc Liễu','tnlieu','tnlieu@a31.com.vn','$2y$12$fBP3zKCEzDLPBtfUiObQYeYNMkb43C7vrP2D5vCuyjKAQBOkNvFWa',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:26','2025-09-29 02:36:05',NULL),(43,22,'Nguyễn T Thu Thanh','ntthanh','ntthanh@a31.com.vn','$2y$12$PW32aiXvBrq5UWo70H91K.R7dif0CEpTIa.mc2rhxuPhufh0477O.',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:27','2025-09-29 02:36:05',NULL),(44,23,'Trần Hữu Ngọc','thngoc','thngoc@a31.com.vn','$2y$12$VyJMNTDJcCZ026IfEexuNe7kgxy14NoQeQhgv27v3NCpC.2PKZqCe',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:27','2025-09-29 02:36:05',NULL),(45,24,'Nguyễn Minh Thanh','nmthanh','nmthanh@a31.com.vn','$2y$12$LN2aGVMzfTkiviiV3j11GO35UGZLrllP8AKNi7S6RIiLxua4utCJy',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:27','2025-09-29 02:36:05',NULL),(46,25,'Nông Tiến Tân','nttan','nttan@a31.com.vn','$2y$12$CRwxPE.KbNgVZCxt5Dq5zefoun.hwk0W7oIravoIlSrNKk5MQS346',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:27','2025-09-29 02:36:05',NULL),(47,26,'Nguyễn Trọng Toàn','nttoan','nttoan@a31.com.vn','$2y$12$LxI3TAsjm0HPFhwH2Amr2.VWah98AOm8uCzYbFL0cAYOlEoSXMdaK',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:27','2025-09-29 02:36:05',NULL),(48,27,'Phạm Văn Bảy','pvbay','pvbay@a31.com.vn','$2y$12$BcfxlE.q.uXN4A2HFc.7D.Jj8K1UvXwuxcU1WiRsDI8fGtjQPiR6y',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:28','2025-09-29 02:36:05',NULL),(49,28,'Phạm Văn Tặng','pvtang','pvtang@a31.com.vn','$2y$12$4jMJpSP1mS1sKGndA1PIbuIFH7RpWYoWniSR8JQ58VJp.JRCZIFYG',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:28','2025-09-29 02:36:05',NULL),(50,29,'Bùi Thanh Quân','btquan','btquan@a31.com.vn','$2y$12$8gBk9UQZB4o.914zfx8VHOLgHPVHtDkd/6H7a.VLMREIDEwsAL.vG',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:28','2025-09-29 02:36:05',NULL),(51,30,'Vũ Hữu Hải','vhhai','vhhai@a31.com.vn','$2y$12$vf7a1JN2M2YgLW8j7DRdBuxdu7zJZ9xCza/QwXMW56nrcLcIJ6eGq',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:28','2025-09-29 02:36:05',NULL),(52,31,'Lê Ngọc Duy','lnduy','lnduy@a31.com.vn','$2y$12$TIOokwqIo5jM6OGVKt05TeIuFqe0TdbCCqjFJd8uI3vPaYa5TwY96',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:28','2025-09-29 02:36:05',NULL),(53,32,'Nguyễn Văn Thắng','nvthang','nvthang@a31.com.vn','$2y$12$BEr4lht9OQzMSsmaRdBAZ.jddQU2FtEUZWMRkOkGFKtutu7LU1tqq',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:29','2025-09-29 02:36:05',NULL),(54,33,'Nguyễn Tiến Cường','ntcuong','ntcuong@a31.com.vn','$2y$12$cE/ZLnycq6pfUAMCCsqy6OJLnu38jiVBvwGB6fl6HQm83Rji3dmkO',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:29','2025-09-29 02:36:05',NULL),(55,34,'Hoàng Văn Tình','hvtinh','hvtinh@a31.com.vn','$2y$12$NIcbftI.qSGUbA42l8Pl8OKzt.rNNjHehSt8TnG1QOpDLXp4kKGlu',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:29','2025-09-29 02:36:05',NULL),(56,35,'Hoàng Anh Đức','haduc','haduc@a31.com.vn','$2y$12$r34gReNKqx.Lpw3r4M6SoOYqLBbYeozN6kYdJ7byuoGJIOUGJleMq',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:29','2025-09-29 02:36:05',NULL),(57,36,'Hoàng Bảo Chung','hbchung','hbchung@a31.com.vn','$2y$12$XvKWxwhqAQQBHfQMHRAzuuvB1PDm6MzUcbOK5Llde6/PabKsTMlQu',NULL,NULL,NULL,NULL,NULL,2,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:30','2025-09-29 02:36:05',NULL),(58,37,'Phạm Thị Thu Hương','pthuong','pthuong@a31.com.vn','$2y$12$BHU26wxiXKuL3nXIVS9aR.sjRJxMbOfs.oEsrp1HyrdYiUpeAxGr6',NULL,NULL,NULL,NULL,NULL,3,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:30','2025-09-29 02:36:05',NULL),(59,38,'Phan Minh Nghĩa','pmnghia','pmnghia@a31.com.vn','$2y$12$LR2raAinn3KaG9IVo7pibOGdvT7M2cG/r.dOiur/IOAaG3mFQ/tTy',NULL,NULL,NULL,NULL,NULL,3,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:30','2025-09-29 02:36:05',NULL),(60,39,'Nguyễn Trung Kiên','ntkien','ntkien@a31.com.vn','$2y$12$hrYP8F0Y1SrWwloSTb8BxuWBtJ.P7xdHipy4W.bZY.tSke/fVUk46',NULL,NULL,NULL,NULL,NULL,3,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:30','2025-09-29 02:36:05',NULL),(61,41,'Đặng Trọng Chánh','dtchanh','dtchanh@a31.com.vn','$2y$12$oNzoRfbQjEy9CbnEmGWCIuXbFpiS6Hab3uhcNqPGQ3DruZGzP87C6',NULL,NULL,NULL,NULL,NULL,3,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:30','2025-09-29 02:36:05',NULL),(62,42,'Nguyễn Minh Hiếu','nmhieu','nmhieu@a31.com.vn','$2y$12$JEPWEpZQx0J.g7sSwzUnHezTWghk2iwV/1FhC/DXsLPTYi4TiznHy',NULL,NULL,NULL,NULL,NULL,3,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:31','2025-09-29 02:36:05',NULL),(63,43,'Bùi Thị Nhật Lệ','btle','btle@a31.com.vn','$2y$12$ieZmbPWDOZ.i2DZ/GOkcyuTpwZHPkyEBtw/oBbkopCyWqXUR/zK1m',NULL,NULL,NULL,NULL,NULL,3,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:31','2025-09-29 02:36:05',NULL),(64,44,'Nguyễn Văn Ngà','nvnga','nvnga@a31.com.vn','$2y$12$CBd54Qf/6NX5ipLNZRIyXO3yxmGXl0QPbcHXpsjfxCdfqzx05MAUS',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:31','2025-09-29 02:36:05',NULL),(65,46,'Phạm Duy Thái','pdthai','pdthai@a31.com.vn','$2y$12$OBA5kDBUyWoCCYSxpDpHj.EBxPMYPuKIc4gmUA.58OmCkHR2g8N9m',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:31','2025-09-29 02:36:05',NULL),(66,47,'Lê Quý Vũ','lqvu','lqvu@a31.com.vn','$2y$12$u0wfjrD3ZbSPGqAKztwSQOint8c.oCHlvlGodX9IlaKjgK3wix17e',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:31','2025-09-29 02:36:05',NULL),(67,48,'Nguyên Hữu Ngọc','nhngoc','nhngoc@a31.com.vn','$2y$12$mp3tFLwdd2CdsFFxWlwtN.xPm2a/FxROnYZexlC460tyPvaTrzUe6',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:32','2025-09-29 02:36:05',NULL),(68,49,'Lại Hoàng Hà','lhha','lhha@a31.com.vn','$2y$12$7T4VJ7t6Use1rYPHVa0zQ.Nygc/M7b4HujCJovy3bqCpkvYWiRBTm',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:32','2025-09-29 02:36:05',NULL),(69,50,'Dương Thế Vinh','dtvinh','dtvinh@a31.com.vn','$2y$12$e/okIsTI0anyUM17jg3Ew.M2JG8w2aUtRw7daMjNop.Fub5vbYgNS',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:32','2025-09-29 02:36:05',NULL),(70,51,'Đỗ Văn Quân','dvquan','dvquan@a31.com.vn','$2y$12$EZl0h.J9hk/XmgSrlgOq5.bdiPxdaEozpdU277y5cgYcxRG3kMgP.',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:32','2025-09-29 02:36:05',NULL),(71,52,'Nguyễn Văn Bình','nvbinh','nvbinh@a31.com.vn','$2y$12$Dpl.ia.nsqUa/nvpAg2QheS1QGL699G1P0qCt5I/3wqHfr09IH5Ze',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:33','2025-09-29 02:36:05',NULL),(72,53,'Bùi Công Đoài','bcdoai','bcdoai@a31.com.vn','$2y$12$Nrh7BSjQaFQzGhgUq3FkQu9XQuSnZPJmgolngC9UQ2/ImUWBCudVm',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:33','2025-09-29 02:36:05',NULL),(73,54,'Đặng Hùng','dhung','dhung@a31.com.vn','$2y$12$NrgQXfCvZdvt0lpeWCiO/uZEX4Mr4hHufLN324gHPRJLjEsMOkPDu',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:33','2025-09-29 02:36:05',NULL),(74,55,'Ngô Văn Hiển','nvhien','nvhien@a31.com.vn','$2y$12$MRef62xL76Ro2vQWRPoGsuT9uZm6cKNl92Wc0IDDH7rOXpywmUpw.',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:33','2025-09-29 02:36:05',NULL),(75,56,'Đỗ Văn Linh','dvlinh','dvlinh@a31.com.vn','$2y$12$A.vrZhSs7vQY80XJevzipuettbuD78nbMWd/mamK5jNSLfCCH9/hy',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:33','2025-09-29 02:36:05',NULL),(76,57,'Hoàng Công Thành','hcthanh','hcthanh@a31.com.vn','$2y$12$PSyZBgd.ia.f.v8FIKGcUOI0YXvWGpEQdsPT3K.cSMi5JyYx7cFIe',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:34','2025-09-29 02:36:05',NULL),(77,58,'Văn Sỹ Lực','vsluc','vsluc@a31.com.vn','$2y$12$HoQz64HWEs56XBV6x9ZvE.afUFHJ1xzknaSSf83rTTl5.QQuW.b7i',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:34','2025-09-29 02:36:05',NULL),(78,59,'Nguyễn Trần Đức','ntduc','ntduc@a31.com.vn','$2y$12$OrBM3qMMkLMLfmh8JP3ydeX.XTx1YjAQrDVkem.SMt7MaLJhjv/iG',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:34','2025-09-29 02:36:05',NULL),(79,60,'Lê Minh Vượng','lmvuong','lmvuong@a31.com.vn','$2y$12$rA5N6zCsZFlAodl/GmvrZu/Ss1pEAyDjMKHJxtwXyH/Ju.zUsBMHy',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:34','2025-09-29 02:36:05',NULL),(80,61,'Tạ Văn Hoàng','tvhoang','tvhoang@a31.com.vn','$2y$12$lXOS.ZVHRaf9PqtPQgIPxuWLx6i1DEMRqYIv.nPyEWClbQKcZ0BQK',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:34','2025-09-29 02:36:05',NULL),(81,62,'Phạm Thị Phương','ptphuong','ptphuong@a31.com.vn','$2y$12$.7QmHBxR2pVAzrrQ4mfPqOKeJ04rzAhr9l6E/DCxfm7FiODsQ1602',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:35','2025-09-29 02:36:05',NULL),(82,63,'Lê Thị Vân','ltvan','ltvan@a31.com.vn','$2y$12$E979eJ3eDYB1ylM9L/APk.5X49pO2mVy9.aW/Ev9kvORBOJcdtI8a',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:35','2025-09-29 02:36:05',NULL),(83,64,'Trần Xuân Trường','txtruong','txtruong@a31.com.vn','$2y$12$NIlLg9a6ygD4wUis1ykkg.Fn410wSFT7nWlgtJue5yGEusX5XfN/O',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:35','2025-09-29 02:36:05',NULL),(84,65,'Nguyễn Đình Tuấn','ndtuan','ndtuan@a31.com.vn','$2y$12$EAjcuclF2wfVF8ITGOdR1ue7Q5QJJqUCtzkcQ9OtFjsFyZRrkdVQi',NULL,NULL,NULL,NULL,NULL,4,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:35','2025-09-29 02:36:05',NULL),(85,66,'Trần Ngọc Dũng','tndung','tndung@a31.com.vn','$2y$12$TQvaJwFIWWeaNnVKHjFThupnAWlQCAyV6al3.fMZl12YF09GWPPHy',NULL,NULL,NULL,NULL,NULL,5,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:36','2025-09-29 02:36:05',NULL),(86,67,'Nguyễn Anh Tuấn','natuan','natuan@a31.com.vn','$2y$12$ps7wCN5TSSNNoBDS5PiJhOkeEz5FbGUJadD3.5LIYnmBsJjrymRxi',NULL,NULL,NULL,NULL,NULL,5,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:36','2025-09-29 02:36:05',NULL),(87,68,'Nguyễn Xuân Dũng','nxdung','nxdung@a31.com.vn','$2y$12$IU.2OeOOxC/Ie/wBKuOfGuN83bbxIqpsSpgaJGkOg5nL6okyOkC3q',NULL,NULL,NULL,NULL,NULL,5,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:36','2025-09-29 02:36:05',NULL),(88,69,'Ngô Viết  Toản','nvtoan','nvtoan@a31.com.vn','$2y$12$r9e3DYsKxNMk6bFdOJE35u8Om0xIX2enOB4Gx3JppuNANrjeiPvku',NULL,NULL,NULL,NULL,NULL,5,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:36','2025-09-29 02:36:06',NULL),(89,70,'Hoàng Minh Ánh','hmanh','hmanh@a31.com.vn','$2y$12$wffCC1ndwrqvbpvHrP5MZu8RtbUk6QhwFJDWlP2hBcf5A1Rex/1Ta',NULL,NULL,NULL,NULL,NULL,5,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:36','2025-09-29 02:36:06',NULL),(90,71,'Nguyễn Văn Luỹ','nvluy','nvluy@a31.com.vn','$2y$12$OY6qVm8CXvvqgHXOTIMTzOB4PYCukn9fmvBe4G0sxROc6FOxvFhz2',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:37','2025-09-29 02:36:06',NULL),(91,72,'Trần Bá Trường','tbtruong','tbtruong@a31.com.vn','$2y$12$MIXj5aK0kdt/TyzBIt/o3..FHIxZGp04W1rDz41..XAg1TGyHHzZ6',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:37','2025-09-29 02:36:06',NULL),(92,73,'Bùi Văn Phong','bvphong','bvphong@a31.com.vn','$2y$12$OwQroy6g4jE0Mx3C7NVoAOORP1WMxF8NCixjYo.qdko9CNyJc/2S2',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:37','2025-09-29 02:36:06',NULL),(93,74,'Mai Văn Thuy','mvthuy','mvthuy@a31.com.vn','$2y$12$XJyyucYfR4yirN2uSG.eIedaGdUwfB5IPRZLhcov1SB/.q4/bS4Ue',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:37','2025-09-29 02:36:06',NULL),(94,75,'Nguyễn Văn Cường','nvcuong','nvcuong@a31.com.vn','$2y$12$ln88coVkCoEfKq.eWr8PQeGduL9ZFa6ycOw5b01df5Uf.5tfWCFiK',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:37','2025-09-29 02:36:06',NULL),(95,77,'Trịnh Thu Huyền','tthuyen','tthuyen@a31.com.vn','$2y$12$I4y7aF0WxTzMRRgi74mvjOaKDVvzCgpTTd9De.3DgBZGuEI2wWUAK',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:38','2025-09-29 02:36:06',NULL),(96,78,'Đặng Thị Huệ','dthue','dthue@a31.com.vn','$2y$12$CrC0LOqL3eCmHakkW.sdzulixUEmt0ULdARkHAEVbPUwK8xUtlX.y',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:38','2025-09-29 02:36:06',NULL),(97,79,'Đinh Tiến Dũng','dtdung','dtdung@a31.com.vn','$2y$12$WQtsrK5pFlWU2yPhLNtHaeRqScka.JMX2pTP21AQiuBSS0PaWeXsa',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:38','2025-09-29 02:36:06',NULL),(98,80,'Đoàn Thị Sự','dtsu','dtsu@a31.com.vn','$2y$12$IT2QERY3LAdY.UPM8X4fa.U2FQWSSo967dsuJ66s6.yI.CEA9Epna',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:38','2025-09-29 02:36:06',NULL),(99,81,'Phạm Thị Thu  Hà','ptha','ptha@a31.com.vn','$2y$12$ZEuZTLYKjdo5kSmWgADNsuox/UjcDL5wJMbB9XBM5EKNdD76RAMvK',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:39','2025-09-29 02:36:06',NULL),(100,82,'Đinh Thị Tâm','dttam','dttam@a31.com.vn','$2y$12$1M7sg2uWlxebiW5VLmfEiu7VsyESyJDPt5Rvpa81bkT2QHinrdPiO',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:39','2025-09-29 02:36:06',NULL),(101,83,'Nguyễn Thị Hiền','nthien','nthien@a31.com.vn','$2y$12$E.pyyRvn.lt7WqKS9A5SbOMz7jY.mLgWOLMtpEPHQ4DGuIaTPSKsa',NULL,NULL,NULL,NULL,NULL,6,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:39','2025-09-29 02:36:06',NULL),(102,84,'Đinh Quang Điềm','dqdiem','dqdiem@a31.com.vn','$2y$12$6bC8mh6QUwxK4FIqk94x3OxYmdXDATDgxEOB1zzTe8swvk1qDXNFu',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:39','2025-09-29 02:36:06',NULL),(103,85,'Huỳnh Thái Tân','httan','httan@a31.com.vn','$2y$12$DS5mJMhWK/grh5Ugcj.9rewwdHy9sb2i7KH97NA5JJfH3bpE6vYoy',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:39','2025-09-29 02:36:06',NULL),(104,86,'Mai Trường Giang','mtgiang','mtgiang@a31.com.vn','$2y$12$EjvziwVPwgBJMvhGryhxv.sB8Fyik5Bxnzt2GCsqGpMStIwYMnhMa',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:40','2025-09-29 02:36:06',NULL),(105,87,'Nguyễn Việt Dũng','nvdung','nvdung@a31.com.vn','$2y$12$1P1wb5oyI8OL9nYpga5v4usxE/Q3Ukq4REJJ3xtAy3JAvB9nBaU.K',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:40','2025-09-29 02:36:06',NULL),(106,88,'Nguyễn Xuân Quý','nxquy','nxquy@a31.com.vn','$2y$12$oeBsxM9UI68CONbfCV8M5.dwgE9pD/H3.CGvGR.9kP2LX7v1ibMUe',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:40','2025-09-29 02:36:06',NULL),(107,89,'Nguyễn Xuân Bách','nxbach','nxbach@a31.com.vn','$2y$12$D8kfYKv2vAs.JGtZRqw1.OdK/WLjlPDKJL5Co7VJNCPMLsbvp5UF6',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:40','2025-09-29 02:36:06',NULL),(108,90,'Nguyễn Ngọc Quý','nnquy','nnquy@a31.com.vn','$2y$12$eyqH1r/vyk6kUgZJqDhARebjiRjPlLRn9V.Z/2vaCPdVr0x0iqxTO',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:41','2025-09-29 02:36:06',NULL),(109,92,'Nguyễn Văn Bách','nvbach','nvbach@a31.com.vn','$2y$12$Ur5SH2ZJYroEOKNfjnpQBO1FF4a45OPzAxygUsx0EdTzybsJMDnHm',NULL,NULL,NULL,NULL,NULL,7,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:41','2025-09-29 02:36:06',NULL),(110,94,'Nguyễn Văn Phú','nvphu','nvphu@a31.com.vn','$2y$12$EZT3YiCSlYBGzq363Qj/o.gMEbT0nQf6QvkE5jAOJZpo./F8.1Rs.',NULL,NULL,NULL,NULL,NULL,8,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:41','2025-09-29 02:36:06',NULL),(111,95,'Phạm Thị Kiều Ân','ptan','ptan@a31.com.vn','$2y$12$D0vEWkbaahmhSb6Hb8iB/uwoa1lW0AV5iM5g7jG3G/OeNEKVZLReS',NULL,NULL,NULL,NULL,NULL,8,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:41','2025-09-29 02:36:06',NULL),(112,96,'Nguyễn Thị Thuý','ntthuy','ntthuy@a31.com.vn','$2y$12$qTOhtHsjysrHjk/rF4DVGeF1tjiil9x8xgxo4hMqnMb9MaH9pqqzW',NULL,NULL,NULL,NULL,NULL,8,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:41','2025-09-29 02:36:06',NULL),(113,97,'Dương Thị Mơ','dtmo','dtmo@a31.com.vn','$2y$12$v7B5SBpkhSAGLLiFpJDzg.nfBGpq637qEdYT5zb7vV0zcrF8AiPfe',NULL,NULL,NULL,NULL,NULL,8,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:42','2025-09-29 02:36:06',NULL),(114,98,'Nguyễn Thị Hằng','nthang','nthang@a31.com.vn','$2y$12$QpzRahgycNc2Av3JIGhTyeZMaHWQwSB.kDEmL5GXSJJPoypUgLA7e',NULL,NULL,NULL,NULL,NULL,8,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:42','2025-09-29 02:36:06',NULL),(115,100,'Chử  Quang Anh','cqanh','cqanh@a31.com.vn','$2y$12$xqoHW0fOzx9TX8RixcWqqOfAvw8qgyxDFE8HXaGEi.wYQAP92NrLO',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:42','2025-09-29 02:36:06',NULL),(116,101,'Đào Văn Tiến','dvtien','dvtien@a31.com.vn','$2y$12$/sfT35ImXlLnSsdyj2PRK.iBNzlTDsT19fddVeLmKgTKxwfL1r6kC',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:42','2025-09-29 02:36:06',NULL),(117,102,'Trần Đình Tám','tdtam','tdtam@a31.com.vn','$2y$12$pw0/Q4Et08Al2tm3x6lg.e0Xy4fa0rUqPvt9Dklkx51pgFfBB1qiq',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:42','2025-09-29 02:36:06',NULL),(118,103,'Nguyễn Quỳnh Trang','nqtrang','nqtrang@a31.com.vn','$2y$12$np79dxikDkTS6ZbdfGGsFuNriLBaBxhCE3kiTegHSI8ebfKHDIn4y',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:43','2025-09-29 02:36:06',NULL),(119,104,'Lê Mạnh Hà','lmha','lmha@a31.com.vn','$2y$12$Gz4d1EbPt3Adv..WTuR64.lunPMSdNi/Ep6/soTHTUgPWMB1eSUhy',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:43','2025-09-29 02:36:06',NULL),(120,105,'Nguyễn Thị Anh','ntanh','ntanh@a31.com.vn','$2y$12$8Dqj.HZTJJw5w/UcA94u4u1eH88al4BGV1CgtrfLyborQKIuc9SY.',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:43','2025-09-29 02:36:06',NULL),(121,106,'Đỗ Đức Toàn','ddtoan','ddtoan@a31.com.vn','$2y$12$b4H5oySUVQlnHjHYtfZGDe0L4jN4U8jzG9ZI72aI4j.KXFN4cKr9u',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:43','2025-09-29 02:36:06',NULL),(122,107,'Triệu T Hoài Phương','ttphuong','ttphuong@a31.com.vn','$2y$12$oEk.dlK.J79vEuGtEMKSFeTndPZRZxjbax6.8xYpy2R1AMvKSaBjm',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:44','2025-09-29 02:36:06',NULL),(123,108,'Trịnh Bá Thuận','tbthuan','tbthuan@a31.com.vn','$2y$12$/9K4L/kw7WgjrS3rgbEZku0HWs/QMTOZnDlQwe7W2E6MVPI9FpIZ2',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:44','2025-09-29 02:36:06',NULL),(124,109,'Đặng Quốc Sỹ','dqsy','dqsy@a31.com.vn','$2y$12$Inbuo0E1kK9pxZOghlmfi.QzI.Fo509l71O/GV2jVRznKBvid4SgC',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:44','2025-09-29 02:36:06',NULL),(125,110,'Phạm Lan Phương','plphuong','plphuong@a31.com.vn','$2y$12$rL3XgsTlh/RvymPFdV5VC.8OA2A5YCTCIkWnL/gigwwRXvOf3f.Ty',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:44','2025-09-29 02:36:06',NULL),(126,111,'Giang Chí Dũng','gcdung','gcdung@a31.com.vn','$2y$12$Aho9Qs1ilvPO0TO0mr1uHOJM3Ty0ZRHHAT8u2MEbsFbIDZXemy8fK',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:44','2025-09-29 02:36:06',NULL),(127,112,'Nguyễn Thị Huyền','nthuyen','nthuyen@a31.com.vn','$2y$12$tKhkX8fZUiAoUFv.JLEWOOKfP.0ztZvT1kmyVb1ZX6YJ/Rmfculr.',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:45','2025-09-29 02:36:06',NULL),(128,113,'Nguyễn T Phương Chi','ntchi','ntchi@a31.com.vn','$2y$12$miO.P3g3GmD1f9vYEDT79uHwouS7s027dgMA72LbRxyqodakZREYq',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:45','2025-09-29 02:36:06',NULL),(129,114,'Phạm Thị Vân Anh','ptanh','ptanh@a31.com.vn','$2y$12$JqZNk28RpK.dirPTb4Rx2.OhMZZ38pUN39Jkx1ZIC0C/6Jnm7allq',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:45','2025-09-29 02:36:06',NULL),(130,115,'Trần Thị Tuyến','tttuyen','tttuyen@a31.com.vn','$2y$12$jOcSzkz4WgE5BoLEkYceiecxOpLD7pAXhgj.hDKJ2FqmfakuS2XQm',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:45','2025-09-29 02:36:06',NULL),(131,116,'Bùi Đức Anh','bdanh','bdanh@a31.com.vn','$2y$12$FPsyNXP14.uTYuzzCLGrv.gdx3GXxTmvQCTvWd44ZoJ3gyvrN602S',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:45','2025-09-29 02:36:06',NULL),(132,117,'Vũ Thị Kim Ngân','vtngan','vtngan@a31.com.vn','$2y$12$R6RcF/LlcW8MskOk7utB/eIt2ErOgR0JrNDOTgnjzrdjS5wI0WIWK',NULL,NULL,NULL,NULL,NULL,9,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:46','2025-09-29 02:36:07',NULL),(133,119,'Trần Trọng Đại','ttdai','ttdai@a31.com.vn','$2y$12$8KbkLX/NCqyO2uDwwnJPV.UAZkCEf.xrADk33XLN2gDs.gbW/BAqi',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:46','2025-09-29 02:36:07',NULL),(134,120,'Lưu Hoàng Văn','lhvan','lhvan@a31.com.vn','$2y$12$F0fbf5Tb5YTt5ICO9Fr1zeAXFsqExkdhPaRnB9SApqLCZJYx59kie',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:46','2025-09-29 02:36:07',NULL),(135,121,'Đồng Xuân Dũng','dxdung','dxdung@a31.com.vn','$2y$12$HC.5uIqLtV.3hOkKvQYqh.F9gSmc1p0Hi4TUKNu9gLRpAOz/Aency',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:46','2025-09-29 02:36:07',NULL),(136,122,'Trương Thanh Tú','tttu','tttu@a31.com.vn','$2y$12$uNaDZzXb95DjeJjPRa0QReDaj0gzAEkJ9HAObsemNHCxV2ExOQ/Ka',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:47','2025-09-29 02:36:07',NULL),(137,123,'Dương T Phương Loan','dtloan','dtloan@a31.com.vn','$2y$12$8i7GE8nDy6Ni0Qxl2L5Hm.m45u0B1OJldXsIkk6ZnYSQZG4MHbAJ6',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:47','2025-09-29 02:36:07',NULL),(138,124,'Nguyễn  Hữu Thanh','nhthanh','nhthanh@a31.com.vn','$2y$12$BMY3O4mNLGSSMHxTMbyWmOogAIzF8yNZG4Yhs47RSN1COa5siPgZS',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:47','2025-09-29 02:36:07',NULL),(139,125,'Nguyễn Thị Tuyền','nttuyen','nttuyen@a31.com.vn','$2y$12$otxSGg5DumK.h4jlJcdolOF8XKenTTDcV8UVfr9ZLXrMUtJCn/kTm',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:47','2025-09-29 02:36:07',NULL),(140,126,'Lê Thị Thuý Hằng','lthang','lthang@a31.com.vn','$2y$12$mdDZgclmRPExKK0sdms.YO04gQfkmcMce6nvm4yTSLYNvQQLXzcy2',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:47','2025-09-29 02:36:07',NULL),(141,127,'Nguyễn T Thuý Bình','ntbinh','ntbinh@a31.com.vn','$2y$12$/dJusxSBixGaU0F5OkN5s.R3Pf9bmSmNA2AAG7eV5pWWaGP501vNq',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:48','2025-09-29 02:36:07',NULL),(142,129,'Dương Thị Thân Thương','dtthuong','dtthuong@a31.com.vn','$2y$12$gts73MHzHhx3afMRkUyW8eOzhqTVZfbJvhGDWt8SzuFUX...GVoIq',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:48','2025-09-29 02:36:07',NULL),(143,130,'Phạm Thị Trang Nhung','ptnhung','ptnhung@a31.com.vn','$2y$12$3L.D4MNlmjSMCwUCzJ8d5u13pcBBFgnfs4HwGJI8AkUDOTxFctkkm',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:48','2025-09-29 02:36:07',NULL),(144,131,'Trần Thị Chuyên','ttchuyen','ttchuyen@a31.com.vn','$2y$12$M8ZXI1Nqmt7rAlKTDQrule9XnAoHTb/n/CeewHEdyfysQ/Hy2lCx2',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:48','2025-09-29 02:36:07',NULL),(145,132,'Phạm Khắc Hùng','pkhung','pkhung@a31.com.vn','$2y$12$GP1zTiH./4z.NZv2PFDYi.erPUSZ9bppVie0FeFept3o.HU7JyUNq',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:48','2025-09-29 02:36:07',NULL),(146,133,'Nguyễn Mạnh Hùng','nmhung','nmhung@a31.com.vn','$2y$12$iaVu0u2GWa4YGvvenREIIebacO9uqg0j3fU8MYkopWgoX2siFlTPW',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:49','2025-09-29 02:36:07',NULL),(147,134,'Vũ Mạnh Tú','vmtu','vmtu@a31.com.vn','$2y$12$3Rj3XiQewepLOXosReeH4.HN9AcRSjg8tRpXSqmWx3nLrSdOwhlnq',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:49','2025-09-29 02:36:07',NULL),(148,135,'Bùi Anh Tuấn','batuan','batuan@a31.com.vn','$2y$12$Na0UYSJLV2fYuEuLIlhEtOKFJHC8uYSJ9pLJmQe2deVvuS/HFkkYC',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:49','2025-09-29 02:36:07',NULL),(149,136,'Nguyễn Văn Thụ','nvthu','nvthu@a31.com.vn','$2y$12$3CqzozFWIGv99Im589BWC.UfIPW6ypRj2.SeXiEJo2maqB5WPqIHG',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:49','2025-09-29 02:36:07',NULL),(150,137,'Đặng Văn Phố','dvpho','dvpho@a31.com.vn','$2y$12$XsfbpSKS0aA7gDJNRaOW/uNXHhFOLYfUofzrHVpw8VAt6tj7x47oW',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:50','2025-09-29 02:36:07',NULL),(151,138,'Nguyễn Xuân Trường','nxtruong','nxtruong@a31.com.vn','$2y$12$R9kBYNuYkRWdmJMmw6ji0.gz0Ow8L./SmtrZAc.Vk240VQxQnDvOS',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:50','2025-09-29 02:36:07',NULL),(152,139,'Hà Thanh Trung','httrung','httrung@a31.com.vn','$2y$12$o1g/DgxTnOUtkcG/xX8Sh.tGtOub09K2HWBvqK5ZH8Q1OOWwUu2Iy',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:50','2025-09-29 02:36:07',NULL),(153,140,'Nguyễn Văn Huyên','nvhuyen','nvhuyen@a31.com.vn','$2y$12$GrwTLGDE6w1bcJU7CZJbXeKBNDcMUNTKRQdpD0/mAtLWiLC5GsQKq',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:50','2025-09-29 02:36:07',NULL),(154,141,'Nguyễn Gia Mạnh','ngmanh','ngmanh@a31.com.vn','$2y$12$VBBWYccCT/EjtBiyB7/WuOyycTA.sWlo7nqGqHvtlIB1FYtFWFxw6',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:50','2025-09-29 02:36:07',NULL),(155,142,'Đỗ Hồng Sơn','dhson','dhson@a31.com.vn','$2y$12$fYIKesybibTaIgNzi/iJKudI872Do47bamMiNySN4grSipIRRdfZm',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:51','2025-09-29 02:36:07',NULL),(156,143,'Nguyễn Tuấn Hiệp','nthiep','nthiep@a31.com.vn','$2y$12$5ZJcldLjHmIEcSKZVrs1n.CDOiBHinrNO7mqXtaes9KK1J6rAisuG',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:51','2025-09-29 02:36:07',NULL),(157,144,'Vũ Mạnh Cương','vmcuong','vmcuong@a31.com.vn','$2y$12$RA/HOFjwgAiMU29AummK2.dI.WLkrZRRkDPqlrfQw31utW7eOyJx2',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:51','2025-09-29 02:36:07',NULL),(158,145,'Lê Trọng Quỳnh','ltquynh','ltquynh@a31.com.vn','$2y$12$YhQbhxDT7hnVMdYVZqCBl.x2tvIKIUF8XahIfQdwtca9.PQscIxeC',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:51','2025-09-29 02:36:07',NULL),(159,146,'Đặng Viết Công','dvcong','dvcong@a31.com.vn','$2y$12$zLMwGTdRl4V2BicMOg0DP.zQ5L2fxSRDlJgiZXDIl4FMUK1HlM.Le',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:51','2025-09-29 02:36:07',NULL),(160,147,'Nguyễn Tiến Dũng','ntdung','ntdung@a31.com.vn','$2y$12$Lm3xc572IlK2aztit3qi2.LXhJSpSQKgoi8Hsic.Cya440pSLNS8a',NULL,NULL,NULL,NULL,NULL,10,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:52','2025-09-29 02:36:07',NULL),(161,148,'Nguyễn Hồng Anh','nhanh','nhanh@a31.com.vn','$2y$12$bEvxuwe4LmoiiqvswcWCCOpHk.E7/kgLu5kmui9t9iFk3DXCq9epm',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:52','2025-09-29 02:36:07',NULL),(162,149,'Trần Đức Tấn','tdtan','tdtan@a31.com.vn','$2y$12$j1nQMVLftUmvaSQ/e51CZuVqD/eq2cOdfe9QL6n6YfJ3lz/b7XPt6',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:52','2025-09-29 02:36:07',NULL),(163,150,'Hoàng Anh Dũng','hadung','hadung@a31.com.vn','$2y$12$wf8/kZXAnuEGJtCxg38FL.2Aj75R2Y0N1OH2junnnKlWsE2eE609i',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:52','2025-09-29 02:36:07',NULL),(164,151,'Nguyễn Mai Hương','nmhuong','nmhuong@a31.com.vn','$2y$12$1NjkBU11HX5QaZWJUOMYlOy/7zvtYf34iO6HLW1dbX7O2oH8tYJFK',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:53','2025-09-29 02:36:07',NULL),(165,152,'Hoàng Văn Tiến','hvtien','hvtien@a31.com.vn','$2y$12$aA0zPKvG3EcJoq4mMXOrR.0LEPEef4hO8l/w1scbl1TgP.quGODMa',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:53','2025-09-29 02:36:07',NULL),(166,153,'Nguyễn Xuân Thụ','nxthu','nxthu@a31.com.vn','$2y$12$O/GGQqECZAYoDfTLfEEOFOCekdGpN6iOAfJv55iYDpFPFuUGJqTgS',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:53','2025-09-29 02:36:07',NULL),(167,154,'Hà Nguyễn Tuấn Anh','hnanh','hnanh@a31.com.vn','$2y$12$yFNyg9ufj2QRvQB8luizyuqKJR8uHc9tNNjNMrwC/QougLhOR/PrS',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:53','2025-09-29 02:36:07',NULL),(168,155,'Đinh Viết Trường','dvtruong','dvtruong@a31.com.vn','$2y$12$0KHT2G2AE4ZfvOzowo1.zOTnX/QLYAInqgTwrZajAQiVLSYd4fKhe',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:53','2025-09-29 02:36:07',NULL),(169,156,'Phan Thanh Quang','ptquang','ptquang@a31.com.vn','$2y$12$LpCYBmzqdiTncGm97tIoCO32.iyEoImxeM3O/GiMndVK3Brks1kv2',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:54','2025-09-29 02:36:07',NULL),(170,157,'Nguyễn Tiến Nam','ntnam','ntnam@a31.com.vn','$2y$12$vS8jbt54MVpNCWnxgsJUk.wxe9.PfUM1oqN6Cp5wMJ.3SzFaxVinO',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:54','2025-09-29 02:36:07',NULL),(171,158,'Nguyễn Huy Thắng','nhthang','nhthang@a31.com.vn','$2y$12$0CYNh1RAET7O.wQasbGiUOymGhMMhBaM1LatRNAPRJMmdMEHoxvvO',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:54','2025-09-29 02:36:07',NULL),(172,159,'Trần Hồng Công','thcong','thcong@a31.com.vn','$2y$12$RlXkQDIgN7ZRX1SAOSSlFOJDnHcIuDDjafiTlUSN6X2gazv.VX1JG',NULL,NULL,NULL,NULL,NULL,11,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:54','2025-09-29 02:36:07',NULL),(173,160,'An Văn Trực','avtruc','avtruc@a31.com.vn','$2y$12$STBtB2BGHcEl774y05PpkeMBk8/Ra3MyZb0LMWThbTQxXdP74LxY.',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:55','2025-09-29 02:36:07',NULL),(174,161,'Phạm Quỳnh Trang','pqtrang','pqtrang@a31.com.vn','$2y$12$VrSvWegIij78rPYAY9rR/O3fgXAJbQD8MZisNVnUpHGw.B.qr2wcO',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:55','2025-09-29 02:36:07',NULL),(175,162,'Ngô Thị Sơn','ntson','ntson@a31.com.vn','$2y$12$B5Z25XKK343jhVlhy7RAzu.RxeI0W0i5WdFs.OdPt35SxRgnButlC',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:55','2025-09-29 02:36:07',NULL),(176,164,'Trần Ngọc Phú','tnphu','tnphu@a31.com.vn','$2y$12$mjtnt0c8aMlMeKaObFyvGu8BITPPZQwMlpOnR8XoJz7e8Sd/f6a8W',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:55','2025-09-29 02:36:07',NULL),(177,165,'Nguyễn Tuấn Long','ntlong','ntlong@a31.com.vn','$2y$12$SJCsfJa1NWUxFUZLfqIGa.BGxYZcXOO76t8j3X8p4oMyqXTT8jKLy',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:55','2025-09-29 02:36:07',NULL),(178,166,'Nguyễn Đức Anh','ndanh','ndanh@a31.com.vn','$2y$12$xShhkOmwZc8txe4EByM9t.qKCAT082P5XRi4zJWKNXIkq4WvM1WkC',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:56','2025-09-29 02:36:08',NULL),(179,167,'Nguyễn Phú Hùng','nphung','nphung@a31.com.vn','$2y$12$6b1bOSI/2d17WE6qrUt0.ekG5JlRpDtcQ8WA9hi7BLEU68cuGEGsO',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:56','2025-09-29 02:36:08',NULL),(180,168,'Nguyễn Anh Đạt','nadat','nadat@a31.com.vn','$2y$12$KMzgU/yiOGTSh22HRBvNYOVOyTVxTyVqYXOBI8YJmP5kfu3PZqe9.',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:56','2025-09-29 02:36:08',NULL),(181,169,'Trịnh Trọng Cường','ttcuong','ttcuong@a31.com.vn','$2y$12$WVr7QSM7WicmcEPRroMt4OFOeIbqjms2XZTPctVDMcTrMDaQIprU6',NULL,NULL,NULL,NULL,NULL,12,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:56','2025-09-29 02:36:08',NULL),(182,170,'Cấn Xuân Khánh','cxkhanh','cxkhanh@a31.com.vn','$2y$12$15lBnyiWSr6tBrZDd9Zx.eX6Dgdr8ieTItblQZVC5Uncq4j0owPDK',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:56','2025-09-29 02:36:08',NULL),(183,171,'Vũ Thị Hiền','vthien','vthien@a31.com.vn','$2y$12$C/24cTDbNRZ5STtS2fR50O4BIVRL0IzbIYPBrnFBVrwKG5eZV.x0e',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:57','2025-09-29 02:36:08',NULL),(184,172,'Phan Văn Đăng','pvdang','pvdang@a31.com.vn','$2y$12$aMwVwpROMxEU5vIdm5ZRLu1Mrin7T3uhjZlhezCYtrDXTpmc5/ohq',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:57','2025-09-29 02:36:08',NULL),(185,173,'Bùi Mạnh Hùng','bmhung','bmhung@a31.com.vn','$2y$12$Qhb09hasSv0.jbXDnysIMuLjP88sjs.MhewOEoElYaBhIaXblDSSW',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:57','2025-09-29 02:36:08',NULL),(186,174,'Trần Văn Thành','tvthanh','tvthanh@a31.com.vn','$2y$12$owPHrNLI3jaVn1PUZUzL..69ELVSNAqVrutVT48AEe.QA6bTNYJ4u',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:57','2025-09-29 02:36:08',NULL),(187,175,'Vũ Trịnh Giang','vtgiang','vtgiang@a31.com.vn','$2y$12$B9jw2sT4aRABVUOMVWUGVej.3vmrfgZ3oLbITspY1OjNDtD0VUdUe',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:58','2025-09-29 02:36:08',NULL),(188,177,'Vũ Huy Phương','vhphuong','vhphuong@a31.com.vn','$2y$12$/Il8Zar8NM0jLftjsxMwJeqwJbOL1EYrHAJ2BkaKV8aGKGjjTCwCy',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:58','2025-09-29 02:36:08',NULL),(189,178,'Vũ Hải Dương','vhduong','vhduong@a31.com.vn','$2y$12$0Qo2Qb2YIEhXuSWXPTWkZe2Kz0IXgt7xcRbjxsT63fiR8uz9Ch2p.',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:58','2025-09-29 02:36:08',NULL),(190,179,'Trịnh Thành Chung','ttchung','ttchung@a31.com.vn','$2y$12$7Kep51xiYH6i58aHhU8p7.l.e6bO.IHSgkGryeuYTxIk7qOhOXT1.',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:58','2025-09-29 02:36:08',NULL),(191,180,'Nguyễn Diên Quang','ndquang','ndquang@a31.com.vn','$2y$12$EsYB6cDSRK3mc86g72hh1O7uYN0hnu/WAfIC02nLfsPRtw4JBdFfu',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:59','2025-09-29 02:36:08',NULL),(192,181,'Mai Thị Phượng','mtphuong','mtphuong@a31.com.vn','$2y$12$Gi6d9vLpaJ3gWRF7Fy0gLeeuK6Xvv63y.Rei/C4ESN5cNXYZDFgwm',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:59','2025-09-29 02:36:08',NULL),(193,182,'Bùi Thị Hồng Thu','btthu','btthu@a31.com.vn','$2y$12$BhLn7YjZxEkBwQglXuGszuIQspemv/2eG10ZmSTF4UVBlwN.3Owrq',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:59','2025-09-29 02:36:08',NULL),(194,183,'Đặng Văn Tường','dvtuong','dvtuong@a31.com.vn','$2y$12$fLCKgM9cezPmY4dhxx8dh.6A..FByy.fS.BxNsBC7KSZrVF6vtYGS',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:59','2025-09-29 02:36:08',NULL),(195,184,'Trần Hồng Tú','thtu','thtu@a31.com.vn','$2y$12$B3g8M9sNVqDVtolmi8C2v.cPOylwgJVeayWKxUF.at4F.t4JzQHqq',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:33:59','2025-09-29 02:36:08',NULL),(196,185,'Lê Trọng Quý','ltquy','ltquy@a31.com.vn','$2y$12$AFT3V1/M1QZquXxXhjqu8OuApyU68tdF24814QGul18Cr512wxeku',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:00','2025-09-29 02:36:08',NULL),(197,186,'Đỗ Trung Kiên','dtkien','dtkien@a31.com.vn','$2y$12$s4ItcvAxOesOjY8UaSKpPO/A4yJMovbgLIlwEgZGL7WE1tYUjN10W',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:00','2025-09-29 02:36:08',NULL),(198,187,'Chu Lê Tuấn Anh','clanh','clanh@a31.com.vn','$2y$12$/IEN.8aWV/5UTtWm8cId9.gjErf3PQHzVFSNngcK7pk9md6sOcgeC',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:00','2025-09-29 02:36:08',NULL),(199,188,'Hoàng Văn Thắng','hvthang','hvthang@a31.com.vn','$2y$12$rBdjFaoRVlDgQAgH/lL7ie1/6zZzsiRn4eMn5IaoKqTo4YkV6o0Vi',NULL,NULL,NULL,NULL,NULL,13,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:00','2025-09-29 02:36:08',NULL),(200,190,'Bùi Trường Giang','btgiang','btgiang@a31.com.vn','$2y$12$Y0pOa.WEBGtPGjrGfgoqM.DHlNk7y38KR6f30ZpiG4cS49E7.47H6',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:01','2025-09-29 02:36:08',NULL),(201,191,'Nguyễn Hải Sơn','nhson','nhson@a31.com.vn','$2y$12$aLsxYnyu.BeQCUgF2I2K1.0ePTohYT9tn0Edi7cpj1I8t2NlvtkqW',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:01','2025-09-29 02:36:08',NULL),(202,193,'Tống Cao Cường','tccuong','tccuong@a31.com.vn','$2y$12$YaLTzdmRfNk/55lky/PBluGAkeJvmcYzcQCLiG8aXbWfeH.U6exp6',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:01','2025-09-29 02:36:08',NULL),(203,194,'Nguyễn Hữu Tâm','nhtam','nhtam@a31.com.vn','$2y$12$KLCn4Xeb.wm8AgsgWgmCfut/Zka5UT.cJ6P3mu/wiWVSnuPtPiV4a',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:01','2025-09-29 02:36:08',NULL),(204,195,'Hồ Thị Hiền','hthien','hthien@a31.com.vn','$2y$12$uUtywFSaWEsJkafS/g0Av.aXV.jzx/AF8kFM7FX0b656szlyDHW5K',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:01','2025-09-29 02:36:08',NULL),(205,196,'Nguyễn T Phương Thảo','ntthao','ntthao@a31.com.vn','$2y$12$rn50rm7RGztzsXO9hqiwh.0fLY6PCwHloCp4tRHWlaDVJfdzYm7L6',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:02','2025-09-29 02:36:08',NULL),(206,197,'Bùi Văn Huy','bvhuy','bvhuy@a31.com.vn','$2y$12$TbmlaWlB23TO5s384qNqbuJaTPVM8TXhnbavIBdgNbs4Zj2DcHRWG',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:02','2025-09-29 02:36:08',NULL),(207,198,'Phan Văn Sáng','pvsang','pvsang@a31.com.vn','$2y$12$86nbKqtPSq/E28YxSSN0SeRNdaEi/5C1a/AbjrHBoQvQkdCVggrHu',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:02','2025-09-29 02:36:08',NULL),(208,200,'Hà Minh Nho','hmnho','hmnho@a31.com.vn','$2y$12$gyAEJdrIaBHZpi/7n9Ku9O1XcLGYRb6vO2zh3jUqTbq9rB8bEspJ2',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:02','2025-09-29 02:36:08',NULL),(209,201,'Nguyễn Văn Đồng','nvdong','nvdong@a31.com.vn','$2y$12$4IwxuADEC3CBc2CLVgjtIuvLtSus0xE2BESy/MYjJGus/pQgwi3dW',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:03','2025-09-29 02:36:08',NULL),(210,202,'Trần T Kim Oanh','ttoanh','ttoanh@a31.com.vn','$2y$12$PfQxThmjbL7Na6m8BToGt.n07O8x1KbcWfmFSBMikddKXml8nxrpC',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:03','2025-09-29 02:36:08',NULL),(211,203,'Bùi Thị Huệ','bthue','bthue@a31.com.vn','$2y$12$mQSoLg0mDuJZeNBVMeCgf.3lbLeJyyA9e3jX70K6ChLsSMEs4jlrK',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:03','2025-09-29 02:36:08',NULL),(212,204,'Bùi Đức Cảnh','bdcanh','bdcanh@a31.com.vn','$2y$12$eRDxxukoMFPlmkAPdAOkJOHLv6Zp.f/1X0CWWYEyREzy11OrjyNkK',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:03','2025-09-29 02:36:08',NULL),(213,205,'Trần Đức Minh','tdminh','tdminh@a31.com.vn','$2y$12$Jpqfvg.w0frsWZUkF8E0reDq3cB1a9OJ8UztVLiJQe.uAH4AUi7MC',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:03','2025-09-29 02:36:08',NULL),(214,206,'Vũ Đình Tùng','vdtung','vdtung@a31.com.vn','$2y$12$r4xUfN3mbdB55xVwGT65/.BK2gXCIUeO2qZSaCH/PuUlaebSG0.6y',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:04','2025-09-29 02:36:08',NULL),(215,207,'Trần Đình Tùng','tdtung','tdtung@a31.com.vn','$2y$12$ew8NFFiDFOCg47P25tsHjOAz2LuxxFkHDKEx0mU/HT.tDTIwLISea',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:04','2025-09-29 02:36:08',NULL),(216,208,'Đào Thị Thu Huyền','dthuyen','dthuyen@a31.com.vn','$2y$12$lnHSpDmg/k/jmUrMK0brQuHSFJvmMOXB.1bXQihFg/PfSgJcZpzcq',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:04','2025-09-29 02:36:08',NULL),(217,209,'Nguyễn Văn Quyết','nvquyet','nvquyet@a31.com.vn','$2y$12$aKbAZo3e1RFvvCq6mrMYI.qSsLLj8b4GLv4s6zYHHyrUA3saymNMC',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:04','2025-09-29 02:36:08',NULL),(218,210,'Nguyễn Thị Thu','ntthu','ntthu@a31.com.vn','$2y$12$Ox/dNH9eExo1J15paKaroeE9Dzrcz5.IwFHhURTJAM583ixNIXh/G',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:05','2025-09-29 02:36:08',NULL),(219,211,'Trần T Ngọc Anh','ttanh','ttanh@a31.com.vn','$2y$12$1oXMzqgbxo5rzOOCunAHZeTOBTDepdty1tMtVoE6BUkWhSeGFIofK',NULL,NULL,NULL,NULL,NULL,14,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:05','2025-09-29 02:36:08',NULL),(220,212,'Đỗ Văn Hưng','dvhung','dvhung@a31.com.vn','$2y$12$AnGoxlU29kGHa6m2mAMLp.T1mp2CnCLYx0nPSR9LaDoUMrLdr1voW',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:05','2025-09-29 02:36:08',NULL),(221,213,'Nguyễn Thị Tân Miền','ntmien','ntmien@a31.com.vn','$2y$12$EC0plVyz9Jz6l871qlX3IeJDBJsnXnhjO20RtmZXdgAy8Uki797au',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:05','2025-09-29 02:36:08',NULL),(222,214,'Nguyễn Ngọc Khánh','nnkhanh','nnkhanh@a31.com.vn','$2y$12$F4cpJ4YgnbXFfU8lpHk/f.zaqhnzyIifDBeIvJVjGsbf5/5tTscvK',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:05','2025-09-29 02:36:08',NULL),(223,215,'Nguyễn Dự Đáng','nddang','nddang@a31.com.vn','$2y$12$gNsOqDLeHRuJKqvcAZ6RFu0Ziughx7pW1yofvc/4pwAosE1bDIkHO',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:06','2025-09-29 02:36:08',NULL),(224,216,'Lê Văn Hội','lvhoi','lvhoi@a31.com.vn','$2y$12$a2CIcCYJvUQGXv8s.EisjOIds73C0lbjUH7/QHyvQGUVdRjziWY8G',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:06','2025-09-29 02:36:08',NULL),(225,217,'Nguyễn Kim Biển','nkbien','nkbien@a31.com.vn','$2y$12$s.V1kjW8W5kQWLNqVxopl.2QvFV9B5aJubU3TAUIFITRmcxgtaNf2',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:06','2025-09-29 02:36:09',NULL),(226,218,'Trần Mạnh Kiều','tmkieu','tmkieu@a31.com.vn','$2y$12$3WI0GPxTOu9cM1zQtdNANOHGsaslNTp5UQ7o9mJOWoUD.QK.b6bgK',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:06','2025-09-29 02:36:09',NULL),(227,219,'Dương Bá Quyền','dbquyen','dbquyen@a31.com.vn','$2y$12$SxT2JgaY5RgIXzQvMguqZO37Q/c90rImIbISWs8z0fJz7sGoVuYW.',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:07','2025-09-29 02:36:09',NULL),(228,220,'Nguyễn Thị Tươi','nttuoi','nttuoi@a31.com.vn','$2y$12$DImwRuARHMf4cxKd7vSrSeCWKe.KoTNm7ru62F8r.QOOWONaNYAF.',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:07','2025-09-29 02:36:09',NULL),(229,221,'Bùi T Khánh Thuỳ','btthuy','btthuy@a31.com.vn','$2y$12$GtacDBkuM3XTS17hCZZ09.7iGcAaSaOlR2ZwVLo6UUk2Dj7aNTToe',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:07','2025-09-29 02:36:09',NULL),(230,222,'Hà Chí Quang','hcquang','hcquang@a31.com.vn','$2y$12$hvwIUXtitv4Yx.aga4SV9eC3YEr2Rc4jg8Ms4R3ZDQdY2lGBWjpce',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:07','2025-09-29 02:36:09',NULL),(231,223,'Võ Văn Tới','vvtoi','vvtoi@a31.com.vn','$2y$12$a2jblatWWoD60AFatftiEO9Z/slfS6IOJJY9u8J8a4h/ZU/aQCkAq',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:07','2025-09-29 02:36:09',NULL),(232,224,'Nguyễn Quang Hùng','nqhung','nqhung@a31.com.vn','$2y$12$iTZ1orxp879COPPY0/UgrunyEIk5B4nuZyXvInHEjPqzGl4m7D1sK',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:08','2025-09-29 02:36:09',NULL),(233,225,'Nguyễn Quyết Tiến','nqtien','nqtien@a31.com.vn','$2y$12$Y9wYR26JxVrShKbNeCWJtebKi8WsJJ72fZsiiTY0Y5RZX9Xt5c7Wi',NULL,NULL,NULL,NULL,NULL,15,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:08','2025-09-29 02:36:09',NULL),(234,226,'Tạ Hồng Đăng','thdang','thdang@a31.com.vn','$2y$12$0ibgOgz9GFYg6X.HuK0J9eNkNqmGhcHaKfKAV13B9WVhkIOUlU/0S',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:08','2025-09-29 02:36:09',NULL),(235,227,'Nguyễn Thị Hoàn','nthoan','nthoan@a31.com.vn','$2y$12$sQWUHBJgSe7k5UO8NmoaxOwB9ouEMG2/liBdxdets3DIJm11naVC6',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:08','2025-09-29 02:36:09',NULL),(236,228,'Nguyễn Sơn Đông','nsdong','nsdong@a31.com.vn','$2y$12$OWw7/4HB11h8IVban2J2o.yAVgYtGR7rQd3iFtRvqTxsPvGQmhVIG',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:09','2025-09-29 02:36:09',NULL),(237,229,'Nguyễn Hải Tiến','nhtien','nhtien@a31.com.vn','$2y$12$yPwT37yVplumS4LNj3zxxeBR.fkBDqbFBEncMGi.r4q2XIAMnVCta',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:09','2025-09-29 02:36:09',NULL),(238,230,'Trần Việt Trung','tvtrung','tvtrung@a31.com.vn','$2y$12$mqicZ26O83nkGsFEQ7cOteSNfBs9J5RPEZgb2AOWmHopndQVijuKi',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:09','2025-09-29 02:36:09',NULL),(239,231,'Trần Thị Việt Hồng','tthong','tthong@a31.com.vn','$2y$12$I47qaPNf4tcXbBcrGJcNa.POPcjxlgsSm9m.p4ZzFPcpT68gYhyyW',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:09','2025-09-29 02:36:09',NULL),(240,232,'Vũ Ngọc Quỳnh','vnquynh','vnquynh@a31.com.vn','$2y$12$sbv119TCLm3Qj1a4xXEw6e.xcQkXUawHQSvxO27/lqmmZ0LLd.5wO',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:09','2025-09-29 02:36:09',NULL),(241,233,'Thái Thị Âu','ttau','ttau@a31.com.vn','$2y$12$lRl2BoQk8bk2Sop9GGaIYusVlBcl6ThhMQlf8yEe8aFArCPi1fWqm',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:10','2025-09-29 02:36:09',NULL),(242,234,'Nguyễn Thuỳ Linh','ntlinh','ntlinh@a31.com.vn','$2y$12$V/724Oy21M37mhTzKoLSkemLeZC.vMtVjaDz0AVeY5muW0l6EZBDa',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:10','2025-09-29 02:36:09',NULL),(243,235,'Nguyễn Thị Mai','ntmai','ntmai@a31.com.vn','$2y$12$gkGBOLvTWzilRRw2B9JVRecEJPqE3fzf76/O2GCPjSf3J728rv5SS',NULL,NULL,NULL,NULL,NULL,16,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:10','2025-09-29 02:36:09',NULL),(244,236,'Hoàng Văn Thành','hvthanh','hvthanh@a31.com.vn','$2y$12$.VTa3m/BatShmEY9ediAfe0iW7jzhK2nd0FTZq8MVOv6Aoy2ERuFS',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:10','2025-09-29 02:36:09',NULL),(245,237,'Vũ Thị Liên','vtlien','vtlien@a31.com.vn','$2y$12$Haj0X4nzOx0HJYHEOPzuZOsHFRIQunuwoTol/9OJbBksI5VxrcYfy',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:11','2025-09-29 02:36:09',NULL),(246,238,'Khuất Duy Mạnh','kdmanh','kdmanh@a31.com.vn','$2y$12$XCeGyQeDHw/H7wkZDWlWeugA6vTWbqBJOsHmeBiV5xdjTNgoIv/MK',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:11','2025-09-29 02:36:09',NULL),(247,239,'Nguyễn Thị Duyên','ntduyen','ntduyen@a31.com.vn','$2y$12$DbEwWQhjCVo/NJfrjFJnYOZQkoXyPUEjmQzGvpXfcjhBcrjvjIA32',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:11','2025-09-29 02:36:09',NULL),(248,241,'Đinh Thị Thành','dtthanh','dtthanh@a31.com.vn','$2y$12$fWb6mapmSGz1e/jll6Ta1uXIrZdv1FxRqIoXo1vXXI8BC5Y9gjP.6',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:11','2025-09-29 02:36:09',NULL),(249,242,'Lương T Thanh Loan','ltloan','ltloan@a31.com.vn','$2y$12$RqOSL6O/2asExED8k.ohmONziMSaTdl5JvnQUQpL9nB59Yx16FnLG',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:11','2025-09-29 02:36:09',NULL),(250,243,'Phan Thanh Trường','pttruong','pttruong@a31.com.vn','$2y$12$VE9FDmVi8pJPhT16eVNwzuqSo5yC1s8H7VYHzRGNTT5faw4NKKdd.',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:12','2025-09-29 02:36:09',NULL),(251,244,'Mai Hồng Sơn','mhson','mhson@a31.com.vn','$2y$12$Xtfc8/u7VdMRLCv8v/S.bu8194bdxlGyXPRu7z.jWWiEAQgk/Q2TK',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:12','2025-09-29 02:36:09',NULL),(252,247,'Trần Ngọc Quang','tnquang','tnquang@a31.com.vn','$2y$12$NS8EM/tK6rNpupkMyi04A.cs/8VM5imfDqTkuSgr8fVE0026GDI1S',NULL,NULL,NULL,NULL,NULL,17,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:12','2025-09-29 02:36:09',NULL),(253,248,'Phạm Trường Giang','ptgiang','ptgiang@a31.com.vn','$2y$12$bs5lqmf2o2ABAEOUq84jt.rODaP1Fex37bExlGpqDIMvolmht6GOi',NULL,NULL,NULL,NULL,NULL,18,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:12','2025-09-29 02:36:09',NULL),(254,250,'Bùi Văn Khởi','bvkhoi','bvkhoi@a31.com.vn','$2y$12$YjprM09nCQ3Vj8dLk1Jzp.7FEjrI4G3LxcFXX8PQVGspZfjE3CXD6',NULL,NULL,NULL,NULL,NULL,18,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:13','2025-09-29 02:36:09',NULL),(255,251,'Cao Văn Tuyển','cvtuyen','cvtuyen@a31.com.vn','$2y$12$UH.FPAUWbtbi51Og44xzG.wP1/33KeVSj1JwNVvSY4MNOSV2zb1LG',NULL,NULL,NULL,NULL,NULL,18,'staff',0,NULL,NULL,NULL,NULL,'2025-09-26 01:34:13','2025-09-29 02:36:09',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicle_registrations`
--

DROP TABLE IF EXISTS `vehicle_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle_registrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `vehicle_id` bigint unsigned DEFAULT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `return_time` time DEFAULT NULL,
  `departure_datetime` datetime DEFAULT NULL,
  `return_datetime` datetime DEFAULT NULL,
  `route` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `passenger_count` int DEFAULT '1',
  `cargo_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `driver_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `driver_license` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','dept_approved','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `workflow_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `department_approved_by` bigint unsigned DEFAULT NULL,
  `department_approved_at` timestamp NULL DEFAULT NULL,
  `digital_signature_dept` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `director_approved_by` bigint unsigned DEFAULT NULL,
  `director_approved_at` timestamp NULL DEFAULT NULL,
  `digital_signature_director` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `signed_pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `rejection_level` enum('department','director') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle_registrations`
--

LOCK TABLES `vehicle_registrations` WRITE;
/*!40000 ALTER TABLE `vehicle_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `vehicle_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `license_plate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fuel_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `status` enum('available','in_use','maintenance','broken') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'available',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deleted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_plate` (`license_plate`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicles`
--

LOCK TABLES `vehicles` WRITE;
/*!40000 ALTER TABLE `vehicles` DISABLE KEYS */;
INSERT INTO `vehicles` VALUES (1,'Xe chỉ huy 2 cầu UAZ-31512','Xe chỉ huy 2 cầu','QA-14-73',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:19',NULL),(2,'Xe chỉ huy 1 cầu TOYOTA COROLLA 1.6','Xe chỉ huy 1 cầu','QA-14-91',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:19',NULL),(3,'Xe chỉ huy 1 cầu TOYOTA INNOVA','Xe chỉ huy 1 cầu','QA-59-79',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:19',NULL),(4,'Xe TOYOTA-FORTUNER','Xe chỉ huy 1 cầu','QA-76-66',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:19',NULL),(5,'Xe TOYOTA-FORTUNER','Xe chỉ huy 1 cầu','QA-79-89',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(6,'Xe vận tải 1 cầu ZIL-130','Xe vận tải 1 cầu','QA-14-80',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(7,'Xe vận tải 1 cầu ISUZU NQR-75K','Xe vận tải 1 cầu','QA-70-58',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(8,'Xe vận tải 1 cầu KIA 2700','Xe vận tải 1 cầu','QA-68-99',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(9,'Xe vận tải 2 cầu KRAZ-257','Xe vận tải 2 cầu','QA-14-88',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(10,'Xe vận tải 3 cầu ZIL-131','Xe vận tải','QA-31-85',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(11,'Xe ca PAZ-320547','Xe ca','QA-14-92',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(12,'Xe ca FORD TRANSIT','Xe ca','QA-60-54',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(13,'Xe ca 15 chỗ ngồi TOYOTA HIACE','Xe ca','QA-14-90',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(14,'Xe ca 16 chỗ ngồi TOYOTA HIACE','Xe ca','QA-69-68',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(15,'Xe ca ISUZU-NRF66R','Xe ca','QA-14-72',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(16,'Xe ca SAMCO 34 C','Xe ca','QA-77-39',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(17,'Xe ca HYUNDAI COUNTY','Xe ca','QA-70-59',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(18,'Xe cứu thương UAZ-3962-016','Xe cứu thương','QA-46-05',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(19,'Xe cứu thương HYUNDAI STAREX','Xe cứu thương','QA-61-82',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(20,'Xe chữa cháy ZIL-157','Xe chữa cháy','QA-14-76',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(21,'Xe chữa cháy DONGFENG','Xe chữa cháy','QA-58-16',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(22,'Xe cần trục KC-327/ISUZU','Xe cần trục','QA-14-71',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(23,'Xe cần trục XCMG/QY25','Xe cần trục','QA-62-95',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(24,'Xe kiểm tra đạn tên lửa KIPS-5К-21/ZIL-131, Liên Xô','Xe chuyên dùng Tên lửa','QA-14-86',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(25,'Xe kiểm tra đạn tên lửa KIPS-V2-75M/ZIL-131, Liên Xô','Xe chuyên dùng Tên lửa','QA-14-87',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:53:20',NULL),(26,'Rơ moóc','Moóc kéo','MTZ - 80',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:46:43','2025-09-05 02:46:43',NULL),(27,'Xe nâng TCM-4,0 - FD40T9','Xe nâng hàng','QA-',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System','System','2025-09-05 02:52:48','2025-09-05 02:53:51','2025-09-05 02:53:51'),(28,'Xe nâng TCM-4,0 - FD40T9','Xe nâng hàng','XN-001',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:53:03','2025-09-05 02:53:03',NULL),(29,'Xe nâng TOYOTAF8F050N','Xe nâng hàng','XN-002',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:53:03','2025-09-05 02:53:03',NULL),(30,'Xe nâng FB10-MQC2','Xe nâng hàng','XN-003',NULL,NULL,NULL,NULL,NULL,NULL,'available',NULL,1,'System','System',NULL,'2025-09-05 02:53:03','2025-09-05 02:53:03',NULL);
/*!40000 ALTER TABLE `vehicles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhook_calls`
--

DROP TABLE IF EXISTS `webhook_calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhook_calls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` json DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `exception` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhook_calls`
--

LOCK TABLES `webhook_calls` WRITE;
/*!40000 ALTER TABLE `webhook_calls` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhook_calls` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-08 10:10:42
