-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 01, 2026 at 09:01 AM
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
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(42, 'admin', 'products_manage', 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', 'c4ca4238a0b923820dcc509a6f75849b', 'علی محمد', 'admin', '1404-03-11'),
(16, 'a', '0cc175b9c0f1b6a831c399e269772661', 'a', 'admin', '1405-03-09 10:10:59');

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
) ENGINE=MyISAM AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `permission_value`) VALUES
(130, 16, 'products_delete', 0),
(129, 16, 'departments_manage', 0),
(63, 1, 'departments_delete', 1),
(128, 16, 'departments_edit', 0),
(127, 16, 'departments_delete', 0),
(126, 16, 'brands_view', 0),
(125, 16, 'brands_manage', 0),
(124, 16, 'brands_edit', 1),
(123, 16, 'brands_delete', 0),
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
(131, 16, 'products_edit', 1),
(133, 16, 'products_view', 0),
(132, 16, 'products_manage', 0),
(134, 16, 'tickets_delete', 0),
(135, 16, 'tickets_edit', 1),
(136, 16, 'tickets_manage', 0),
(137, 16, 'topics_delete', 0),
(138, 16, 'topics_edit', 0),
(139, 16, 'topics_manage', 0),
(140, 16, 'users_delete', 0),
(141, 16, 'users_edit', 0),
(142, 16, 'users_manage', 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
