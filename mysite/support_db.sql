-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 01, 2026 at 12:20 PM
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
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `name`, `created_at`) VALUES
(17, 'aaa', '1405/04/03');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `created_at`) VALUES
(39, 'Gplus', '1405/04/03'),
(38, 'LG', '1405/04/03'),
(37, 'Dell', '1405/04/03');

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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `status`, `created_at`) VALUES
(12, 'd', 'active', '1405/04/05');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_number` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` bigint DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` date NOT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `company_name`, `invoice_number`, `subject`, `amount`, `invoice_date`, `description`, `created_at`, `created_by`) VALUES
(12, 'ی', '3', 'درخواست دسترسی', 1, '2026-06-28', '', '1405-04-07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kala`
--

DROP TABLE IF EXISTS `kala`;
CREATE TABLE IF NOT EXISTS `kala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `computer_code` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `property_code` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `receiver_person_id` int DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `brand_id` int DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
CREATE TABLE IF NOT EXISTS `models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `brand_id` int DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_models_brand` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`id`, `name`, `brand_id`, `created_at`) VALUES
(8, 'لب', 37, '1405-04-07');

-- --------------------------------------------------------

--
-- Table structure for table `monitors`
--

DROP TABLE IF EXISTS `monitors`;
CREATE TABLE IF NOT EXISTS `monitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `monitors`
--

INSERT INTO `monitors` (`id`, `brand_id`, `model_id`, `property_code`, `created_at`) VALUES
(2, 39, 8, '2', '1405-04-09');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motherboards`
--

INSERT INTO `motherboards` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
(2, 39, 8, '1405-04-09');

-- --------------------------------------------------------

--
-- Table structure for table `peripherals`
--

DROP TABLE IF EXISTS `peripherals`;
CREATE TABLE IF NOT EXISTS `peripherals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `computer_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `model_id` int DEFAULT NULL,
  `connection_type` enum('USB','Network','Bluetooth','Parallel','Wireless','Other') COLLATE utf8mb4_general_ci DEFAULT 'USB',
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peripherals`
--

INSERT INTO `peripherals` (`id`, `type_id`, `computer_code`, `property_code`, `brand_id`, `model_id`, `connection_type`, `created_at`, `created_by`) VALUES
(2, 1, '', '2', 37, 8, 'USB', '1405-04-09', 1),
(3, 1, '', '3', 39, 8, 'Network', '1405-04-09', 1),
(4, 2, '', '3', 37, 8, 'USB', '1405-04-09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `peripheral_types`
--

DROP TABLE IF EXISTS `peripheral_types`;
CREATE TABLE IF NOT EXISTS `peripheral_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `role` enum('admin','support','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `permission_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `permission_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission` (`role`,`permission_key`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `role`, `permission_key`, `permission_name`, `group_name`) VALUES
(1, 'admin', 'activities_view', 'مشاهده فعالیت', 'تعاریف'),
(2, 'admin', 'activities_edit', 'ویرایش فعالیت', 'تعاریف'),
(3, 'admin', 'activities_delete', 'حذف فعالیت', 'تعاریف'),
(4, 'admin', 'departments_view', 'مشاهده بخش', 'تعاریف'),
(5, 'admin', 'departments_edit', 'ویرایش بخش', 'تعاریف'),
(6, 'admin', 'departments_delete', 'حذف بخش', 'تعاریف'),
(7, 'admin', 'topics_view', 'مشاهده موضوع', 'تعاریف'),
(8, 'admin', 'topics_edit', 'ویرایش موضوع', 'تعاریف'),
(9, 'admin', 'topics_delete', 'حذف موضوع', 'تعاریف'),
(10, 'admin', 'brands_view', 'مشاهده برند', 'تعاریف'),
(11, 'admin', 'brands_edit', 'ویرایش برند', 'تعاریف'),
(12, 'admin', 'brands_delete', 'حذف برند', 'تعاریف'),
(13, 'admin', 'models_view', 'مشاهده مدل', 'تعاریف'),
(14, 'admin', 'models_edit', 'ویرایش مدل', 'تعاریف'),
(15, 'admin', 'models_delete', 'حذف مدل', 'تعاریف'),
(16, 'admin', 'products_view', 'مشاهده کالا', 'تعاریف'),
(17, 'admin', 'products_edit', 'ویرایش کالا', 'تعاریف'),
(18, 'admin', 'products_delete', 'حذف کالا', 'تعاریف'),
(19, 'admin', 'persons_view', 'مشاهده شخص', 'تعاریف'),
(20, 'admin', 'persons_edit', 'ویرایش شخص', 'تعاریف'),
(21, 'admin', 'persons_delete', 'حذف شخص', 'تعاریف'),
(22, 'admin', 'tickets_view', 'مشاهده درخواست', 'عملیات'),
(23, 'admin', 'tickets_edit', 'ویرایش درخواست', 'عملیات'),
(24, 'admin', 'tickets_delete', 'حذف درخواست', 'عملیات'),
(25, 'admin', 'services_view', 'مشاهده فعالیت', 'عملیات'),
(26, 'admin', 'services_edit', 'ویرایش فعالیت', 'عملیات'),
(27, 'admin', 'services_delete', 'حذف فعالیت', 'عملیات'),
(28, 'admin', 'invoices_view', 'مشاهده فاکتورها', 'عملیات'),
(29, 'admin', 'invoices_edit', 'ویرایش فاکتور', 'عملیات'),
(30, 'admin', 'invoices_delete', 'حذف فاکتور', 'عملیات'),
(31, 'admin', 'kala_view', 'مشاهده کالاها', 'عملیات'),
(32, 'admin', 'kala_edit', 'ویرایش کالا', 'عملیات'),
(33, 'admin', 'kala_delete', 'حذف کالا', 'عملیات'),
(34, 'admin', 'printers_view', 'مشاهده پرینترها', 'عملیات'),
(35, 'admin', 'printers_edit', 'ویرایش پرینتر', 'عملیات'),
(36, 'admin', 'printers_delete', 'حذف پرینتر', 'عملیات'),
(37, 'admin', 'systems_view', 'مشاهده سیستم‌ها', 'عملیات'),
(38, 'admin', 'systems_edit', 'ویرایش سیستم', 'عملیات'),
(39, 'admin', 'systems_delete', 'حذف سیستم', 'عملیات'),
(40, 'admin', 'users_view', 'مشاهده کاربران', 'کاربران'),
(41, 'admin', 'users_edit', 'ویرایش کاربر', 'کاربران'),
(42, 'admin', 'users_delete', 'حذف کاربر', 'کاربران'),
(43, 'admin', 'reports_view', 'مشاهده گزارشات', 'گزارشات');

-- --------------------------------------------------------

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
CREATE TABLE IF NOT EXISTS `persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `powers`
--

INSERT INTO `powers` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
(2, 39, 8, '1405-04-09');

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

DROP TABLE IF EXISTS `printers`;
CREATE TABLE IF NOT EXISTS `printers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `computer_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activity_id` int DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `brand_id`, `name`, `created_at`) VALUES
(9, 39, 'شd', '1405/04/04'),
(10, 37, 'ش', '1405/04/04');

