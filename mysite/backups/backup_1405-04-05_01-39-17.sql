-- ============================================
-- پشتیبان‌گیری از دیتابیس
-- تاریخ: 1405-04-05 01:39:17
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activities` (`id`, `name`, `created_at`) VALUES
('17', 'aaa', '1405/04/03');

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `brands` (`id`, `name`, `created_at`) VALUES
('10', 'Western Digital', '2026-06-19'),
('7', 'Intel', '2026-06-19'),
('9', 'Samsung', '2026-06-19'),
('11', 'Kingston', '2026-06-19'),
('15', 'MSI', '2026-06-19'),
('16', 'Gigabyte', '2026-06-19'),
('17', 'Cooler Master', '2026-06-19'),
('39', 'Gplus', '1405/04/03'),
('38', 'LG', '1405/04/03'),
('37', 'Dell', '1405/04/03');

DROP TABLE IF EXISTS `cpus`;
CREATE TABLE `cpus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cpus` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
('8', '36', '3', '1405-03-30');

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments` (`id`, `name`, `status`, `created_at`) VALUES
('11', 'aaaa', 'active', '1405/04/03');

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
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

DROP TABLE IF EXISTS `kala`;
CREATE TABLE `kala` (
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

DROP TABLE IF EXISTS `models`;
CREATE TABLE `models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` int DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_models_brand` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `models` (`id`, `name`, `brand_id`, `created_at`) VALUES
('7', 'trf', NULL, '1405-04-04');

DROP TABLE IF EXISTS `monitors`;
CREATE TABLE `monitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `property_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `monitors` (`id`, `brand_id`, `model_id`, `property_code`, `created_at`) VALUES
('1', '13', '3', '1', '1405-03-30');

DROP TABLE IF EXISTS `motherboards`;
CREATE TABLE `motherboards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `motherboards` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
('1', '10', '3', '1405-03-30');

DROP TABLE IF EXISTS `peripheral_types`;
CREATE TABLE `peripheral_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `peripheral_types` (`id`, `name`, `sort_order`) VALUES
('1', 'پرینتر', '1'),
('2', 'بارکدخوان', '2'),
('3', 'اسکنر', '3'),
('4', 'کارت‌خوان', '4'),
('5', 'وب‌کم', '5');

DROP TABLE IF EXISTS `peripherals`;
CREATE TABLE `peripherals` (
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

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` enum('admin','support','user') DEFAULT 'user',
  `permission_key` varchar(100) NOT NULL,
  `permission_value` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission` (`role`,`permission_key`)
) ENGINE=MyISAM AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `permissions` (`id`, `role`, `permission_key`, `permission_value`) VALUES
('22', 'admin', 'tickets_manage', '1'),
('23', 'admin', 'tickets_edit', '1'),
('24', 'admin', 'tickets_delete', '1'),
('25', 'admin', 'users_manage', '1'),
('26', 'admin', 'users_edit', '1'),
('27', 'admin', 'users_delete', '1'),
('28', 'admin', 'departments_manage', '1'),
('29', 'admin', 'departments_edit', '1'),
('30', 'admin', 'departments_delete', '1'),
('31', 'admin', 'topics_manage', '1'),
('32', 'admin', 'topics_edit', '1'),
('33', 'admin', 'topics_delete', '1'),
('35', 'admin', 'brands_view', '1'),
('36', 'admin', 'brands_edit', '1'),
('37', 'admin', 'brands_delete', '1'),
('38', 'admin', 'products_view', '1'),
('39', 'admin', 'products_edit', '1'),
('40', 'admin', 'products_delete', '1'),
('41', 'admin', 'brands_manage', '1'),
('42', 'admin', 'products_manage', '1'),
('43', 'admin', 'activities_manage', '1'),
('44', 'admin', 'activities_edit', '1'),
('45', 'admin', 'activities_delete', '1'),
('46', 'admin', 'reports_view', '1'),
('47', 'admin', 'models_view', '1'),
('48', 'admin', 'models_edit', '1'),
('49', 'admin', 'models_delete', '1'),
('50', 'admin', 'persons_view', '1'),
('51', 'admin', 'persons_edit', '1'),
('52', 'admin', 'persons_delete', '1'),
('53', 'admin', 'services_view', '1'),
('54', 'admin', 'services_edit', '1'),
('55', 'admin', 'services_delete', '1'),
('56', 'admin', 'invoices_view', '1'),
('57', 'admin', 'invoices_manage', '1'),
('58', 'admin', 'invoices_edit', '1'),
('59', 'admin', 'invoices_delete', '1'),
('60', 'admin', 'goods_view', '1'),
('61', 'admin', 'goods_manage', '1'),
('62', 'admin', 'goods_edit', '1'),
('63', 'admin', 'goods_delete', '1'),
('64', 'admin', 'printers_view', '1'),
('65', 'admin', 'printers_manage', '1'),
('66', 'admin', 'printers_edit', '1'),
('67', 'admin', 'printers_delete', '1'),
('68', 'admin', 'systems_view', '1'),
('69', 'admin', 'systems_manage', '1'),
('70', 'admin', 'systems_edit', '1'),
('71', 'admin', 'systems_delete', '1');

DROP TABLE IF EXISTS `persons`;
CREATE TABLE `persons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `persons` (`id`, `name`, `created_at`) VALUES
('3', 'mmd', '1405-03-15 21:32:47');

DROP TABLE IF EXISTS `powers`;
CREATE TABLE `powers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`),
  KEY `model_id` (`model_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `powers` (`id`, `brand_id`, `model_id`, `created_at`) VALUES
('1', '15', '3', '1405-03-30');

DROP TABLE IF EXISTS `printers`;
CREATE TABLE `printers` (
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

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brand_id` (`brand_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `brand_id`, `name`, `created_at`) VALUES
('7', '7', 'بیس', '1405/04/04'),
('9', NULL, 'ش', '1405/04/04'),
('10', '37', 'ش', '1405/04/04');

DROP TABLE IF EXISTS `rams`;
CREATE TABLE `rams` (
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

INSERT INTO `rams` (`id`, `brand_id`, `model_id`, `type`, `capacity`, `created_at`) VALUES
('1', '20', '3', 'DDR3', '8GB', '1405-03-30'),
('2', '13', '3', 'DDR4', '64GB', '1405-03-30');

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE `service_requests` (
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

DROP TABLE IF EXISTS `storages`;
CREATE TABLE `storages` (
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

INSERT INTO `storages` (`id`, `brand_id`, `model_id`, `type`, `capacity`, `created_at`) VALUES
('1', '17', '3', 'HDD', '512GB', '1405-03-30');

DROP TABLE IF EXISTS `system_ips`;
CREATE TABLE `system_ips` (
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

DROP TABLE IF EXISTS `system_peripherals`;
CREATE TABLE `system_peripherals` (
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

DROP TABLE IF EXISTS `system_rams`;
CREATE TABLE `system_rams` (
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

INSERT INTO `system_rams` (`id`, `system_id`, `ram_id`, `description`, `created_at`, `created_by`) VALUES
('2', '9', '1', NULL, '1405-03-30', '1'),
('3', '10', '1', NULL, '1405-03-30', '1'),
('4', '11', '1', NULL, '1405-03-30', '1'),
('5', '12', '2', NULL, '1405-03-30', '1'),
('6', '12', '1', NULL, '1405-03-30', '1');

DROP TABLE IF EXISTS `system_storages`;
CREATE TABLE `system_storages` (
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

INSERT INTO `system_storages` (`id`, `system_id`, `storage_id`, `description`, `created_at`, `created_by`) VALUES
('2', '9', '1', NULL, '1405-03-30', '1'),
('3', '10', '1', NULL, '1405-03-30', '1'),
('4', '11', '1', NULL, '1405-03-30', '1'),
('5', '12', '1', NULL, '1405-03-30', '1');

DROP TABLE IF EXISTS `systems`;
CREATE TABLE `systems` (
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

INSERT INTO `systems` (`id`, `computer_code`, `property_code`, `name`, `department_id`, `cpu_id`, `motherboard_id`, `power_id`, `monitor_id`, `created_at`, `created_by`) VALUES
('11', '3', '', '3', NULL, NULL, NULL, NULL, NULL, '1405-03-30', '1');

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
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

INSERT INTO `tickets` (`id`, `tracking_code`, `user_id`, `fullname`, `subject`, `department_id`, `message`, `status`, `created_at`) VALUES
('20', 'TK-14050331-7721', '25', '2', 'درخواست دسترسی', '5', '2', 'جدید', '1405-03-31');

DROP TABLE IF EXISTS `topics`;
CREATE TABLE `topics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `topics` (`id`, `name`, `created_at`) VALUES
('22', 'مشکل در نرم افزار', '1405/04/03'),
('21', 'مشکل سخت افزاری', '1405/04/03'),
('20', 'مشکل شبکه', '1405/04/03'),
('19', 'درخواست دسترسی', '1405/04/03'),
('18', 'گزارش خطا', '1405/04/03');

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_value` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`)
) ENGINE=MyISAM AUTO_INCREMENT=964 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user_permissions` (`id`, `user_id`, `permission_key`, `permission_value`) VALUES
('963', '27', 'users_manage', '0'),
('962', '27', 'users_edit', '0'),
('63', '1', 'departments_delete', '1'),
('961', '27', 'users_delete', '0'),
('960', '27', 'topics_manage', '0'),
('959', '27', 'topics_edit', '0'),
('958', '27', 'topics_delete', '0'),
('957', '27', 'tickets_manage', '0'),
('956', '27', 'tickets_edit', '0'),
('64', '1', 'departments_edit', '1'),
('65', '1', 'departments_manage', '1'),
('66', '1', 'tickets_delete', '1'),
('67', '1', 'tickets_edit', '1'),
('68', '1', 'tickets_manage', '1'),
('69', '1', 'topics_delete', '1'),
('70', '1', 'topics_edit', '1'),
('71', '1', 'topics_manage', '1'),
('72', '1', 'users_delete', '1'),
('73', '1', 'users_edit', '1'),
('74', '1', 'users_manage', '1'),
('955', '27', 'tickets_delete', '0'),
('954', '27', 'systems_view', '1'),
('953', '27', 'systems_manage', '1'),
('952', '27', 'systems_edit', '1'),
('951', '27', 'systems_delete', '1'),
('950', '27', 'services_view', '1'),
('949', '27', 'services_edit', '1'),
('948', '27', 'services_delete', '1'),
('947', '27', 'reports_view', '0'),
('946', '27', 'products_view', '0'),
('945', '27', 'products_manage', '0'),
('944', '27', 'products_edit', '0'),
('943', '27', 'products_delete', '0'),
('942', '27', 'printers_view', '0'),
('941', '27', 'printers_manage', '0'),
('940', '27', 'printers_edit', '0'),
('939', '27', 'printers_delete', '0'),
('938', '27', 'persons_view', '0'),
('937', '27', 'persons_edit', '0'),
('936', '27', 'persons_delete', '0'),
('935', '27', 'models_view', '0'),
('934', '27', 'models_edit', '0'),
('933', '27', 'models_delete', '0'),
('932', '27', 'invoices_view', '0'),
('931', '27', 'invoices_manage', '0'),
('930', '27', 'invoices_edit', '0'),
('929', '27', 'invoices_delete', '0'),
('928', '27', 'goods_view', '0'),
('927', '27', 'goods_manage', '0'),
('926', '27', 'goods_edit', '0'),
('925', '27', 'goods_delete', '0'),
('924', '27', 'departments_manage', '0'),
('923', '27', 'departments_edit', '0'),
('922', '27', 'departments_delete', '0'),
('921', '27', 'brands_view', '0'),
('920', '27', 'brands_manage', '0'),
('919', '27', 'brands_edit', '0'),
('918', '27', 'brands_delete', '0'),
('917', '27', 'activities_manage', '0'),
('916', '27', 'activities_edit', '1'),
('915', '27', 'activities_delete', '0');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `created_at`) VALUES
('1', 'admin', 'c4ca4238a0b923820dcc509a6f75849b', 'علی محمد', 'admin', '1404-03-11'),
('27', 'd', 'c4ca4238a0b923820dcc509a6f75849b', 'd', 'admin', '1405-03-17'),
('25', 'a', 'c4ca4238a0b923820dcc509a6f75849b', 'a', 'user', '1405-03-14 21:08:44');

SET FOREIGN_KEY_CHECKS = 1;
