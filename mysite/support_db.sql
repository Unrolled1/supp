-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 16, 2026 at 08:19 PM
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
  `name` varchar(100) NOT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `name` varchar(100) NOT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `amount` bigint DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `description` text,
  `created_at` date NOT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `company_name`, `invoice_number`, `subject`, `amount`, `invoice_date`, `description`, `created_at`, `created_by`) VALUES
(3, 'ش', '2', 'درخواست دسترسی', 1, '2026-06-15', 'ddddddddddddddddddd', '1405-03-25', 1),
(4, 'ب', '3', 'گزارش خطا', 1000, '2026-06-15', '', '1405-03-25', 1),
(6, 'ت', '8', 'مشکل در نرم‌افزار', 1220, '2026-06-15', '', '1405-03-25', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kala`
--

INSERT INTO `kala` (`id`, `computer_code`, `property_code`, `name`, `department_id`, `receiver_person_id`, `quantity`, `brand_id`, `serial_number`, `created_at`, `created_by`) VALUES
(1, '', '', 'غ', NULL, NULL, 1, NULL, '', '1405-03-26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

DROP TABLE IF EXISTS `models`;
CREATE TABLE IF NOT EXISTS `models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`id`, `name`, `created_at`) VALUES
(1, 'ش', '1405-03-15 11:26:09');

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
  `name` varchar(100) NOT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `persons`
--

INSERT INTO `persons` (`id`, `name`, `created_at`) VALUES
(3, 'mmd', '1405-03-15 21:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `department_id` int DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `receiver_person_id` int DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `service_date` varchar(50) DEFAULT NULL,
  `computer_code` varchar(50) DEFAULT NULL,
  `description` text,
  `created_at` varchar(50) DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `service_name`, `department_id`, `brand_id`, `receiver_person_id`, `serial_number`, `service_date`, `computer_code`, `description`, `created_at`, `created_by`) VALUES
(33, 'یشی', NULL, NULL, NULL, '', '2026-06-07', '', '', '1405-03-17', 27),
(35, 'ی', NULL, NULL, NULL, '1', '2026-06-11', '', '', '1405-03-21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(20) NOT NULL,
  `user_id` int DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `department_id` int DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('جدید','در حال بررسی','پاسخ داده شده','بسته شده') DEFAULT 'جدید',
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_code` (`tracking_code`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE IF NOT EXISTS `topics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `department_id` int DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int DEFAULT '0',
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', 'c4ca4238a0b923820dcc509a6f75849b', 'علی محمد', 'admin', '1404-03-11'),
(27, 'd', '8277e0910d750195b448797616e091ad', 'd', 'admin', '1405-03-17'),
(25, 'a', '0cc175b9c0f1b6a831c399e269772661', 'a', 'user', '1405-03-14 21:08:44');

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
) ENGINE=MyISAM AUTO_INCREMENT=719 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `permission_value`) VALUES
(704, 27, 'services_edit', 1),
(703, 27, 'services_delete', 1),
(63, 1, 'departments_delete', 1),
(702, 27, 'reports_view', 0),
(701, 27, 'products_view', 0),
(700, 27, 'products_manage', 0),
(699, 27, 'products_edit', 0),
(698, 27, 'products_delete', 0),
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
(705, 27, 'services_view', 1),
(706, 27, 'systems_delete', 0),
(707, 27, 'systems_edit', 0),
(708, 27, 'systems_manage', 0),
(709, 27, 'systems_view', 0),
(710, 27, 'tickets_delete', 1),
(711, 27, 'tickets_edit', 1),
(712, 27, 'tickets_manage', 1),
(713, 27, 'topics_delete', 0),
(714, 27, 'topics_edit', 0),
(697, 27, 'printers_view', 0),
(696, 27, 'printers_manage', 0),
(694, 27, 'printers_delete', 0),
(695, 27, 'printers_edit', 0),
(693, 27, 'persons_view', 0),
(692, 27, 'persons_edit', 0),
(691, 27, 'persons_delete', 0),
(690, 27, 'models_view', 0),
(689, 27, 'models_edit', 0),
(688, 27, 'models_delete', 0),
(687, 27, 'invoices_view', 0),
(686, 27, 'invoices_manage', 0),
(685, 27, 'invoices_edit', 0),
(684, 27, 'invoices_delete', 0),
(683, 27, 'goods_view', 0),
(682, 27, 'goods_manage', 0),
(681, 27, 'goods_edit', 0),
(680, 27, 'goods_delete', 0),
(679, 27, 'departments_manage', 0),
(678, 27, 'departments_edit', 0),
(677, 27, 'departments_delete', 0),
(676, 27, 'brands_view', 0),
(675, 27, 'brands_manage', 0),
(674, 27, 'brands_edit', 0),
(673, 27, 'brands_delete', 0),
(672, 27, 'activities_manage', 0),
(671, 27, 'activities_edit', 0),
(670, 27, 'activities_delete', 0),
(715, 27, 'topics_manage', 0),
(716, 27, 'users_delete', 0),
(717, 27, 'users_edit', 0),
(718, 27, 'users_manage', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
