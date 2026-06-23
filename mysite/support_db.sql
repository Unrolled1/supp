-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 23, 2026 at 12:41 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `support_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `created_at`) VALUES
(6, 'یشی', '1405-03-16 17:11:53'),
(5, 'ی', '1405-03-15 23:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `created_at`) VALUES
(10, 'Western Digital', '2026-06-19'),
(7, 'Intel', '2026-06-19'),
(9, 'Samsung', '2026-06-19'),
(11, 'Kingston', '2026-06-19'),
(12, 'Corsair', '2026-06-19'),
(13, 'G.Skill', '2026-06-19'),
(36, 'Gplus', '1405-03-30'),
(15, 'MSI', '2026-06-19'),
(16, 'Gigabyte', '2026-06-19'),
(17, 'Cooler Master', '2026-06-19'),
(18, 'Seasonic', '2026-06-19'),
(19, 'LG', '2026-06-19'),
(20, 'Dell', '2026-06-19');

-- --------------------------------------------------------

--
-- Table structure for table `cpus`
--

DROP TABLE IF EXISTS `cpus`;
CREATE TABLE IF NOT EXISTS `cpus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cpus`
--

INSERT INTO `cpus` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
(8, 36, 3, '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(5, 'زنان', '', 'active', '1405-03-31');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` bigint DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` date NOT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kala`
--