-- --------------------------------------------------------

--
-- Table structure for table `rams`
--

DROP TABLE IF EXISTS `rams`;
CREATE TABLE IF NOT EXISTS `rams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacity` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rams`
--

INSERT INTO `rams` (`id`, `brand_id`, `model_id`, `type`, `capacity`, `created_at`) VALUES
(7, 37, 8, 'DDR4', '4GB', '1405-04-09');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `receiver_person_id` int DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_date` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `computer_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storages`
--

DROP TABLE IF EXISTS `storages`;
CREATE TABLE IF NOT EXISTS `storages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacity` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storages`
--

INSERT INTO `storages` (`id`, `brand_id`, `model_id`, `type`, `capacity`, `created_at`) VALUES
(1, 17, 3, 'HDD', '512GB', '1405-03-30'),
(3, 37, 8, '', '128GB', '1405-04-09');

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

DROP TABLE IF EXISTS `systems`;
CREATE TABLE IF NOT EXISTS `systems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `computer_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_ips`
--

DROP TABLE IF EXISTS `system_ips`;
CREATE TABLE IF NOT EXISTS `system_ips` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `network_type` enum('LAN','WAN','VPN','WiFi','Other') COLLATE utf8mb4_general_ci DEFAULT 'LAN',
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_peripherals`
--

DROP TABLE IF EXISTS `system_peripherals`;
CREATE TABLE IF NOT EXISTS `system_peripherals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `peripheral_id` int NOT NULL,
  `connection_port` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_system_peripheral` (`system_id`,`peripheral_id`),
  KEY `peripheral_id` (`peripheral_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_rams`
--

DROP TABLE IF EXISTS `system_rams`;
CREATE TABLE IF NOT EXISTS `system_rams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `ram_id` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`),
  KEY `ram_id` (`ram_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_rams`
--

INSERT INTO `system_rams` (`id`, `system_id`, `ram_id`, `description`, `created_at`, `created_by`) VALUES
(7, 13, 3, NULL, '1405-04-09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `system_storages`
--

DROP TABLE IF EXISTS `system_storages`;
CREATE TABLE IF NOT EXISTS `system_storages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `system_id` int NOT NULL,
  `storage_id` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`),
  KEY `storage_id` (`storage_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `department_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('جدید','در حال بررسی','پاسخ داده شده','بسته شده') COLLATE utf8mb4_general_ci DEFAULT 'جدید',
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `name`, `created_at`) VALUES
(22, 'مشکل در نرم افزار', '1405/04/03'),
(21, 'مشکل سخت افزاری', '1405/04/03'),
(20, 'مشکل شبکه', '1405/04/03'),
(19, 'درخواست دسترسی', '1405/04/03'),
(18, 'گزارش خطا', '1405/04/03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', 'c4ca4238a0b923820dcc509a6f75849b', 'علی محمد', 'admin', '1404-03-11'),
(34, 'd', '8277e0910d750195b448797616e091ad', 'd', 'admin', '1405/04/05'),
(25, 'a', 'c4ca4238a0b923820dcc509a6f75849b', 'a', 'user', '1405-03-14 21:08:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `permission_value` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`)
) ENGINE=MyISAM AUTO_INCREMENT=1659 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `permission_value`) VALUES
(1262, 1, 'topics_edit', 1),
(1261, 1, 'topics_delete', 1),
(1255, 1, 'systems_delete', 1),
(1260, 1, 'tickets_view', 1),
(1259, 1, 'tickets_edit', 1),
(1258, 1, 'tickets_delete', 1),
(1257, 1, 'systems_view', 1),
(1256, 1, 'systems_edit', 1),
(1254, 1, 'services_view', 1),
(1253, 1, 'services_edit', 1),
(1252, 1, 'services_delete', 1),
(1251, 1, 'reports_view', 1),
(1250, 1, 'products_view', 1),
(1249, 1, 'products_edit', 1),
(1248, 1, 'products_delete', 1),
(1247, 1, 'printers_view', 1),
(1246, 1, 'printers_edit', 1),
(1225, 1, 'activities_edit', 1),
(1226, 1, 'activities_view', 1),
(1227, 1, 'brands_delete', 1),
(1228, 1, 'brands_edit', 1),
(1229, 1, 'brands_view', 1),
(1230, 1, 'departments_delete', 1),
(1231, 1, 'departments_edit', 1),
(1232, 1, 'departments_view', 1),
(1233, 1, 'invoices_delete', 1),
(1234, 1, 'invoices_edit', 1),
(1235, 1, 'invoices_view', 1),
(1236, 1, 'kala_delete', 1),
(1237, 1, 'kala_edit', 1),
(1238, 1, 'kala_view', 1),
(1239, 1, 'models_delete', 1),
(1240, 1, 'models_edit', 1),
(1241, 1, 'models_view', 1),
(1242, 1, 'persons_delete', 1),
(1243, 1, 'persons_edit', 1),
(1244, 1, 'persons_view', 1),
(1245, 1, 'printers_delete', 1),
(1224, 1, 'activities_delete', 1),
(1655, 34, 'reports', 1),
(1654, 34, 'reports_section', 0),
(1653, 34, 'systems_delete', 0),
(1652, 34, 'systems_edit', 0),
(1651, 34, 'systems_view', 0),
(1650, 34, 'systems', 0),
(1649, 34, 'printers_delete', 0),
(1648, 34, 'printers_edit', 0),
(1647, 34, 'printers_view', 0),
(1646, 34, 'printers', 0),
(1645, 34, 'kala_delete', 0),
(1644, 34, 'kala_edit', 0),
(1643, 34, 'kala_view', 0),
(1642, 34, 'goods', 0),
(1641, 34, 'invoices', 0),
(1640, 34, 'invoices_delete', 0),
(1639, 34, 'invoices_edit', 0),
(1638, 34, 'invoices_view', 0),
(1637, 34, 'services', 0),
(1263, 1, 'topics_view', 1),
(1264, 1, 'users_delete', 1),
(1265, 1, 'users_edit', 1),
(1266, 1, 'users_view', 1),
(1636, 34, 'tickets_delete', 0),
(1635, 34, 'tickets_edit', 0),
(1634, 34, 'tickets_view', 0),
(1633, 34, 'tickets', 0),
(1632, 34, 'operations', 0),
(1631, 34, 'users_delete', 0),
(1630, 34, 'users_edit', 0),
(1629, 34, 'users_view', 0),
(1628, 34, 'users', 0),
(1627, 34, 'persons_delete', 0),
(1625, 34, 'persons_view', 0),
(1626, 34, 'persons_edit', 0),
(1624, 34, 'persons', 0),
(1623, 34, 'products_delete', 0),
(1622, 34, 'products_edit', 0),
(1621, 34, 'products_view', 0),
(1620, 34, 'products', 0),
(1619, 34, 'models_delete', 0),
(1618, 34, 'models_edit', 0),
(1617, 34, 'models_view', 0),
(1616, 34, 'models', 0),
(1615, 34, 'brands_delete', 0),
(1614, 34, 'brands_edit', 0),
(1613, 34, 'brands_view', 0),
(1612, 34, 'brands', 0),
(1611, 34, 'topics_delete', 0),
(1610, 34, 'topics_edit', 0),
(1609, 34, 'topics_view', 0),
(1608, 34, 'topics', 0),
(1607, 34, 'departments_delete', 0),
(1606, 34, 'departments_edit', 0),
(1605, 34, 'departments_view', 0),
(1604, 34, 'departments', 0),
(1603, 34, 'activities_delete', 0),
(1602, 34, 'activities_edit', 0),
(1601, 34, 'activities_view', 1),
(1600, 34, 'activities', 0),
(1599, 34, 'definitions', 0),
(1656, 34, 'reports_view', 1),
(1657, 34, 'backup', 0),
(1658, 34, 'backup_view', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