DROP TABLE IF EXISTS `kala`;
CREATE TABLE IF NOT EXISTS `kala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `computer_code` varchar(100) DEFAULT NULL,
  `property_code` varchar(100) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `department_id` int DEFAULT NULL,
  `receiver_person_id` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `brand_id` int DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
CREATE TABLE IF NOT EXISTS `models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` int DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_models_brand` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`id`, `name`, `brand_id`, `created_at`) VALUES
(3, 'GDM', 36, '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `monitors`
--

DROP TABLE IF EXISTS `monitors`;
CREATE TABLE IF NOT EXISTS `monitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `monitors`
--

INSERT INTO `monitors` (`id`, `brand_id`, `model_id`, `property_code`, `created_at`) VALUES
(1, 13, 3, '1', '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `motherboards`
--

DROP TABLE IF EXISTS `motherboards`;
CREATE TABLE IF NOT EXISTS `motherboards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `motherboards`
--

INSERT INTO `motherboards` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
(1, 10, 3, '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `peripherals`
--

DROP TABLE IF EXISTS `peripherals`;
CREATE TABLE IF NOT EXISTS `peripherals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `computer_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `model_id` int DEFAULT NULL,
  `connection_type` enum('USB','Network','Bluetooth','Parallel','Wireless','Other') COLLATE utf8mb4_unicode_ci DEFAULT 'USB',
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peripheral_types`
--

DROP TABLE IF EXISTS `peripheral_types`;
CREATE TABLE IF NOT EXISTS `peripheral_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peripheral_types`
--

INSERT INTO `peripheral_types` (`id`, `name`, `sort_order`) VALUES
(1, 'پرینتر', 1),
(2, 'بارکدخوان', 2),
(3, 'اسکنر', 3),
(4, 'کارت‌خوان', 4),
(5, 'وب‌کم', 5);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` enum('admin','support','user') DEFAULT 'user',
  `permission_key` varchar(100) NOT NULL,
  `permission_value` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission` (`role`,`permission_key`)
) ENGINE=MyISAM AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `role`, `permission_key`, `permission_value`) VALUES
(22, 'admin', 'tickets_manage', 1),
(23, 'admin', 'tickets_edit', 1),
(24, 'admin', 'tickets_delete', 1),
(25, 'admin', 'users_manage', 1),
(26, 'admin', 'users_edit', 1),
(27, 'admin', 'users_delete', 1),
(28, 'admin', 'departments_manage', 1),
(29, 'admin', 'departments_edit', 1),
(30, 'admin', 'departments_delete', 1),
(31, 'admin', 'topics_manage', 1),
(32, 'admin', 'topics_edit', 1),
(33, 'admin', 'topics_delete', 1),
(35, 'admin', 'brands_view', 1),
(36, 'admin', 'brands_edit', 1),
(37, 'admin', 'brands_delete', 1),
(38, 'admin', 'products_view', 1),
(39, 'admin', 'products_edit', 1),
(40, 'admin', 'products_delete', 1),
(41, 'admin', 'brands_manage', 1),
(42, 'admin', 'products_manage', 1),
(43, 'admin', 'activities_manage', 1),
(44, 'admin', 'activities_edit', 1),
(45, 'admin', 'activities_delete', 1),
(46, 'admin', 'reports_view', 1),
(47, 'admin', 'models_view', 1),
(48, 'admin', 'models_edit', 1),
(49, 'admin', 'models_delete', 1),
(50, 'admin', 'persons_view', 1),
(51, 'admin', 'persons_edit', 1),
(52, 'admin', 'persons_delete', 1),
(53, 'admin', 'services_view', 1),
(54, 'admin', 'services_edit', 1),
(55, 'admin', 'services_delete', 1),
(56, 'admin', 'invoices_view', 1),
(57, 'admin', 'invoices_manage', 1),
(58, 'admin', 'invoices_edit', 1),
(59, 'admin', 'invoices_delete', 1),
(60, 'admin', 'goods_view', 1),
(61, 'admin', 'goods_manage', 1),
(62, 'admin', 'goods_edit', 1),
(63, 'admin', 'goods_delete', 1),
(64, 'admin', 'printers_view', 1),
(65, 'admin', 'printers_manage', 1),
(66, 'admin', 'printers_edit', 1),
(67, 'admin', 'printers_delete', 1),
(68, 'admin', 'systems_view', 1),
(69, 'admin', 'systems_manage', 1),
(70, 'admin', 'systems_edit', 1),
(71, 'admin', 'systems_delete', 1);

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
CREATE TABLE IF NOT EXISTS `persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`id`, `name`, `created_at`) VALUES
(3, 'mmd', '1405-03-15 21:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `powers`
--

DROP TABLE IF EXISTS `powers`;
CREATE TABLE IF NOT EXISTS `powers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `powers`
--

INSERT INTO `powers` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
(1, 15, 3, '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

DROP TABLE IF EXISTS `printers`;
CREATE TABLE IF NOT EXISTS `printers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `computer_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rams`
--

DROP TABLE IF EXISTS `rams`;
CREATE TABLE IF NOT EXISTS `rams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rams`
--

INSERT INTO `rams` (`id`, `brand_id`, `model_id`, `type`, `capacity`, `created_at`) VALUES
(1, 20, 3, 'DDR3', '8GB', '1405-03-30'),
(2, 13, 3, 'DDR4', '64GB', '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `receiver_person_id` int DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_date` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `computer_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  KEY `brand_id` (`brand_id`),
  KEY `receiver_person_id` (`receiver_person_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_service_name` (`service_name`),
  KEY `idx_service_date` (`service_date`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_name_status` (`service_name`),
  KEY `idx_date_status` (`service_date`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storages`
--

DROP TABLE IF EXISTS `storages`;
CREATE TABLE IF NOT EXISTS `storages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `storages`
--

INSERT INTO `storages` (`id`, `brand_id`, `model_id`, `type`, `capacity`, `created_at`) VALUES
(1, 17, 3, 'HDD', '512GB', '1405-03-30');

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

DROP TABLE IF EXISTS `systems`;
CREATE TABLE IF NOT EXISTS `systems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `computer_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `cpu_id` int DEFAULT NULL,
  `motherboard_id` int DEFAULT NULL,
  `power_id` int DEFAULT NULL,
  `monitor_id` int DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `computer_code` (`computer_code`),
  KEY `department_id` (`department_id`),
  KEY `cpu_id` (`cpu_id`),
  KEY `motherboard_id` (`motherboard_id`),
  KEY `power_id` (`power_id`),
  KEY `monitor_id` (`monitor_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `systems`
--

INSERT INTO `systems` (`id`, `computer_code`, `property_code`, `name`, `department_id`, `cpu_id`, `motherboard_id`, `power_id`, `monitor_id`, `created_at`, `created_by`) VALUES
(11, '3', '', '3', NULL, NULL, NULL, NULL, NULL, '1405-03-30', 1),
(12, '4', '', '4', NULL, NULL, NULL, NULL, NULL, '1405-03-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `system_ips`
--

DROP TABLE IF EXISTS `system_ips`;
CREATE TABLE IF NOT EXISTS `system_ips` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `network_type` enum('LAN','WAN','VPN','WiFi','Other') COLLATE utf8mb4_unicode_ci DEFAULT 'LAN',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_peripherals`
--

DROP TABLE IF EXISTS `system_peripherals`;
CREATE TABLE IF NOT EXISTS `system_peripherals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `peripheral_id` int NOT NULL,
  `connection_port` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_system_peripheral` (`system_id`,`peripheral_id`),
  KEY `peripheral_id` (`peripheral_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_rams`
--

DROP TABLE IF EXISTS `system_rams`;
CREATE TABLE IF NOT EXISTS `system_rams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `ram_id` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`),
  KEY `ram_id` (`ram_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_rams`
--

INSERT INTO `system_rams` (`id`, `system_id`, `ram_id`, `description`, `created_at`, `created_by`) VALUES
(2, 9, 1, NULL, '1405-03-30', 1),
(3, 10, 1, NULL, '1405-03-30', 1),
(4, 11, 1, NULL, '1405-03-30', 1),
(5, 12, 2, NULL, '1405-03-30', 1),
(6, 12, 1, NULL, '1405-03-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `system_storages`
--

DROP TABLE IF EXISTS `system_storages`;
CREATE TABLE IF NOT EXISTS `system_storages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `storage_id` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`),
  KEY `storage_id` (`storage_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_storages`
--

INSERT INTO `system_storages` (`id`, `system_id`, `storage_id`, `description`, `created_at`, `created_by`) VALUES
(2, 9, 1, NULL, '1405-03-30', 1),
(3, 10, 1, NULL, '1405-03-30', 1),
(4, 11, 1, NULL, '1405-03-30', 1),
(5, 12, 1, NULL, '1405-03-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('جدید','در حال بررسی','پاسخ داده شده','بسته شده') COLLATE utf8mb4_unicode_ci DEFAULT 'جدید',
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `tracking_code`, `user_id`, `fullname`, `subject`, `department_id`, `message`, `status`, `created_at`) VALUES
(20, 'TK-14050331-7721', 25, '2', 'درخواست دسترسی', 5, '2', 'جدید', '1405-03-31');

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE IF NOT EXISTS `topics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `department_id` int DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `sort_order` int DEFAULT '0',
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `name`, `description`, `department_id`, `status`, `sort_order`, `created_at`) VALUES
(1, 'مشکل در نرم‌افزار', 'مشکلات مربوط به نرم‌افزارهای بیمارستانی', NULL, 'active', 1, '2026-05-20 21:51:29'),
(2, 'مشکل سخت‌افزاری', 'مشکلات مربوط به کامپیوتر و تجهیزات', NULL, 'active', 2, '2026-05-20 21:51:29'),
(3, 'مشکل شبکه', 'مشکلات مربوط به اینترنت و شبکه داخلی', NULL, 'active', 3, '2026-05-20 21:51:29'),
(4, 'درخواست دسترسی', 'درخواست دسترسی به سیستم‌های مختلف', NULL, 'active', 4, '2026-05-20 21:51:29'),
(5, 'گزارش خطا', 'گزارش خطاهای سیستمی', NULL, 'active', 5, '2026-05-20 21:51:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', 'c4ca4238a0b923820dcc509a6f75849b', 'علی محمد', 'admin', '1404-03-11'),
(27, 'd', 'c4ca4238a0b923820dcc509a6f75849b', 'd', 'admin', '1405-03-17'),
(25, 'a', 'c4ca4238a0b923820dcc509a6f75849b', 'a', 'user', '1405-03-14 21:08:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_value` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`)
) ENGINE=MyISAM AUTO_INCREMENT=768 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `permission_value`) VALUES
(765, 27, 'users_delete', 0),
(764, 27, 'topics_manage', 0),
(63, 1, 'departments_delete', 1),
(763, 27, 'topics_edit', 0),
(762, 27, 'topics_delete', 0),
(761, 27, 'tickets_manage', 1),
(760, 27, 'tickets_edit', 1),
(759, 27, 'tickets_delete', 1),
(747, 27, 'products_delete', 0),
(64, 1, 'departments_edit', 1),
(65, 1, 'departments_manage', 1),
(66, 1, 'tickets_delete', 1),
(67, 1, 'tickets_edit', 1),
(68, 1, 'tickets_manage', 1),
(69, 1, 'topics_delete', 1),
(70, 1, 'topics_edit', 1),
(71, 1, 'topics_manage', 1),
(72, 1, 'users_delete', 1),
(73, 1, 'users_edit', 1),
(74, 1, 'users_manage', 1),
(758, 27, 'systems_view', 1),
(757, 27, 'systems_manage', 1),
(756, 27, 'systems_edit', 1),
(755, 27, 'systems_delete', 1),
(754, 27, 'services_view', 1),
(753, 27, 'services_edit', 1),
(752, 27, 'services_delete', 1),
(751, 27, 'reports_view', 0),
(750, 27, 'products_view', 0),
(749, 27, 'products_manage', 0),
(748, 27, 'products_edit', 0),
(746, 27, 'printers_view', 0),
(745, 27, 'printers_manage', 0),
(744, 27, 'printers_edit', 0),
(743, 27, 'printers_delete', 0),
(742, 27, 'persons_view', 0),
(741, 27, 'persons_edit', 0),
(740, 27, 'persons_delete', 0),
(739, 27, 'models_view', 0),
(738, 27, 'models_edit', 0),
(737, 27, 'models_delete', 0),
(736, 27, 'invoices_view', 0),
(735, 27, 'invoices_manage', 0),
(734, 27, 'invoices_edit', 0),
(733, 27, 'invoices_delete', 0),
(732, 27, 'goods_view', 0),
(731, 27, 'goods_manage', 0),
(730, 27, 'goods_edit', 0),
(729, 27, 'goods_delete', 0),
(728, 27, 'departments_manage', 0),
(727, 27, 'departments_edit', 0),
(726, 27, 'departments_delete', 0),
(725, 27, 'brands_view', 0),
(724, 27, 'brands_manage', 0),
(723, 27, 'brands_edit', 0),
(722, 27, 'brands_delete', 0),
(721, 27, 'activities_manage', 0),
(720, 27, 'activities_edit', 0),
(719, 27, 'activities_delete', 0),
(766, 27, 'users_edit', 0),
(767, 27, 'users_manage', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
