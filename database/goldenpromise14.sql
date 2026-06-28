-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 27, 2026 at 04:28 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `goldenpromise`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_lockout_logs`
--

CREATE TABLE `account_lockout_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `event` enum('locked','unlocked') NOT NULL,
  `reason` enum('password_attempts','otp_attempts') DEFAULT NULL,
  `attempt_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `unlocked_by` bigint(20) DEFAULT NULL,
  `locked_until` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_lockout_logs`
--

INSERT INTO `account_lockout_logs` (`id`, `user_id`, `event`, `reason`, `attempt_count`, `unlocked_by`, `locked_until`, `ip_address`, `created_at`) VALUES
(1, 109, 'locked', 'otp_attempts', 3, NULL, '2026-06-23 13:19:36', '::1', '2026-06-23 13:04:36'),
(2, 109, 'unlocked', 'password_attempts', 0, NULL, NULL, '::1', '2026-06-23 13:39:39');

-- --------------------------------------------------------

--
-- Table structure for table `attire_items`
--

CREATE TABLE `attire_items` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `borrow_package_price` decimal(12,2) DEFAULT NULL,
  `borrow_customize_price` decimal(12,2) DEFAULT NULL,
  `buy_package_price` decimal(12,2) DEFAULT NULL,
  `buy_customize_price` decimal(12,2) DEFAULT NULL,
  `return_days` int(11) DEFAULT NULL,
  `buffer_days` int(11) NOT NULL DEFAULT 1 COMMENT 'Days blocked after return for cleaning/alteration',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attire_items`
--

INSERT INTO `attire_items` (`id`, `service_id`, `name`, `description`, `photo_url`, `borrow_package_price`, `borrow_customize_price`, `buy_package_price`, `buy_customize_price`, `return_days`, `buffer_days`, `sort_order`, `created_at`) VALUES
(5, 55, 'Long Sleve', '', 'http://localhost/GP/public/uploads/suppliers/21/service-management/attire-item/20260619054200-81b2bc18.jpg', 40000.00, 410000.00, 50000.00, 500000.00, 3, 1, 0, '2026-06-20 04:17:22'),
(7, 57, 'Long Sleeve', '', 'http://localhost/GP/public/uploads/suppliers/21/service-management/attire-item/20260620090252-c82101c2.jpg', 750000.00, 790000.00, 1000000.00, 750000.00, 3, 1, 0, '2026-06-20 07:03:10'),
(8, 64, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 750000.00, 975000.00, 2250000.00, 2700000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(9, 64, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 412500.00, 600000.00, 1500000.00, 1800000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(10, 64, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 900000.00, 1200000.00, 2550000.00, 3000000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(11, 65, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 800000.00, 1040000.00, 2400000.00, 2880000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(12, 65, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 440000.00, 640000.00, 1600000.00, 1920000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(13, 65, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 960000.00, 1280000.00, 2720000.00, 3200000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(14, 66, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 1500000.00, 1950000.00, 4500000.00, 5400000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(15, 66, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 825000.00, 1200000.00, 3000000.00, 3600000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(16, 66, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 1800000.00, 2400000.00, 5100000.00, 6000000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(17, 67, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 200000.00, 260000.00, 600000.00, 720000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(18, 67, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 110000.00, 160000.00, 400000.00, 480000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(19, 67, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 240000.00, 320000.00, 680000.00, 800000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(20, 68, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 400000.00, 520000.00, 1200000.00, 1440000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(21, 68, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 220000.00, 320000.00, 800000.00, 960000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(22, 68, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 480000.00, 640000.00, 1360000.00, 1600000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(23, 69, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 300000.00, 390000.00, 900000.00, 1080000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(24, 69, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 165000.00, 240000.00, 600000.00, 720000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(25, 69, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 360000.00, 480000.00, 1020000.00, 1200000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(26, 70, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 500000.00, 650000.00, 1500000.00, 1800000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(27, 70, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 275000.00, 400000.00, 1000000.00, 1200000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(28, 70, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 600000.00, 800000.00, 1700000.00, 2000000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(29, 71, 'Bridal Gown', 'Bridal Gown — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero2.png', 480000.00, 624000.00, 1440000.00, 1728000.00, 3, 1, 0, '2026-06-20 13:57:16'),
(30, 71, 'Groom\'s Suit / Taik-pon', 'Groom\'s Suit / Taik-pon — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero3.png', 264000.00, 384000.00, 960000.00, 1152000.00, 3, 1, 1, '2026-06-20 13:57:16'),
(31, 71, 'Traditional Htaing-ma-theim Set', 'Traditional Htaing-ma-theim Set — rental and purchase available.', 'http://localhost/GP/public/uploads/serviceHero1.png', 576000.00, 768000.00, 1632000.00, 1920000.00, 5, 1, 2, '2026-06-20 13:57:16'),
(34, 166, 'Western Wedding Dress', '', NULL, 250000.00, 350000.00, 850000.00, 120000.00, 3, 1, 0, '2026-06-24 02:48:34'),
(35, 167, 'Dress', '', 'https://4b2b50ea8de75a5e-204-157-173-127.serveousercontent.com/GP/public/uploads/suppliers/134/service-management/attire-item/20260626100614-e8c5b682.png', 2500000.00, 23000.00, 450000.00, 12000.00, 22, 1, 0, '2026-06-26 03:36:14'),
(42, 171, 'Tranditional Dress', '', 'http://localhost/GP/public/uploads/suppliers/20/service-management/attire-item/20260627151756-bf42c19e.jpg', 1000000.00, 1200000.00, 1500000.00, 1500000.00, 3, 1, 0, '2026-06-27 08:50:00'),
(43, 171, 'Long Sleeve Bridle', '', 'http://localhost/GP/public/uploads/suppliers/20/service-management/attire-item/20260627151924-80138b27.jpg', NULL, NULL, 3000000.00, 3000000.00, NULL, 1, 1, '2026-06-27 08:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `attire_rental_bookings`
--

CREATE TABLE `attire_rental_bookings` (
  `id` bigint(20) NOT NULL,
  `booking_item_id` bigint(20) NOT NULL,
  `attire_item_id` bigint(20) NOT NULL,
  `rental_type` enum('borrow','buy') NOT NULL,
  `borrow_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `rental_days` int(11) DEFAULT NULL,
  `buffer_until` date DEFAULT NULL COMMENT 'return_date + buffer_days — end of blocked range',
  `status` enum('reserved','picked_up','returned','cancelled') NOT NULL DEFAULT 'reserved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attire_rental_options`
--

CREATE TABLE `attire_rental_options` (
  `id` bigint(20) NOT NULL,
  `attire_item_id` bigint(20) NOT NULL,
  `days` int(11) NOT NULL COMMENT 'Rental duration in days',
  `price` decimal(12,2) NOT NULL COMMENT 'Package price for this duration',
  `customize_price` decimal(12,2) DEFAULT NULL COMMENT 'Customize price for this duration',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attire_rental_options`
--

INSERT INTO `attire_rental_options` (`id`, `attire_item_id`, `days`, `price`, `customize_price`, `sort_order`, `created_at`) VALUES
(2, 5, 3, 40000.00, 410000.00, 0, '2026-06-26 11:12:00'),
(3, 7, 3, 750000.00, 790000.00, 0, '2026-06-26 11:12:00'),
(4, 8, 3, 750000.00, 975000.00, 0, '2026-06-26 11:12:00'),
(5, 9, 3, 412500.00, 600000.00, 0, '2026-06-26 11:12:00'),
(6, 10, 5, 900000.00, 1200000.00, 0, '2026-06-26 11:12:00'),
(7, 11, 3, 800000.00, 1040000.00, 0, '2026-06-26 11:12:00'),
(8, 12, 3, 440000.00, 640000.00, 0, '2026-06-26 11:12:00'),
(9, 13, 5, 960000.00, 1280000.00, 0, '2026-06-26 11:12:00'),
(10, 14, 3, 1500000.00, 1950000.00, 0, '2026-06-26 11:12:00'),
(11, 15, 3, 825000.00, 1200000.00, 0, '2026-06-26 11:12:00'),
(12, 16, 5, 1800000.00, 2400000.00, 0, '2026-06-26 11:12:00'),
(13, 17, 3, 200000.00, 260000.00, 0, '2026-06-26 11:12:00'),
(14, 18, 3, 110000.00, 160000.00, 0, '2026-06-26 11:12:00'),
(15, 19, 5, 240000.00, 320000.00, 0, '2026-06-26 11:12:00'),
(16, 20, 3, 400000.00, 520000.00, 0, '2026-06-26 11:12:00'),
(17, 21, 3, 220000.00, 320000.00, 0, '2026-06-26 11:12:00'),
(18, 22, 5, 480000.00, 640000.00, 0, '2026-06-26 11:12:00'),
(19, 23, 3, 300000.00, 390000.00, 0, '2026-06-26 11:12:00'),
(20, 24, 3, 165000.00, 240000.00, 0, '2026-06-26 11:12:00'),
(21, 25, 5, 360000.00, 480000.00, 0, '2026-06-26 11:12:00'),
(22, 26, 3, 500000.00, 650000.00, 0, '2026-06-26 11:12:00'),
(23, 27, 3, 275000.00, 400000.00, 0, '2026-06-26 11:12:00'),
(24, 28, 5, 600000.00, 800000.00, 0, '2026-06-26 11:12:00'),
(25, 29, 3, 480000.00, 624000.00, 0, '2026-06-26 11:12:00'),
(26, 30, 3, 264000.00, 384000.00, 0, '2026-06-26 11:12:00'),
(27, 31, 5, 576000.00, 768000.00, 0, '2026-06-26 11:12:00'),
(28, 34, 3, 250000.00, 350000.00, 0, '2026-06-26 11:12:00'),
(29, 35, 22, 2500000.00, 23000.00, 0, '2026-06-26 11:12:00'),
(34, 42, 2, 1800000.00, 1850000.00, 0, '2026-06-27 08:50:00'),
(35, 43, 2, 2000000.00, 2100000.00, 0, '2026-06-27 08:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `cart_id` bigint(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('unpaid','partial','paid') DEFAULT NULL,
  `status` enum('draft','pending_supplier_response','pending_payment','payment_submitted','payment_verified','paid','suppliers_responding','confirmed','replacement_pending','pending_final_payment','finalized','completed','cancelled','cancellation_requested') NOT NULL DEFAULT 'draft',
  `supplier_response_deadline` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `cart_id`, `total_amount`, `paid_amount`, `payment_status`, `status`, `supplier_response_deadline`, `approved_by`, `approved_at`, `created_at`) VALUES
(48, 27, 2, 600000.00, 120000.00, 'partial', 'paid', '2026-06-20 05:02:46', NULL, NULL, '2026-06-18 09:32:46'),
(49, 30, 3, 1473360.00, 294671.00, 'partial', 'cancelled', NULL, 1, '2026-06-19 13:03:53', '2026-06-18 11:10:23'),
(50, 30, 3, 76860.00, 15372.00, 'partial', 'cancelled', NULL, 1, '2026-06-19 13:17:24', '2026-06-18 14:36:16'),
(51, 30, 3, 7035000.00, 0.00, 'unpaid', 'cancelled', '2026-06-21 11:18:47', NULL, NULL, '2026-06-19 15:48:47'),
(52, 30, 3, 4074000.00, 814800.00, 'partial', 'cancelled', NULL, 1, '2026-06-27 10:48:41', '2026-06-20 01:33:55'),
(53, 30, 3, 5074000.00, 1814800.00, 'partial', 'cancelled', '2026-06-22 10:00:35', 1, '2026-06-22 09:39:43', '2026-06-20 02:31:24'),
(126, 1, 4, 2900000.00, 2900000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(127, 24, 5, 2900000.00, 2900000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(128, 24, 5, 2000000.00, 2000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(129, 27, 2, 2000000.00, 2000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(130, 29, 6, 2000000.00, 2000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(131, 27, 2, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(132, 29, 6, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(133, 29, 6, 900000.00, 900000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(134, 30, 3, 900000.00, 900000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(135, 1, 4, 900000.00, 900000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(136, 30, 3, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(137, 1, 4, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(138, 1, 4, 750000.00, 750000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(139, 24, 5, 750000.00, 750000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(140, 27, 2, 750000.00, 750000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(141, 24, 5, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(142, 27, 2, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(143, 27, 2, 1500000.00, 1500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(144, 29, 6, 1500000.00, 1500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(145, 30, 3, 1500000.00, 1500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(146, 29, 6, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(147, 30, 3, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(148, 30, 3, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(149, 1, 4, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(150, 24, 5, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(151, 1, 4, 300000.00, 300000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(152, 24, 5, 300000.00, 300000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(153, 24, 5, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(154, 27, 2, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(155, 29, 6, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(156, 27, 2, 480000.00, 480000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(157, 29, 6, 480000.00, 480000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(158, 29, 6, 445000.00, 445000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(159, 30, 3, 445000.00, 445000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(160, 1, 4, 445000.00, 445000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(161, 30, 3, 430000.00, 430000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(162, 1, 4, 430000.00, 430000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(163, 1, 4, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(164, 24, 5, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(165, 27, 2, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(166, 24, 5, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(167, 27, 2, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(168, 27, 2, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(169, 29, 6, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(170, 30, 3, 400000.00, 400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(171, 29, 6, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(172, 30, 3, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(173, 30, 3, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(174, 1, 4, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(175, 24, 5, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(176, 1, 4, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(177, 24, 5, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(178, 24, 5, 5400000.00, 5400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(179, 27, 2, 5400000.00, 5400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(180, 29, 6, 5400000.00, 5400000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(181, 27, 2, 2800000.00, 2800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(182, 29, 6, 2800000.00, 2800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(183, 29, 6, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(184, 30, 3, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(185, 1, 4, 1000000.00, 1000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(186, 30, 3, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(187, 1, 4, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(188, 1, 4, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(189, 24, 5, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(190, 27, 2, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(191, 24, 5, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(192, 27, 2, 200000.00, 200000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(193, 27, 2, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(194, 29, 6, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(195, 30, 3, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(196, 29, 6, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(197, 30, 3, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(198, 30, 3, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(199, 1, 4, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(200, 24, 5, 60000.00, 60000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(201, 1, 4, 18000.00, 18000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(202, 24, 5, 18000.00, 18000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(203, 24, 5, 30000.00, 30000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(204, 27, 2, 30000.00, 30000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(205, 29, 6, 30000.00, 30000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(206, 27, 2, 10000.00, 10000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(207, 29, 6, 10000.00, 10000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(208, 29, 6, 28000.00, 28000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(209, 30, 3, 28000.00, 28000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(210, 1, 4, 28000.00, 28000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(211, 30, 3, 18000.00, 18000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(212, 1, 4, 18000.00, 18000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(213, 1, 4, 11000.00, 11000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(214, 24, 5, 11000.00, 11000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(215, 27, 2, 11000.00, 11000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(216, 24, 5, 12000.00, 12000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(217, 27, 2, 12000.00, 12000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(218, 27, 2, 10000.00, 10000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(219, 29, 6, 10000.00, 10000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(220, 30, 3, 10000.00, 10000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(221, 29, 6, 330000.00, 330000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(222, 30, 3, 330000.00, 330000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(223, 30, 3, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(224, 1, 4, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(225, 24, 5, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(226, 1, 4, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(227, 24, 5, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(228, 24, 5, 3000000.00, 3000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(229, 27, 2, 3000000.00, 3000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(230, 29, 6, 3000000.00, 3000000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(231, 27, 2, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(232, 29, 6, 500000.00, 500000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(233, 29, 6, 4300000.00, 4300000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(234, 30, 3, 4300000.00, 4300000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(235, 1, 4, 4300000.00, 4300000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(236, 30, 3, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(237, 1, 4, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(238, 1, 4, 99000.00, 99000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(239, 24, 5, 99000.00, 99000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(240, 27, 2, 99000.00, 99000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(241, 24, 5, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(242, 27, 2, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(243, 27, 2, 63000.00, 63000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(244, 29, 6, 63000.00, 63000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(245, 30, 3, 63000.00, 63000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(246, 29, 6, 85500.00, 85500.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(247, 30, 3, 85500.00, 85500.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(248, 30, 3, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(249, 1, 4, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(250, 24, 5, 800000.00, 800000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(251, 1, 4, 135000.00, 135000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(252, 24, 5, 135000.00, 135000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(253, 24, 5, 180000.00, 180000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(254, 27, 2, 180000.00, 180000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(255, 29, 6, 180000.00, 180000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(256, 27, 2, 100000.00, 100000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(257, 29, 6, 100000.00, 100000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(258, 29, 6, 135000.00, 135000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(259, 30, 3, 135000.00, 135000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(260, 1, 4, 135000.00, 135000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(261, 30, 3, 55000.00, 55000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(262, 1, 4, 55000.00, 55000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(263, 1, 4, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(264, 24, 5, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(265, 27, 2, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(266, 24, 5, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(267, 27, 2, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(268, 27, 2, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(269, 29, 6, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(270, 30, 3, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(271, 29, 6, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(272, 30, 3, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(273, 30, 3, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(274, 1, 4, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(275, 24, 5, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(276, 1, 4, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(277, 24, 5, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(278, 24, 5, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(279, 27, 2, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(280, 29, 6, 50000.00, 50000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(281, 27, 2, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(282, 29, 6, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(283, 29, 6, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(284, 30, 3, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(285, 1, 4, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(286, 30, 3, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(287, 1, 4, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(288, 1, 4, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(289, 24, 5, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(290, 27, 2, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(291, 24, 5, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(292, 27, 2, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(293, 27, 2, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(294, 29, 6, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(295, 30, 3, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(296, 29, 6, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(297, 30, 3, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(298, 30, 3, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(299, 1, 4, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(300, 24, 5, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(301, 1, 4, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(302, 24, 5, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(303, 24, 5, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(304, 27, 2, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(305, 29, 6, 150000.00, 150000.00, 'paid', 'completed', NULL, 1, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(310, 30, 3, 4150650.00, 830130.00, 'partial', 'pending_final_payment', NULL, NULL, NULL, '2026-06-21 06:25:06'),
(311, 30, 3, 750000.00, 150000.00, 'partial', 'cancellation_requested', '2026-06-23 02:42:10', NULL, NULL, '2026-06-21 07:12:10'),
(312, 30, 3, 150000.00, 30000.00, 'partial', 'cancelled', '2026-06-23 03:09:00', 1, '2026-06-21 12:32:52', '2026-06-21 07:39:00'),
(313, 30, 3, 1000000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-23 23:03:34', NULL, NULL, '2026-06-22 03:33:34'),
(314, 30, 3, 150000.00, 0.00, 'unpaid', 'cancelled', '2026-06-23 23:15:48', NULL, NULL, '2026-06-22 03:45:48'),
(315, 30, 3, 15000000.00, 0.00, 'unpaid', 'payment_submitted', '2026-06-24 03:07:58', NULL, NULL, '2026-06-22 07:37:58'),
(316, 30, 3, 4150650.00, 1079169.00, 'partial', 'confirmed', NULL, NULL, NULL, '2026-06-22 09:49:24'),
(317, 112, 8, 3953000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-23 05:07:04'),
(318, 113, 9, 3953000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-23 05:15:13'),
(319, 113, 9, 2100000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-25 05:17:31', NULL, NULL, '2026-06-23 05:17:31'),
(320, 109, 10, 18000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-25 15:37:50', NULL, NULL, '2026-06-23 15:37:50'),
(321, 109, 10, 1400000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-26 02:45:33', NULL, NULL, '2026-06-24 02:45:33'),
(322, 109, 10, 4600000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-26 02:50:09', NULL, NULL, '2026-06-24 02:50:09'),
(323, 112, 8, 3953000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-24 02:53:58'),
(324, 109, 10, 18000.00, 0.00, 'unpaid', 'cancellation_requested', '2026-06-26 02:55:00', NULL, NULL, '2026-06-24 02:55:00'),
(325, 109, 10, 70000.00, 0.00, 'unpaid', 'payment_submitted', NULL, NULL, NULL, '2026-06-24 03:09:11'),
(326, 123, 11, 200000.00, 0.00, 'unpaid', 'cancellation_requested', '2026-06-26 03:37:59', NULL, NULL, '2026-06-24 03:37:59'),
(327, 123, 11, 300000.00, 0.00, 'unpaid', 'cancellation_requested', '2026-06-26 04:18:13', NULL, NULL, '2026-06-24 04:18:13'),
(328, 113, 9, 300000.00, 0.00, 'unpaid', 'cancellation_requested', '2026-06-26 04:22:13', NULL, NULL, '2026-06-24 04:22:13'),
(329, 124, 12, 200000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-26 04:53:07', NULL, NULL, '2026-06-24 04:53:07'),
(330, 109, 10, 300000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-27 02:48:29', NULL, NULL, '2026-06-25 02:48:29'),
(331, 131, 13, 50000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-27 03:27:09', NULL, NULL, '2026-06-25 03:27:09'),
(332, 131, 13, 135000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-27 04:57:50', NULL, NULL, '2026-06-25 04:57:50'),
(333, 132, 15, 845000.00, 0.00, 'unpaid', 'cancellation_requested', '2026-06-27 05:01:14', NULL, NULL, '2026-06-25 05:01:14'),
(334, 29, 6, 900000.00, 0.00, 'unpaid', 'payment_submitted', '2026-06-27 13:10:35', NULL, NULL, '2026-06-25 13:10:35'),
(335, 29, 6, 500000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-28 03:33:41', NULL, NULL, '2026-06-26 03:33:41'),
(336, 126, 14, 2500000.00, 0.00, 'unpaid', 'pending_payment', '2026-06-28 04:40:29', NULL, NULL, '2026-06-26 04:40:29'),
(337, 30, 3, 3000000.00, 0.00, 'unpaid', 'cancelled', '2026-06-28 07:41:56', NULL, NULL, '2026-06-27 07:41:56'),
(338, 30, 3, 3000000.00, 780000.00, 'partial', 'pending_final_payment', '2026-06-28 08:07:50', NULL, NULL, '2026-06-27 08:07:50'),
(339, 30, 3, 2100000.00, 546000.00, 'partial', 'cancelled', '2026-06-28 09:57:00', 1, '2026-06-27 09:59:09', '2026-06-27 09:57:00'),
(340, 30, 3, 2100000.00, 546000.00, 'partial', 'cancelled', '2026-06-28 10:17:07', 1, '2026-06-27 10:36:22', '2026-06-27 10:17:07'),
(341, 30, 3, 2100000.00, 546000.00, 'partial', 'pending_final_payment', '2026-06-28 10:39:57', NULL, NULL, '2026-06-27 10:39:57'),
(342, 30, 3, 2100000.00, 546000.00, 'partial', 'pending_final_payment', '2026-06-28 10:53:32', NULL, NULL, '2026-06-27 10:53:32'),
(343, 30, 3, 2100000.00, 546000.00, 'partial', 'pending_final_payment', '2026-06-28 11:04:29', NULL, NULL, '2026-06-27 11:04:29'),
(344, 30, 3, 2100000.00, 546000.00, 'partial', 'cancelled', '2026-06-28 11:09:35', 1, '2026-06-27 11:36:37', '2026-06-27 11:09:35'),
(345, 29, 6, 2100000.00, 2100000.00, 'paid', 'completed', '2026-06-28 11:39:58', NULL, NULL, '2026-06-27 11:39:58'),
(346, 29, 6, 3000000.00, 780000.00, 'partial', 'paid', '2026-06-28 12:01:48', NULL, NULL, '2026-06-27 12:01:48');

-- --------------------------------------------------------

--
-- Table structure for table `booking_items`
--

CREATE TABLE `booking_items` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `item_type` enum('service','package','supplier_package') NOT NULL,
  `source` enum('package','custom') NOT NULL DEFAULT 'custom',
  `item_id` bigint(20) NOT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `price` decimal(10,2) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `status` enum('pending','accepted','completed','cancelled') DEFAULT NULL,
  `venue_room_id` bigint(20) DEFAULT NULL,
  `attire_item_id` bigint(20) DEFAULT NULL,
  `rental_type` enum('borrow','buy') DEFAULT NULL,
  `borrow_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `decoration_style_id` bigint(20) DEFAULT NULL,
  `cake_design_id` bigint(20) DEFAULT NULL,
  `slot_id` bigint(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `booking_type` enum('fullday','slot','flexible') DEFAULT NULL,
  `package_booking_item_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_items`
--

INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `source`, `item_id`, `booking_date`, `price`, `item_name`, `supplier_name`, `category_name`, `thumbnail_url`, `status`, `venue_room_id`, `attire_item_id`, `rental_type`, `borrow_date`, `return_date`, `decoration_style_id`, `cake_design_id`, `slot_id`, `start_time`, `end_time`, `booking_type`, `package_booking_item_id`) VALUES
(99, 48, 'service', 'custom', 42, '2026-09-24 06:00:00', 600000.00, NULL, NULL, NULL, NULL, 'accepted', 20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '06:00:00', '17:00:00', 'slot', NULL),
(100, 49, 'package', 'package', 19, NULL, 1473360.00, NULL, NULL, NULL, 'http://localhost/GP/public/uploads/suppliers/21-wyndham-grand-yangon-hotel/documents/cover-photo-20260611070115-9e2abb41.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(101, 50, 'package', 'package', 20, NULL, 76860.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618152115-7d249ee0.png', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(102, 51, 'package', 'package', 23, NULL, 4935000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(103, 51, 'service', 'custom', 50, '2026-06-27 09:00:00', 2100000.00, 'H &amp; H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260619040841-541df810.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(105, 52, 'package', 'package', 26, NULL, 4074000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(106, 53, 'package', 'package', 26, NULL, 2900000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(179, 126, 'service', 'custom', 59, '2026-03-15 18:00:00', 2900000.00, 'Excel Jade Hall — Grand Wedding Decoration', 'Excel River View Hotel & Resort', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(180, 127, 'service', 'custom', 59, '2026-04-12 18:00:00', 2900000.00, 'Excel Jade Hall — Grand Wedding Decoration', 'Excel River View Hotel & Resort', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(181, 128, 'service', 'custom', 60, '2026-04-12 18:00:00', 2000000.00, 'Golden Inya - Lakeside Wedding Venue', 'Golden Inya Restaurant', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(182, 129, 'service', 'custom', 60, '2026-04-26 18:00:00', 2000000.00, 'Golden Inya - Lakeside Wedding Venue', 'Golden Inya Restaurant', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(183, 130, 'service', 'custom', 60, '2026-05-10 18:00:00', 2000000.00, 'Golden Inya - Lakeside Wedding Venue', 'Golden Inya Restaurant', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(184, 131, 'service', 'custom', 61, '2026-04-26 18:00:00', 500000.00, 'Western Park Ruby - Garden Wedding Venue', 'Western Park Ruby - People\'s Park', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(185, 132, 'service', 'custom', 61, '2026-05-10 18:00:00', 500000.00, 'Western Park Ruby - Garden Wedding Venue', 'Western Park Ruby - People\'s Park', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(186, 133, 'service', 'custom', 62, '2026-05-10 18:00:00', 900000.00, 'Zephyr - Garden Wedding Venue', 'Zephyr (Sein Lann So Pyay Garden)', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(187, 134, 'service', 'custom', 62, '2026-05-24 18:00:00', 900000.00, 'Zephyr - Garden Wedding Venue', 'Zephyr (Sein Lann So Pyay Garden)', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(188, 135, 'service', 'custom', 62, '2026-06-07 18:00:00', 900000.00, 'Zephyr - Garden Wedding Venue', 'Zephyr (Sein Lann So Pyay Garden)', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(189, 136, 'service', 'custom', 63, '2026-05-24 18:00:00', 800000.00, 'The White Cottage - Garden & Lounge Venue', 'The White Cottage Restaurant & Lounge', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(190, 137, 'service', 'custom', 63, '2026-06-07 18:00:00', 800000.00, 'The White Cottage - Garden & Lounge Venue', 'The White Cottage Restaurant & Lounge', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(191, 138, 'service', 'custom', 64, '2026-06-07 18:00:00', 750000.00, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire', 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(192, 139, 'service', 'custom', 64, '2026-03-15 18:00:00', 750000.00, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire', 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(193, 140, 'service', 'custom', 64, '2026-04-12 18:00:00', 750000.00, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire', 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(194, 141, 'service', 'custom', 65, '2026-03-15 18:00:00', 800000.00, 'Dear Brides Wedding Dress Studio - Wedding Attire', 'Dear Brides Wedding Dress Studio', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(195, 142, 'service', 'custom', 65, '2026-04-12 18:00:00', 800000.00, 'Dear Brides Wedding Dress Studio - Wedding Attire', 'Dear Brides Wedding Dress Studio', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(196, 143, 'service', 'custom', 66, '2026-04-12 18:00:00', 1500000.00, 'The Vow Wedding Studio Myanmar - Wedding Attire', 'The Vow Wedding Studio Myanmar', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(197, 144, 'service', 'custom', 66, '2026-04-26 18:00:00', 1500000.00, 'The Vow Wedding Studio Myanmar - Wedding Attire', 'The Vow Wedding Studio Myanmar', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(198, 145, 'service', 'custom', 66, '2026-05-10 18:00:00', 1500000.00, 'The Vow Wedding Studio Myanmar - Wedding Attire', 'The Vow Wedding Studio Myanmar', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(199, 146, 'service', 'custom', 67, '2026-04-26 18:00:00', 200000.00, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN - Wedding Attire', 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(200, 147, 'service', 'custom', 67, '2026-05-10 18:00:00', 200000.00, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN - Wedding Attire', 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(201, 148, 'service', 'custom', 68, '2026-05-10 18:00:00', 400000.00, 'T&T Bridal Collection - Wedding Attire', 'T&T Bridal Collection', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(202, 149, 'service', 'custom', 68, '2026-05-24 18:00:00', 400000.00, 'T&T Bridal Collection - Wedding Attire', 'T&T Bridal Collection', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(203, 150, 'service', 'custom', 68, '2026-06-07 18:00:00', 400000.00, 'T&T Bridal Collection - Wedding Attire', 'T&T Bridal Collection', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(204, 151, 'service', 'custom', 69, '2026-05-24 18:00:00', 300000.00, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire', 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(205, 152, 'service', 'custom', 69, '2026-06-07 18:00:00', 300000.00, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire', 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(206, 153, 'service', 'custom', 70, '2026-06-07 18:00:00', 500000.00, 'Peter\'s Bridal Garden - Studio - Wedding Attire', 'Peter\'s Bridal Garden - Studio', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(207, 154, 'service', 'custom', 70, '2026-03-15 18:00:00', 500000.00, 'Peter\'s Bridal Garden - Studio - Wedding Attire', 'Peter\'s Bridal Garden - Studio', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(208, 155, 'service', 'custom', 70, '2026-04-12 18:00:00', 500000.00, 'Peter\'s Bridal Garden - Studio - Wedding Attire', 'Peter\'s Bridal Garden - Studio', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(209, 156, 'service', 'custom', 71, '2026-03-15 18:00:00', 480000.00, 'My Everything Wedding Dresses - Wedding Attire', 'My Everything Wedding Dresses', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(210, 157, 'service', 'custom', 71, '2026-04-12 18:00:00', 480000.00, 'My Everything Wedding Dresses - Wedding Attire', 'My Everything Wedding Dresses', 'Attire', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(211, 158, 'service', 'custom', 103, '2026-04-12 18:00:00', 445000.00, 'Forever One Stop Wedding Studio - Studio', 'Forever One Stop Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(212, 159, 'service', 'custom', 103, '2026-04-26 18:00:00', 445000.00, 'Forever One Stop Wedding Studio - Studio', 'Forever One Stop Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(213, 160, 'service', 'custom', 103, '2026-05-10 18:00:00', 445000.00, 'Forever One Stop Wedding Studio - Studio', 'Forever One Stop Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(214, 161, 'service', 'custom', 104, '2026-04-26 18:00:00', 430000.00, 'H & H Photo Studio - Studio', 'H & H Photo Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(215, 162, 'service', 'custom', 104, '2026-05-10 18:00:00', 430000.00, 'H & H Photo Studio - Studio', 'H & H Photo Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(216, 163, 'service', 'custom', 105, '2026-05-10 18:00:00', 400000.00, 'Venus Wedding Studio - Studio', 'Venus Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(217, 164, 'service', 'custom', 105, '2026-05-24 18:00:00', 400000.00, 'Venus Wedding Studio - Studio', 'Venus Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(218, 165, 'service', 'custom', 105, '2026-06-07 18:00:00', 400000.00, 'Venus Wedding Studio - Studio', 'Venus Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(219, 166, 'service', 'custom', 106, '2026-05-24 18:00:00', 400000.00, 'PNA’S Wedding Studio - Studio', 'PNA’S Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(220, 167, 'service', 'custom', 106, '2026-06-07 18:00:00', 400000.00, 'PNA’S Wedding Studio - Studio', 'PNA’S Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(221, 168, 'service', 'custom', 107, '2026-06-07 18:00:00', 400000.00, 'Together Wedding Studio - Studio', 'Together Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(222, 169, 'service', 'custom', 107, '2026-03-15 18:00:00', 400000.00, 'Together Wedding Studio - Studio', 'Together Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(223, 170, 'service', 'custom', 107, '2026-04-12 18:00:00', 400000.00, 'Together Wedding Studio - Studio', 'Together Wedding Studio', 'Studio', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(224, 171, 'service', 'custom', 108, '2026-03-15 18:00:00', 500000.00, 'Western Park Ruby – People’s Park - Venue', 'Western Park Ruby – People’s Park', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(225, 172, 'service', 'custom', 108, '2026-04-12 18:00:00', 500000.00, 'Western Park Ruby – People’s Park - Venue', 'Western Park Ruby – People’s Park', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(226, 173, 'service', 'custom', 109, '2026-04-12 18:00:00', 1000000.00, 'MG & J Jewelry - Jewelry', 'MG & J Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(227, 174, 'service', 'custom', 109, '2026-04-26 18:00:00', 1000000.00, 'MG & J Jewelry - Jewelry', 'MG & J Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(228, 175, 'service', 'custom', 109, '2026-05-10 18:00:00', 1000000.00, 'MG & J Jewelry - Jewelry', 'MG & J Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(229, 176, 'service', 'custom', 110, '2026-04-26 18:00:00', 1000000.00, 'U Hton - Jewelry', 'U Hton', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(230, 177, 'service', 'custom', 110, '2026-05-10 18:00:00', 1000000.00, 'U Hton - Jewelry', 'U Hton', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(231, 178, 'service', 'custom', 111, '2026-05-10 18:00:00', 5400000.00, 'Myat Pan Tha Zin Diamond and Jewelry - Jewelry', 'Myat Pan Tha Zin Diamond and Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(232, 179, 'service', 'custom', 111, '2026-05-24 18:00:00', 5400000.00, 'Myat Pan Tha Zin Diamond and Jewelry - Jewelry', 'Myat Pan Tha Zin Diamond and Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(233, 180, 'service', 'custom', 111, '2026-06-07 18:00:00', 5400000.00, 'Myat Pan Tha Zin Diamond and Jewelry - Jewelry', 'Myat Pan Tha Zin Diamond and Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(234, 181, 'service', 'custom', 112, '2026-05-24 18:00:00', 2800000.00, 'Vivian Diamond Jewellery - Jewelry', 'Vivian Diamond Jewellery', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(235, 182, 'service', 'custom', 112, '2026-06-07 18:00:00', 2800000.00, 'Vivian Diamond Jewellery - Jewelry', 'Vivian Diamond Jewellery', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(236, 183, 'service', 'custom', 113, '2026-06-07 18:00:00', 1000000.00, 'Theingi Moe Jewelry - Jewelry', 'Theingi Moe Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(237, 184, 'service', 'custom', 113, '2026-03-15 18:00:00', 1000000.00, 'Theingi Moe Jewelry - Jewelry', 'Theingi Moe Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(238, 185, 'service', 'custom', 113, '2026-04-12 18:00:00', 1000000.00, 'Theingi Moe Jewelry - Jewelry', 'Theingi Moe Jewelry', 'Jewelry', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(239, 186, 'service', 'custom', 118, '2026-03-15 18:00:00', 200000.00, 'Parisian Cake&Cafe - Cake', 'Parisian Cake&Cafe', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(240, 187, 'service', 'custom', 118, '2026-04-12 18:00:00', 200000.00, 'Parisian Cake&Cafe - Cake', 'Parisian Cake&Cafe', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(241, 188, 'service', 'custom', 119, '2026-04-12 18:00:00', 200000.00, 'Season - Cake', 'Season', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(242, 189, 'service', 'custom', 119, '2026-04-26 18:00:00', 200000.00, 'Season - Cake', 'Season', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(243, 190, 'service', 'custom', 119, '2026-05-10 18:00:00', 200000.00, 'Season - Cake', 'Season', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(244, 191, 'service', 'custom', 120, '2026-04-26 18:00:00', 200000.00, 'Kudo’s - Cake', 'Kudo’s', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(245, 192, 'service', 'custom', 120, '2026-05-10 18:00:00', 200000.00, 'Kudo’s - Cake', 'Kudo’s', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(246, 193, 'service', 'custom', 121, '2026-05-10 18:00:00', 60000.00, 'Shwe Pu Zun - Cake', 'Shwe Pu Zun', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(247, 194, 'service', 'custom', 121, '2026-05-24 18:00:00', 60000.00, 'Shwe Pu Zun - Cake', 'Shwe Pu Zun', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(248, 195, 'service', 'custom', 121, '2026-06-07 18:00:00', 60000.00, 'Shwe Pu Zun - Cake', 'Shwe Pu Zun', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(249, 196, 'service', 'custom', 122, '2026-05-24 18:00:00', 60000.00, '77 Cake - Cake', '77 Cake', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(250, 197, 'service', 'custom', 122, '2026-06-07 18:00:00', 60000.00, '77 Cake - Cake', '77 Cake', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(251, 198, 'service', 'custom', 123, '2026-06-07 18:00:00', 60000.00, 'El Dorado - Cake', 'El Dorado', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(252, 199, 'service', 'custom', 123, '2026-03-15 18:00:00', 60000.00, 'El Dorado - Cake', 'El Dorado', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(253, 200, 'service', 'custom', 123, '2026-04-12 18:00:00', 60000.00, 'El Dorado - Cake', 'El Dorado', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(254, 201, 'service', 'custom', 124, '2026-03-15 18:00:00', 18000.00, 'Shan Yoe Yar Restaurant - Catering', 'Shan Yoe Yar Restaurant', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(255, 202, 'service', 'custom', 124, '2026-04-12 18:00:00', 18000.00, 'Shan Yoe Yar Restaurant - Catering', 'Shan Yoe Yar Restaurant', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(256, 203, 'service', 'custom', 125, '2026-04-12 18:00:00', 30000.00, 'KSS နတ်သုဒ္ဓါဒံပေါက် - Catering', 'KSS နတ်သုဒ္ဓါဒံပေါက်', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(257, 204, 'service', 'custom', 125, '2026-04-26 18:00:00', 30000.00, 'KSS နတ်သုဒ္ဓါဒံပေါက် - Catering', 'KSS နတ်သုဒ္ဓါဒံပေါက်', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(258, 205, 'service', 'custom', 125, '2026-05-10 18:00:00', 30000.00, 'KSS နတ်သုဒ္ဓါဒံပေါက် - Catering', 'KSS နတ်သုဒ္ဓါဒံပေါက်', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(259, 206, 'service', 'custom', 126, '2026-04-26 18:00:00', 10000.00, 'ထူး ရေခဲမုန့် - Catering', 'ထူး ရေခဲမုန့်', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(260, 207, 'service', 'custom', 126, '2026-05-10 18:00:00', 10000.00, 'ထူး ရေခဲမုန့် - Catering', 'ထူး ရေခဲမုန့်', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(261, 208, 'service', 'custom', 127, '2026-05-10 18:00:00', 28000.00, 'The Hundred -Grilled Chicken - Catering', 'The Hundred -Grilled Chicken', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(262, 209, 'service', 'custom', 127, '2026-05-24 18:00:00', 28000.00, 'The Hundred -Grilled Chicken - Catering', 'The Hundred -Grilled Chicken', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(263, 210, 'service', 'custom', 127, '2026-06-07 18:00:00', 28000.00, 'The Hundred -Grilled Chicken - Catering', 'The Hundred -Grilled Chicken', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(264, 211, 'service', 'custom', 128, '2026-05-24 18:00:00', 18000.00, 'Royal Chef - Catering', 'Royal Chef', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(265, 212, 'service', 'custom', 128, '2026-06-07 18:00:00', 18000.00, 'Royal Chef - Catering', 'Royal Chef', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(266, 213, 'service', 'custom', 129, '2026-06-07 18:00:00', 11000.00, 'Rice Box - Catering', 'Rice Box', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(267, 214, 'service', 'custom', 129, '2026-03-15 18:00:00', 11000.00, 'Rice Box - Catering', 'Rice Box', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(268, 215, 'service', 'custom', 129, '2026-04-12 18:00:00', 11000.00, 'Rice Box - Catering', 'Rice Box', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(269, 216, 'service', 'custom', 130, '2026-03-15 18:00:00', 12000.00, 'Boke & Bee - Catering', 'Boke & Bee', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(270, 217, 'service', 'custom', 130, '2026-04-12 18:00:00', 12000.00, 'Boke & Bee - Catering', 'Boke & Bee', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(271, 218, 'service', 'custom', 131, '2026-04-12 18:00:00', 10000.00, 'နှင်းသီရိ - Catering', 'နှင်းသီရိ', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(272, 219, 'service', 'custom', 131, '2026-04-26 18:00:00', 10000.00, 'နှင်းသီရိ - Catering', 'နှင်းသီရိ', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(273, 220, 'service', 'custom', 131, '2026-05-10 18:00:00', 10000.00, 'နှင်းသီရိ - Catering', 'နှင်းသီရိ', 'Food', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(274, 221, 'service', 'custom', 132, '2026-04-26 18:00:00', 330000.00, 'H&H Floral and Wedding Service - Decoration', 'H&H Floral and Wedding Service', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(275, 222, 'service', 'custom', 132, '2026-05-10 18:00:00', 330000.00, 'H&H Floral and Wedding Service - Decoration', 'H&H Floral and Wedding Service', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(276, 223, 'service', 'custom', 133, '2026-05-10 18:00:00', 500000.00, 'Eternal Flowers - Decoration', 'Eternal Flowers', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(277, 224, 'service', 'custom', 133, '2026-05-24 18:00:00', 500000.00, 'Eternal Flowers - Decoration', 'Eternal Flowers', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(278, 225, 'service', 'custom', 133, '2026-06-07 18:00:00', 500000.00, 'Eternal Flowers - Decoration', 'Eternal Flowers', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(279, 226, 'service', 'custom', 134, '2026-05-24 18:00:00', 500000.00, 'Aphrodite Wedding Planning & Decoration - Decoration', 'Aphrodite Wedding Planning & Decoration', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(280, 227, 'service', 'custom', 134, '2026-06-07 18:00:00', 500000.00, 'Aphrodite Wedding Planning & Decoration - Decoration', 'Aphrodite Wedding Planning & Decoration', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(281, 228, 'service', 'custom', 135, '2026-06-07 18:00:00', 3000000.00, 'Elysian Floral Art & Events Planning - Decoration', 'Elysian Floral Art & Events Planning', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(282, 229, 'service', 'custom', 135, '2026-03-15 18:00:00', 3000000.00, 'Elysian Floral Art & Events Planning - Decoration', 'Elysian Floral Art & Events Planning', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(283, 230, 'service', 'custom', 135, '2026-04-12 18:00:00', 3000000.00, 'Elysian Floral Art & Events Planning - Decoration', 'Elysian Floral Art & Events Planning', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(284, 231, 'service', 'custom', 136, '2026-03-15 18:00:00', 500000.00, 'S&S Events and Floral - Decoration', 'S&S Events and Floral', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(285, 232, 'service', 'custom', 136, '2026-04-12 18:00:00', 500000.00, 'S&S Events and Floral - Decoration', 'S&S Events and Floral', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(286, 233, 'service', 'custom', 137, '2026-04-12 18:00:00', 4300000.00, 'His & Hers Events and Wedding Studio - Decoration', 'His & Hers Events and Wedding Studio', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(287, 234, 'service', 'custom', 137, '2026-04-26 18:00:00', 4300000.00, 'His & Hers Events and Wedding Studio - Decoration', 'His & Hers Events and Wedding Studio', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(288, 235, 'service', 'custom', 137, '2026-05-10 18:00:00', 4300000.00, 'His & Hers Events and Wedding Studio - Decoration', 'His & Hers Events and Wedding Studio', 'Decoration', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(289, 236, 'service', 'custom', 138, '2026-04-26 18:00:00', 800000.00, 'Governor’s Residence - Venue', 'Governor’s Residence', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(290, 237, 'service', 'custom', 138, '2026-05-10 18:00:00', 800000.00, 'Governor’s Residence - Venue', 'Governor’s Residence', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(291, 238, 'service', 'custom', 139, '2026-05-10 18:00:00', 99000.00, 'Novotel Yangon Max - Venue', 'Novotel Yangon Max', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(292, 239, 'service', 'custom', 139, '2026-05-24 18:00:00', 99000.00, 'Novotel Yangon Max - Venue', 'Novotel Yangon Max', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(293, 240, 'service', 'custom', 139, '2026-06-07 18:00:00', 99000.00, 'Novotel Yangon Max - Venue', 'Novotel Yangon Max', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(294, 241, 'service', 'custom', 140, '2026-05-24 18:00:00', 800000.00, 'Sedona Hotel Yangon - Venue', 'Sedona Hotel Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(295, 242, 'service', 'custom', 140, '2026-06-07 18:00:00', 800000.00, 'Sedona Hotel Yangon - Venue', 'Sedona Hotel Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(296, 243, 'service', 'custom', 141, '2026-06-07 18:00:00', 63000.00, 'Inya Lake Hotel - Venue', 'Inya Lake Hotel', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(297, 244, 'service', 'custom', 141, '2026-03-15 18:00:00', 63000.00, 'Inya Lake Hotel - Venue', 'Inya Lake Hotel', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(298, 245, 'service', 'custom', 141, '2026-04-12 18:00:00', 63000.00, 'Inya Lake Hotel - Venue', 'Inya Lake Hotel', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(299, 246, 'service', 'custom', 142, '2026-03-15 18:00:00', 85500.00, 'Meliá Yangon - Venue', 'Meliá Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(300, 247, 'service', 'custom', 142, '2026-04-12 18:00:00', 85500.00, 'Meliá Yangon - Venue', 'Meliá Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(301, 248, 'service', 'custom', 143, '2026-04-12 18:00:00', 800000.00, 'Hotel Yangon - Venue', 'Hotel Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(302, 249, 'service', 'custom', 143, '2026-04-26 18:00:00', 800000.00, 'Hotel Yangon - Venue', 'Hotel Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(303, 250, 'service', 'custom', 143, '2026-05-10 18:00:00', 800000.00, 'Hotel Yangon - Venue', 'Hotel Yangon', 'Venue', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(304, 251, 'service', 'custom', 144, '2026-04-26 18:00:00', 135000.00, 'Myanmar Car Rental - Car Rental', 'Myanmar Car Rental', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(305, 252, 'service', 'custom', 144, '2026-05-10 18:00:00', 135000.00, 'Myanmar Car Rental - Car Rental', 'Myanmar Car Rental', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(306, 253, 'service', 'custom', 145, '2026-05-10 18:00:00', 180000.00, 'The Experience Rent A Car - Car Rental', 'The Experience Rent A Car', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(307, 254, 'service', 'custom', 145, '2026-05-24 18:00:00', 180000.00, 'The Experience Rent A Car - Car Rental', 'The Experience Rent A Car', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(308, 255, 'service', 'custom', 145, '2026-06-07 18:00:00', 180000.00, 'The Experience Rent A Car - Car Rental', 'The Experience Rent A Car', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(309, 256, 'service', 'custom', 146, '2026-05-24 18:00:00', 100000.00, 'AVIS MYANMAR - Car Rental', 'AVIS MYANMAR', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(310, 257, 'service', 'custom', 146, '2026-06-07 18:00:00', 100000.00, 'AVIS MYANMAR - Car Rental', 'AVIS MYANMAR', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(311, 258, 'service', 'custom', 147, '2026-06-07 18:00:00', 135000.00, 'inoventure - Car Rental', 'inoventure', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(312, 259, 'service', 'custom', 147, '2026-03-15 18:00:00', 135000.00, 'inoventure - Car Rental', 'inoventure', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(313, 260, 'service', 'custom', 147, '2026-04-12 18:00:00', 135000.00, 'inoventure - Car Rental', 'inoventure', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(314, 261, 'service', 'custom', 148, '2026-03-15 18:00:00', 55000.00, 'Concierge Business Limousine - Car Rental', 'Concierge Business Limousine', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(315, 262, 'service', 'custom', 148, '2026-04-12 18:00:00', 55000.00, 'Concierge Business Limousine - Car Rental', 'Concierge Business Limousine', 'Car', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(316, 263, 'service', 'custom', 149, '2026-04-12 18:00:00', 50000.00, 'Elegant Star (Recommended) - Invitation & Gifts', 'Elegant Star (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(317, 264, 'service', 'custom', 149, '2026-04-26 18:00:00', 50000.00, 'Elegant Star (Recommended) - Invitation & Gifts', 'Elegant Star (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(318, 265, 'service', 'custom', 149, '2026-05-10 18:00:00', 50000.00, 'Elegant Star (Recommended) - Invitation & Gifts', 'Elegant Star (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(319, 266, 'service', 'custom', 150, '2026-04-26 18:00:00', 50000.00, 'Memory Memory Handmade invitation cards and gifts (Recommended) - Invitation & Gifts', 'Memory Memory Handmade invitation cards and gifts (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(320, 267, 'service', 'custom', 150, '2026-05-10 18:00:00', 50000.00, 'Memory Memory Handmade invitation cards and gifts (Recommended) - Invitation & Gifts', 'Memory Memory Handmade invitation cards and gifts (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(321, 268, 'service', 'custom', 151, '2026-05-10 18:00:00', 50000.00, 'Moe Kaung Kin - Invitation & Gifts', 'Moe Kaung Kin', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(322, 269, 'service', 'custom', 151, '2026-05-24 18:00:00', 50000.00, 'Moe Kaung Kin - Invitation & Gifts', 'Moe Kaung Kin', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(323, 270, 'service', 'custom', 151, '2026-06-07 18:00:00', 50000.00, 'Moe Kaung Kin - Invitation & Gifts', 'Moe Kaung Kin', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(324, 271, 'service', 'custom', 152, '2026-05-24 18:00:00', 50000.00, 'Y Collection - Invitation & Gifts', 'Y Collection', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(325, 272, 'service', 'custom', 152, '2026-06-07 18:00:00', 50000.00, 'Y Collection - Invitation & Gifts', 'Y Collection', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(326, 273, 'service', 'custom', 153, '2026-06-07 18:00:00', 50000.00, 'Paperie Tale (Recommended) - Invitation & Gifts', 'Paperie Tale (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(327, 274, 'service', 'custom', 153, '2026-03-15 18:00:00', 50000.00, 'Paperie Tale (Recommended) - Invitation & Gifts', 'Paperie Tale (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(328, 275, 'service', 'custom', 153, '2026-04-12 18:00:00', 50000.00, 'Paperie Tale (Recommended) - Invitation & Gifts', 'Paperie Tale (Recommended)', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(329, 276, 'service', 'custom', 154, '2026-03-15 18:00:00', 50000.00, 'THIRI Handmade Invatation - Invitation & Gifts', 'THIRI Handmade Invatation', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(330, 277, 'service', 'custom', 154, '2026-04-12 18:00:00', 50000.00, 'THIRI Handmade Invatation - Invitation & Gifts', 'THIRI Handmade Invatation', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(331, 278, 'service', 'custom', 155, '2026-04-12 18:00:00', 50000.00, 'Pyan Kann - Invitation & Gifts', 'Pyan Kann', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(332, 279, 'service', 'custom', 155, '2026-04-26 18:00:00', 50000.00, 'Pyan Kann - Invitation & Gifts', 'Pyan Kann', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(333, 280, 'service', 'custom', 155, '2026-05-10 18:00:00', 50000.00, 'Pyan Kann - Invitation & Gifts', 'Pyan Kann', 'Invitation & Gifts', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(334, 281, 'service', 'custom', 156, '2026-04-26 18:00:00', 150000.00, 'SORA - Makeup & Hair', 'SORA', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(335, 282, 'service', 'custom', 156, '2026-05-10 18:00:00', 150000.00, 'SORA - Makeup & Hair', 'SORA', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(336, 283, 'service', 'custom', 157, '2026-05-10 18:00:00', 150000.00, 'ကိုသာဂိ - Makeup & Hair', 'ကိုသာဂိ', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(337, 284, 'service', 'custom', 157, '2026-05-24 18:00:00', 150000.00, 'ကိုသာဂိ - Makeup & Hair', 'ကိုသာဂိ', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(338, 285, 'service', 'custom', 157, '2026-06-07 18:00:00', 150000.00, 'ကိုသာဂိ - Makeup & Hair', 'ကိုသာဂိ', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(339, 286, 'service', 'custom', 158, '2026-05-24 18:00:00', 150000.00, 'Ma Htet-pop soul - Makeup & Hair', 'Ma Htet-pop soul', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(340, 287, 'service', 'custom', 158, '2026-06-07 18:00:00', 150000.00, 'Ma Htet-pop soul - Makeup & Hair', 'Ma Htet-pop soul', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(341, 288, 'service', 'custom', 159, '2026-06-07 18:00:00', 150000.00, 'Lin Lin - Makeup & Hair', 'Lin Lin', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(342, 289, 'service', 'custom', 159, '2026-03-15 18:00:00', 150000.00, 'Lin Lin - Makeup & Hair', 'Lin Lin', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(343, 290, 'service', 'custom', 159, '2026-04-12 18:00:00', 150000.00, 'Lin Lin - Makeup & Hair', 'Lin Lin', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(344, 291, 'service', 'custom', 160, '2026-03-15 18:00:00', 150000.00, 'make up Kin San Win - Makeup & Hair', 'make up Kin San Win', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(345, 292, 'service', 'custom', 160, '2026-04-12 18:00:00', 150000.00, 'make up Kin San Win - Makeup & Hair', 'make up Kin San Win', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(346, 293, 'service', 'custom', 161, '2026-04-12 18:00:00', 150000.00, 'Magic Touch Beauty Boutique - Makeup & Hair', 'Magic Touch Beauty Boutique', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(347, 294, 'service', 'custom', 161, '2026-04-26 18:00:00', 150000.00, 'Magic Touch Beauty Boutique - Makeup & Hair', 'Magic Touch Beauty Boutique', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(348, 295, 'service', 'custom', 161, '2026-05-10 18:00:00', 150000.00, 'Magic Touch Beauty Boutique - Makeup & Hair', 'Magic Touch Beauty Boutique', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(349, 296, 'service', 'custom', 162, '2026-04-26 18:00:00', 150000.00, 'Chi Chi’s Touch - Makeup & Hair', 'Chi Chi’s Touch', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(350, 297, 'service', 'custom', 162, '2026-05-10 18:00:00', 150000.00, 'Chi Chi’s Touch - Makeup & Hair', 'Chi Chi’s Touch', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(351, 298, 'service', 'custom', 163, '2026-05-10 18:00:00', 150000.00, 'Makeup Hazel - Makeup & Hair', 'Makeup Hazel', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(352, 299, 'service', 'custom', 163, '2026-05-24 18:00:00', 150000.00, 'Makeup Hazel - Makeup & Hair', 'Makeup Hazel', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(353, 300, 'service', 'custom', 163, '2026-06-07 18:00:00', 150000.00, 'Makeup Hazel - Makeup & Hair', 'Makeup Hazel', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(354, 301, 'service', 'custom', 164, '2026-05-24 18:00:00', 150000.00, 'Makeup Non Thit San - Makeup & Hair', 'Makeup Non Thit San', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(355, 302, 'service', 'custom', 164, '2026-06-07 18:00:00', 150000.00, 'Makeup Non Thit San - Makeup & Hair', 'Makeup Non Thit San', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(356, 303, 'service', 'custom', 165, '2026-06-07 18:00:00', 150000.00, 'Sweet Hair& Make up - Makeup & Hair', 'Sweet Hair& Make up', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(357, 304, 'service', 'custom', 165, '2026-03-15 18:00:00', 150000.00, 'Sweet Hair& Make up - Makeup & Hair', 'Sweet Hair& Make up', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(358, 305, 'service', 'custom', 165, '2026-04-12 18:00:00', 150000.00, 'Sweet Hair& Make up - Makeup & Hair', 'Sweet Hair& Make up', 'Make Up & Hair', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(363, 310, 'package', 'package', 30, NULL, 4150650.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(364, 311, 'service', 'custom', 64, '2026-06-21 00:00:00', 750000.00, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire', 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'Attire', 'http://localhost/GP/public/uploads/serviceHero3.png', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(365, 312, 'service', 'custom', 56, '2026-06-21 14:00:00', 150000.00, 'Lin Lin', 'Wyndham Grand Yangon Hotel', 'Make Up & Hair', 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260620065739-732ff480.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '14:00:00', '17:00:00', 'slot', NULL),
(366, 313, 'service', 'custom', 110, '2026-06-22 00:00:00', 1000000.00, 'U Hton - Jewelry', 'U Hton', 'Jewelry', 'http://localhost/GP/public/uploads/serviceHero3.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(367, 314, 'service', 'custom', 56, '2026-06-22 13:00:00', 150000.00, 'Lin Lin', 'Wyndham Grand Yangon Hotel', 'Make Up & Hair', 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260620065739-732ff480.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '13:00:00', '16:00:00', 'slot', NULL),
(368, 315, 'service', 'custom', 164, '2026-06-24 00:00:00', 15000000.00, 'Makeup Non Thit San - Makeup & Hair', 'Makeup Non Thit San', 'Make Up & Hair', 'http://localhost/GP/public/uploads/serviceHero2.png', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(369, 316, 'package', 'package', 30, NULL, 4150650.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(370, 317, 'package', 'package', 30, NULL, 3953000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(371, 318, 'package', 'package', 30, NULL, 3953000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(372, 319, 'service', 'custom', 48, '2026-06-26 09:00:00', 2100000.00, 'H&amp;H Floral and Wedding Service', 'JV', 'Decoration', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618210245-d5b57c03.jpg', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '13:00:00', 'slot', NULL),
(373, 320, 'service', 'custom', 124, '2026-06-24 00:00:00', 18000.00, 'Shan Yoe Yar Restaurant - Catering', 'Shan Yoe Yar Restaurant', 'Food', 'http://localhost/GP/public/uploads/serviceHero1.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(374, 321, 'service', 'custom', 105, '2026-07-01 00:00:00', 400000.00, 'Venus Wedding Studio - Studio', 'Venus Wedding Studio', 'Studio', 'http://localhost/GP/public/uploads/serviceHero1.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(375, 321, 'service', 'custom', 110, '2026-06-24 00:00:00', 1000000.00, 'U Hton - Jewelry', 'U Hton', 'Jewelry', 'http://localhost/GP/public/uploads/serviceHero3.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(377, 322, 'service', 'custom', 137, '2026-06-30 00:00:00', 4300000.00, 'His & Hers Events and Wedding Studio - Decoration', 'His & Hers Events and Wedding Studio', 'Decoration', 'http://localhost/GP/public/uploads/serviceHero2.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(378, 322, 'service', 'custom', 69, '2026-06-30 00:00:00', 300000.00, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire', 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး', 'Attire', 'http://localhost/GP/public/uploads/serviceHero2.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL);
INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `source`, `item_id`, `booking_date`, `price`, `item_name`, `supplier_name`, `category_name`, `thumbnail_url`, `status`, `venue_room_id`, `attire_item_id`, `rental_type`, `borrow_date`, `return_date`, `decoration_style_id`, `cake_design_id`, `slot_id`, `start_time`, `end_time`, `booking_type`, `package_booking_item_id`) VALUES
(380, 323, 'package', 'package', 31, NULL, 3953000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(381, 324, 'service', 'custom', 124, '2026-06-25 00:00:00', 18000.00, 'Shan Yoe Yar Restaurant - Catering', 'Shan Yoe Yar Restaurant', 'Food', 'http://localhost/GP/public/uploads/serviceHero1.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(382, 325, 'package', 'package', 20, NULL, 70000.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618152115-7d249ee0.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(383, 326, 'service', 'custom', 67, '2026-06-28 10:00:00', 200000.00, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN - Wedding Attire', 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'Attire', 'http://localhost/GP/public/uploads/serviceHero3.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10:00:00', '18:00:00', 'fullday', NULL),
(384, 327, 'service', 'custom', 69, '2026-06-25 00:00:00', 300000.00, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire', 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး', 'Attire', 'http://localhost/GP/public/uploads/serviceHero2.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(385, 328, 'service', 'custom', 163, '2026-06-25 00:00:00', 300000.00, 'Makeup Hazel - Makeup & Hair', 'Makeup Hazel', 'Make Up & Hair', 'http://localhost/GP/public/uploads/serviceHero1.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(386, 329, 'service', 'custom', 119, '2026-06-25 00:00:00', 200000.00, 'Season - Cake', 'Season', 'Food', 'http://localhost/GP/public/uploads/serviceHero2.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(387, 330, 'service', 'custom', 156, '2026-06-30 00:00:00', 300000.00, 'SORA - Makeup & Hair', 'SORA', 'Make Up & Hair', 'http://localhost/GP/public/uploads/serviceHero3.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(388, 331, 'service', 'custom', 154, '2026-06-28 00:00:00', 50000.00, 'THIRI Handmade Invatation - Invitation & Gifts', 'THIRI Handmade Invatation', 'Invitation & Gifts', 'http://localhost/GP/public/uploads/serviceHero1.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(389, 332, 'service', 'custom', 144, '2026-06-26 00:00:00', 135000.00, 'Myanmar Car Rental - Car Rental', 'Myanmar Car Rental', 'Car', 'http://localhost/GP/public/uploads/serviceHero3.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(390, 333, 'service', 'custom', 105, '2026-06-26 00:00:00', 400000.00, 'Venus Wedding Studio - Studio', 'Venus Wedding Studio', 'Studio', 'http://localhost/GP/public/uploads/serviceHero1.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(391, 333, 'service', 'custom', 103, '2026-06-26 00:00:00', 445000.00, 'Forever One Stop Wedding Studio - Studio', 'Forever One Stop Wedding Studio', 'Studio', 'http://localhost/GP/public/uploads/serviceHero2.png', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fullday', NULL),
(393, 334, 'service', 'custom', 49, '2026-07-26 09:00:00', 900000.00, 'Zephyr Sein Lann So pyay', 'JV', 'Venue', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618212654-323d369a.jpg', 'accepted', 22, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(394, 335, 'service', 'custom', 55, '2026-06-29 10:00:00', 500000.00, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'Wyndham Grand Yangon Hotel', 'Attire', 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260619054309-45b53c74.jpg', 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10:00:00', '11:00:00', 'slot', NULL),
(395, 336, 'service', 'custom', 167, '2026-07-08 09:00:00', 2500000.00, 'Grand', 'Shwe Phoo Sar', 'Attire', '', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '10:00:00', 'slot', NULL),
(396, 337, 'service', 'custom', 49, '2026-07-06 09:00:00', 3000000.00, 'Zephyr Sein Lann So pyay', 'JV', 'Venue', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618212654-323d369a.jpg', 'cancelled', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(397, 338, 'service', 'custom', 49, '2026-07-06 09:00:00', 3000000.00, 'Zephyr Sein Lann So pyay', 'JV', 'Venue', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618212654-323d369a.jpg', 'accepted', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(398, 339, 'service', 'custom', 50, '2026-07-04 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(399, 340, 'service', 'custom', 50, '2026-06-28 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 88, '09:00:00', '17:00:00', 'slot', NULL),
(400, 341, 'service', 'custom', 50, '2026-06-28 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 88, '09:00:00', '17:00:00', 'slot', NULL),
(401, 342, 'service', 'custom', 50, '2026-06-29 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 106, '09:00:00', '17:00:00', 'slot', NULL),
(402, 343, 'service', 'custom', 50, '2026-06-30 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(403, 344, 'service', 'custom', 50, '2026-06-30 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(404, 345, 'service', 'custom', 50, '2026-06-30 09:00:00', 2100000.00, 'H & H Wedding Studio', 'JV', 'Studio', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 'accepted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL),
(405, 346, 'service', 'custom', 49, '2026-07-05 09:00:00', 3000000.00, 'Zephyr Sein Lann So pyay', 'JV', 'Venue', 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618212654-323d369a.jpg', 'accepted', 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '09:00:00', '17:00:00', 'slot', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_slot_reservations`
--

CREATE TABLE `booking_slot_reservations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `booking_item_id` bigint(20) DEFAULT NULL,
  `package_item_id` bigint(20) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `slot_id` bigint(20) NOT NULL,
  `source` enum('custom','package') NOT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `released_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_slot_reservations`
--

INSERT INTO `booking_slot_reservations` (`id`, `booking_id`, `booking_item_id`, `package_item_id`, `service_id`, `slot_id`, `source`, `reserved_at`, `released_at`) VALUES
(1, 53, NULL, 82, 105, 27, 'package', '2026-06-20 14:30:35', '2026-06-22 09:39:43'),
(22, 310, NULL, 117, 56, 84, 'package', '2026-06-21 06:25:06', NULL),
(23, 310, NULL, 110, 47, 85, 'package', '2026-06-21 06:25:06', NULL),
(24, 310, NULL, 113, 55, 86, 'package', '2026-06-21 06:25:06', NULL),
(25, 310, NULL, 111, 48, 87, 'package', '2026-06-21 06:25:06', NULL),
(26, 310, NULL, 112, 50, 88, 'package', '2026-06-21 06:25:06', NULL),
(27, 316, NULL, 117, 56, 102, 'package', '2026-06-22 09:49:24', NULL),
(28, 316, NULL, 110, 47, 103, 'package', '2026-06-22 09:49:24', NULL),
(29, 316, NULL, 113, 55, 104, 'package', '2026-06-22 09:49:24', NULL),
(30, 316, NULL, 111, 48, 105, 'package', '2026-06-22 09:49:24', NULL),
(31, 316, NULL, 112, 50, 106, 'package', '2026-06-22 09:49:24', NULL),
(32, 317, NULL, 117, 56, 108, 'package', '2026-06-23 05:07:04', NULL),
(33, 317, NULL, 117, 56, 109, 'package', '2026-06-23 05:07:04', NULL),
(34, 317, NULL, 110, 47, 110, 'package', '2026-06-23 05:07:04', NULL),
(35, 317, NULL, 113, 55, 111, 'package', '2026-06-23 05:07:04', NULL),
(36, 317, NULL, 111, 48, 112, 'package', '2026-06-23 05:07:04', NULL),
(37, 317, NULL, 112, 50, 113, 'package', '2026-06-23 05:07:04', NULL),
(38, 317, NULL, 117, 56, 114, 'package', '2026-06-23 05:07:04', NULL),
(39, 317, NULL, 110, 47, 115, 'package', '2026-06-23 05:07:04', NULL),
(40, 317, NULL, 113, 55, 116, 'package', '2026-06-23 05:07:04', NULL),
(41, 317, NULL, 111, 48, 117, 'package', '2026-06-23 05:07:04', NULL),
(42, 317, NULL, 112, 50, 118, 'package', '2026-06-23 05:07:04', NULL),
(43, 317, NULL, 117, 56, 119, 'package', '2026-06-23 05:07:04', NULL),
(44, 318, NULL, 117, 56, 120, 'package', '2026-06-23 05:15:13', NULL),
(45, 318, NULL, 117, 56, 121, 'package', '2026-06-23 05:15:13', NULL),
(46, 318, NULL, 110, 47, 122, 'package', '2026-06-23 05:15:13', NULL),
(47, 318, NULL, 113, 55, 123, 'package', '2026-06-23 05:15:13', NULL),
(48, 318, NULL, 111, 48, 124, 'package', '2026-06-23 05:15:13', NULL),
(49, 318, NULL, 112, 50, 125, 'package', '2026-06-23 05:15:13', NULL),
(50, 318, NULL, 117, 56, 126, 'package', '2026-06-23 05:15:13', NULL),
(51, 318, NULL, 110, 47, 127, 'package', '2026-06-23 05:15:13', NULL),
(52, 318, NULL, 113, 55, 128, 'package', '2026-06-23 05:15:13', NULL),
(53, 318, NULL, 111, 48, 129, 'package', '2026-06-23 05:15:13', NULL),
(54, 318, NULL, 112, 50, 130, 'package', '2026-06-23 05:15:13', NULL),
(55, 318, NULL, 117, 56, 131, 'package', '2026-06-23 05:15:13', NULL),
(56, 323, NULL, 122, 56, 132, 'package', '2026-06-24 02:53:58', NULL),
(57, 323, NULL, 122, 56, 133, 'package', '2026-06-24 02:53:58', NULL),
(58, 323, NULL, 118, 47, 134, 'package', '2026-06-24 02:53:58', NULL),
(59, 323, NULL, 121, 55, 135, 'package', '2026-06-24 02:53:58', NULL),
(60, 323, NULL, 119, 48, 136, 'package', '2026-06-24 02:53:58', NULL),
(61, 323, NULL, 120, 50, 137, 'package', '2026-06-24 02:53:58', NULL),
(62, 323, NULL, 122, 56, 138, 'package', '2026-06-24 02:53:58', NULL),
(63, 323, NULL, 118, 47, 139, 'package', '2026-06-24 02:53:58', NULL),
(64, 323, NULL, 121, 55, 140, 'package', '2026-06-24 02:53:58', NULL),
(65, 323, NULL, 119, 48, 141, 'package', '2026-06-24 02:53:58', NULL),
(66, 323, NULL, 120, 50, 142, 'package', '2026-06-24 02:53:58', NULL),
(67, 323, NULL, 122, 56, 143, 'package', '2026-06-24 02:53:58', NULL),
(68, 325, NULL, 65, 42, 144, 'package', '2026-06-24 03:09:11', NULL),
(69, 325, NULL, 65, 42, 145, 'package', '2026-06-24 03:09:11', NULL),
(70, 340, 399, NULL, 50, 88, 'custom', '2026-06-27 10:17:07', '2026-06-27 10:36:22'),
(71, 341, 400, NULL, 50, 88, 'custom', '2026-06-27 10:39:57', NULL),
(72, 342, 401, NULL, 50, 106, 'custom', '2026-06-27 10:53:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_status_logs`
--

CREATE TABLE `booking_status_logs` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` bigint(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_status_logs`
--

INSERT INTO `booking_status_logs` (`id`, `booking_id`, `old_status`, `new_status`, `changed_by`, `note`, `created_at`) VALUES
(78, 48, NULL, 'draft', 27, NULL, '2026-06-18 09:32:46'),
(79, 48, 'draft', 'pending_supplier_response', 27, NULL, '2026-06-18 09:32:46'),
(80, 48, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-18 09:33:50'),
(81, 48, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-18 09:33:50'),
(82, 48, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-18 09:35:16'),
(83, 49, NULL, 'draft', 30, NULL, '2026-06-18 11:10:23'),
(84, 49, 'draft', 'pending_payment', 30, NULL, '2026-06-18 11:10:23'),
(85, 49, 'payment_submitted', 'confirmed', 1, 'Deposit verified by admin', '2026-06-18 11:11:51'),
(86, 50, NULL, 'draft', 30, NULL, '2026-06-18 14:36:16'),
(87, 50, 'draft', 'pending_payment', 30, NULL, '2026-06-18 14:36:16'),
(88, 50, 'payment_submitted', 'cancellation_requested', NULL, 'Cancellation requested: ငါတို့ မဂ်လာဆောင်မယ့်ရက် ပြောင်းသွားလို့ပါ', '2026-06-18 15:15:29'),
(89, 49, 'confirmed', 'cancellation_requested', NULL, 'Cancellation requested: the wedding date is change', '2026-06-19 07:38:05'),
(90, 50, NULL, 'cancelled', 1, 'Cancelled by admin: မဂ်လာဆောင်မယ့်ရက်ပြောင်းခြင်း', '2026-06-19 10:51:54'),
(91, 50, 'cancelled', 'confirmed', 1, 'Deposit verified by admin', '2026-06-19 10:52:27'),
(92, 49, NULL, 'cancelled', 1, 'Cancelled by admin: deposit payed and the wedding date is changed', '2026-06-19 13:03:53'),
(93, 50, 'cancelled', 'cancelled', 1, 'Deposit marked as refunded by admin (manual): 15,372 MMK', '2026-06-19 13:17:24'),
(94, 50, NULL, 'cancelled', 1, 'Cancelled by admin: blah blah', '2026-06-19 13:17:24'),
(95, 51, NULL, 'draft', 30, NULL, '2026-06-19 15:48:47'),
(96, 51, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-19 15:48:47'),
(97, 51, NULL, 'supplier_rejected', NULL, 'Supplier declineed booking', '2026-06-20 01:32:06'),
(98, 51, 'pending_supplier_response', 'cancelled', NULL, 'Supplier declined', '2026-06-20 01:32:06'),
(99, 52, NULL, 'draft', 30, NULL, '2026-06-20 01:33:55'),
(100, 52, 'draft', 'pending_payment', 30, NULL, '2026-06-20 01:33:55'),
(101, 52, 'payment_submitted', 'confirmed', 1, 'Deposit verified by admin', '2026-06-20 01:36:40'),
(102, 53, NULL, 'draft', 30, NULL, '2026-06-20 02:31:24'),
(103, 53, 'draft', 'pending_payment', 30, NULL, '2026-06-20 02:31:24'),
(104, 53, 'payment_submitted', 'confirmed', 1, 'Deposit verified by admin', '2026-06-20 02:34:02'),
(105, 53, NULL, 'supplier_needs_replacement', NULL, 'Supplier declined; awaiting admin replacement', '2026-06-20 03:00:00'),
(106, 53, 'confirmed', 'replacement_pending', NULL, 'JV declined; replacement needed', '2026-06-20 03:00:00'),
(107, 53, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-20 15:03:31'),
(108, 53, 'replacement_pending', 'confirmed', NULL, 'Replacement supplier accepted', '2026-06-20 15:03:31'),
(109, 53, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-20 15:24:12'),
(110, 53, NULL, 'replacement_supplier_accepted', NULL, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN accepted; other replacements are still pending', '2026-06-20 15:24:12'),
(111, 50, NULL, 'replacement_delta_refunded', NULL, 'Refunded paid price-difference of 30,000 after replacement fell through', '2026-06-20 19:53:02'),
(112, 310, NULL, 'draft', 30, NULL, '2026-06-21 06:25:06'),
(113, 310, 'draft', 'pending_payment', 30, NULL, '2026-06-21 06:25:06'),
(114, 310, 'payment_submitted', 'confirmed', 1, 'Deposit verified by admin', '2026-06-21 06:25:58'),
(115, 311, NULL, 'draft', 30, NULL, '2026-06-21 07:12:10'),
(116, 311, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-21 07:12:10'),
(117, 311, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-21 07:13:07'),
(118, 311, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-21 07:13:07'),
(119, 311, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-21 07:24:36'),
(120, 312, NULL, 'draft', 30, NULL, '2026-06-21 07:39:00'),
(121, 312, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-21 07:39:00'),
(122, 312, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-21 07:39:55'),
(123, 312, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-21 07:39:55'),
(124, 312, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-21 07:45:29'),
(125, 312, 'paid', 'cancellation_requested', NULL, 'Cancellation requested: ပွဲနေ့က မနက်ဖြန် ပြောင်းသွားလို့ပါ', '2026-06-21 09:46:23'),
(126, 312, 'confirmed', 'cancellation_requested', NULL, 'Cancellation requested: Testing cancellation flow', '2026-06-21 10:44:43'),
(128, 312, 'confirmed', 'cancellation_requested', NULL, 'Cancellation requested: Testing cancellation flow', '2026-06-21 10:47:50'),
(129, 312, 'cancellation_requested', 'supplier_cancellation_approved', 29, 'Supplier approved cancellation request.', '2026-06-21 10:47:50'),
(130, 312, 'confirmed', 'cancellation_requested', NULL, 'Cancellation requested: Testing decline', '2026-06-21 10:47:50'),
(131, 312, 'cancellation_requested', 'confirmed', 29, 'Supplier declined cancellation. Reason: Work already in progress', '2026-06-21 10:47:50'),
(132, 312, 'cancellation_requested', 'supplier_cancellation_approved', 29, 'Supplier approved cancellation request.', '2026-06-21 12:30:00'),
(133, 312, 'cancelled', 'cancelled', 1, 'Deposit marked as refunded by admin (manual): 30,000 MMK', '2026-06-21 12:32:52'),
(134, 312, NULL, 'cancelled', 1, 'Cancelled by admin: customer request cancle and supplier accept', '2026-06-21 12:32:52'),
(135, 311, 'paid', 'cancellation_requested', NULL, 'Cancellation requested: we don\'t need this anymore. Thanks', '2026-06-21 13:27:23'),
(136, 313, NULL, 'draft', 30, NULL, '2026-06-22 03:33:34'),
(137, 313, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-22 03:33:34'),
(138, 314, NULL, 'draft', 30, NULL, '2026-06-22 03:45:48'),
(139, 314, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-22 03:45:48'),
(140, 314, NULL, 'supplier_rejected', NULL, 'Supplier declineed booking', '2026-06-22 03:46:27'),
(141, 314, 'pending_supplier_response', 'cancelled', NULL, 'Supplier declined', '2026-06-22 03:46:27'),
(142, 315, NULL, 'draft', 30, NULL, '2026-06-22 07:37:58'),
(143, 315, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-22 07:37:58'),
(144, 315, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-22 07:48:12'),
(145, 315, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-22 07:48:12'),
(146, 53, 'cancelled', 'cancelled', 1, 'Deposit marked as refunded by admin (manual): 1,014,800 MMK', '2026-06-22 09:39:43'),
(147, 53, NULL, 'cancelled', 1, 'Cancelled by admin: cancle booking', '2026-06-22 09:39:43'),
(148, 316, NULL, 'draft', 30, NULL, '2026-06-22 09:49:24'),
(149, 316, 'draft', 'pending_payment', 30, NULL, '2026-06-22 09:49:24'),
(150, 317, NULL, 'draft', 112, NULL, '2026-06-23 05:07:04'),
(151, 317, 'draft', 'pending_payment', 112, NULL, '2026-06-23 05:07:04'),
(152, 318, NULL, 'draft', 113, NULL, '2026-06-23 05:15:13'),
(153, 318, 'draft', 'pending_payment', 113, NULL, '2026-06-23 05:15:13'),
(154, 319, NULL, 'draft', 113, NULL, '2026-06-23 05:17:31'),
(155, 319, 'draft', 'pending_supplier_response', 113, NULL, '2026-06-23 05:17:31'),
(156, 320, NULL, 'draft', 109, NULL, '2026-06-23 15:37:50'),
(157, 320, 'draft', 'pending_supplier_response', 109, NULL, '2026-06-23 15:37:50'),
(158, 321, NULL, 'draft', 109, NULL, '2026-06-24 02:45:33'),
(159, 321, 'draft', 'pending_supplier_response', 109, NULL, '2026-06-24 02:45:33'),
(160, 322, NULL, 'draft', 109, NULL, '2026-06-24 02:50:09'),
(161, 322, 'draft', 'pending_supplier_response', 109, NULL, '2026-06-24 02:50:09'),
(162, 323, NULL, 'draft', 112, NULL, '2026-06-24 02:53:58'),
(163, 323, 'draft', 'pending_payment', 112, NULL, '2026-06-24 02:53:58'),
(164, 324, NULL, 'draft', 109, NULL, '2026-06-24 02:55:00'),
(165, 324, 'draft', 'pending_supplier_response', 109, NULL, '2026-06-24 02:55:00'),
(166, 325, NULL, 'draft', 109, NULL, '2026-06-24 03:09:11'),
(167, 325, 'draft', 'pending_payment', 109, NULL, '2026-06-24 03:09:11'),
(168, 324, 'pending_supplier_response', 'cancellation_requested', NULL, 'Cancellation requested: bjm,jnhbgfghjkl;l,mnbvc', '2026-06-24 03:14:24'),
(169, 326, NULL, 'draft', 123, NULL, '2026-06-24 03:37:59'),
(170, 326, 'draft', 'pending_supplier_response', 123, NULL, '2026-06-24 03:37:59'),
(171, 326, 'pending_supplier_response', 'cancellation_requested', NULL, 'Cancellation requested: အလျင်လိုနေလို့ပါ', '2026-06-24 03:39:10'),
(172, 327, NULL, 'draft', 123, NULL, '2026-06-24 04:18:13'),
(173, 327, 'draft', 'pending_supplier_response', 123, NULL, '2026-06-24 04:18:13'),
(174, 328, NULL, 'draft', 113, NULL, '2026-06-24 04:22:13'),
(175, 328, 'draft', 'pending_supplier_response', 113, NULL, '2026-06-24 04:22:13'),
(176, 327, 'pending_supplier_response', 'cancellation_requested', NULL, 'Cancellation requested: အချိန်မမှီတော့လို့ပါ', '2026-06-24 04:22:52'),
(177, 328, 'pending_supplier_response', 'cancellation_requested', NULL, 'Cancellation requested: gu gu gar gar', '2026-06-24 04:23:43'),
(178, 329, NULL, 'draft', 124, NULL, '2026-06-24 04:53:07'),
(179, 329, 'draft', 'pending_supplier_response', 124, NULL, '2026-06-24 04:53:07'),
(180, 330, NULL, 'draft', 109, NULL, '2026-06-25 02:48:29'),
(181, 330, 'draft', 'pending_supplier_response', 109, NULL, '2026-06-25 02:48:29'),
(182, 331, NULL, 'draft', 131, NULL, '2026-06-25 03:27:09'),
(183, 331, 'draft', 'pending_supplier_response', 131, NULL, '2026-06-25 03:27:09'),
(184, 332, NULL, 'draft', 131, NULL, '2026-06-25 04:57:50'),
(185, 332, 'draft', 'pending_supplier_response', 131, NULL, '2026-06-25 04:57:50'),
(186, 333, NULL, 'draft', 132, NULL, '2026-06-25 05:01:14'),
(187, 333, 'draft', 'pending_supplier_response', 132, NULL, '2026-06-25 05:01:14'),
(188, 333, 'pending_supplier_response', 'cancellation_requested', NULL, 'Cancellation requested: I don\'t have money', '2026-06-25 05:04:43'),
(189, 334, NULL, 'draft', 29, NULL, '2026-06-25 13:10:35'),
(190, 334, 'draft', 'pending_supplier_response', 29, NULL, '2026-06-25 13:10:35'),
(191, 334, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-25 16:17:16'),
(192, 334, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-25 16:17:16'),
(193, 335, NULL, 'draft', 29, NULL, '2026-06-26 03:33:41'),
(194, 335, 'draft', 'pending_supplier_response', 29, NULL, '2026-06-26 03:33:41'),
(195, 336, NULL, 'draft', 126, NULL, '2026-06-26 04:40:29'),
(196, 336, 'draft', 'pending_supplier_response', 126, NULL, '2026-06-26 04:40:29'),
(197, 336, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-26 04:41:05'),
(198, 336, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-26 04:41:05'),
(199, 316, 'payment_submitted', 'confirmed', 1, 'Deposit verified by admin', '2026-06-27 07:02:43'),
(200, 337, NULL, 'draft', 30, NULL, '2026-06-27 07:41:56'),
(201, 337, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 07:41:56'),
(202, 337, NULL, 'supplier_rejected', NULL, 'Supplier declineed booking', '2026-06-27 07:42:12'),
(203, 337, 'pending_supplier_response', 'cancelled', NULL, 'Supplier declined', '2026-06-27 07:42:12'),
(204, 338, NULL, 'draft', 30, NULL, '2026-06-27 08:07:50'),
(205, 338, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 08:07:50'),
(206, 338, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 08:08:05'),
(207, 338, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 08:08:05'),
(208, 338, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 08:09:04'),
(209, 339, NULL, 'draft', 30, NULL, '2026-06-27 09:57:00'),
(210, 339, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 09:57:00'),
(211, 339, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 09:57:13'),
(212, 339, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 09:57:13'),
(213, 339, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 09:58:02'),
(214, 339, 'cancelled', 'cancelled', 1, 'Refund of 273,000 MMK queued (50% refund - cancelled 2-7 days before event)', '2026-06-27 09:59:09'),
(215, 339, NULL, 'cancelled', 1, 'Cancelled by admin: cancle ချင်လို့ကွာ', '2026-06-27 09:59:09'),
(216, 339, 'cancelled', 'cancelled', 1, 'Refund processing: proof uploaded via AYA Pay (ref: TXN-12345678)', '2026-06-27 10:12:16'),
(217, 340, NULL, 'draft', 30, NULL, '2026-06-27 10:17:07'),
(218, 340, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 10:17:07'),
(219, 340, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 10:17:26'),
(220, 340, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 10:17:26'),
(221, 340, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 10:26:01'),
(222, 340, 'pending_final_payment', 'cancellation_requested', NULL, 'Cancellation requested: we don\'t want you', '2026-06-27 10:35:33'),
(223, 340, 'cancelled', 'cancelled', 1, 'Refund of 0 MMK queued (No refund - cancelled less than 2 days before event)', '2026-06-27 10:36:22'),
(224, 340, NULL, 'cancelled', 1, 'Cancelled by admin: the customer sent the canclelation request', '2026-06-27 10:36:22'),
(225, 340, 'cancelled', 'cancelled', 1, 'Refund processing: proof uploaded via AYA Pay (ref: TXN-12345678)', '2026-06-27 10:36:38'),
(226, 340, 'cancelled', 'cancelled', 1, 'Refund completed: 0 MMK', '2026-06-27 10:37:19'),
(227, 339, 'cancelled', 'cancelled', 1, 'Refund processing: proof uploaded via AYA Pay (ref: TXN-12345678)', '2026-06-27 10:37:41'),
(228, 339, 'cancelled', 'cancelled', 1, 'Refund completed: 273,000 MMK', '2026-06-27 10:38:01'),
(229, 341, NULL, 'draft', 30, NULL, '2026-06-27 10:39:57'),
(230, 341, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 10:39:57'),
(231, 341, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 10:40:10'),
(232, 341, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 10:40:10'),
(233, 341, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 10:41:11'),
(234, 52, 'cancelled', 'cancelled', 1, 'Refund of 0 MMK queued (No refund - cancelled less than 2 days before event)', '2026-06-27 10:48:41'),
(235, 52, NULL, 'cancelled', 1, 'Cancelled by admin: refund', '2026-06-27 10:48:41'),
(236, 52, 'cancelled', 'cancelled', 1, 'Refund processing: proof uploaded via AYA Pay (ref: TXN-12345678)', '2026-06-27 10:48:59'),
(237, 52, 'cancelled', 'cancelled', 1, 'Refund completed: 0 MMK', '2026-06-27 10:49:09'),
(238, 342, NULL, 'draft', 30, NULL, '2026-06-27 10:53:32'),
(239, 342, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 10:53:32'),
(240, 342, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 10:53:43'),
(241, 342, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 10:53:43'),
(242, 342, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 10:54:18'),
(243, 343, NULL, 'draft', 30, NULL, '2026-06-27 11:04:29'),
(244, 343, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 11:04:29'),
(245, 343, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 11:04:45'),
(246, 343, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 11:04:45'),
(247, 343, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 11:05:25'),
(248, 344, NULL, 'draft', 30, NULL, '2026-06-27 11:09:35'),
(249, 344, 'draft', 'pending_supplier_response', 30, NULL, '2026-06-27 11:09:35'),
(250, 344, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 11:09:51'),
(251, 344, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 11:09:51'),
(252, 344, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 11:11:25'),
(253, 344, 'paid', 'cancellation_requested', 24, 'Supplier requested cancellation. Reason: i don\'t want to do', '2026-06-27 11:30:41'),
(254, 344, 'cancelled', 'cancelled', 1, 'Refund of 273,000 MMK queued (50% refund - cancelled 2-7 days before event)', '2026-06-27 11:36:37'),
(255, 344, NULL, 'cancelled', 1, 'Cancelled by admin: supplier request to cancle', '2026-06-27 11:36:37'),
(256, 344, 'cancelled', 'cancelled', 1, 'Refund processing: proof uploaded via AYA Pay (ref: TXN-12345678)', '2026-06-27 11:36:50'),
(257, 345, NULL, 'draft', 29, NULL, '2026-06-27 11:39:58'),
(258, 345, 'draft', 'pending_supplier_response', 29, NULL, '2026-06-27 11:39:58'),
(259, 345, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 11:40:10'),
(260, 345, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 11:40:10'),
(261, 345, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 11:40:52'),
(262, 345, 'pending_final_payment', 'finalized', 1, 'Remaining payment verified by admin', '2026-06-27 11:56:40'),
(263, 346, NULL, 'draft', 29, NULL, '2026-06-27 12:01:48'),
(264, 346, 'draft', 'pending_supplier_response', 29, NULL, '2026-06-27 12:01:48'),
(265, 346, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-27 12:02:09'),
(266, 346, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-27 12:02:09'),
(267, 346, 'payment_submitted', 'paid', 1, 'Deposit verified by admin', '2026-06-27 12:03:14');

-- --------------------------------------------------------

--
-- Table structure for table `booking_suppliers`
--

CREATE TABLE `booking_suppliers` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `package_item_id` bigint(20) DEFAULT NULL,
  `item_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled','rejected','needs_replacement','replaced','declined_again','cancellation_pending','cancellation_approved','supplier_cancellation_requested') NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `declined_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `payout_status` enum('unpaid','processing','paid') NOT NULL DEFAULT 'unpaid',
  `replaced_by_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_suppliers`
--

INSERT INTO `booking_suppliers` (`id`, `booking_id`, `supplier_id`, `service_id`, `category_id`, `package_item_id`, `item_price`, `status`, `confirmed_at`, `declined_at`, `completed_at`, `payout_status`, `replaced_by_id`, `created_at`, `updated_at`) VALUES
(44, 48, 21, NULL, NULL, NULL, NULL, 'confirmed', '2026-06-18 09:33:50', NULL, NULL, 'unpaid', NULL, '2026-06-18 09:32:46', '2026-06-18 09:33:50'),
(45, 49, 20, 47, 2, 67, 750000.00, 'cancelled', '2026-06-18 11:11:38', NULL, NULL, 'unpaid', NULL, '2026-06-18 11:10:23', '2026-06-19 15:25:05'),
(46, 49, 21, NULL, NULL, NULL, NULL, 'cancelled', '2026-06-18 11:11:38', NULL, NULL, 'unpaid', NULL, '2026-06-18 11:10:23', '2026-06-19 13:03:53'),
(48, 50, 20, NULL, NULL, NULL, NULL, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-18 14:36:16', '2026-06-19 10:51:54'),
(49, 50, 21, 42, 6, 65, 70000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-18 14:36:16', '2026-06-19 13:11:44'),
(59, 51, 20, 47, 2, 71, 750000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-20 03:17:33'),
(60, 51, 20, 48, 12, 73, 2100000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-20 03:17:33'),
(61, 51, 20, 49, 6, 74, 900000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-20 03:17:33'),
(62, 51, 20, 50, 5, 75, 200000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-20 03:17:33'),
(63, 52, 20, 47, 2, 79, 750000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-27 10:48:41'),
(64, 52, 20, 48, 12, 80, 2100000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-27 10:48:41'),
(65, 52, 20, 50, 5, 82, 200000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-27 10:48:41'),
(66, 52, 21, 55, 2, 86, 40000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-27 10:48:41'),
(67, 53, 20, 47, 2, NULL, 750000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', 75, '2026-06-20 03:17:33', '2026-06-22 09:39:43'),
(68, 53, 20, 48, 12, NULL, 2100000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', 74, '2026-06-20 03:17:33', '2026-06-22 09:39:43'),
(69, 53, 20, 50, 5, NULL, 200000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', 256, '2026-06-20 03:17:33', '2026-06-22 09:39:43'),
(70, 53, 21, 55, 2, 86, 40000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 03:17:33', '2026-06-22 09:39:43'),
(74, 53, 23, 59, 12, 80, 2900000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 07:33:09', '2026-06-22 09:39:43'),
(75, 53, 31, 67, 2, 79, 750000.00, 'cancelled', '2026-06-20 15:24:12', NULL, NULL, 'unpaid', NULL, '2026-06-20 09:24:06', '2026-06-22 09:39:43'),
(76, 126, 23, 59, 12, NULL, 2900000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(77, 127, 23, 59, 12, NULL, 2900000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(78, 128, 24, 60, 6, NULL, 2000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(79, 129, 24, 60, 6, NULL, 2000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(80, 130, 24, 60, 6, NULL, 2000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(81, 131, 25, 61, 6, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(82, 132, 25, 61, 6, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(83, 133, 26, 62, 6, NULL, 900000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(84, 134, 26, 62, 6, NULL, 900000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(85, 135, 26, 62, 6, NULL, 900000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(86, 136, 27, 63, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(87, 137, 27, 63, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(88, 138, 28, 64, 2, NULL, 750000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(89, 139, 28, 64, 2, NULL, 750000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(90, 140, 28, 64, 2, NULL, 750000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(91, 141, 29, 65, 2, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(92, 142, 29, 65, 2, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(93, 143, 30, 66, 2, NULL, 1500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(94, 144, 30, 66, 2, NULL, 1500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(95, 145, 30, 66, 2, NULL, 1500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(96, 146, 31, 67, 2, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(97, 147, 31, 67, 2, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(98, 148, 32, 68, 2, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(99, 149, 32, 68, 2, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(100, 150, 32, 68, 2, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(101, 151, 33, 69, 2, NULL, 300000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(102, 152, 33, 69, 2, NULL, 300000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(103, 153, 34, 70, 2, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(104, 154, 34, 70, 2, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(105, 155, 34, 70, 2, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(106, 156, 35, 71, 2, NULL, 480000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(107, 157, 35, 71, 2, NULL, 480000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(108, 158, 68, 103, 5, NULL, 445000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(109, 159, 68, 103, 5, NULL, 445000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(110, 160, 68, 103, 5, NULL, 445000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(111, 161, 69, 104, 5, NULL, 430000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(112, 162, 69, 104, 5, NULL, 430000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(113, 163, 70, 105, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(114, 164, 70, 105, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(115, 165, 70, 105, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(116, 166, 71, 106, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(117, 167, 71, 106, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(118, 168, 72, 107, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(119, 169, 72, 107, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(120, 170, 72, 107, 5, NULL, 400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(121, 171, 73, 108, 6, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(122, 172, 73, 108, 6, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(123, 173, 74, 109, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(124, 174, 74, 109, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(125, 175, 74, 109, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(126, 176, 75, 110, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(127, 177, 75, 110, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(128, 178, 76, 111, 9, NULL, 5400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(129, 179, 76, 111, 9, NULL, 5400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(130, 180, 76, 111, 9, NULL, 5400000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(131, 181, 77, 112, 9, NULL, 2800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(132, 182, 77, 112, 9, NULL, 2800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(133, 183, 78, 113, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(134, 184, 78, 113, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(135, 185, 78, 113, 9, NULL, 1000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(136, 186, 83, 118, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(137, 187, 83, 118, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(138, 188, 84, 119, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(139, 189, 84, 119, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(140, 190, 84, 119, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(141, 191, 85, 120, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(142, 192, 85, 120, 3, NULL, 200000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(143, 193, 86, 121, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(144, 194, 86, 121, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(145, 195, 86, 121, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(146, 196, 87, 122, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(147, 197, 87, 122, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(148, 198, 88, 123, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(149, 199, 88, 123, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(150, 200, 88, 123, 3, NULL, 60000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(151, 201, 89, 124, 3, NULL, 18000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(152, 202, 89, 124, 3, NULL, 18000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(153, 203, 90, 125, 3, NULL, 30000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(154, 204, 90, 125, 3, NULL, 30000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(155, 205, 90, 125, 3, NULL, 30000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(156, 206, 91, 126, 3, NULL, 10000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(157, 207, 91, 126, 3, NULL, 10000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(158, 208, 92, 127, 3, NULL, 28000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(159, 209, 92, 127, 3, NULL, 28000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(160, 210, 92, 127, 3, NULL, 28000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(161, 211, 93, 128, 3, NULL, 18000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(162, 212, 93, 128, 3, NULL, 18000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(163, 213, 94, 129, 3, NULL, 11000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(164, 214, 94, 129, 3, NULL, 11000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(165, 215, 94, 129, 3, NULL, 11000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(166, 216, 95, 130, 3, NULL, 12000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(167, 217, 95, 130, 3, NULL, 12000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(168, 218, 96, 131, 3, NULL, 10000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(169, 219, 96, 131, 3, NULL, 10000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(170, 220, 96, 131, 3, NULL, 10000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(171, 221, 97, 132, 12, NULL, 330000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(172, 222, 97, 132, 12, NULL, 330000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(173, 223, 98, 133, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(174, 224, 98, 133, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(175, 225, 98, 133, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(176, 226, 99, 134, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(177, 227, 99, 134, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(178, 228, 100, 135, 12, NULL, 3000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(179, 229, 100, 135, 12, NULL, 3000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(180, 230, 100, 135, 12, NULL, 3000000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(181, 231, 101, 136, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(182, 232, 101, 136, 12, NULL, 500000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(183, 233, 102, 137, 12, NULL, 4300000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(184, 234, 102, 137, 12, NULL, 4300000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(185, 235, 102, 137, 12, NULL, 4300000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(186, 236, 103, 138, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(187, 237, 103, 138, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(188, 238, 104, 139, 6, NULL, 99000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(189, 239, 104, 139, 6, NULL, 99000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(190, 240, 104, 139, 6, NULL, 99000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(191, 241, 105, 140, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(192, 242, 105, 140, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(193, 243, 106, 141, 6, NULL, 63000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(194, 244, 106, 141, 6, NULL, 63000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(195, 245, 106, 141, 6, NULL, 63000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(196, 246, 107, 142, 6, NULL, 85500.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(197, 247, 107, 142, 6, NULL, 85500.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(198, 248, 108, 143, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(199, 249, 108, 143, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(200, 250, 108, 143, 6, NULL, 800000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(201, 251, 109, 144, 11, NULL, 135000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(202, 252, 109, 144, 11, NULL, 135000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(203, 253, 110, 145, 11, NULL, 180000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(204, 254, 110, 145, 11, NULL, 180000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(205, 255, 110, 145, 11, NULL, 180000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(206, 256, 111, 146, 11, NULL, 100000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(207, 257, 111, 146, 11, NULL, 100000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(208, 258, 112, 147, 11, NULL, 135000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(209, 259, 112, 147, 11, NULL, 135000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(210, 260, 112, 147, 11, NULL, 135000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(211, 261, 113, 148, 11, NULL, 55000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(212, 262, 113, 148, 11, NULL, 55000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(213, 263, 114, 149, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(214, 264, 114, 149, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(215, 265, 114, 149, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(216, 266, 115, 150, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(217, 267, 115, 150, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(218, 268, 116, 151, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(219, 269, 116, 151, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(220, 270, 116, 151, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(221, 271, 117, 152, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(222, 272, 117, 152, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(223, 273, 118, 153, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(224, 274, 118, 153, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(225, 275, 118, 153, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(226, 276, 119, 154, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(227, 277, 119, 154, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(228, 278, 120, 155, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(229, 279, 120, 155, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(230, 280, 120, 155, 8, NULL, 50000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(231, 281, 121, 156, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(232, 282, 121, 156, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(233, 283, 122, 157, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(234, 284, 122, 157, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(235, 285, 122, 157, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(236, 286, 123, 158, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(237, 287, 123, 158, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(238, 288, 124, 159, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(239, 289, 124, 159, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(240, 290, 124, 159, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(241, 291, 125, 160, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(242, 292, 125, 160, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(243, 293, 126, 161, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(244, 294, 126, 161, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(245, 295, 126, 161, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(246, 296, 127, 162, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(247, 297, 127, 162, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(248, 298, 128, 163, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(249, 299, 128, 163, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(250, 300, 128, 163, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(251, 301, 129, 164, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(252, 302, 129, 164, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(253, 303, 130, 165, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(254, 304, 130, 165, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(255, 305, 130, 165, 10, NULL, 150000.00, 'completed', '2026-06-20 14:19:11', NULL, '2026-06-20 14:19:11', 'unpaid', NULL, '2026-06-20 14:19:11', '2026-06-20 14:19:11'),
(256, 53, 70, 105, 5, 82, 400000.00, 'cancelled', '2026-06-20 15:03:31', NULL, NULL, 'unpaid', NULL, '2026-06-20 14:30:35', '2026-06-22 09:39:43'),
(257, 310, 20, 47, 2, 110, 750000.00, 'confirmed', '2026-06-21 06:25:46', NULL, NULL, 'unpaid', NULL, '2026-06-21 06:25:06', '2026-06-21 06:25:46'),
(258, 310, 20, 48, 12, 111, 2100000.00, 'confirmed', '2026-06-21 06:25:46', NULL, NULL, 'unpaid', NULL, '2026-06-21 06:25:06', '2026-06-21 06:25:46'),
(259, 310, 20, 50, 5, 112, 200000.00, 'confirmed', '2026-06-21 06:25:46', NULL, NULL, 'unpaid', NULL, '2026-06-21 06:25:06', '2026-06-21 06:25:46'),
(260, 310, 21, 55, 2, 113, 40000.00, 'confirmed', '2026-06-21 06:25:46', NULL, NULL, 'unpaid', NULL, '2026-06-21 06:25:06', '2026-06-21 06:25:46'),
(261, 310, 21, 56, 10, 117, 73000.00, 'confirmed', '2026-06-21 06:25:46', NULL, NULL, 'unpaid', NULL, '2026-06-21 06:25:06', '2026-06-21 06:25:46'),
(264, 311, 28, 64, 2, NULL, 750000.00, 'cancellation_pending', '2026-06-21 07:13:07', NULL, NULL, 'unpaid', NULL, '2026-06-21 07:12:10', '2026-06-21 13:27:23'),
(265, 312, 21, 56, 10, NULL, 150000.00, 'cancelled', '2026-06-21 07:39:55', NULL, NULL, 'unpaid', NULL, '2026-06-21 07:39:00', '2026-06-21 12:32:52'),
(266, 313, 75, 110, 9, NULL, 1000000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-22 03:33:34', '2026-06-22 03:33:34'),
(267, 314, 21, 56, 10, NULL, 150000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-22 03:45:48', '2026-06-22 03:46:27'),
(268, 315, 129, 164, 10, NULL, 15000000.00, 'confirmed', '2026-06-22 07:48:12', NULL, NULL, 'unpaid', NULL, '2026-06-22 07:37:58', '2026-06-22 07:48:12'),
(269, 316, 20, 47, 2, 110, 750000.00, 'confirmed', '2026-06-27 07:02:32', NULL, NULL, 'unpaid', NULL, '2026-06-22 09:49:24', '2026-06-27 07:02:32'),
(270, 316, 20, 48, 12, 111, 2100000.00, 'confirmed', '2026-06-27 07:02:32', NULL, NULL, 'unpaid', NULL, '2026-06-22 09:49:24', '2026-06-27 07:02:32'),
(271, 316, 20, 50, 5, 112, 200000.00, 'confirmed', '2026-06-27 07:02:32', NULL, NULL, 'unpaid', NULL, '2026-06-22 09:49:24', '2026-06-27 07:02:32'),
(272, 316, 21, 55, 2, 113, 40000.00, 'confirmed', '2026-06-27 07:02:32', NULL, NULL, 'unpaid', NULL, '2026-06-22 09:49:24', '2026-06-27 07:02:32'),
(273, 316, 21, 56, 10, 117, 73000.00, 'confirmed', '2026-06-27 07:02:32', NULL, NULL, 'unpaid', NULL, '2026-06-22 09:49:24', '2026-06-27 07:02:32'),
(276, 317, 20, 47, 2, 110, 750000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:07:04', '2026-06-23 05:07:04'),
(277, 317, 20, 48, 12, 111, 2100000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:07:04', '2026-06-23 05:07:04'),
(278, 317, 20, 50, 5, 112, 200000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:07:04', '2026-06-23 05:07:04'),
(279, 317, 21, 55, 2, 113, 40000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:07:04', '2026-06-23 05:07:04'),
(280, 317, 21, 56, 10, 117, 73000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:07:04', '2026-06-23 05:07:04'),
(283, 318, 20, 47, 2, 110, 750000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:15:13', '2026-06-23 05:15:13'),
(284, 318, 20, 48, 12, 111, 2100000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:15:13', '2026-06-23 05:15:13'),
(285, 318, 20, 50, 5, 112, 200000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:15:13', '2026-06-23 05:15:13'),
(286, 318, 21, 55, 2, 113, 40000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:15:13', '2026-06-23 05:15:13'),
(287, 318, 21, 56, 10, 117, 73000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:15:13', '2026-06-23 05:15:13'),
(290, 319, 20, 48, 12, NULL, 2100000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 05:17:31', '2026-06-23 05:17:31'),
(291, 320, 89, 124, 3, NULL, 18000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-23 15:37:50', '2026-06-23 15:37:50'),
(292, 321, 70, 105, 5, NULL, 400000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:45:33', '2026-06-24 02:45:33'),
(293, 321, 75, 110, 9, NULL, 1000000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:45:33', '2026-06-24 02:45:33'),
(295, 322, 102, 137, 12, NULL, 4300000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:50:09', '2026-06-24 02:50:09'),
(296, 322, 33, 69, 2, NULL, 300000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:50:09', '2026-06-24 02:50:09'),
(298, 323, 20, 47, 2, 118, 750000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:53:58', '2026-06-24 02:53:58'),
(299, 323, 20, 48, 12, 119, 2100000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:53:58', '2026-06-24 02:53:58'),
(300, 323, 20, 50, 5, 120, 200000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:53:58', '2026-06-24 02:53:58'),
(301, 323, 21, 55, 2, 121, 40000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:53:58', '2026-06-24 02:53:58'),
(302, 323, 21, 56, 10, 122, 73000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:53:58', '2026-06-24 02:53:58'),
(305, 324, 89, 124, 3, NULL, 18000.00, 'cancellation_pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 02:55:00', '2026-06-24 03:14:24'),
(306, 325, 21, 42, 6, 65, 70000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 03:09:11', '2026-06-24 03:09:11'),
(307, 326, 31, 67, 2, NULL, 200000.00, 'cancellation_pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 03:37:59', '2026-06-24 03:39:10'),
(308, 327, 33, 69, 2, NULL, 300000.00, 'cancellation_pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 04:18:13', '2026-06-24 04:22:52'),
(309, 328, 128, 163, 10, NULL, 300000.00, 'cancellation_pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 04:22:13', '2026-06-24 04:23:43'),
(310, 329, 84, 119, 3, NULL, 200000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-24 04:53:07', '2026-06-24 04:53:07'),
(311, 330, 121, 156, 10, NULL, 300000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-25 02:48:29', '2026-06-25 02:48:29'),
(312, 331, 119, 154, 8, NULL, 50000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-25 03:27:09', '2026-06-25 03:27:09'),
(313, 332, 109, 144, 11, NULL, 135000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-25 04:57:50', '2026-06-25 04:57:50'),
(314, 333, 70, 105, 5, NULL, 400000.00, 'cancellation_pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-25 05:01:14', '2026-06-25 05:04:43'),
(315, 333, 68, 103, 5, NULL, 445000.00, 'cancellation_pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-25 05:01:14', '2026-06-25 05:04:43'),
(317, 303, 134, NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-10 03:30:00', '2026-06-25 07:33:59'),
(318, 304, 134, NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-15 07:30:00', '2026-06-25 07:33:59'),
(319, 305, 134, NULL, NULL, NULL, NULL, 'completed', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-20 02:30:00', '2026-06-25 07:33:59'),
(320, 310, 134, NULL, NULL, NULL, NULL, 'confirmed', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-22 04:30:00', '2026-06-25 07:33:59'),
(321, 334, 20, 49, 6, NULL, 900000.00, 'confirmed', '2026-06-25 16:17:16', NULL, NULL, 'unpaid', NULL, '2026-06-25 13:10:35', '2026-06-25 16:17:16'),
(322, 335, 21, 55, 2, NULL, 500000.00, 'pending', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-26 03:33:41', '2026-06-26 03:33:41'),
(323, 336, 134, 167, 2, NULL, 2500000.00, 'confirmed', '2026-06-26 04:41:05', NULL, NULL, 'unpaid', NULL, '2026-06-26 04:40:29', '2026-06-26 04:41:05'),
(324, 337, 20, 49, 6, NULL, 3000000.00, 'cancelled', NULL, NULL, NULL, 'unpaid', NULL, '2026-06-27 07:41:56', '2026-06-27 07:42:12'),
(325, 338, 20, 49, 6, NULL, 3000000.00, 'confirmed', '2026-06-27 08:08:05', NULL, NULL, 'unpaid', NULL, '2026-06-27 08:07:50', '2026-06-27 08:08:05'),
(326, 339, 20, 50, 5, NULL, 2100000.00, 'cancelled', '2026-06-27 09:57:13', NULL, NULL, 'unpaid', NULL, '2026-06-27 09:57:00', '2026-06-27 09:59:09'),
(327, 340, 20, 50, 5, NULL, 2100000.00, 'cancelled', '2026-06-27 10:17:26', NULL, NULL, 'unpaid', NULL, '2026-06-27 10:17:07', '2026-06-27 10:36:22'),
(328, 341, 20, 50, 5, NULL, 2100000.00, 'confirmed', '2026-06-27 10:40:10', NULL, NULL, 'unpaid', NULL, '2026-06-27 10:39:57', '2026-06-27 10:40:10'),
(329, 342, 20, 50, 5, NULL, 2100000.00, 'confirmed', '2026-06-27 10:53:43', NULL, NULL, 'unpaid', NULL, '2026-06-27 10:53:32', '2026-06-27 10:53:43'),
(330, 343, 20, 50, 5, NULL, 2100000.00, 'confirmed', '2026-06-27 11:04:45', NULL, NULL, 'unpaid', NULL, '2026-06-27 11:04:29', '2026-06-27 11:04:45'),
(331, 344, 20, 50, 5, NULL, 2100000.00, 'cancelled', '2026-06-27 11:09:51', NULL, NULL, 'unpaid', NULL, '2026-06-27 11:09:35', '2026-06-27 11:36:37'),
(332, 345, 20, 50, 5, NULL, 2100000.00, 'completed', '2026-06-27 11:40:10', NULL, '2026-06-27 11:58:14', 'unpaid', NULL, '2026-06-27 11:39:58', '2026-06-27 11:58:14'),
(333, 346, 20, 49, 6, NULL, 3000000.00, 'confirmed', '2026-06-27 12:02:09', NULL, NULL, 'unpaid', NULL, '2026-06-27 12:01:48', '2026-06-27 12:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `booking_supplier_replacements`
--

CREATE TABLE `booking_supplier_replacements` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `booking_supplier_id` bigint(20) NOT NULL,
  `package_item_id` bigint(20) DEFAULT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `old_supplier_id` bigint(20) NOT NULL,
  `old_service_id` bigint(20) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_supplier_id` bigint(20) DEFAULT NULL,
  `new_service_id` bigint(20) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `price_delta` decimal(10,2) DEFAULT NULL,
  `requires_customer_approval` tinyint(1) NOT NULL DEFAULT 0,
  `customer_approved_at` timestamp NULL DEFAULT NULL,
  `proposed_at` timestamp NULL DEFAULT NULL,
  `delta_payment_id` bigint(20) DEFAULT NULL,
  `status` enum('pending_admin','pending_customer','assigned','accepted','declined_again','rejected_by_customer','cancelled') NOT NULL DEFAULT 'pending_admin',
  `chosen_by_admin_id` bigint(20) DEFAULT NULL,
  `decline_reason` varchar(500) DEFAULT NULL,
  `rejected_service_ids` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_supplier_replacements`
--

INSERT INTO `booking_supplier_replacements` (`id`, `booking_id`, `booking_supplier_id`, `package_item_id`, `category_id`, `old_supplier_id`, `old_service_id`, `old_price`, `new_supplier_id`, `new_service_id`, `new_price`, `price_delta`, `requires_customer_approval`, `customer_approved_at`, `proposed_at`, `delta_payment_id`, `status`, `chosen_by_admin_id`, `decline_reason`, `rejected_service_ids`, `created_at`, `assigned_at`, `resolved_at`) VALUES
(4, 53, 67, 79, 2, 20, 47, 750000.00, 31, 67, 200000.00, -550000.00, 0, NULL, NULL, NULL, 'accepted', 1, NULL, NULL, '2026-06-20 06:51:32', '2026-06-20 09:24:06', '2026-06-20 10:54:12'),
(5, 53, 68, 80, 12, 20, 48, 2100000.00, 23, 59, 2900000.00, 800000.00, 1, '2026-06-20 03:01:35', NULL, 52, 'assigned', 1, NULL, NULL, '2026-06-20 06:51:32', '2026-06-20 07:33:09', NULL),
(6, 53, 69, 82, 5, 20, 50, 200000.00, 70, 105, 400000.00, 200000.00, 1, '2026-06-20 10:00:35', NULL, 53, 'accepted', 1, NULL, NULL, '2026-06-20 06:51:32', '2026-06-20 14:30:35', '2026-06-20 10:33:31');

-- --------------------------------------------------------

--
-- Table structure for table `booking_vouchers`
--

CREATE TABLE `booking_vouchers` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `voucher_number` varchar(50) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `service_name` varchar(150) DEFAULT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','used','expired') DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_vouchers`
--

INSERT INTO `booking_vouchers` (`id`, `booking_id`, `voucher_number`, `service_id`, `supplier_id`, `service_name`, `category_name`, `event_date`, `start_time`, `end_time`, `location`, `price`, `status`, `issued_at`) VALUES
(11, 49, 'VCH-PKG-D262B5AD', NULL, 20, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, 'No 39. Hnin Si Street', 1473360.00, 'active', '2026-06-18 11:11:38'),
(12, 50, 'VCH-PKG-12F37AD8', NULL, 21, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 76860.00, 'active', '2026-06-19 10:52:23'),
(13, 52, 'VCH-PKG-365D3EEE', NULL, 20, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, '35, Taw Win Road, Dagon Township, Yangon', 4074000.00, 'active', '2026-06-20 01:36:28'),
(14, 53, 'VCH-PKG-77D47D61', NULL, 20, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 4074000.00, 'active', '2026-06-20 02:33:51'),
(15, 310, 'VCH-PKG-36A2060D', NULL, 20, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, 'No 39. Hnin Si Street', 4150650.00, 'active', '2026-06-21 06:25:46'),
(16, 316, 'VCH-PKG-1E2DBDEC', NULL, 20, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, 'No 39. Hnin Si Street', 4150650.00, 'active', '2026-06-27 07:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`) VALUES
(2, 27, '2026-06-14 15:00:24'),
(3, 30, '2026-06-18 09:56:57'),
(4, 1, '2026-06-20 14:13:50'),
(5, 24, '2026-06-20 14:13:50'),
(6, 29, '2026-06-20 14:13:50'),
(8, 112, '2026-06-23 05:01:56'),
(9, 113, '2026-06-23 05:08:42'),
(10, 109, '2026-06-23 05:09:20'),
(11, 123, '2026-06-24 03:37:17'),
(12, 124, '2026-06-24 04:50:55'),
(13, 131, '2026-06-25 03:26:35'),
(14, 126, '2026-06-25 04:49:05'),
(15, 132, '2026-06-25 04:55:42');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) NOT NULL,
  `cart_id` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `item_type` enum('service','package','supplier_package') NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `selected_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `source` enum('package','custom') DEFAULT NULL,
  `slot_id` bigint(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue_room_id` bigint(20) DEFAULT NULL,
  `attire_item_id` bigint(20) DEFAULT NULL,
  `rental_type` enum('borrow','buy') DEFAULT NULL,
  `borrow_date` date DEFAULT NULL,
  `rental_option_id` bigint(20) DEFAULT NULL COMMENT 'References attire_rental_options.id',
  `decoration_style_id` bigint(20) DEFAULT NULL,
  `cake_design_id` bigint(20) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `package_cart_item_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `user_id`, `item_type`, `item_id`, `selected_date`, `price`, `source`, `slot_id`, `start_time`, `end_time`, `venue_room_id`, `attire_item_id`, `rental_type`, `borrow_date`, `rental_option_id`, `decoration_style_id`, `cake_design_id`, `guest_count`, `package_cart_item_id`) VALUES
(96, 4, 1, 'package', 30, NULL, 4150650.00, 'package', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, 2, 27, 'service', 121, '2026-06-28', 60000.00, 'custom', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(2, 'Attire', 'attire', '2026-05-24 05:07:27'),
(3, 'Food', 'food', '2026-05-24 05:07:27'),
(4, 'Package', 'package', '2026-05-24 05:07:27'),
(5, 'Studio', 'studio', '2026-05-24 05:07:27'),
(6, 'Venue', 'venue', '2026-05-24 05:07:27'),
(7, 'Others', 'others', '2026-06-10 11:23:37'),
(8, 'Invitation & Gifts', 'invitation & gifts', '2026-06-10 11:23:37'),
(9, 'Jewelry', 'jewelry', '2026-06-10 11:23:37'),
(10, 'Make Up & Hair', 'makeup & hair', '2026-06-10 11:23:37'),
(11, 'Car', 'car', '2026-06-10 11:23:37'),
(12, 'Decoration', 'decoration', '2026-06-14 09:24:08');

-- --------------------------------------------------------

--
-- Table structure for table `customer_status_logs`
--

CREATE TABLE `customer_status_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `action` varchar(40) NOT NULL COMMENT 'suspend | ban | unban | soft_delete | edit_contact',
  `reason` text DEFAULT NULL,
  `changed_by` bigint(20) DEFAULT NULL COMMENT 'admin user_id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `decoration_styles`
--

CREATE TABLE `decoration_styles` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `package_price` decimal(12,2) DEFAULT NULL,
  `customize_price` decimal(12,2) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `decoration_styles`
--

INSERT INTO `decoration_styles` (`id`, `service_id`, `name`, `price`, `package_price`, `customize_price`, `photo_url`, `sort_order`, `created_at`) VALUES
(2, 37, 'Ballon Arch', 2000000.00, NULL, NULL, NULL, 0, '2026-06-17 09:27:00'),
(13, 43, 'Ballon Arch', 3400000.00, 3400000.00, 3400000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/decoration-style/20260619054808-272a7a46.png', 0, '2026-06-19 03:48:08'),
(14, 59, 'Classic Elegance', 2900000.00, 2900000.00, 3480000.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 0, '2026-06-20 13:57:16'),
(15, 59, 'Floral Romance', 3770000.00, 3770000.00, 4524000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, '2026-06-20 13:57:16'),
(16, 59, 'Theme-based Custom', 4640000.00, 4640000.00, 5568000.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 2, '2026-06-20 13:57:16'),
(17, 132, 'Classic Elegance', 330000.00, 330000.00, 396000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 0, '2026-06-20 13:57:16'),
(18, 132, 'Floral Romance', 429000.00, 429000.00, 514800.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, '2026-06-20 13:57:16'),
(19, 132, 'Theme-based Custom', 528000.00, 528000.00, 633600.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 2, '2026-06-20 13:57:16'),
(20, 133, 'Classic Elegance', 500000.00, 500000.00, 600000.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 0, '2026-06-20 13:57:16'),
(21, 133, 'Floral Romance', 650000.00, 650000.00, 780000.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, '2026-06-20 13:57:16'),
(22, 133, 'Theme-based Custom', 800000.00, 800000.00, 960000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 2, '2026-06-20 13:57:16'),
(23, 134, 'Classic Elegance', 500000.00, 500000.00, 600000.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 0, '2026-06-20 13:57:16'),
(24, 134, 'Floral Romance', 650000.00, 650000.00, 780000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, '2026-06-20 13:57:16'),
(25, 134, 'Theme-based Custom', 800000.00, 800000.00, 960000.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 2, '2026-06-20 13:57:16'),
(26, 135, 'Classic Elegance', 3000000.00, 3000000.00, 3600000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 0, '2026-06-20 13:57:16'),
(27, 135, 'Floral Romance', 3900000.00, 3900000.00, 4680000.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, '2026-06-20 13:57:16'),
(28, 135, 'Theme-based Custom', 4800000.00, 4800000.00, 5760000.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 2, '2026-06-20 13:57:16'),
(29, 136, 'Classic Elegance', 500000.00, 500000.00, 600000.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 0, '2026-06-20 13:57:16'),
(30, 136, 'Floral Romance', 650000.00, 650000.00, 780000.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, '2026-06-20 13:57:16'),
(31, 136, 'Theme-based Custom', 800000.00, 800000.00, 960000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 2, '2026-06-20 13:57:16'),
(32, 137, 'Classic Elegance', 4300000.00, 4300000.00, 5160000.00, 'http://localhost/GP/public/uploads/serviceHero1.png', 0, '2026-06-20 13:57:16'),
(33, 137, 'Floral Romance', 5590000.00, 5590000.00, 6708000.00, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, '2026-06-20 13:57:16'),
(34, 137, 'Theme-based Custom', 6880000.00, 6880000.00, 8256000.00, 'http://localhost/GP/public/uploads/serviceHero3.png', 2, '2026-06-20 13:57:16'),
(35, 48, 'Flower Deco', 2100000.00, 2100000.00, 2100000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/decoration-style/20260618210222-a1b9542f.png', 0, '2026-06-26 11:40:47'),
(36, 48, 'Golden Deco', 1800000.00, 1800000.00, 1800000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/decoration-style/20260618211557-3e2f52ad.png', 1, '2026-06-26 11:40:47');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `token_hash`, `expires_at`, `used`, `created_at`) VALUES
(6, 27, 'c9af74c98a6f1759d78a1c2143dd000b8e88e7f8533cac9ce74e69d25b25c6fb', '2026-06-11 02:39:48', 1, '2026-06-11 02:32:31'),
(8, 105, '104b76c4d9e0711cd8a6464682408e3c9d944f26e7568f229dfef4e914da0e6f', '2026-06-23 03:16:48', 1, '2026-06-23 03:15:16'),
(9, 106, 'c999baaefed2158db24b7eb3faa2056da2644b8c66f0bcc182c78b5acd58ceb5', '2026-06-25 02:51:13', 1, '2026-06-23 03:20:07'),
(10, 107, '9ad4ce83486c232502ede7a15efae9194c8179bf2d9e09b53aa3683350961a45', '2026-06-24 04:07:45', 0, '2026-06-23 04:07:45'),
(12, 109, '0272618cc89d63fc5ec333d8a751217dfdde2601c018637819d24ac9b98827dc', '2026-06-23 04:24:57', 1, '2026-06-23 04:23:00'),
(14, 111, 'c41717e168fea4b52b1aaf3cd1721c1ded4094a1632db4a1793e6a5686df0187', '2026-06-24 04:39:31', 1, '2026-06-23 04:54:27'),
(15, 112, '384742ddc3dcd39ce1dba8d46bcc7fa7ecf5f14ce5aa7eea451ac1ac604d6caf', '2026-06-23 05:00:11', 1, '2026-06-23 04:58:22'),
(16, 113, '0c0507ef638cca184a41aced85f8c4ff55d2508b04a7887375ae11325747cabd', '2026-06-23 05:03:11', 1, '2026-06-23 05:02:36'),
(17, 114, '9adaef21a950e1f39eefe343e2c64b4878a78bf60b264eec8b86cc1440057556', '2026-06-23 05:30:11', 1, '2026-06-23 05:27:33'),
(18, 115, '303f8f1d61816b9ac9c7b4cf3889322a5dd7b11acd0992d2d33c8e2e1aa8c2c0', '2026-06-23 13:17:50', 1, '2026-06-23 13:15:22'),
(19, 116, '10c33569e09e5504e29199a5e49b906dd1f6c5bb48a28d3d46032bddf17122b1', '2026-06-24 14:25:48', 0, '2026-06-23 14:25:48'),
(20, 117, 'c3dae0e6ed9aafb9d3eb0bbb2756eacab6deaac5a557da4ec71d875ba1ce29dd', '2026-06-24 14:26:03', 0, '2026-06-23 14:26:03'),
(21, 118, '0cf60708c754d8575367e69f21611a9446caa549456451c2939429c7591709a4', '2026-06-25 02:23:58', 0, '2026-06-24 02:23:58'),
(22, 119, '460e8595ca620b76b5e5ad5c0343c72b06d538d937b6b61f19571ea762b91225', '2026-06-25 03:07:57', 0, '2026-06-24 03:07:57'),
(23, 120, 'd01f3683712d80672450e412d6293938c32d744afaeb3f2c57e09b94fff1a81a', '2026-06-25 03:12:13', 0, '2026-06-24 03:12:13'),
(24, 121, '4e566998e6cb5e30ad055abd7bd45a5ca4289c5c700ae3d5ab1442f24cfc494e', '2026-06-24 03:18:58', 1, '2026-06-24 03:15:03'),
(25, 122, '010544c35eae68717d1a24ae67ce1fa4f135c62e1d9a88038d9094335558f4c6', '2026-06-24 03:19:59', 1, '2026-06-24 03:17:26'),
(26, 123, '11412135ff97775ffc1011887c7f73b56cdaaf3307e0af1348b5991816f69ba3', '2026-06-24 03:29:09', 1, '2026-06-24 03:24:38'),
(27, 124, '528039c5302a3de4bd8f827390701ac9f4b700137cbab901e04a7fe603b35e68', '2026-06-24 03:51:13', 1, '2026-06-24 03:28:35'),
(28, 124, '2b4b2cb8af638fbcefa25ff139f5e72b0fc948717b3f41d2a327ae67898838b5', '2026-06-24 03:51:36', 1, '2026-06-24 03:51:13'),
(29, 125, '2295ef309b1373109fb9e40119fc4f63463356f2d96e196af6c2c2924c49b21c', '2026-06-25 03:55:30', 0, '2026-06-24 03:55:30'),
(30, 126, 'f1e02e0af300f0b51e6fc7d25f9b2d1e1c5ed35e8a0c936b6c5ab93bd2b77d93', '2026-06-24 03:58:47', 1, '2026-06-24 03:58:34'),
(31, 126, '3be5ccc94db6030a46e0c79c99cb9d5cf7474795723e972006a0f457b0a26cac', '2026-06-24 03:59:23', 1, '2026-06-24 03:58:47'),
(32, 126, '786794d1cb8acc61ddb1d9fec09209d7104d1ef3918c2d34e1cc56acdd95fd2b', '2026-06-24 04:00:00', 1, '2026-06-24 03:59:23'),
(33, 126, 'dc37be254f43d8db9248d4a1d9bbfeba994f694cb76e3783efa83dd84ec0cd9c', '2026-06-24 04:01:28', 1, '2026-06-24 04:00:00'),
(34, 126, '02a285d50f963c38983b2cdc76f2592c8ddb79e315ff6101b195c145bc71d381', '2026-06-24 04:01:41', 1, '2026-06-24 04:01:28'),
(35, 126, 'ca9e7d75ebdbc12a90516fa6a8e141f0f00d727b1bcf97f63e8979ab0272a8f0', '2026-06-24 04:06:06', 1, '2026-06-24 04:01:41'),
(36, 126, '090c76219d11e9979ec0a95a3f7a8be22c5874d8a6ef55a0638d8c408e8fd08a', '2026-06-24 04:06:17', 1, '2026-06-24 04:06:06'),
(37, 126, 'b15a97895ac57737c45011c935566e386c80d9793b3c41f944e1fadd9a0b95e7', '2026-06-24 04:07:11', 1, '2026-06-24 04:06:17'),
(38, 126, '164f92996a37d975d0d89a6e4c3848f1830e489aa0be2779da085486c76ffd21', '2026-06-24 04:07:22', 1, '2026-06-24 04:07:11'),
(39, 126, '83cdd0b4736fef320078ef3f8a62fccc1a6a3cbc0ff56416bbd2216be459fe47', '2026-06-25 02:22:39', 1, '2026-06-24 04:07:22'),
(40, 127, '11c55df6772f2325c3ab1f3692e85a075c6f93ab2de02c41048fe98a0b186ef0', '2026-06-24 04:35:11', 1, '2026-06-24 04:34:08'),
(41, 127, '4a803741e7c25267719e99d4c6efff373d3742dd48dac782a090d0ff26fa2b12', '2026-06-25 04:35:11', 0, '2026-06-24 04:35:11'),
(42, 111, 'b8a98e1caa4b3d3ab261a095b29a004b32b53ba7830e8c6927a66ea8e548f52e', '2026-06-24 04:40:53', 1, '2026-06-24 04:39:31'),
(43, 111, '56b7ef795fd46bd04ffc68477aa2559d69f43f73cd3e3204d3d7cc35e62dc594', '2026-06-24 04:41:32', 1, '2026-06-24 04:40:53'),
(44, 111, 'bae65465e9155d3c9ca253fb2ae8b357c8341fc95a9b9f65082614d5eca14484', '2026-06-24 04:41:50', 1, '2026-06-24 04:41:32'),
(45, 126, '25689d40313c6c219f80fc0021acf1ea6904e3ddd0bdc37232ad3bf08e419634', '2026-06-25 02:22:51', 1, '2026-06-25 02:22:39'),
(46, 126, 'b949530ed370c866c1a29eeb9e57c1268ef0134c4ddf10c3e041263a336bb9ad', '2026-06-25 02:24:25', 1, '2026-06-25 02:22:51'),
(47, 126, '211f68fd0def38f35bb31d837ecc662b5bbb70e6c2cf45833c9d428ee9a1b70f', '2026-06-25 02:24:32', 1, '2026-06-25 02:24:25'),
(48, 126, 'af98dd76062703ae5f190e55510ebfc8c5c5e554f753f46adf281e95cac4916f', '2026-06-25 02:25:26', 1, '2026-06-25 02:24:32'),
(49, 126, 'bdb226e46ec8f41f75b85aa19aedfb8e777842c95b2c53eef6a1429ec332435a', '2026-06-25 02:25:32', 1, '2026-06-25 02:25:26'),
(50, 126, '13a4b6d263742a31b067d089ea64852c409ad06255833128fc95de9221db4b65', '2026-06-25 02:26:14', 1, '2026-06-25 02:25:32'),
(51, 126, '9e719808338d4cbf874b3c28f2fe673e96e4f12135a9994e1cf94ecc08b28e94', '2026-06-25 02:26:22', 1, '2026-06-25 02:26:14'),
(52, 126, '753390b15a9906160c841bc2f314fdaae3f7292cf2dbe79aedb1b20051aa94ef', '2026-06-25 02:27:08', 1, '2026-06-25 02:26:22'),
(53, 128, '3e3c4936e798004ba736a88848dc2120a12cf2bb042387f91faf61d3823b1656', '2026-06-25 02:48:51', 1, '2026-06-25 02:48:38'),
(54, 128, '58d67923b212a858174f9eba2586a07ff0354a2e87ec565078196de827587d5c', '2026-06-26 02:48:51', 0, '2026-06-25 02:48:51'),
(55, 106, '802cd949417008cacb590e526ccd9f3b0bc52728b3af17847b7768fc8ea39b0f', '2026-06-26 02:51:13', 0, '2026-06-25 02:51:13'),
(56, 129, 'bf806cb13fdb9775ebae0187d693728cc68ef21811e8ec026483aad0cb27e604', '2026-06-25 02:53:03', 1, '2026-06-25 02:52:21'),
(57, 129, '4c4af58bbc8feae8d25a3e1a6c113a4b7aad190226f6d9f949a475cc714d012b', '2026-06-25 02:55:00', 1, '2026-06-25 02:53:03'),
(58, 129, '7abe9003a6f6f58a19e19d91ca38059834dc8a5a449540d0d154bea3c35bc9b8', '2026-06-25 02:55:10', 1, '2026-06-25 02:55:00'),
(59, 129, '9ee5532999ecba73e74e637427f8f0280309b9765614235edf29c9a1bf8b6e68', '2026-06-25 02:56:39', 1, '2026-06-25 02:55:10'),
(60, 129, 'c86842b07af1e35e90dcd04c28e79fa2e98a1d420fa0a16ae54b8c80984dc2b0', '2026-06-25 02:56:59', 1, '2026-06-25 02:56:39'),
(61, 129, 'dbfdd061ce9b7e9f94b14261b6d5417816f1e7fcd81fad432ca1e09d3cfbfe53', '2026-06-26 02:56:59', 0, '2026-06-25 02:56:59'),
(62, 130, '00974f644df7e3a0ecb6d77fdc46017ceb8ae5e216a0f006ea683adff1e02215', '2026-06-25 03:03:12', 1, '2026-06-25 03:03:02'),
(63, 130, '74a6d416b5250e721d6c77c8d3f3b392b2660765bd819b230f68a7b888e4d594', '2026-06-26 03:03:12', 0, '2026-06-25 03:03:12'),
(64, 131, '204ba631981b5b58b3794239229873cfe27476218b5148c181aeec254d040b8e', '2026-06-25 03:14:46', 1, '2026-06-25 03:13:18'),
(65, 131, '0dadac9feb09df2cfba0564519baab506fc03e6d34a7f7004645e689aba01d36', '2026-06-25 03:14:58', 1, '2026-06-25 03:14:46'),
(66, 131, '966d79da9ccc302346683b6e9b981179d9e4940718d766f2337d78f0880e4b58', '2026-06-25 03:16:02', 1, '2026-06-25 03:14:58'),
(67, 131, '4b4a63f029fc6da24a9d99bdc34ebd3c691181d398eb28b44adb447d80808cb2', '2026-06-25 03:19:56', 1, '2026-06-25 03:16:02'),
(68, 131, 'cbaae11b4844fe7899c7ae41c99873be3d270233c8906f6e63c4dab620cd5cf0', '2026-06-25 03:20:10', 1, '2026-06-25 03:19:56'),
(69, 131, '3bd3a80811cccbb84760f06ccdc46a71676331ece87eaaa134867c23c4734ffc', '2026-06-25 03:21:10', 1, '2026-06-25 03:20:10'),
(70, 131, 'fb3b891a1c3cd1be7a3c1839dc5ea3fe36748ceeb8c487521c4a181ccc92dae6', '2026-06-25 03:21:27', 1, '2026-06-25 03:21:10'),
(71, 131, 'f5b4e33a5ac945cecd73b94e5ba7017736d54c8942935a46fa666aebb7cf57d4', '2026-06-25 03:21:40', 1, '2026-06-25 03:21:27'),
(72, 132, '5437772731a716e73ac2bf25e0515920da2aa04ebb6b8b806557f903414fea4b', '2026-06-25 04:44:46', 1, '2026-06-25 04:43:53'),
(73, 132, '1a07835c8eae4ddd8057429e8a698721502094d8c5587746536aafce8b01fe46', '2026-06-25 04:44:58', 1, '2026-06-25 04:44:46'),
(74, 132, 'b18e875c07e16a979bded155985415495d169cb6334ddbdcf797ae79410c219d', '2026-06-25 04:46:13', 1, '2026-06-25 04:44:58'),
(75, 132, '5d273981fe6e7c09eda619307708b29e55a8eb6f941a00528d4eb815ceb76485', '2026-06-25 04:46:21', 1, '2026-06-25 04:46:13'),
(76, 132, 'ce7aadfa3b63e909c789fe7ddfcbcebda0bd5ca966f0526718a80baa6e432e36', '2026-06-25 04:46:41', 1, '2026-06-25 04:46:21'),
(77, 133, 'c7eca90fa5b2914868184d72e16ca9456afcfca2928203906d041c61254e0aa6', '2026-06-26 02:25:18', 1, '2026-06-26 02:25:10'),
(78, 133, '9b23fd410ba7ea2f9cd211d62bd06b9a6822f9802d0951ebdea3beaa91f9ec2c', '2026-06-26 02:25:48', 1, '2026-06-26 02:25:18'),
(79, 133, 'afac4ab53ef436100394a3e34879f466c399e91f98fecab43cf26cb6c11bb467', '2026-06-26 02:25:59', 1, '2026-06-26 02:25:48'),
(80, 133, '7836c637277a87beee4ec59caa0a576a91f0401b29497d810d4300265c93c22a', '2026-06-26 02:26:18', 1, '2026-06-26 02:25:59'),
(81, 133, '75a60f7238fa9609c13bfaea8892a17ae0e52a24c8237630bf5cf0b76781c0b3', '2026-06-26 02:26:34', 1, '2026-06-26 02:26:18'),
(82, 133, '3f0cb476848429ec0afe9ee731579fcaa0eaa884f4b4824455d4dde0db7ef315', '2026-06-26 02:27:16', 1, '2026-06-26 02:26:34'),
(83, 133, 'a5e33e8ce7d8d05c7020f28d08cf8a54c8190c9425b60ac85b14dde22d2cf595', '2026-06-26 02:27:50', 1, '2026-06-26 02:27:16'),
(84, 133, '817d44a422a068814b85fef5d2b2983a118aa9adf7f86909d0cb4e345ded2af2', '2026-06-26 02:28:08', 1, '2026-06-26 02:27:50'),
(85, 133, '9a45688235ce1b8a933d88af7b4938e9455d783bc52890edb8bb6f0b348f8400', '2026-06-26 02:28:29', 1, '2026-06-26 02:28:08');

-- --------------------------------------------------------

--
-- Table structure for table `event_details`
--

CREATE TABLE `event_details` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `booking_item_id` bigint(20) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `venue_type` enum('indoor','outdoor','both') DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `seating_arrangement` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_details`
--

INSERT INTO `event_details` (`id`, `booking_id`, `booking_item_id`, `event_date`, `start_time`, `end_time`, `guest_count`, `venue_type`, `location`, `theme`, `notes`, `contact_name`, `contact_phone`, `special_requests`, `seating_arrangement`, `created_at`) VALUES
(71, 48, 99, '2026-09-24', '06:00:00', '17:00:00', 300, NULL, '35, Taw Win Road, Dagon Township, Yangon', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-18 09:32:46'),
(72, 49, 100, '2026-09-16', '09:00:00', '17:00:00', 200, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-18 11:10:23'),
(73, 50, 101, '2026-07-18', '09:00:00', '17:00:00', 220, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-18 14:36:16'),
(74, 51, 102, '2026-07-19', '09:00:00', '17:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-19 15:48:47'),
(75, 51, 103, '2026-06-27', '09:00:00', '17:00:00', 1, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-19 15:48:47'),
(76, 52, 105, '2026-06-27', '09:00:00', '17:00:00', 300, NULL, '35, Taw Win Road, Dagon Township, Yangon', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-20 01:33:55'),
(77, 53, 106, '2026-06-27', '09:00:00', '17:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-20 02:31:24'),
(78, 126, 179, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(79, 127, 180, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(80, 128, 181, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(81, 129, 182, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(82, 130, 183, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(83, 131, 184, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(84, 132, 185, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(85, 133, 186, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(86, 134, 187, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(87, 135, 188, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(88, 136, 189, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(89, 137, 190, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(90, 138, 191, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(91, 139, 192, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(92, 140, 193, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(93, 141, 194, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(94, 142, 195, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(95, 143, 196, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(96, 144, 197, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(97, 145, 198, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(98, 146, 199, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(99, 147, 200, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(100, 148, 201, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(101, 149, 202, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(102, 150, 203, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(103, 151, 204, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(104, 152, 205, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(105, 153, 206, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(106, 154, 207, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(107, 155, 208, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(108, 156, 209, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(109, 157, 210, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(110, 158, 211, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(111, 159, 212, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(112, 160, 213, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(113, 161, 214, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(114, 162, 215, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(115, 163, 216, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(116, 164, 217, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(117, 165, 218, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(118, 166, 219, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(119, 167, 220, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(120, 168, 221, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(121, 169, 222, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(122, 170, 223, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(123, 171, 224, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(124, 172, 225, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(125, 173, 226, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(126, 174, 227, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(127, 175, 228, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(128, 176, 229, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(129, 177, 230, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(130, 178, 231, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(131, 179, 232, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(132, 180, 233, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(133, 181, 234, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(134, 182, 235, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(135, 183, 236, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(136, 184, 237, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(137, 185, 238, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(138, 186, 239, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(139, 187, 240, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(140, 188, 241, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(141, 189, 242, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(142, 190, 243, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(143, 191, 244, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(144, 192, 245, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(145, 193, 246, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(146, 194, 247, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(147, 195, 248, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(148, 196, 249, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(149, 197, 250, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(150, 198, 251, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(151, 199, 252, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(152, 200, 253, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(153, 201, 254, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(154, 202, 255, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(155, 203, 256, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(156, 204, 257, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(157, 205, 258, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(158, 206, 259, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(159, 207, 260, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(160, 208, 261, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(161, 209, 262, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(162, 210, 263, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(163, 211, 264, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(164, 212, 265, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(165, 213, 266, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(166, 214, 267, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(167, 215, 268, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(168, 216, 269, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(169, 217, 270, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(170, 218, 271, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(171, 219, 272, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(172, 220, 273, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(173, 221, 274, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(174, 222, 275, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(175, 223, 276, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(176, 224, 277, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(177, 225, 278, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(178, 226, 279, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(179, 227, 280, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(180, 228, 281, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(181, 229, 282, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(182, 230, 283, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(183, 231, 284, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(184, 232, 285, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(185, 233, 286, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(186, 234, 287, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(187, 235, 288, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(188, 236, 289, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(189, 237, 290, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(190, 238, 291, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(191, 239, 292, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(192, 240, 293, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(193, 241, 294, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(194, 242, 295, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(195, 243, 296, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(196, 244, 297, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(197, 245, 298, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(198, 246, 299, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(199, 247, 300, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(200, 248, 301, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(201, 249, 302, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(202, 250, 303, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(203, 251, 304, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(204, 252, 305, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(205, 253, 306, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(206, 254, 307, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(207, 255, 308, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(208, 256, 309, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(209, 257, 310, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(210, 258, 311, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(211, 259, 312, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(212, 260, 313, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(213, 261, 314, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(214, 262, 315, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(215, 263, 316, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(216, 264, 317, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(217, 265, 318, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(218, 266, 319, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(219, 267, 320, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(220, 268, 321, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(221, 269, 322, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(222, 270, 323, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(223, 271, 324, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(224, 272, 325, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(225, 273, 326, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(226, 274, 327, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(227, 275, 328, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(228, 276, 329, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(229, 277, 330, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(230, 278, 331, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(231, 279, 332, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(232, 280, 333, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(233, 281, 334, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(234, 282, 335, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(235, 283, 336, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(236, 284, 337, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(237, 285, 338, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(238, 286, 339, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(239, 287, 340, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(240, 288, 341, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(241, 289, 342, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(242, 290, 343, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(243, 291, 344, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(244, 292, 345, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(245, 293, 346, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(246, 294, 347, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(247, 295, 348, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(248, 296, 349, '2026-04-26', '18:00:00', '22:00:00', 250, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(249, 297, 350, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(250, 298, 351, '2026-05-10', '18:00:00', '22:00:00', 300, 'both', 'Yangon', NULL, NULL, 'Customer 30', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(251, 299, 352, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(252, 300, 353, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(253, 301, 354, '2026-05-24', '18:00:00', '22:00:00', 350, 'both', 'Yangon', NULL, NULL, 'Customer 1', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(254, 302, 355, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(255, 303, 356, '2026-06-07', '18:00:00', '22:00:00', 400, 'both', 'Yangon', NULL, NULL, 'Customer 24', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(256, 304, 357, '2026-03-15', '18:00:00', '22:00:00', 150, 'both', 'Yangon', NULL, NULL, 'Customer 27', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(257, 305, 358, '2026-04-12', '18:00:00', '22:00:00', 200, 'both', 'Yangon', NULL, NULL, 'Customer 29', NULL, NULL, NULL, '2026-06-20 14:19:11'),
(262, 310, 363, '2026-06-28', '04:00:00', '17:00:00', 200, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-21 06:25:06'),
(263, 311, 364, '2026-06-21', '10:00:00', '18:00:00', 2, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-21 07:12:10'),
(264, 312, 365, '2026-06-21', '11:00:00', '17:00:00', 2, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-21 07:39:00'),
(265, 313, 366, '2026-06-22', '09:00:00', '18:00:00', 2, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-22 03:33:34'),
(266, 314, 367, '2026-06-22', '07:00:00', '16:00:00', 2, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-22 03:45:48'),
(267, 315, 368, '2026-06-24', '09:00:00', '18:00:00', 100, NULL, 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', 'thanks', NULL, '2026-06-22 07:37:58'),
(268, 316, 369, '2026-06-29', '04:00:00', '17:00:00', 1, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-22 09:49:24'),
(269, 317, 370, '2026-07-01', '04:00:00', '16:00:00', 7, NULL, 'Novotel', NULL, NULL, 'Kaung Min Khant', '0978134486111', '', NULL, '2026-06-23 05:07:04'),
(270, 318, 371, '2027-08-04', '04:00:00', '16:00:00', 400, NULL, 'thingangyum', NULL, NULL, 'AmKiio', '09676992431', 'pu', NULL, '2026-06-23 05:15:13'),
(271, 319, 372, '2026-06-26', '09:00:00', '17:00:00', 200, NULL, 'thingangyum', NULL, NULL, 'AmKiio', '09676992431`', 'dtd5yhfdsdyudfftyfuigi7rfyuvrsdfgcddteazsxuiuredtyvye3qagyhpioytdfgdvkivcetwashji87tfgdsxcryjugfcxwqasfguufdhjipoytdr5dcthjisar4wgydsauy8', NULL, '2026-06-23 05:17:31'),
(272, 320, 373, '2026-06-24', '09:00:00', '18:00:00', 50, NULL, 'Novotel', NULL, NULL, 'Yadanar', '09455658079', '', NULL, '2026-06-23 15:37:50'),
(273, 321, 374, '2026-07-01', '09:00:00', '18:00:00', 1, NULL, 'Novotel', NULL, NULL, 'Naw Pan', '09455658079', 'sjdkjdhiooekjgmcnkcnknviklklejflwkele', NULL, '2026-06-24 02:45:33'),
(274, 321, 375, '2026-07-01', '09:00:00', '18:00:00', 2, NULL, 'Novotel', NULL, NULL, 'Yadanar', '09455658079', 'jsdskrhjwkekirjfkdsjhhdsscsdsbcbkwerjkjs', NULL, '2026-06-24 02:45:33'),
(275, 322, 377, '2026-06-30', '09:00:00', '18:00:00', 1, NULL, 'Metro', NULL, NULL, 'Yadanar', '09455658079', 'fjkdddfknmmcslkdkld', NULL, '2026-06-24 02:50:09'),
(276, 322, 378, '2026-06-30', '10:00:00', '18:00:00', 1, NULL, 'Metro', NULL, NULL, 'Yadanar', '09455658079', 'oijfkkgtldks;ipwirHPLKDCNV,MDNJKSAJEHAHI', NULL, '2026-06-24 02:50:09'),
(277, 323, 380, '2026-07-02', '04:00:00', '16:00:00', 51, NULL, 'NOVOTEL', NULL, NULL, 'Kaung Min Khant', '09781344861', 'Very Good', NULL, '2026-06-24 02:53:58'),
(278, 324, 381, '2026-06-25', '09:00:00', '18:00:00', 1, NULL, 'Metro', NULL, NULL, 'Yadanar', '09455658079', 'ghjsjkdkjflkgljk', NULL, '2026-06-24 02:55:00'),
(279, 325, 382, '2026-07-24', '09:00:00', '15:00:00', 100, NULL, 'Metro', NULL, NULL, 'Yadanar', '09455658079', 'ffgdsfdgfhgjhjkjhg', NULL, '2026-06-24 03:09:11'),
(280, 326, 383, '2026-06-28', '10:00:00', '18:00:00', 1, NULL, 'Wedding room', NULL, NULL, 'Lae Yati Htun', '09676992431', 'မြန်မြန်လေးလုပ်ပေးပါ', NULL, '2026-06-24 03:37:59'),
(281, 327, 384, '2026-06-25', '10:00:00', '18:00:00', 2, NULL, 'Ballroom2', NULL, NULL, 'Lae Yati Htun', '09676992431', 'Faster', NULL, '2026-06-24 04:18:13'),
(282, 328, 385, '2026-06-25', '09:00:00', '18:00:00', 2, NULL, 'Capital,Thaketa', NULL, NULL, 'Zaw MIn Thein', '0966776677', 'gu gu gar gar', NULL, '2026-06-24 04:22:13'),
(283, 329, 386, '2026-06-25', '09:00:00', '18:00:00', 2, NULL, 'Metro', NULL, NULL, 'Yadanar', '09455658079', '', NULL, '2026-06-24 04:53:07'),
(284, 330, 387, '2026-06-30', '09:00:00', '18:00:00', 2, NULL, 'Home', NULL, NULL, 'Yadanar', '09455658079', '', NULL, '2026-06-25 02:48:29'),
(285, 331, 388, '2026-06-28', '09:00:00', '18:00:00', 1, NULL, 'THINGANGYUN', NULL, NULL, 'hans', '09989915314', 'ryghvryhifkfyvrk48i5u8ri', NULL, '2026-06-25 03:27:09'),
(286, 332, 389, '2026-06-26', '09:00:00', '18:00:00', 2, NULL, 'THINGANGYUN', NULL, NULL, 'Hans', '09989915314', '7hgvcv vfv', NULL, '2026-06-25 04:57:50'),
(287, 333, 390, '2026-06-26', '09:00:00', '18:00:00', 2, NULL, 'Ballroom A', NULL, NULL, 'Tina', '09252208158', 'Please make it pretty', NULL, '2026-06-25 05:01:14'),
(288, 333, 391, '2026-06-26', '09:00:00', '18:00:00', 2, NULL, 'Ballroom A', NULL, NULL, 'Tina', '09252208158', 'make it pretty too', NULL, '2026-06-25 05:01:14'),
(289, 334, 393, '2026-07-27', '09:00:00', '17:00:00', 200, NULL, 'Ballon A', NULL, NULL, 'Saen', '09123456789', '', NULL, '2026-06-25 13:10:35'),
(290, 335, 394, '2026-06-29', '10:00:00', '17:00:00', 1, NULL, 'Ballon A', NULL, NULL, 'Saen', '09451777705', 'special request', NULL, '2026-06-26 03:33:41'),
(291, 336, 395, '2026-07-08', '09:00:00', '17:00:00', 1, NULL, 'Yangon', NULL, NULL, 'dfnsdjfb', '09781344861', 'make more beautiful', NULL, '2026-06-26 04:40:29'),
(292, 337, 396, '2026-07-07', '09:00:00', '17:00:00', 700, NULL, 'Zpyr', NULL, NULL, 'Zaw Moe', '09123456789', '', NULL, '2026-06-27 07:41:56'),
(293, 338, 397, '2026-07-06', '09:00:00', '17:00:00', 700, NULL, 'အမှတ်-(28) စိမ်းလန်းစိုပြေပန်းခြံ၊ အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-27 08:07:50'),
(294, 339, 398, '2026-07-04', '09:00:00', '17:00:00', 1, NULL, 'အနော်မာ', NULL, NULL, 'Aye Aye', '09123456789', '', NULL, '2026-06-27 09:57:00'),
(295, 340, 399, '2026-06-28', '09:00:00', '17:00:00', 1, NULL, 'sein mya kan thar', NULL, NULL, 'Zin Gyi', '09123456789', '', NULL, '2026-06-27 10:17:07'),
(296, 341, 400, '2026-06-28', '09:00:00', '17:00:00', 1, NULL, 'kan taw gyi', NULL, NULL, 'ZM', '09123456789', '', NULL, '2026-06-27 10:39:57'),
(297, 342, 401, '2026-06-29', '09:00:00', '17:00:00', 1, NULL, 'babylone', NULL, NULL, 'Kyaw Kyaw', '09123456789', '', NULL, '2026-06-27 10:53:32'),
(298, 343, 402, '2026-06-30', '09:00:00', '17:00:00', 1, NULL, 'a naw mar', NULL, NULL, 'HH', '0912345678', '', NULL, '2026-06-27 11:04:29'),
(299, 344, 403, '2026-06-30', '09:00:00', '17:00:00', 1, NULL, 'ballon', NULL, NULL, 'HH', '0912345678', '', NULL, '2026-06-27 11:09:35'),
(300, 345, 404, '2026-06-30', '09:00:00', '17:00:00', 1, NULL, 'a naw mar', NULL, NULL, 'Saen', '0912345678', '', NULL, '2026-06-27 11:39:58'),
(301, 346, 405, '2026-07-05', '09:00:00', '17:00:00', 700, NULL, 'boollon', NULL, NULL, 'Saen', '09123456789', '', NULL, '2026-06-27 12:01:48');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `item_type` enum('service','package','supplier_package') NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `collection_id` bigint(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `item_type`, `item_id`, `collection_id`, `notes`, `created_at`) VALUES
(2, 30, 'service', 55, 1, 'i like it', '2026-06-19 07:32:59'),
(3, 30, 'service', 165, NULL, NULL, '2026-06-20 13:11:59'),
(4, 30, 'service', 154, 2, NULL, '2026-06-22 03:23:37'),
(5, 30, 'service', 144, NULL, NULL, '2026-06-22 03:24:38');

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `package_price` decimal(12,2) DEFAULT NULL,
  `customize_price` decimal(12,2) DEFAULT NULL,
  `pricing_model` enum('flat','per_person') NOT NULL DEFAULT 'flat',
  `photo_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`id`, `service_id`, `name`, `description`, `price`, `package_price`, `customize_price`, `pricing_model`, `photo_url`, `sort_order`, `created_at`) VALUES
(1, 170, 'Chocolate Wedding Cake', NULL, 300000.00, 300000.00, 400000.00, 'flat', 'http://localhost/GP/public/uploads/suppliers/20/service-management/food-item/20260627150931-4169c320.png', 0, '2026-06-27 08:39:31');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `attempt_count` int(11) DEFAULT NULL,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `locked_until` timestamp NULL DEFAULT NULL,
  `max_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('booking','payment','approval','system','payout') DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 1, 'New supplier application', 'Blossom &amp; co submitted a supplier application.', 'approval', 'supplier', 9, 1, '2026-06-03 09:14:27'),
(2, 1, 'Supplier payment submitted', 'Blossom &amp; co submitted membership payment details.', 'payment', 'payment', 1, 1, '2026-06-03 12:16:22'),
(3, 1, 'New supplier application', 'Zaw Moe submitted a supplier application.', 'approval', 'supplier', 10, 1, '2026-06-04 13:57:12'),
(4, 1, 'New supplier application', 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ submitted a supplier application.', 'approval', 'supplier', 11, 1, '2026-06-10 02:16:26'),
(5, 1, 'New supplier application', 'HMM submitted a supplier application.', 'approval', 'supplier', 12, 1, '2026-06-10 04:43:15'),
(6, 1, 'New supplier application', 'JV submitted a supplier application.', 'approval', 'supplier', 20, 1, '2026-06-10 06:38:51'),
(7, 1, 'New supplier application', 'Wyndham Grand Yangon Hotel submitted a supplier application.', 'approval', 'supplier', 21, 1, '2026-06-11 05:01:15'),
(8, 1, 'Supplier payment submitted', 'Wyndham Grand Yangon Hotel uploaded an AYA Bank Transfer payment slip.', 'payment', 'payment', 8, 1, '2026-06-11 05:09:30'),
(9, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Wedding Planning &amp; Decoration\".', 'approval', 'service', 19, 1, '2026-06-11 13:01:53'),
(10, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Wedding Planning &amp; Decoration\".', 'approval', 'service', 19, 1, '2026-06-11 13:08:59'),
(11, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Wedding Planning &amp; Decoration\".', 'approval', 'service', 19, 1, '2026-06-11 13:16:42'),
(12, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Governor&amp;#039;s Residence\".', 'approval', 'service', 18, 1, '2026-06-12 10:27:23'),
(13, 1, 'Service publish request', 'JV requested publishing for \"77 Cakes\".', 'approval', 'service', 21, 1, '2026-06-13 06:16:15'),
(14, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Governor&amp;#039;s Residence\".', 'approval', 'service', 32, 1, '2026-06-14 12:54:59'),
(15, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Dear Brides\".', 'approval', 'service', 33, 1, '2026-06-14 12:57:56'),
(16, 1, 'Service publish request', 'JV requested publishing for \"Lin Lin Make Up\".', 'approval', 'service', 34, 1, '2026-06-14 14:10:46'),
(17, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Governor&amp;amp;amp;#039;s Residence\".', 'approval', 'service', 32, 1, '2026-06-15 03:43:29'),
(18, 24, 'New Booking', 'A customer booked: Lin Lin Make Up. Please review and confirm.', 'booking', 'booking', 26, 1, '2026-06-16 07:34:59'),
(19, 24, 'New Booking', 'A customer booked: Lin Lin Make Up, Dear Brides. Please review and confirm.', 'booking', 'booking', 27, 1, '2026-06-16 07:37:37'),
(20, 29, 'New Booking', 'A customer booked: Lin Lin Make Up, Dear Brides. Please review and confirm.', 'booking', 'booking', 27, 1, '2026-06-16 07:37:37'),
(21, 27, 'Booking Accepted', 'JV has accepted your booking! Your service is confirmed.', 'booking', 'booking', 27, 1, '2026-06-16 07:42:39'),
(22, 27, 'Booking Accepted', 'Wyndham Grand Yangon Hotel has accepted your booking! Your service is confirmed.', 'booking', 'booking', 27, 1, '2026-06-16 10:05:56'),
(23, 27, 'Booking Accepted', 'Wyndham Grand Yangon Hotel has accepted your booking! Your service is confirmed.', 'booking', 'booking', 24, 1, '2026-06-16 10:48:38'),
(24, 29, 'New Booking', 'A customer booked: Dear Brides. Please review and confirm.', 'booking', 'booking', 28, 1, '2026-06-16 13:51:01'),
(25, 1, 'Service publish request', 'JV requested publishing for \"Lin Lin Make Up\".', 'approval', 'service', 34, 1, '2026-06-16 14:02:54'),
(26, 1, 'Service publish request', 'JV requested publishing for \"Golden Inya\".', 'approval', 'service', 36, 1, '2026-06-17 01:34:37'),
(27, 1, 'Service publish request', 'JV requested publishing for \"Lin Lin\".', 'approval', 'service', 34, 1, '2026-06-17 01:49:23'),
(28, 24, 'Publish request sent', 'Your request to publish \"Lin Lin\" was sent to admin.', 'approval', 'service', 34, 1, '2026-06-17 01:49:23'),
(29, 24, 'New Booking', 'A customer booked: Golden Inya. Please review and confirm.', 'booking', 'booking', 29, 1, '2026-06-17 01:52:06'),
(30, 27, 'Payment Confirmed', 'Your deposit of 200,000 MMK has been confirmed.', 'payment', 'booking', 29, 1, '2026-06-17 02:49:58'),
(31, 24, 'Deposit Paid', 'The customer has paid the deposit. Please review and confirm the booking.', 'booking', 'booking', 29, 1, '2026-06-17 02:49:58'),
(32, 1, 'Service publish request', 'JV requested publishing for \"T &amp;amp; T\".', 'approval', 'service', 39, 1, '2026-06-17 08:01:50'),
(33, 24, 'Publish request sent', 'Your request to publish \"T &amp;amp; T\" was sent to admin.', 'approval', 'service', 39, 1, '2026-06-17 08:01:50'),
(34, 1, 'Service publish request', 'JV requested publishing for \"Hotel Yangon\".', 'approval', 'service', 41, 1, '2026-06-17 09:03:25'),
(35, 24, 'Publish request sent', 'Your request to publish \"Hotel Yangon\" was sent to admin.', 'approval', 'service', 41, 1, '2026-06-17 09:03:25'),
(36, 24, 'New Booking Request', 'A customer is requesting: Hotel Yangon. Please accept or decline within 48 hours.', 'booking', 'booking', 34, 1, '2026-06-17 10:08:29'),
(37, 27, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 34, 1, '2026-06-17 10:08:53'),
(38, 27, 'Payment Confirmed', 'Your deposit of 60,000 MMK has been confirmed.', 'payment', 'booking', 34, 1, '2026-06-17 10:09:09'),
(39, 24, 'Deposit Paid', 'The customer has paid the deposit. The booking is now confirmed.', 'booking', 'booking', 34, 1, '2026-06-17 10:09:09'),
(40, 24, 'New Booking Request', 'A customer is requesting: Lin Lin. Please accept or decline within 48 hours.', 'booking', 'booking', 35, 1, '2026-06-17 12:10:16'),
(41, 27, 'Payment Confirmed', 'Your deposit of 200,000 MMK has been confirmed.', 'payment', 'booking', 33, 1, '2026-06-17 12:11:55'),
(42, 24, 'Deposit Paid', 'The customer has paid the deposit. The booking is now confirmed.', 'booking', 'booking', 33, 1, '2026-06-17 12:11:55'),
(43, 27, 'Payment Confirmed', 'Your deposit of 60,000 MMK has been confirmed.', 'payment', 'booking', 31, 1, '2026-06-17 12:12:11'),
(44, 24, 'Deposit Paid', 'The customer has paid the deposit. The booking is now confirmed.', 'booking', 'booking', 31, 1, '2026-06-17 12:12:11'),
(45, 27, 'Booking Declined', 'JV has declined your booking. You may need to find an alternative service.', 'booking', 'booking', 31, 1, '2026-06-17 12:25:50'),
(46, 27, 'Booking Request Declined', 'JV is unavailable for your requested dates. Please search for another supplier.', 'booking', 'booking', 35, 1, '2026-06-17 12:25:57'),
(47, 24, 'New Booking Request', 'A customer is requesting: Golden Inya. Please accept or decline within 48 hours.', 'booking', 'booking', 36, 1, '2026-06-17 12:26:59'),
(48, 27, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 36, 0, '2026-06-17 12:27:24'),
(49, 24, 'New Booking Request', 'A customer is requesting: Golden Inya. Please accept or decline within 48 hours.', 'booking', 'booking', 37, 1, '2026-06-17 15:03:58'),
(50, 24, 'New Booking Request', 'A customer is requesting: Hotel Yangon. Please accept or decline within 48 hours.', 'booking', 'booking', 38, 1, '2026-06-17 15:13:04'),
(51, 27, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 38, 1, '2026-06-17 15:16:27'),
(52, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 38, 1, '2026-06-17 15:17:04'),
(53, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 60,000 MMK for booking BK-20260617-038. Please verify it.', 'payment', 'booking', 38, 1, '2026-06-17 15:17:04'),
(54, 24, 'New Booking Request', 'A customer is requesting: Hotel Yangon. Please accept or decline within 48 hours.', 'booking', 'booking', 39, 1, '2026-06-17 18:18:00'),
(55, 27, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 39, 1, '2026-06-17 18:18:18'),
(56, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 39, 1, '2026-06-17 18:19:01'),
(57, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 60,000 MMK for booking BK-20260618-039. Please verify it.', 'payment', 'booking', 39, 1, '2026-06-17 18:19:01'),
(58, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 40, 0, '2026-06-18 03:01:11'),
(59, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 299,250 MMK for booking BK-20260618-040. Please verify it.', 'payment', 'booking', 40, 1, '2026-06-18 03:01:11'),
(60, 27, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 37, 0, '2026-06-18 03:04:07'),
(61, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 41, 1, '2026-06-18 03:32:43'),
(62, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 509,250 MMK for booking BK-20260618-041. Please verify it.', 'payment', 'booking', 41, 1, '2026-06-18 03:32:43'),
(63, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 42, 1, '2026-06-18 04:08:49'),
(64, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 299,248 MMK for booking BK-20260618-042. Please verify it.', 'payment', 'booking', 42, 1, '2026-06-18 04:08:49'),
(65, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 43, 1, '2026-06-18 04:28:10'),
(66, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 509,248 MMK for booking BK-20260618-043. Please verify it.', 'payment', 'booking', 43, 1, '2026-06-18 04:28:10'),
(67, 24, 'New Booking Request', 'A customer is requesting: Lin Lin, Hotel Yangon. Please accept or decline within 48 hours.', 'booking', 'booking', 45, 1, '2026-06-18 05:06:47'),
(68, 27, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 45, 1, '2026-06-18 05:07:08'),
(69, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 45, 1, '2026-06-18 05:09:42'),
(70, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 82,499 MMK for booking BK-20260618-045. Please verify it.', 'payment', 'booking', 45, 1, '2026-06-18 05:09:42'),
(71, 27, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 45, 1, '2026-06-18 05:58:53'),
(72, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 45, 1, '2026-06-18 05:58:53'),
(73, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 46, 1, '2026-06-18 06:24:02'),
(74, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 509,250 MMK for booking BK-20260618-046. Please verify it.', 'payment', 'booking', 46, 1, '2026-06-18 06:24:02'),
(75, 24, 'New Booking Request', 'A customer is requesting: Hotel Yangon, Standard Complete Wedding Package. Please accept or decline within 48 hours.', 'booking', 'booking', 47, 1, '2026-06-18 07:19:28'),
(76, 29, 'New Booking Request', 'A customer is requesting: Hotel Yangon, Standard Complete Wedding Package. Please accept or decline within 48 hours.', 'booking', 'booking', 47, 1, '2026-06-18 07:19:28'),
(77, 1, 'New Custom Booking Request', 'A customer created a custom or mixed booking for: Hotel Yangon, Standard Complete Wedding Package. Supplier responses are pending.', 'booking', 'booking', 47, 1, '2026-06-18 07:19:28'),
(78, 27, 'Supplier Accepted — Please Pay', 'Wyndham Grand Yangon Hotel accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 47, 1, '2026-06-18 07:24:42'),
(79, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 47, 1, '2026-06-18 07:25:55'),
(80, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 359,250 MMK for booking BK-20260618-047. Please verify it.', 'payment', 'booking', 47, 1, '2026-06-18 07:25:55'),
(81, 27, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 47, 1, '2026-06-18 07:26:29'),
(82, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 47, 1, '2026-06-18 07:26:29'),
(83, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 47, 1, '2026-06-18 07:26:29'),
(84, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Governor&amp;amp;#039;s Residence\".', 'approval', 'service', 42, 1, '2026-06-18 08:44:05'),
(85, 29, 'Publish request sent', 'Your request to publish \"Governor&amp;amp;#039;s Residence\" was sent to admin.', 'approval', 'service', 42, 1, '2026-06-18 08:44:05'),
(86, 1, 'Service publish request', 'JV requested publishing for \"Elegance Star\".', 'approval', 'service', 44, 1, '2026-06-18 09:27:46'),
(87, 24, 'Publish request sent', 'Your request to publish \"Elegance Star\" was sent to admin.', 'approval', 'service', 44, 1, '2026-06-18 09:27:46'),
(88, 29, 'New Booking Request', 'A customer is requesting: Governor&amp;amp;#039;s Residence. Please accept or decline within 48 hours.', 'booking', 'booking', 48, 1, '2026-06-18 09:32:46'),
(89, 1, 'New Custom Booking Request', 'A customer created a custom or mixed booking for: Governor&amp;amp;#039;s Residence. Supplier responses are pending.', 'booking', 'booking', 48, 1, '2026-06-18 09:32:46'),
(90, 27, 'Supplier Accepted — Please Pay', 'Wyndham Grand Yangon Hotel accepted your booking request. Please complete your 10% deposit to confirm.', 'booking', 'booking', 48, 1, '2026-06-18 09:33:50'),
(91, 27, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 48, 0, '2026-06-18 09:34:51'),
(92, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 120,000 MMK for booking BK-20260618-048. Please verify it.', 'payment', 'booking', 48, 1, '2026-06-18 09:34:51'),
(93, 27, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 48, 1, '2026-06-18 09:35:07'),
(94, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 48, 1, '2026-06-18 09:35:07'),
(95, 1, 'Service publish request', 'JV requested publishing for \"Dear Brides\".', 'approval', 'service', 45, 1, '2026-06-18 10:32:35'),
(96, 24, 'Publish request sent', 'Your request to publish \"Dear Brides\" was sent to admin.', 'approval', 'service', 45, 1, '2026-06-18 10:32:35'),
(97, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 49, 1, '2026-06-18 11:10:53'),
(98, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 294,671 MMK for booking BK-20260618-049. Please verify it.', 'payment', 'booking', 49, 1, '2026-06-18 11:10:53'),
(99, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 49, 1, '2026-06-18 11:11:38'),
(100, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 49, 1, '2026-06-18 11:11:38'),
(101, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 49, 1, '2026-06-18 11:11:38'),
(102, 1, 'Service publish request', 'JV requested publishing for \"မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ\".', 'approval', 'service', 47, 1, '2026-06-18 13:36:53'),
(103, 24, 'Publish request sent', 'Your request to publish \"မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ\" was sent to admin.', 'approval', 'service', 47, 1, '2026-06-18 13:36:53'),
(104, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 50, 1, '2026-06-18 14:37:10'),
(105, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 15,372 MMK for booking BK-20260618-050. Please verify it.', 'payment', 'booking', 50, 1, '2026-06-18 14:37:10'),
(106, 1, 'Service publish request', 'JV requested publishing for \"H&H Floral and Wedding Service\".', 'approval', 'service', 48, 1, '2026-06-18 19:13:07'),
(107, 24, 'Publish request sent', 'Your request to publish \"H&H Floral and Wedding Service\" was sent to admin.', 'approval', 'service', 48, 1, '2026-06-18 19:13:07'),
(108, 1, 'Service publish request', 'JV requested publishing for \"Zephyr Sein Lann So pyay\".', 'approval', 'service', 49, 1, '2026-06-18 19:27:52'),
(109, 24, 'Publish request sent', 'Your request to publish \"Zephyr Sein Lann So pyay\" was sent to admin.', 'approval', 'service', 49, 1, '2026-06-18 19:27:52'),
(110, 1, 'Service publish request', 'JV requested publishing for \"H & H Wedding Studio\".', 'approval', 'service', 50, 1, '2026-06-19 02:09:33'),
(111, 24, 'Publish request sent', 'Your request to publish \"H & H Wedding Studio\" was sent to admin.', 'approval', 'service', 50, 1, '2026-06-19 02:09:33'),
(112, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN\".', 'approval', 'service', 55, 1, '2026-06-19 03:43:44'),
(113, 29, 'Publish request sent', 'Your request to publish \"ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN\" was sent to admin.', 'approval', 'service', 55, 1, '2026-06-19 03:43:44'),
(114, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Aphrodite Wedding Planning & Decoration\".', 'approval', 'service', 43, 1, '2026-06-19 03:48:09'),
(115, 29, 'Publish request sent', 'Your request to publish \"Aphrodite Wedding Planning & Decoration\" was sent to admin.', 'approval', 'service', 43, 1, '2026-06-19 03:48:09'),
(116, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: မဂ်လာဆောင်မယ့်ရက်ပြောင်းခြင်း Your deposit will be refunded.', 'booking', 'booking', 50, 0, '2026-06-19 10:51:54'),
(117, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: မဂ်လာဆောင်မယ့်ရက်ပြောင်းခြင်း', 'booking', 'booking', 50, 1, '2026-06-19 10:51:54'),
(118, 29, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: မဂ်လာဆောင်မယ့်ရက်ပြောင်းခြင်း', 'booking', 'booking', 50, 1, '2026-06-19 10:51:54'),
(119, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 50, 1, '2026-06-19 10:52:23'),
(120, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 50, 1, '2026-06-19 10:52:23'),
(121, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 50, 1, '2026-06-19 10:52:23'),
(122, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: deposit payed and the wedding date is changed Your deposit will be refunded.', 'booking', 'booking', 49, 1, '2026-06-19 13:03:53'),
(123, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: deposit payed and the wedding date is changed', 'booking', 'booking', 49, 1, '2026-06-19 13:03:53'),
(124, 29, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: deposit payed and the wedding date is changed', 'booking', 'booking', 49, 1, '2026-06-19 13:03:53'),
(125, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: blah blah Your deposit will be refunded.', 'booking', 'booking', 50, 1, '2026-06-19 13:17:24'),
(126, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: blah blah', 'booking', 'booking', 50, 1, '2026-06-19 13:17:24'),
(127, 29, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: blah blah', 'booking', 'booking', 50, 1, '2026-06-19 13:17:24'),
(128, 24, 'New Booking Request', 'zaw moe is requesting: Standard Wedding Package, H &amp; H Wedding Studio. Please accept or decline within 48 hours.', 'booking', 'booking', 51, 1, '2026-06-19 15:48:47'),
(129, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: Standard Wedding Package, H &amp; H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 51, 1, '2026-06-19 15:48:47'),
(130, 30, 'Booking Request Declined', 'JV is unavailable for your requested dates. Please search for another supplier.', 'booking', 'booking', 51, 1, '2026-06-20 01:32:06'),
(131, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 52, 0, '2026-06-20 01:34:23'),
(132, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 814,800 MMK for booking BK-20260620-052. Please verify it.', 'payment', 'booking', 52, 1, '2026-06-20 01:34:23'),
(133, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 52, 0, '2026-06-20 01:36:28'),
(134, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 52, 1, '2026-06-20 01:36:28'),
(135, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 52, 1, '2026-06-20 01:36:28'),
(136, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 53, 0, '2026-06-20 02:31:46'),
(137, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 814,800 MMK for booking BK-20260620-053. Please verify it.', 'payment', 'booking', 53, 1, '2026-06-20 02:31:46'),
(138, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 53, 1, '2026-06-20 02:33:51'),
(139, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 53, 1, '2026-06-20 02:33:51'),
(140, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 53, 1, '2026-06-20 02:33:51'),
(141, 1, 'Supplier Replacement Needed', 'JV declined booking #53. Please choose a replacement supplier.', 'booking', 'booking', 53, 1, '2026-06-20 03:00:00'),
(142, 30, 'Arranging a Replacement', 'JV is unavailable for your date. We are arranging a replacement for you — no action needed right now.', 'booking', 'booking', 53, 1, '2026-06-20 03:00:00'),
(143, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Lin Lin\".', 'approval', 'service', 56, 1, '2026-06-20 05:17:19'),
(144, 29, 'Publish request sent', 'Your request to publish \"Lin Lin\" was sent to admin.', 'approval', 'service', 56, 1, '2026-06-20 05:17:19'),
(145, 1, 'Service publish request', 'Wyndham Grand Yangon Hotel requested publishing for \"Dear Brides Wedding Dress Studio\".', 'approval', 'service', 57, 1, '2026-06-20 07:03:48'),
(146, 29, 'Publish request sent', 'Your request to publish \"Dear Brides Wedding Dress Studio\" was sent to admin.', 'approval', 'service', 57, 0, '2026-06-20 07:03:48'),
(147, 32, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 0, '2026-06-20 09:24:06'),
(148, 40, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 09:24:06'),
(149, 24, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 09:24:06'),
(150, 29, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 09:24:06'),
(151, 30, 'Replacement Arranged', 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN has been assigned to your booking at no extra cost.', 'booking', 'booking', 53, 1, '2026-06-20 09:24:06'),
(152, 30, 'Replacement Needs Your Approval', 'Venus Wedding Studio is available but costs 200,000 MMK more. Approve and pay the difference to confirm the replacement.', 'booking', 'booking', 53, 1, '2026-06-20 09:25:14'),
(153, 1, 'Replacement Delta Paid — Verify', 'Customer paid the difference for booking #53. Verify the payment to finalize the replacement.', 'payment', 'booking', 53, 1, '2026-06-20 14:01:17'),
(154, 30, 'Replacement Payment Submitted', 'Thanks! We received your payment proof for the replacement and will confirm shortly.', 'payment', 'booking', 53, 1, '2026-06-20 14:01:17'),
(155, 24, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 14:30:35'),
(156, 40, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 14:30:35'),
(157, 32, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 0, '2026-06-20 14:30:35'),
(158, 47, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 14:30:35'),
(159, 29, 'New Package Booking — Please Respond', 'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.', 'booking', 'booking', 53, 1, '2026-06-20 14:30:35'),
(160, 30, 'Replacement Confirmed', 'Your replacement supplier is confirmed. Thank you for the additional payment.', 'booking', 'booking', 53, 1, '2026-06-20 14:30:35'),
(161, 30, 'Booking Accepted', 'Venus Wedding Studio has accepted your booking! Your service is confirmed.', 'booking', 'booking', 53, 1, '2026-06-20 15:03:31'),
(162, 30, 'Booking Accepted', 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN has accepted your booking! Your service is confirmed.', 'booking', 'booking', 53, 1, '2026-06-20 15:24:12'),
(163, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 310, 1, '2026-06-21 06:25:29'),
(164, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 830,130 MMK for booking BK-20260621-310. Please verify it.', 'payment', 'booking', 310, 1, '2026-06-21 06:25:29'),
(165, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 310, 1, '2026-06-21 06:25:46'),
(166, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 310, 1, '2026-06-21 06:25:46'),
(167, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 310, 1, '2026-06-21 06:25:46'),
(168, 37, 'New Booking Request', 'zaw moe is requesting: မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire. Please accept or decline within 48 hours.', 'booking', 'booking', 311, 1, '2026-06-21 07:12:10'),
(169, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire. Supplier responses are pending.', 'booking', 'booking', 311, 1, '2026-06-21 07:12:10'),
(170, 30, 'Supplier Accepted — Please Pay', 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 311, 0, '2026-06-21 07:13:07'),
(171, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 311, 0, '2026-06-21 07:23:44'),
(172, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 150,000 MMK for booking BK-20260621-311. Please verify it.', 'payment', 'booking', 311, 1, '2026-06-21 07:23:44'),
(173, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 311, 1, '2026-06-21 07:24:24'),
(174, 37, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 311, 1, '2026-06-21 07:24:24'),
(175, 29, 'New Booking Request', 'zaw moe is requesting: Lin Lin. Please accept or decline within 48 hours.', 'booking', 'booking', 312, 1, '2026-06-21 07:39:00'),
(176, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: Lin Lin. Supplier responses are pending.', 'booking', 'booking', 312, 1, '2026-06-21 07:39:00'),
(177, 30, 'Supplier Accepted — Please Pay', 'Wyndham Grand Yangon Hotel accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 312, 0, '2026-06-21 07:39:55'),
(178, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 312, 0, '2026-06-21 07:44:58'),
(179, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 30,000 MMK for booking BK-20260621-312. Please verify it.', 'payment', 'booking', 312, 1, '2026-06-21 07:44:58'),
(180, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 312, 1, '2026-06-21 07:45:19'),
(181, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 312, 1, '2026-06-21 07:45:19'),
(182, 1, 'Cancellation Request — BK-20260621-312', 'zaw moe has requested cancellation of booking BK-20260621-312. Reason: ပွဲနေ့က မနက်ဖြန် ပြောင်းသွားလို့ပါ', 'booking', 'booking', 312, 1, '2026-06-21 09:46:23'),
(183, 29, 'Cancellation Request — BK-20260621-312', 'zaw moe has requested cancellation of booking BK-20260621-312. Reason: ပွဲနေ့က မနက်ဖြန် ပြောင်းသွားလို့ပါ. Please stop any work in progress. Admin will review and finalize.', 'booking', 'booking', 312, 1, '2026-06-21 09:46:23'),
(184, 30, 'Cancellation Approved by Supplier — BK-20260621-312', 'Your supplier has approved your cancellation request for booking BK-20260621-312. Admin will review and process your refund.', 'booking', 'booking', 312, 1, '2026-06-21 12:30:00'),
(185, 1, 'Supplier Approved Cancellation — BK-20260621-312', 'The supplier has approved the cancellation for booking BK-20260621-312. Please review and finalize.', 'booking', 'booking', 312, 1, '2026-06-21 12:30:00'),
(186, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: customer request cancle and supplier accept Your deposit will be refunded.', 'booking', 'booking', 312, 1, '2026-06-21 12:32:52'),
(187, 29, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: customer request cancle and supplier accept', 'booking', 'booking', 312, 1, '2026-06-21 12:32:52'),
(188, 37, 'Cancellation Request — BK-20260621-311', 'zaw moe has requested cancellation of booking BK-20260621-311. Reason: we don\'t need this anymore. Thanks. Please review and approve or decline this request.', 'booking', 'booking', 311, 1, '2026-06-21 13:27:23'),
(189, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260621-311', 'zaw moe has requested cancellation of customize booking BK-20260621-311. The supplier has been asked to review first.', 'booking', 'booking', 311, 1, '2026-06-21 13:27:23'),
(190, 52, 'New Booking Request', 'zaw moe is requesting: U Hton - Jewelry. Please accept or decline within 48 hours.', 'booking', 'booking', 313, 0, '2026-06-22 03:33:34'),
(191, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: U Hton - Jewelry. Supplier responses are pending.', 'booking', 'booking', 313, 1, '2026-06-22 03:33:34'),
(192, 29, 'New Booking Request', 'zaw moe is requesting: Lin Lin. Please accept or decline within 48 hours.', 'booking', 'booking', 314, 1, '2026-06-22 03:45:48'),
(193, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: Lin Lin. Supplier responses are pending.', 'booking', 'booking', 314, 1, '2026-06-22 03:45:48'),
(194, 30, 'Booking Request Declined', 'Wyndham Grand Yangon Hotel is unavailable for your requested dates. Please search for another supplier.', 'booking', 'booking', 314, 1, '2026-06-22 03:46:27'),
(195, 102, 'New Booking Request', 'zaw moe is requesting: Makeup Non Thit San - Makeup & Hair. Please accept or decline within 48 hours.', 'booking', 'booking', 315, 1, '2026-06-22 07:37:58'),
(196, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: Makeup Non Thit San - Makeup & Hair. Supplier responses are pending.', 'booking', 'booking', 315, 1, '2026-06-22 07:37:58'),
(197, 30, 'Supplier Accepted — Please Pay', 'Makeup Non Thit San accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 315, 1, '2026-06-22 07:48:12'),
(198, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 315, 0, '2026-06-22 08:23:21'),
(199, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 3,750,000 MMK for booking BK-20260622-315. Please verify it.', 'payment', 'booking', 315, 1, '2026-06-22 08:23:21'),
(200, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: cancle booking Your deposit will be refunded.', 'booking', 'booking', 53, 1, '2026-06-22 09:39:43'),
(201, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: cancle booking', 'booking', 'booking', 53, 1, '2026-06-22 09:39:43'),
(202, 40, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: cancle booking', 'booking', 'booking', 53, 0, '2026-06-22 09:39:43'),
(203, 32, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: cancle booking', 'booking', 'booking', 53, 0, '2026-06-22 09:39:43'),
(204, 47, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: cancle booking', 'booking', 'booking', 53, 0, '2026-06-22 09:39:43'),
(205, 29, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: cancle booking', 'booking', 'booking', 53, 1, '2026-06-22 09:39:43'),
(206, 1, 'New supplier application', 'Chanel submitted a supplier application.', 'approval', 'supplier', 131, 1, '2026-06-23 04:23:48'),
(207, 1, 'New Supplier Fee Submitted', 'Chanel has submitted a supplier membership fee payment. Please review.', 'payment', 'supplier', 131, 1, '2026-06-23 05:10:01'),
(208, 24, 'New Booking Request', 'AmKiio is requesting: H&amp;H Floral and Wedding Service. Please accept or decline within 48 hours.', 'booking', 'booking', 319, 1, '2026-06-23 05:17:31'),
(209, 1, 'New Custom Booking Request', 'AmKiio created a custom or mixed booking for: H&amp;H Floral and Wedding Service. Supplier responses are pending.', 'booking', 'booking', 319, 1, '2026-06-23 05:17:31'),
(210, 1, 'New supplier application', 'Forever Wedding Studio submitted a supplier application.', 'approval', 'supplier', 132, 1, '2026-06-23 05:50:02'),
(211, 62, 'New Booking Request', 'Yadanar is requesting: Shan Yoe Yar Restaurant - Catering. Please accept or decline within 48 hours.', 'booking', 'booking', 320, 0, '2026-06-23 15:37:50'),
(212, 1, 'New Custom Booking Request', 'Yadanar created a custom or mixed booking for: Shan Yoe Yar Restaurant - Catering. Supplier responses are pending.', 'booking', 'booking', 320, 1, '2026-06-23 15:37:50'),
(213, 47, 'New Booking Request', 'Yadanar is requesting: Venus Wedding Studio - Studio, U Hton - Jewelry. Please accept or decline within 48 hours.', 'booking', 'booking', 321, 0, '2026-06-24 02:45:33'),
(214, 52, 'New Booking Request', 'Yadanar is requesting: Venus Wedding Studio - Studio, U Hton - Jewelry. Please accept or decline within 48 hours.', 'booking', 'booking', 321, 0, '2026-06-24 02:45:33'),
(215, 1, 'New Custom Booking Request', 'Yadanar created a custom or mixed booking for: Venus Wedding Studio - Studio, U Hton - Jewelry. Supplier responses are pending.', 'booking', 'booking', 321, 0, '2026-06-24 02:45:33'),
(216, 75, 'New Booking Request', 'Yadanar is requesting: His & Hers Events and Wedding Studio - Decoration, ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire. Please accept or decline within 48 hours.', 'booking', 'booking', 322, 0, '2026-06-24 02:50:09'),
(217, 42, 'New Booking Request', 'Yadanar is requesting: His & Hers Events and Wedding Studio - Decoration, ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire. Please accept or decline within 48 hours.', 'booking', 'booking', 322, 0, '2026-06-24 02:50:09'),
(218, 1, 'New Custom Booking Request', 'Yadanar created a custom or mixed booking for: His & Hers Events and Wedding Studio - Decoration, ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire. Supplier responses are pending.', 'booking', 'booking', 322, 1, '2026-06-24 02:50:09'),
(219, 62, 'New Booking Request', 'Yadanar is requesting: Shan Yoe Yar Restaurant - Catering. Please accept or decline within 48 hours.', 'booking', 'booking', 324, 0, '2026-06-24 02:55:00'),
(220, 1, 'New Custom Booking Request', 'Yadanar created a custom or mixed booking for: Shan Yoe Yar Restaurant - Catering. Supplier responses are pending.', 'booking', 'booking', 324, 1, '2026-06-24 02:55:00'),
(221, 109, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 325, 1, '2026-06-24 03:11:43'),
(222, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 17,500 MMK for booking BK-20260624-325. Please verify it.', 'payment', 'booking', 325, 1, '2026-06-24 03:11:43'),
(223, 62, 'Cancellation Request — BK-20260624-324', 'Yadanar has requested cancellation of booking BK-20260624-324. Reason: bjm,jnhbgfghjkl;l,mnbvc. Please review and approve or decline this request.', 'booking', 'booking', 324, 0, '2026-06-24 03:14:24'),
(224, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260624-324', 'Yadanar has requested cancellation of customize booking BK-20260624-324. The supplier has been asked to review first.', 'booking', 'booking', 324, 0, '2026-06-24 03:14:24'),
(225, 40, 'New Booking Request', 'Lae Yati Htun is requesting: ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN - Wedding Attire. Please accept or decline within 48 hours.', 'booking', 'booking', 326, 0, '2026-06-24 03:37:59'),
(226, 1, 'New Custom Booking Request', 'Lae Yati Htun created a custom or mixed booking for: ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN - Wedding Attire. Supplier responses are pending.', 'booking', 'booking', 326, 0, '2026-06-24 03:37:59'),
(227, 40, 'Cancellation Request — BK-20260624-326', 'Lae Yati Htun has requested cancellation of booking BK-20260624-326. Reason: အလျင်လိုနေလို့ပါ. Please review and approve or decline this request.', 'booking', 'booking', 326, 0, '2026-06-24 03:39:10'),
(228, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260624-326', 'Lae Yati Htun has requested cancellation of customize booking BK-20260624-326. The supplier has been asked to review first.', 'booking', 'booking', 326, 0, '2026-06-24 03:39:10'),
(229, 42, 'New Booking Request', 'Lae Yati Htun is requesting: ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire. Please accept or decline within 48 hours.', 'booking', 'booking', 327, 0, '2026-06-24 04:18:13'),
(230, 1, 'New Custom Booking Request', 'Lae Yati Htun created a custom or mixed booking for: ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire. Supplier responses are pending.', 'booking', 'booking', 327, 1, '2026-06-24 04:18:13'),
(231, 1, 'New supplier application', 'Yadanar submitted a supplier application.', 'approval', 'supplier', 133, 1, '2026-06-24 04:20:55'),
(232, 101, 'New Booking Request', 'AmKiio is requesting: Makeup Hazel - Makeup & Hair. Please accept or decline within 48 hours.', 'booking', 'booking', 328, 0, '2026-06-24 04:22:13'),
(233, 1, 'New Custom Booking Request', 'AmKiio created a custom or mixed booking for: Makeup Hazel - Makeup & Hair. Supplier responses are pending.', 'booking', 'booking', 328, 0, '2026-06-24 04:22:13'),
(234, 42, 'Cancellation Request — BK-20260624-327', 'Lae Yati Htun has requested cancellation of booking BK-20260624-327. Reason: အချိန်မမှီတော့လို့ပါ. Please review and approve or decline this request.', 'booking', 'booking', 327, 0, '2026-06-24 04:22:52'),
(235, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260624-327', 'Lae Yati Htun has requested cancellation of customize booking BK-20260624-327. The supplier has been asked to review first.', 'booking', 'booking', 327, 1, '2026-06-24 04:22:52'),
(236, 101, 'Cancellation Request — BK-20260624-328', 'AmKiio has requested cancellation of booking BK-20260624-328. Reason: gu gu gar gar. Please review and approve or decline this request.', 'booking', 'booking', 328, 0, '2026-06-24 04:23:43'),
(237, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260624-328', 'AmKiio has requested cancellation of customize booking BK-20260624-328. The supplier has been asked to review first.', 'booking', 'booking', 328, 0, '2026-06-24 04:23:43'),
(238, 114, 'Application Approved', 'Your supplier application has been approved! You can now submit your membership payment to unlock your dashboard.', 'approval', 'supplier', 132, 1, '2026-06-24 04:35:08'),
(239, 1, 'Service publish request', 'Chanel requested publishing for \"Y\'s dress\".', 'approval', 'service', 166, 1, '2026-06-24 04:38:59'),
(240, 105, 'Publish request sent', 'Your request to publish \"Y\'s dress\" was sent to admin.', 'approval', 'service', 166, 1, '2026-06-24 04:38:59'),
(241, 1, 'New Supplier Fee Submitted', 'Forever Wedding Studio has submitted a supplier membership fee payment. Please review.', 'payment', 'supplier', 132, 1, '2026-06-24 04:52:36'),
(242, 57, 'New Booking Request', 'Yadanar is requesting: Season - Cake. Please accept or decline within 48 hours.', 'booking', 'booking', 329, 0, '2026-06-24 04:53:07'),
(243, 1, 'New Custom Booking Request', 'Yadanar created a custom or mixed booking for: Season - Cake. Supplier responses are pending.', 'booking', 'booking', 329, 0, '2026-06-24 04:53:07'),
(244, 94, 'New Booking Request', 'Yadanar is requesting: SORA - Makeup & Hair. Please accept or decline within 48 hours.', 'booking', 'booking', 330, 0, '2026-06-25 02:48:29'),
(245, 1, 'New Custom Booking Request', 'Yadanar created a custom or mixed booking for: SORA - Makeup & Hair. Supplier responses are pending.', 'booking', 'booking', 330, 0, '2026-06-25 02:48:29'),
(246, 124, 'Application Approved', 'Your supplier application has been approved! You can now submit your membership payment to unlock your dashboard.', 'approval', 'supplier', 133, 0, '2026-06-25 03:11:12'),
(247, 124, 'Application Approved', 'Your supplier application has been approved! You can now submit your membership payment to unlock your dashboard.', 'approval', 'supplier', 133, 0, '2026-06-25 03:13:12'),
(248, 92, 'New Booking Request', 'Hans is requesting: THIRI Handmade Invatation - Invitation & Gifts. Please accept or decline within 48 hours.', 'booking', 'booking', 331, 0, '2026-06-25 03:27:09'),
(249, 1, 'New Custom Booking Request', 'Hans created a custom or mixed booking for: THIRI Handmade Invatation - Invitation & Gifts. Supplier responses are pending.', 'booking', 'booking', 331, 1, '2026-06-25 03:27:09'),
(250, 1, 'New supplier application', 'Shwe Phoo Sar submitted a supplier application.', 'approval', 'supplier', 134, 1, '2026-06-25 04:43:57'),
(251, 82, 'New Booking Request', 'Hans is requesting: Myanmar Car Rental - Car Rental. Please accept or decline within 48 hours.', 'booking', 'booking', 332, 0, '2026-06-25 04:57:50'),
(252, 1, 'New Custom Booking Request', 'Hans created a custom or mixed booking for: Myanmar Car Rental - Car Rental. Supplier responses are pending.', 'booking', 'booking', 332, 1, '2026-06-25 04:57:50'),
(253, 47, 'New Booking Request', 'Tina is requesting: Venus Wedding Studio - Studio, Forever One Stop Wedding Studio - Studio. Please accept or decline within 48 hours.', 'booking', 'booking', 333, 0, '2026-06-25 05:01:14'),
(254, 45, 'New Booking Request', 'Tina is requesting: Venus Wedding Studio - Studio, Forever One Stop Wedding Studio - Studio. Please accept or decline within 48 hours.', 'booking', 'booking', 333, 0, '2026-06-25 05:01:14'),
(255, 1, 'New Custom Booking Request', 'Tina created a custom or mixed booking for: Venus Wedding Studio - Studio, Forever One Stop Wedding Studio - Studio. Supplier responses are pending.', 'booking', 'booking', 333, 1, '2026-06-25 05:01:14'),
(256, 47, 'Cancellation Request — BK-20260625-333', 'Tina has requested cancellation of booking BK-20260625-333. Reason: I don\'t have money. Please review and approve or decline this request.', 'booking', 'booking', 333, 0, '2026-06-25 05:04:43'),
(257, 45, 'Cancellation Request — BK-20260625-333', 'Tina has requested cancellation of booking BK-20260625-333. Reason: I don\'t have money. Please review and approve or decline this request.', 'booking', 'booking', 333, 0, '2026-06-25 05:04:43'),
(258, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260625-333', 'Tina has requested cancellation of customize booking BK-20260625-333. The supplier has been asked to review first.', 'booking', 'booking', 333, 0, '2026-06-25 05:04:43'),
(259, 27, 'Application Approved', 'Your supplier application has been approved! You can now submit your membership payment to unlock your dashboard.', 'approval', 'supplier', 134, 0, '2026-06-25 05:12:13'),
(260, 1, 'New Supplier Fee Submitted', 'Shwe Phoo Sar has submitted a supplier membership fee payment. Please review.', 'payment', 'supplier', 134, 1, '2026-06-25 05:13:40'),
(261, 27, 'Application Approved', 'Your supplier application has been approved! You can now submit your membership payment to unlock your dashboard.', 'approval', 'supplier', 134, 1, '2026-06-25 05:13:55'),
(262, 24, 'New Booking Request', 'Saen is requesting: Zephyr Sein Lann So pyay. Please accept or decline within 48 hours.', 'booking', 'booking', 334, 1, '2026-06-25 13:10:35'),
(263, 1, 'New Custom Booking Request', 'Saen created a custom or mixed booking for: Zephyr Sein Lann So pyay. Supplier responses are pending.', 'booking', 'booking', 334, 1, '2026-06-25 13:10:35'),
(264, 29, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 334, 0, '2026-06-25 16:17:16'),
(265, 29, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 334, 0, '2026-06-25 16:56:57'),
(266, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 225,000 MMK for booking BK-20260625-334. Please verify it.', 'payment', 'booking', 334, 1, '2026-06-25 16:56:57'),
(267, 1, 'New supplier application', 'Cake submitted a supplier application.', 'approval', 'supplier', 135, 1, '2026-06-26 02:36:13'),
(268, 133, 'Application Approved', 'Your supplier application has been approved! You can now submit your membership payment to unlock your dashboard.', 'approval', 'supplier', 135, 0, '2026-06-26 02:53:11'),
(269, 1, 'New Supplier Fee Submitted', 'Cake has submitted a supplier membership fee payment. Please review.', 'payment', 'supplier', 135, 0, '2026-06-26 02:59:22'),
(270, 29, 'New Booking Request', 'Saen is requesting: ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN. Please accept or decline within 48 hours.', 'booking', 'booking', 335, 0, '2026-06-26 03:33:41'),
(271, 1, 'New Custom Booking Request', 'Saen created a custom or mixed booking for: ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN. Supplier responses are pending.', 'booking', 'booking', 335, 0, '2026-06-26 03:33:41'),
(272, 1, 'Service publish request', 'Shwe Phoo Sar requested publishing for \"Grand\".', 'approval', 'service', 167, 1, '2026-06-26 04:13:08'),
(273, 27, 'Publish request sent', 'Your request to publish \"Grand\" was sent to admin.', 'approval', 'service', 167, 0, '2026-06-26 04:13:08'),
(274, 27, 'New Booking Request', 'Avary is requesting: Grand. Please accept or decline within 48 hours.', 'booking', 'booking', 336, 1, '2026-06-26 04:40:29');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `reference_type`, `reference_id`, `is_read`, `created_at`) VALUES
(275, 1, 'New Custom Booking Request', 'Avary created a custom or mixed booking for: Grand. Supplier responses are pending.', 'booking', 'booking', 336, 0, '2026-06-26 04:40:29'),
(276, 126, 'Supplier Accepted — Please Pay', 'Shwe Phoo Sar accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 336, 1, '2026-06-26 04:41:05'),
(277, 1, 'Service publish request', 'JV requested publishing for \"Aphrodite Wedding Planning & Decoration\".', 'approval', 'service', 46, 1, '2026-06-27 04:27:30'),
(278, 24, 'Publish request sent', 'Your request to publish \"Aphrodite Wedding Planning & Decoration\" was sent to admin.', 'approval', 'service', 46, 0, '2026-06-27 04:27:30'),
(279, 30, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 310, 0, '2026-06-27 04:48:09'),
(280, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 3,320,520 MMK for booking BK-20260621-310. Please verify it.', 'payment', 'booking', 310, 1, '2026-06-27 04:48:09'),
(281, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 316, 0, '2026-06-27 07:02:18'),
(282, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 1,079,169 MMK for booking BK-20260622-316. Please verify it.', 'payment', 'booking', 316, 1, '2026-06-27 07:02:18'),
(283, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 316, 0, '2026-06-27 07:02:32'),
(284, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 316, 1, '2026-06-27 07:02:32'),
(285, 29, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 316, 0, '2026-06-27 07:02:32'),
(286, 1, 'Service publish request', 'JV requested publishing for \"Zephyr Sein Lann So pyay\".', 'approval', 'service', 49, 1, '2026-06-27 07:39:09'),
(287, 24, 'Publish request sent', 'Your request to publish \"Zephyr Sein Lann So pyay\" was sent to admin.', 'approval', 'service', 49, 1, '2026-06-27 07:39:09'),
(288, 24, 'New Booking Request', 'zaw moe is requesting: Zephyr Sein Lann So pyay. Please accept or decline within 24 hours.', 'booking', 'booking', 337, 1, '2026-06-27 07:41:56'),
(289, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: Zephyr Sein Lann So pyay. Supplier responses are pending.', 'booking', 'booking', 337, 0, '2026-06-27 07:41:56'),
(290, 30, 'Booking Request Declined', 'JV is unavailable for your requested dates. Please search for another supplier.', 'booking', 'booking', 337, 0, '2026-06-27 07:42:12'),
(291, 24, 'New Booking Request', 'zaw moe is requesting: Zephyr Sein Lann So pyay. Please accept or decline within 24 hours.', 'booking', 'booking', 338, 1, '2026-06-27 08:07:50'),
(292, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: Zephyr Sein Lann So pyay. Supplier responses are pending.', 'booking', 'booking', 338, 0, '2026-06-27 08:07:50'),
(293, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 338, 0, '2026-06-27 08:08:05'),
(294, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 338, 0, '2026-06-27 08:08:32'),
(295, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 780,000 MMK for booking BK-20260627-338. Please verify it.', 'payment', 'booking', 338, 1, '2026-06-27 08:08:32'),
(296, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 338, 0, '2026-06-27 08:08:57'),
(297, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 338, 1, '2026-06-27 08:08:57'),
(298, 1, 'Service publish request', 'JV requested publishing for \"Eldora\".', 'approval', 'service', 170, 1, '2026-06-27 08:40:33'),
(299, 24, 'Publish request sent', 'Your request to publish \"Eldora\" was sent to admin.', 'approval', 'service', 170, 1, '2026-06-27 08:40:33'),
(300, 30, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 338, 0, '2026-06-27 09:04:13'),
(301, 1, 'Remaining Payment Submitted', 'A customer submitted remaining balance payment proof for 2,220,000 MMK for booking BK-20260627-338. Please verify it.', 'payment', 'booking', 338, 1, '2026-06-27 09:04:13'),
(302, 24, 'New Booking Request', 'zaw moe is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 339, 1, '2026-06-27 09:57:00'),
(303, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 339, 0, '2026-06-27 09:57:00'),
(304, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 339, 0, '2026-06-27 09:57:13'),
(305, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 339, 0, '2026-06-27 09:57:44'),
(306, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-339. Please verify it.', 'payment', 'booking', 339, 1, '2026-06-27 09:57:44'),
(307, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 339, 0, '2026-06-27 09:57:54'),
(308, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 339, 0, '2026-06-27 09:57:54'),
(309, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: cancle ချင်လို့ကွာ Your deposit will be refunded.', 'booking', 'booking', 339, 0, '2026-06-27 09:59:09'),
(310, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: cancle ချင်လို့ကွာ', 'booking', 'booking', 339, 1, '2026-06-27 09:59:09'),
(311, 30, 'Refund Being Processed', 'Your refund of 273,000 MMK is being processed. You will receive it shortly.', 'booking', 'booking', 339, 0, '2026-06-27 10:12:16'),
(312, 24, 'New Booking Request', 'zaw moe is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 340, 1, '2026-06-27 10:17:07'),
(313, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 340, 0, '2026-06-27 10:17:07'),
(314, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 340, 0, '2026-06-27 10:17:26'),
(315, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 340, 0, '2026-06-27 10:17:50'),
(316, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-340. Please verify it.', 'payment', 'booking', 340, 1, '2026-06-27 10:17:50'),
(317, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 340, 0, '2026-06-27 10:25:53'),
(318, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 340, 1, '2026-06-27 10:25:53'),
(319, 30, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 340, 0, '2026-06-27 10:26:27'),
(320, 1, 'Remaining Payment Submitted', 'A customer submitted remaining balance payment proof for 1,680,000 MMK for booking BK-20260627-340. Please verify it.', 'payment', 'booking', 340, 1, '2026-06-27 10:26:27'),
(321, 24, 'Cancellation Request — BK-20260627-340', 'zaw moe has requested cancellation of booking BK-20260627-340. Reason: we don\'t want you. Please review and approve or decline this request.', 'booking', 'booking', 340, 0, '2026-06-27 10:35:33'),
(322, 1, 'Cancellation Request (Pending Supplier Review) — BK-20260627-340', 'zaw moe has requested cancellation of customize booking BK-20260627-340. The supplier has been asked to review first.', 'booking', 'booking', 340, 1, '2026-06-27 10:35:33'),
(323, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: the customer sent the canclelation request Your deposit will be refunded.', 'booking', 'booking', 340, 0, '2026-06-27 10:36:22'),
(324, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: the customer sent the canclelation request', 'booking', 'booking', 340, 0, '2026-06-27 10:36:22'),
(325, 30, 'Refund Being Processed', 'Your refund of 0 MMK is being processed. You will receive it shortly.', 'booking', 'booking', 340, 0, '2026-06-27 10:36:38'),
(326, 30, 'Refund Completed', 'Your refund of 0 MMK has been completed. Please check your account.', 'booking', 'booking', 340, 0, '2026-06-27 10:37:19'),
(327, 30, 'Refund Being Processed', 'Your refund of 273,000 MMK is being processed. You will receive it shortly.', 'booking', 'booking', 339, 0, '2026-06-27 10:37:41'),
(328, 30, 'Refund Completed', 'Your refund of 273,000 MMK has been completed. Please check your account.', 'booking', 'booking', 339, 0, '2026-06-27 10:38:01'),
(329, 24, 'New Booking Request', 'zaw moe is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 341, 1, '2026-06-27 10:39:57'),
(330, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 341, 1, '2026-06-27 10:39:57'),
(331, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 341, 0, '2026-06-27 10:40:10'),
(332, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 341, 0, '2026-06-27 10:40:57'),
(333, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-341. Please verify it.', 'payment', 'booking', 341, 0, '2026-06-27 10:40:57'),
(334, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 341, 0, '2026-06-27 10:41:04'),
(335, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 341, 0, '2026-06-27 10:41:04'),
(336, 30, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 341, 0, '2026-06-27 10:41:54'),
(337, 1, 'Remaining Payment Submitted', 'A customer submitted remaining balance payment proof for 1,680,000 MMK for booking BK-20260627-341. Please verify it.', 'payment', 'booking', 341, 0, '2026-06-27 10:41:54'),
(338, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: refund Your deposit will be refunded.', 'booking', 'booking', 52, 0, '2026-06-27 10:48:41'),
(339, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: refund', 'booking', 'booking', 52, 0, '2026-06-27 10:48:41'),
(340, 29, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: refund', 'booking', 'booking', 52, 0, '2026-06-27 10:48:41'),
(341, 30, 'Refund Being Processed', 'Your refund of 0 MMK is being processed. You will receive it shortly.', 'booking', 'booking', 52, 0, '2026-06-27 10:48:59'),
(342, 30, 'Refund Completed', 'Your refund of 0 MMK has been completed. Please check your account.', 'booking', 'booking', 52, 0, '2026-06-27 10:49:09'),
(343, 24, 'New Booking Request', 'zaw moe is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 342, 1, '2026-06-27 10:53:32'),
(344, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 342, 0, '2026-06-27 10:53:32'),
(345, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 342, 0, '2026-06-27 10:53:43'),
(346, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 342, 0, '2026-06-27 10:54:01'),
(347, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-342. Please verify it.', 'payment', 'booking', 342, 1, '2026-06-27 10:54:01'),
(348, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 342, 0, '2026-06-27 10:54:09'),
(349, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 342, 1, '2026-06-27 10:54:09'),
(350, 30, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 342, 0, '2026-06-27 11:03:15'),
(351, 1, 'Remaining Payment Submitted', 'A customer submitted remaining balance payment proof for 1,680,000 MMK for booking BK-20260627-342. Please verify it.', 'payment', 'booking', 342, 0, '2026-06-27 11:03:15'),
(352, 24, 'New Booking Request', 'zaw moe is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 343, 1, '2026-06-27 11:04:29'),
(353, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 343, 0, '2026-06-27 11:04:29'),
(354, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 343, 0, '2026-06-27 11:04:45'),
(355, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 343, 0, '2026-06-27 11:05:06'),
(356, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-343. Please verify it.', 'payment', 'booking', 343, 1, '2026-06-27 11:05:06'),
(357, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 343, 0, '2026-06-27 11:05:15'),
(358, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 343, 1, '2026-06-27 11:05:15'),
(359, 30, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 343, 0, '2026-06-27 11:05:49'),
(360, 1, 'Remaining Payment Submitted', 'A customer submitted remaining balance payment proof for 1,680,000 MMK for booking BK-20260627-343. Please verify it.', 'payment', 'booking', 343, 0, '2026-06-27 11:05:49'),
(361, 24, 'New Booking Request', 'zaw moe is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 344, 1, '2026-06-27 11:09:35'),
(362, 1, 'New Custom Booking Request', 'zaw moe created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 344, 0, '2026-06-27 11:09:35'),
(363, 30, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 344, 0, '2026-06-27 11:09:51'),
(364, 30, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 344, 0, '2026-06-27 11:11:04'),
(365, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-344. Please verify it.', 'payment', 'booking', 344, 1, '2026-06-27 11:11:04'),
(366, 30, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 344, 0, '2026-06-27 11:11:17'),
(367, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 344, 1, '2026-06-27 11:11:17'),
(368, 30, 'Supplier Cancellation Request — BK-20260627-344', 'JV has requested to cancel your booking BK-20260627-344. Reason: i don\'t want to do. Admin will review and process your refund.', 'booking', 'booking', 344, 0, '2026-06-27 11:30:41'),
(369, 1, 'Supplier Requests Cancellation — BK-20260627-344', 'JV has requested cancellation of booking BK-20260627-344. Reason: i don\'t want to do. Please review and process the refund.', 'booking', 'booking', 344, 1, '2026-06-27 11:30:41'),
(370, 30, 'Booking Cancelled by Admin', 'Your booking has been cancelled by the administrator. Reason: supplier request to cancle Your deposit will be refunded.', 'booking', 'booking', 344, 0, '2026-06-27 11:36:37'),
(371, 24, 'Booking Cancelled', 'A booking has been cancelled by the administrator. Reason: supplier request to cancle', 'booking', 'booking', 344, 0, '2026-06-27 11:36:37'),
(372, 30, 'Refund Being Processed', 'Your refund of 273,000 MMK is being processed. You will receive it shortly.', 'booking', 'booking', 344, 0, '2026-06-27 11:36:50'),
(373, 24, 'New Booking Request', 'Saen is requesting: H & H Wedding Studio. Please accept or decline within 24 hours.', 'booking', 'booking', 345, 1, '2026-06-27 11:39:58'),
(374, 1, 'New Custom Booking Request', 'Saen created a custom or mixed booking for: H & H Wedding Studio. Supplier responses are pending.', 'booking', 'booking', 345, 1, '2026-06-27 11:39:58'),
(375, 29, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 345, 0, '2026-06-27 11:40:10'),
(376, 29, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 345, 0, '2026-06-27 11:40:40'),
(377, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 546,000 MMK for booking BK-20260627-345. Please verify it.', 'payment', 'booking', 345, 0, '2026-06-27 11:40:40'),
(378, 29, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 345, 0, '2026-06-27 11:40:45'),
(379, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 345, 1, '2026-06-27 11:40:45'),
(380, 29, 'Remaining Payment Submitted', 'Your remaining balance payment proof has been received. Our team will verify and confirm shortly.', 'payment', 'booking', 345, 0, '2026-06-27 11:41:09'),
(381, 1, 'Remaining Payment Submitted', 'A customer submitted remaining balance payment proof for 1,680,000 MMK for booking BK-20260627-345. Please verify it.', 'payment', 'booking', 345, 0, '2026-06-27 11:41:09'),
(382, 29, 'Remaining Payment Verified', 'Your remaining balance payment has been verified! Your booking is now fully paid and finalized.', 'payment', 'booking', 345, 0, '2026-06-27 11:56:37'),
(383, 29, 'Booking Completed', 'Your booking has been marked as completed. Supplier payouts have been processed.', 'booking', 'booking', 345, 0, '2026-06-27 11:58:14'),
(384, 24, 'New Booking Request', 'Saen is requesting: Zephyr Sein Lann So pyay. Please accept or decline within 24 hours.', 'booking', 'booking', 346, 1, '2026-06-27 12:01:48'),
(385, 1, 'New Custom Booking Request', 'Saen created a custom or mixed booking for: Zephyr Sein Lann So pyay. Supplier responses are pending.', 'booking', 'booking', 346, 0, '2026-06-27 12:01:48'),
(386, 29, 'Supplier Accepted — Please Pay', 'JV accepted your booking request. Please complete your 20% deposit to confirm.', 'booking', 'booking', 346, 0, '2026-06-27 12:02:09'),
(387, 29, 'Payment Proof Submitted', 'Your bank transfer details have been received. Our team will verify and confirm shortly.', 'payment', 'booking', 346, 0, '2026-06-27 12:02:53'),
(388, 1, 'Deposit Proof Submitted', 'A customer submitted deposit payment proof for 780,000 MMK for booking BK-20260627-346. Please verify it.', 'payment', 'booking', 346, 1, '2026-06-27 12:02:53'),
(389, 29, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 346, 0, '2026-06-27 12:03:08'),
(390, 24, 'New Booking — Payment Verified', 'A new booking with confirmed payment is ready for your review.', 'booking', 'booking', 346, 1, '2026-06-27 12:03:08');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `type` enum('signup','login','password_reset','supplier_verify','payment_verify') DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `max_attempts` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `user_id`, `code`, `type`, `expires_at`, `is_used`, `attempt_count`, `max_attempts`, `created_at`) VALUES
(1, 1, '780876', 'login', '2026-05-22 02:21:23', 1, 0, 3, '2026-05-22 02:20:57'),
(2, 1, '466931', 'login', '2026-05-23 07:41:43', 1, 0, 3, '2026-05-23 07:41:22'),
(3, 1, '941150', 'login', '2026-05-24 02:33:41', 1, 0, 3, '2026-05-24 02:32:37'),
(4, 1, '518223', 'login', '2026-05-24 02:33:57', 1, 0, 3, '2026-05-24 02:33:46'),
(13, 27, '157701', 'login', '2026-06-11 04:11:48', 1, 0, 3, '2026-06-11 04:11:34'),
(14, 1, '359082', 'login', '2026-06-11 05:06:54', 1, 0, 3, '2026-06-11 05:06:40'),
(15, 27, '666825', 'login', '2026-06-11 12:41:37', 1, 0, 3, '2026-06-11 12:41:11'),
(16, 1, '606311', 'login', '2026-06-17 04:18:19', 1, 1, 3, '2026-06-17 04:17:51'),
(17, 1, '471707', 'login', '2026-06-17 04:18:39', 1, 0, 3, '2026-06-17 04:18:22'),
(18, 47, '354551', 'login', '2026-06-20 14:58:16', 1, 0, 3, '2026-06-20 14:57:53'),
(19, 40, '615376', 'login', '2026-06-20 15:24:02', 1, 0, 3, '2026-06-20 15:23:32'),
(20, 37, '392114', 'login', '2026-06-21 06:30:34', 1, 0, 3, '2026-06-21 06:29:56'),
(21, 37, '982130', 'login', '2026-06-21 07:21:11', 1, 0, 3, '2026-06-21 07:18:31'),
(22, 37, '777326', 'login', '2026-06-21 07:21:31', 1, 0, 3, '2026-06-21 07:21:11'),
(23, 102, '250452', 'login', '2026-06-22 07:46:46', 1, 0, 3, '2026-06-22 07:46:01'),
(24, 1, '847431', 'login', '2026-06-23 03:15:28', 1, 0, 3, '2026-06-23 03:15:09'),
(25, 1, '250047', 'login', '2026-06-23 04:02:22', 1, 0, 3, '2026-06-23 04:01:52'),
(26, 105, '228256', 'login', '2026-06-23 04:21:18', 1, 1, 3, '2026-06-23 04:20:42'),
(27, 105, '787794', 'login', '2026-06-23 04:22:14', 1, 0, 3, '2026-06-23 04:21:43'),
(28, 1, '996073', 'login', '2026-06-23 04:25:37', 1, 0, 3, '2026-06-23 04:25:20'),
(29, 1, '333373', 'login', '2026-06-23 04:30:29', 1, 0, 3, '2026-06-23 04:30:11'),
(30, 109, '775224', 'login', '2026-06-23 04:32:21', 1, 0, 3, '2026-06-23 04:30:59'),
(31, 109, '658745', 'login', '2026-06-23 04:33:02', 1, 0, 3, '2026-06-23 04:32:24'),
(38, 112, '137426', 'login', '2026-06-23 05:01:18', 1, 0, 3, '2026-06-23 05:00:59'),
(39, 113, '707168', 'login', '2026-06-23 05:04:47', 1, 0, 3, '2026-06-23 05:04:29'),
(40, 112, '699290', 'login', '2026-06-23 05:09:23', 1, 0, 3, '2026-06-23 05:09:01'),
(41, 1, '155361', 'login', '2026-06-23 05:17:25', 1, 0, 3, '2026-06-23 05:16:45'),
(42, 24, '148692', 'login', '2026-06-23 05:20:47', 1, 0, 3, '2026-06-23 05:20:28'),
(43, 114, '442018', 'login', '2026-06-23 05:31:17', 1, 0, 3, '2026-06-23 05:30:40'),
(44, 109, '235390', 'login', '2026-06-23 12:58:55', 1, 0, 3, '2026-06-23 12:58:33'),
(45, 109, '561529', 'login', '2026-06-23 12:59:01', 1, 0, 3, '2026-06-23 12:58:55'),
(46, 109, '258056', 'login', '2026-06-23 13:00:16', 1, 0, 3, '2026-06-23 12:59:01'),
(47, 109, '355460', 'login', '2026-06-23 13:01:27', 1, 0, 3, '2026-06-23 13:00:16'),
(48, 109, '875943', 'login', '2026-06-23 13:02:38', 1, 1, 3, '2026-06-23 13:01:27'),
(49, 109, '591850', 'login', '2026-06-23 13:03:59', 1, 1, 3, '2026-06-23 13:02:38'),
(50, 109, '192915', 'login', '2026-06-23 13:06:16', 1, 1, 3, '2026-06-23 13:03:59'),
(51, 109, '800927', 'login', '2026-06-23 13:06:40', 1, 0, 3, '2026-06-23 13:06:16'),
(52, 109, '837330', 'login', '2026-06-23 13:44:40', 1, 1, 3, '2026-06-23 13:43:22'),
(53, 109, '397304', 'login', '2026-06-23 13:45:18', 1, 0, 3, '2026-06-23 13:44:40'),
(54, 109, '682294', 'login', '2026-06-23 14:58:11', 1, 0, 3, '2026-06-23 14:57:44'),
(55, 109, '581777', 'login', '2026-06-23 14:59:57', 1, 0, 3, '2026-06-23 14:59:45'),
(56, 109, '305689', 'login', '2026-06-23 15:00:34', 1, 0, 3, '2026-06-23 14:59:57'),
(57, 109, '295942', 'login', '2026-06-23 15:01:30', 1, 0, 3, '2026-06-23 15:00:34'),
(58, 109, '953371', 'login', '2026-06-23 15:02:56', 1, 0, 3, '2026-06-23 15:01:30'),
(59, 109, '475175', 'login', '2026-06-23 15:04:10', 1, 1, 3, '2026-06-23 15:02:56'),
(60, 109, '221155', 'login', '2026-06-23 15:04:34', 1, 0, 3, '2026-06-23 15:04:10'),
(61, 114, '290089', 'login', '2026-06-23 15:13:45', 1, 0, 3, '2026-06-23 15:13:30'),
(62, 114, '684160', 'login', '2026-06-23 15:14:50', 1, 1, 3, '2026-06-23 15:13:45'),
(63, 114, '408376', 'login', '2026-06-23 15:16:11', 1, 0, 3, '2026-06-23 15:14:56'),
(64, 114, '418548', 'login', '2026-06-24 04:28:35', 1, 0, 3, '2026-06-23 15:16:11'),
(65, 109, '916289', 'login', '2026-06-23 15:24:48', 1, 0, 3, '2026-06-23 15:24:21'),
(66, 105, '385949', 'login', '2026-06-24 02:05:39', 1, 0, 3, '2026-06-24 02:04:34'),
(67, 105, '793589', 'login', '2026-06-24 02:06:18', 1, 0, 3, '2026-06-24 02:05:39'),
(68, 1, '420231', 'login', '2026-06-24 02:08:30', 1, 0, 3, '2026-06-24 02:07:53'),
(69, 109, '423319', 'login', '2026-06-24 02:09:06', 1, 0, 3, '2026-06-24 02:08:45'),
(70, 112, '504137', 'login', '2026-06-24 02:19:38', 1, 0, 3, '2026-06-24 02:19:19'),
(71, 112, '923177', 'login', '2026-06-24 03:18:23', 1, 0, 3, '2026-06-24 03:18:06'),
(72, 112, '520321', 'login', '2026-06-24 03:19:54', 1, 1, 3, '2026-06-24 03:19:23'),
(73, 112, '663134', 'login', '2026-06-24 03:20:13', 1, 0, 3, '2026-06-24 03:19:54'),
(74, 122, '884214', 'login', '2026-06-24 03:21:47', 1, 0, 3, '2026-06-24 03:21:24'),
(75, 109, '447116', 'login', '2026-06-24 03:22:08', 1, 0, 3, '2026-06-24 03:21:42'),
(76, 109, '214932', 'login', '2026-06-24 03:22:17', 1, 0, 3, '2026-06-24 03:22:08'),
(77, 109, '470026', 'login', '2026-06-24 03:22:37', 1, 0, 3, '2026-06-24 03:22:17'),
(78, 109, '688266', 'login', '2026-06-24 03:22:59', 1, 0, 3, '2026-06-24 03:22:37'),
(79, 112, '696627', 'login', '2026-06-24 03:23:06', 1, 0, 3, '2026-06-24 03:22:42'),
(80, 109, '894815', 'login', '2026-06-24 03:23:23', 1, 0, 3, '2026-06-24 03:22:59'),
(81, 121, '963995', 'login', '2026-06-24 03:23:33', 1, 0, 3, '2026-06-24 03:23:08'),
(82, 124, '295751', 'login', '2026-06-24 03:53:32', 1, 0, 3, '2026-06-24 03:53:02'),
(83, 124, '400410', 'login', '2026-06-24 03:53:55', 1, 0, 3, '2026-06-24 03:53:32'),
(84, 124, '563573', 'login', '2026-06-24 03:59:50', 1, 0, 3, '2026-06-24 03:57:24'),
(85, 123, '540966', 'login', '2026-06-24 03:59:17', 1, 0, 3, '2026-06-24 03:58:55'),
(86, 124, '427995', 'login', '2026-06-24 04:00:16', 1, 0, 3, '2026-06-24 03:59:50'),
(87, 123, '562280', 'login', '2026-06-24 04:00:58', 1, 0, 3, '2026-06-24 04:00:32'),
(88, 124, '854787', 'login', '2026-06-24 04:05:44', 1, 0, 3, '2026-06-24 04:04:26'),
(89, 124, '822258', 'login', '2026-06-24 04:06:15', 1, 0, 3, '2026-06-24 04:05:44'),
(90, 105, '608772', 'login', '2026-06-24 04:08:35', 1, 0, 3, '2026-06-24 04:06:37'),
(91, 113, '903774', 'login', '2026-06-24 04:07:51', 1, 0, 3, '2026-06-24 04:07:22'),
(92, 105, '268283', 'login', '2026-06-24 04:08:58', 1, 0, 3, '2026-06-24 04:08:35'),
(93, 124, '633989', 'login', '2026-06-24 04:18:07', 1, 1, 3, '2026-06-24 04:17:32'),
(94, 112, '333932', 'login', '2026-06-24 04:18:37', 1, 0, 3, '2026-06-24 04:18:15'),
(95, 124, '271904', 'login', '2026-06-24 04:19:18', 1, 0, 3, '2026-06-24 04:18:50'),
(96, 24, '835396', 'login', '2026-06-24 04:21:16', 1, 0, 3, '2026-06-24 04:20:15'),
(97, 24, '834918', 'login', '2026-06-24 04:22:06', 1, 0, 3, '2026-06-24 04:21:39'),
(98, 121, '772357', 'login', '2026-06-24 04:22:09', 1, 0, 3, '2026-06-24 04:21:43'),
(99, 114, '605657', 'login', '2026-06-24 04:29:31', 1, 0, 3, '2026-06-24 04:28:35'),
(100, 124, '511605', 'login', '2026-06-24 04:30:00', 1, 0, 3, '2026-06-24 04:29:42'),
(101, 114, '947640', 'login', '2026-06-24 04:37:03', 1, 0, 3, '2026-06-24 04:35:54'),
(102, 114, '619113', 'login', '2026-06-24 04:37:51', 1, 1, 3, '2026-06-24 04:37:03'),
(103, 114, '190225', 'login', '2026-06-24 04:38:54', 1, 0, 3, '2026-06-24 04:38:30'),
(104, 111, '264256', 'login', '2026-06-24 04:49:35', 1, 0, 3, '2026-06-24 04:49:06'),
(105, 109, '601373', 'login', '2026-06-24 05:04:17', 1, 0, 3, '2026-06-24 05:03:55'),
(106, 114, '978086', 'login', '2026-06-24 05:28:25', 1, 0, 3, '2026-06-24 05:26:48'),
(107, 114, '368560', 'login', '2026-06-24 05:28:47', 1, 0, 3, '2026-06-24 05:28:25'),
(108, 29, '406516', 'login', '2026-06-24 05:35:45', 1, 0, 3, '2026-06-24 05:34:10'),
(109, 29, '390961', 'login', '2026-06-24 05:36:04', 1, 0, 3, '2026-06-24 05:35:45'),
(110, 114, '119691', 'login', '2026-06-24 05:41:59', 1, 0, 3, '2026-06-24 05:41:34'),
(111, 27, '217326', 'login', '2026-06-24 13:15:33', 1, 0, 3, '2026-06-24 13:15:13'),
(112, 27, '199093', 'login', '2026-06-24 13:16:05', 1, 0, 3, '2026-06-24 13:15:45'),
(113, 27, '509939', 'login', '2026-06-24 13:17:20', 1, 0, 3, '2026-06-24 13:16:05'),
(114, 27, '861668', 'login', '2026-06-24 13:17:46', 1, 0, 3, '2026-06-24 13:17:23'),
(115, 27, '511599', 'login', '2026-06-24 13:19:09', 1, 0, 3, '2026-06-24 13:18:45'),
(116, 109, '823810', 'login', '2026-06-25 02:31:48', 1, 0, 3, '2026-06-25 02:31:26'),
(117, 126, '604062', 'login', '2026-06-25 02:34:09', 1, 0, 3, '2026-06-25 02:33:20'),
(118, 105, '394627', 'login', '2026-06-25 02:34:49', 1, 0, 3, '2026-06-25 02:34:13'),
(119, 105, '657889', 'login', '2026-06-25 02:43:17', 1, 1, 3, '2026-06-25 02:41:38'),
(120, 126, '360166', 'login', '2026-06-25 02:43:54', 1, 0, 3, '2026-06-25 02:43:04'),
(121, 109, '210748', 'login', '2026-06-25 02:44:51', 1, 0, 3, '2026-06-25 02:43:22'),
(122, 105, '202141', 'login', '2026-06-25 02:45:11', 1, 0, 3, '2026-06-25 02:44:38'),
(123, 109, '943089', 'login', '2026-06-25 02:45:06', 1, 0, 3, '2026-06-25 02:44:51'),
(124, 124, '285738', 'login', '2026-06-25 03:00:30', 1, 0, 3, '2026-06-25 03:00:02'),
(125, 1, '129803', 'login', '2026-06-25 03:02:39', 1, 0, 3, '2026-06-25 03:02:21'),
(126, 124, '558652', 'login', '2026-06-25 03:14:30', 1, 0, 3, '2026-06-25 03:14:08'),
(127, 1, '509509', 'login', '2026-06-25 03:16:41', 1, 0, 3, '2026-06-25 03:16:09'),
(128, 1, '489639', 'login', '2026-06-25 03:24:46', 1, 0, 3, '2026-06-25 03:24:32'),
(129, 1, '149706', 'login', '2026-06-25 03:29:12', 1, 0, 3, '2026-06-25 03:28:58'),
(130, 27, '891267', 'login', '2026-06-25 03:31:23', 1, 0, 3, '2026-06-25 03:30:46'),
(131, 1, '884254', 'login', '2026-06-25 03:35:49', 1, 0, 3, '2026-06-25 03:33:41'),
(132, 1, '447404', 'login', '2026-06-25 03:41:12', 1, 0, 3, '2026-06-25 03:35:49'),
(133, 1, '796857', 'login', '2026-06-25 03:41:29', 1, 0, 3, '2026-06-25 03:41:12'),
(134, 1, '279462', 'login', '2026-06-25 03:46:32', 1, 0, 3, '2026-06-25 03:46:07'),
(135, 131, '722308', 'login', '2026-06-25 03:47:58', 1, 0, 3, '2026-06-25 03:47:30'),
(136, 1, '910950', 'login', '2026-06-25 03:50:37', 1, 0, 3, '2026-06-25 03:50:14'),
(137, 105, '328514', 'login', '2026-06-25 04:26:43', 1, 0, 3, '2026-06-25 04:26:27'),
(138, 126, '995935', 'login', '2026-06-25 04:32:32', 1, 0, 3, '2026-06-25 04:30:56'),
(139, 126, '359600', 'login', '2026-06-26 04:24:19', 1, 0, 3, '2026-06-25 04:32:32'),
(140, 27, '384388', 'login', '2026-06-25 04:33:54', 1, 0, 3, '2026-06-25 04:33:13'),
(141, 131, '103854', 'login', '2026-06-25 04:40:38', 1, 0, 3, '2026-06-25 04:40:17'),
(142, 131, '375129', 'login', '2026-06-25 04:45:23', 1, 0, 3, '2026-06-25 04:44:53'),
(143, 27, '229567', 'login', '2026-06-25 04:48:46', 1, 0, 3, '2026-06-25 04:48:09'),
(144, 132, '463839', 'login', '2026-06-25 05:17:45', 0, 0, 3, '2026-06-25 05:16:45'),
(145, 27, '220026', 'login', '2026-06-25 06:45:46', 1, 0, 3, '2026-06-25 06:45:20'),
(146, 133, '964780', 'login', '2026-06-26 02:30:22', 1, 1, 3, '2026-06-26 02:29:48'),
(147, 133, '434523', 'login', '2026-06-26 02:31:22', 1, 0, 3, '2026-06-26 02:31:05'),
(148, 1, '556725', 'login', '2026-06-26 02:44:21', 1, 0, 3, '2026-06-26 02:44:02'),
(149, 27, '333972', 'login', '2026-06-26 03:11:15', 1, 0, 3, '2026-06-26 03:10:29'),
(150, 1, '795263', 'login', '2026-06-26 03:21:20', 1, 0, 3, '2026-06-26 03:21:03'),
(151, 27, '938329', 'login', '2026-06-26 04:10:47', 1, 0, 3, '2026-06-26 04:09:30'),
(152, 27, '951586', 'login', '2026-06-26 04:11:06', 1, 0, 3, '2026-06-26 04:10:47'),
(153, 126, '278803', 'login', '2026-06-26 04:24:37', 1, 0, 3, '2026-06-26 04:24:19'),
(154, 1, '435152', 'login', '2026-06-26 05:00:51', 1, 0, 3, '2026-06-26 05:00:34'),
(155, 1, '834756', 'login', '2026-06-27 03:58:44', 1, 0, 3, '2026-06-27 03:58:18'),
(156, 1, '926504', 'login', '2026-06-27 03:59:05', 1, 0, 3, '2026-06-27 03:58:52'),
(157, 1, '140268', 'login', '2026-06-27 04:00:42', 1, 0, 3, '2026-06-27 04:00:31'),
(158, 27, '223095', 'login', '2026-06-27 04:01:25', 1, 0, 3, '2026-06-27 04:01:04'),
(159, 24, '470945', 'login', '2026-06-27 04:03:28', 1, 0, 3, '2026-06-27 04:03:12'),
(160, 27, '988739', 'login', '2026-06-27 04:56:50', 1, 1, 3, '2026-06-27 04:56:14'),
(161, 27, '137464', 'login', '2026-06-27 04:57:21', 1, 0, 3, '2026-06-27 04:56:50'),
(162, 24, '917931', 'login', '2026-06-27 07:28:09', 1, 0, 3, '2026-06-27 07:27:51');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `package_id` bigint(20) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'curated',
  `description` text DEFAULT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `max_concurrent` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'published',
  `replaces_package_id` bigint(20) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`package_id`, `name`, `category_id`, `slug`, `type`, `description`, `tagline`, `base_price`, `max_concurrent`, `image_url`, `is_active`, `status`, `replaces_package_id`, `sort_order`, `created_at`, `deleted_at`) VALUES
(19, 'Standard Wedding Package', 4, 'standard-wedding-package', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 1500000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-18 09:55:29', '2026-06-19 02:52:44'),
(20, 'Standard Wedding Package', 4, 'standard-wedding-package-2', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 70000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618152115-7d249ee0.png', 1, 'published', NULL, 0, '2026-06-18 10:30:53', NULL),
(23, 'Standard Wedding Package', 4, 'standard-wedding-package-3', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 4700000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-18 19:16:38', '2026-06-20 01:31:27'),
(26, 'Standard Wedding Package', 4, 'standard-wedding-package-3-2', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 3880000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-20 01:29:58', '2026-06-20 05:32:03'),
(29, 'Standard Wedding Package', 4, 'standard-wedding-package-3-2-2', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 3953000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-20 05:19:32', '2026-06-20 05:49:06'),
(30, 'Standard Wedding Package', 4, 'standard-wedding-package-3-2-2-2', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 3953000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-20 05:45:31', '2026-06-23 05:14:57'),
(31, 'Standard Wedding Package', 4, 'standard-wedding-package-3-2-2-2-2', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 3953000.00, 0, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-20 08:32:17', NULL),
(35, 'Luxury Package', 2, 'luxury-package', 'curated', 'အဆင့်မြင့်၊ သီးသန့်ဆန်ပြီး အထူးခမ်းနားထည်ဝါသော မင်္ဂလာပွဲကို ရည်ရွယ်သော စုံတွဲများအတွက် အကောင်းဆုံး Package ဖြစ်ပါသည်။ အစီအစဉ်ရေးဆွဲခြင်းမှ စ၍ ပွဲပြီးဆုံးသည်အထိ Premium Service များဖြင့် အသေးစိတ် ဂရုစိုက်ဆောင်ရွက်ပေးကာ ဇိမ်ခံဆန်သော အလှဆင်ဒီဇိုင်းများ၊ သီးသန့်ဝန်ဆောင်မှုများနှင့် အကောင်းဆုံးအတွေ့အကြုံများကို ပေးအပ်ပါသည်။ သင့်၏ ချစ်ခြင်းမေတ္တာဇာတ်လမ်းကို အထူးခမ်းနားစွာ ဖော်ဆောင်ပေးမည့် Luxury Wedding Experience ကို ရရှိစေပါသည်။', 'Enjoy your journey with us', 0.00, 10, '', 1, 'published', NULL, 0, '2026-06-26 04:21:34', '2026-06-26 11:17:28'),
(37, 'Luxury Package', 2, 'luxury-package-2', 'curated', 'အဆင့်မြင့်၊ သီးသန့်ဆန်ပြီး အထူးခမ်းနားထည်ဝါသော မင်္ဂလာပွဲကို ရည်ရွယ်သော စုံတွဲများအတွက် အကောင်းဆုံး Package ဖြစ်ပါသည်။ အစီအစဉ်ရေးဆွဲခြင်းမှ စ၍ ပွဲပြီးဆုံးသည်အထိ Premium Service များဖြင့် အသေးစိတ် ဂရုစိုက်ဆောင်ရွက်ပေးကာ ဇိမ်ခံဆန်သော အလှဆင်ဒီဇိုင်းများ၊ သီးသန့်ဝန်ဆောင်မှုများနှင့် အကောင်းဆုံးအတွေ့အကြုံများကို ပေးအပ်ပါသည်။ သင့်၏ ချစ်ခြင်းမေတ္တာဇာတ်လမ်းကို အထူးခမ်းနားစွာ ဖော်ဆောင်ပေးမည့် Luxury Wedding Experience ကို ရရှိစေပါသည်။', 'Enjoy your journey with us', 0.00, 10, '', 1, 'published', NULL, 0, '2026-06-26 11:08:12', '2026-06-26 11:18:27'),
(38, 'Luxury Package', 2, 'luxury-package-2-2', 'curated', 'အဆင့်မြင့်၊ သီးသန့်ဆန်ပြီး အထူးခမ်းနားထည်ဝါသော မင်္ဂလာပွဲကို ရည်ရွယ်သော စုံတွဲများအတွက် အကောင်းဆုံး Package ဖြစ်ပါသည်။ အစီအစဉ်ရေးဆွဲခြင်းမှ စ၍ ပွဲပြီးဆုံးသည်အထိ Premium Service များဖြင့် အသေးစိတ် ဂရုစိုက်ဆောင်ရွက်ပေးကာ ဇိမ်ခံဆန်သော အလှဆင်ဒီဇိုင်းများ၊ သီးသန့်ဝန်ဆောင်မှုများနှင့် အကောင်းဆုံးအတွေ့အကြုံများကို ပေးအပ်ပါသည်။ သင့်၏ ချစ်ခြင်းမေတ္တာဇာတ်လမ်းကို အထူးခမ်းနားစွာ ဖော်ဆောင်ပေးမည့် Luxury Wedding Experience ကို ရရှိစေပါသည်။', 'Enjoy your journey with us', 0.00, 10, '', 1, 'published', NULL, 0, '2026-06-26 11:18:07', '2026-06-26 11:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `package_items`
--

CREATE TABLE `package_items` (
  `id` bigint(20) NOT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `venue_room_id` bigint(20) DEFAULT NULL,
  `attire_item_id` bigint(20) DEFAULT NULL,
  `decoration_style_id` bigint(20) DEFAULT NULL,
  `cake_design_id` bigint(20) DEFAULT NULL,
  `default_supplier_id` bigint(20) DEFAULT NULL,
  `default_price` decimal(10,2) DEFAULT NULL,
  `customize_price` decimal(10,2) DEFAULT NULL,
  `max_concurrent` smallint(5) UNSIGNED DEFAULT NULL,
  `quantity_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_items`
--

INSERT INTO `package_items` (`id`, `package_id`, `category_id`, `service_id`, `venue_room_id`, `attire_item_id`, `decoration_style_id`, `cake_design_id`, `default_supplier_id`, `default_price`, `customize_price`, `max_concurrent`, `quantity_type`, `quantity`, `deleted_at`) VALUES
(65, 20, 6, 42, 21, NULL, NULL, NULL, 21, 70000.00, NULL, NULL, 'fixed', 1, NULL),
(73, 23, 12, 48, NULL, NULL, 11, NULL, 20, 2100000.00, NULL, NULL, 'guests', 1, NULL),
(74, 23, 6, 49, 22, NULL, NULL, NULL, 20, 900000.00, 910000.00, NULL, 'fixed', 1, NULL),
(75, 23, 5, 50, NULL, NULL, NULL, NULL, 20, 200000.00, 2100000.00, NULL, 'guests', 1, NULL),
(80, 26, 12, 48, NULL, NULL, NULL, NULL, 20, 2100000.00, NULL, NULL, 'guests', 1, NULL),
(82, 26, 5, 50, NULL, NULL, NULL, NULL, 20, 200000.00, 2100000.00, NULL, 'guests', 1, NULL),
(86, 26, 2, 55, NULL, 3, NULL, NULL, 21, 40000.00, 500000.00, 1, 'guests', 2, NULL),
(103, 29, 12, 48, NULL, NULL, NULL, NULL, 20, 2100000.00, NULL, NULL, 'guests', 1, NULL),
(104, 29, 5, 50, NULL, NULL, NULL, NULL, 20, 200000.00, 2100000.00, NULL, 'guests', 1, NULL),
(105, 29, 2, 55, NULL, NULL, NULL, NULL, 21, 40000.00, 500000.00, 1, 'guests', 2, NULL),
(109, 29, 10, 56, NULL, NULL, NULL, NULL, 21, 73000.00, 75000.00, 3, 'guests', 1, NULL),
(111, 30, 12, 48, NULL, NULL, NULL, NULL, 20, 2100000.00, NULL, NULL, 'guests', 1, NULL),
(112, 30, 5, 50, NULL, NULL, NULL, NULL, 20, 200000.00, 2100000.00, NULL, 'guests', 1, NULL),
(113, 30, 2, 55, NULL, NULL, NULL, NULL, 21, 40000.00, 500000.00, 1, 'guests', 2, NULL),
(117, 30, 10, 56, NULL, NULL, NULL, NULL, 21, 73000.00, 75000.00, 2, 'guests', 1, NULL),
(119, 31, 12, 48, NULL, NULL, NULL, NULL, 20, 2100000.00, NULL, NULL, 'guests', 1, NULL),
(120, 31, 5, 50, NULL, NULL, NULL, NULL, 20, 200000.00, 2100000.00, NULL, 'guests', 1, NULL),
(121, 31, 2, 55, NULL, NULL, NULL, NULL, 21, 40000.00, 500000.00, 1, 'guests', 2, NULL),
(122, 31, 10, 56, NULL, NULL, NULL, NULL, 21, 73000.00, 75000.00, 2, 'guests', 1, NULL),
(140, 35, 11, 148, NULL, NULL, NULL, NULL, 113, 55000.00, NULL, NULL, 'fixed', 1, NULL),
(141, 35, 12, 135, NULL, NULL, NULL, NULL, 100, 3000000.00, NULL, NULL, 'guests', 100, NULL),
(142, 35, 3, 130, NULL, NULL, NULL, NULL, 95, 12000.00, NULL, NULL, 'guests', 100, NULL),
(144, 35, 10, 159, NULL, NULL, NULL, NULL, 124, 150000.00, NULL, NULL, 'guests', 100, NULL),
(145, 35, 6, 138, NULL, NULL, NULL, NULL, 103, 800000.00, NULL, NULL, 'fixed', 1, NULL),
(153, 37, 11, 148, NULL, NULL, NULL, NULL, 113, 55000.00, NULL, NULL, 'fixed', 1, NULL),
(154, 37, 12, 135, NULL, NULL, NULL, NULL, 100, 3000000.00, NULL, NULL, 'guests', 100, NULL),
(155, 37, 3, 130, NULL, NULL, NULL, NULL, 95, 12000.00, NULL, NULL, 'guests', 100, NULL),
(157, 37, 10, 159, NULL, NULL, NULL, NULL, 124, 150000.00, NULL, NULL, 'guests', 100, NULL),
(158, 37, 6, 138, NULL, NULL, NULL, NULL, 103, 800000.00, NULL, NULL, 'fixed', 1, NULL),
(161, 38, 12, 135, NULL, NULL, NULL, NULL, 100, 3000000.00, NULL, NULL, 'guests', 100, NULL),
(162, 38, 3, 130, NULL, NULL, NULL, NULL, 95, 12000.00, NULL, NULL, 'guests', 100, NULL),
(164, 38, 10, 159, NULL, NULL, NULL, NULL, 124, 150000.00, NULL, NULL, 'guests', 100, NULL),
(165, 38, 6, 138, NULL, NULL, NULL, NULL, 103, 800000.00, NULL, NULL, 'fixed', 1, NULL),
(167, 38, 2, 166, NULL, 34, NULL, NULL, 131, 250000.00, 350000.00, 0, 'fixed', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used`, `created_at`) VALUES
(1, 120, '42234bfb810017c065469ddbfb9fe0ebaa04e7cf1e1ad7c54a6512ee36a9ab5f', '2026-06-24 10:51:08', 0, '2026-06-24 03:21:08'),
(2, 123, '954654ad8dda5058069ff225abfb2bdec06428a023df57b8135e8c0b2e4fe40d', '2026-06-24 10:55:41', 0, '2026-06-24 03:25:41'),
(3, 126, '985d9367d01bdb52c7db5cae2413582508e144b752a7825ed27f760f2f6e7bbc', '2026-06-25 10:00:55', 1, '2026-06-25 02:30:55'),
(4, 126, 'ee6749efa8ca1f8fadc04473328582e5798d383ccd474b1ca6f5d3f9214d906e', '2026-06-25 10:02:08', 1, '2026-06-25 02:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `platform_fee` decimal(10,2) DEFAULT NULL,
  `supplier_amount` decimal(10,2) DEFAULT NULL,
  `escrow_status` enum('held','released','refunded') DEFAULT NULL,
  `refund_id` bigint(20) DEFAULT NULL,
  `type` enum('deposit','remaining','full','supplier_fee','replacement_delta','payout') DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` enum('pending','processing','success','failed') DEFAULT 'pending',
  `remark` text DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `payment_slip_path` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_note` text DEFAULT NULL,
  `payout_batch_id` varchar(100) DEFAULT NULL,
  `payout_requested_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `supplier_id`, `amount`, `platform_fee`, `supplier_amount`, `escrow_status`, `refund_id`, `type`, `method`, `bank_name`, `account_name`, `mobile_number`, `paid_amount`, `paid_at`, `status`, `remark`, `transaction_ref`, `payment_slip_path`, `verified_by`, `verified_at`, `verified_note`, `payout_batch_id`, `payout_requested_at`, `created_at`) VALUES
(47, 48, NULL, 120000.00, NULL, NULL, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 120000.00, '2026-06-18 11:34:51', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260618113451-2fe925b3.jpg', 1, '2026-06-18 09:35:07', '', NULL, NULL, '2026-06-18 09:34:51'),
(48, 49, NULL, 294671.00, NULL, NULL, 'refunded', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 294671.00, '2026-06-18 13:10:53', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260618131053-55929485.jpg', 1, '2026-06-18 11:11:38', '', NULL, NULL, '2026-06-18 11:10:53'),
(49, 50, NULL, 15372.00, NULL, NULL, 'refunded', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'U Kyaw Kyaw', '09123456789', 15372.00, '2026-06-18 16:37:10', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260618163710-6140d9cb.jpg', 1, '2026-06-19 10:52:23', '', NULL, NULL, '2026-06-18 14:37:10'),
(50, 52, NULL, 814800.00, NULL, NULL, 'refunded', 3, 'deposit', 'AYA Pay', 'AYA Pay', 'U Zaw Zaw', '09123456789', 814800.00, '2026-06-20 03:34:23', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260620033423-1013b3f3.jpg', 1, '2026-06-20 01:36:28', '', NULL, NULL, '2026-06-20 01:34:23'),
(51, 53, NULL, 814800.00, NULL, NULL, 'refunded', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 814800.00, '2026-06-20 04:31:46', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260620043146-a61b0104.jpg', 1, '2026-06-20 02:33:51', '', NULL, NULL, '2026-06-20 02:31:46'),
(52, 53, NULL, 800000.00, NULL, NULL, NULL, NULL, 'replacement_delta', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:31:01'),
(53, 53, NULL, 200000.00, NULL, NULL, 'refunded', NULL, 'replacement_delta', 'AYA Pay', 'AYA Pay', 'U Zaw Moe', '09123456789', 200000.00, '2026-06-20 20:31:17', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260620160117-7d2a74be.jpg', 1, '2026-06-20 14:30:35', NULL, NULL, NULL, '2026-06-20 09:25:14'),
(55, 310, NULL, 830130.00, NULL, NULL, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 830130.00, '2026-06-21 08:25:29', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260621082529-6d65c0ed.jpg', 1, '2026-06-21 06:25:46', '', NULL, NULL, '2026-06-21 06:25:29'),
(56, 311, NULL, 150000.00, NULL, NULL, NULL, NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 150000.00, '2026-06-21 09:23:44', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260621092344-b6af1b2b.jpg', 1, '2026-06-21 07:24:24', '', NULL, NULL, '2026-06-21 07:23:44'),
(57, 312, NULL, 30000.00, NULL, NULL, 'refunded', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 30000.00, '2026-06-21 09:44:57', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260621094457-430edc68.jpg', 1, '2026-06-21 07:45:19', '', NULL, NULL, '2026-06-21 07:44:58'),
(59, 315, NULL, 3750000.00, 750000.00, 3000000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 3750000.00, '2026-06-22 10:23:21', 'pending', NULL, 'transction-id-222222222', 'public/uploads/payment-slips/2026/06/slip-20260622102321-12bdb90d.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-22 08:23:21'),
(60, NULL, 131, 50000.00, 50000.00, 0.00, NULL, NULL, 'supplier_fee', 'KBZ Pay', 'KBZ Pay', 'Hnin Yu Yu Hlaing Tun', '09252208158', 52000.00, '2026-06-23 11:39:00', 'success', 'for member fee', 'TXN-12345678', 'public/uploads/payment-slips/2026/06/slip-20260623114001-11b5fa22.jpg', 1, '2026-06-24 02:10:08', NULL, NULL, NULL, '2026-06-23 05:10:01'),
(61, 325, NULL, 17500.00, 3500.00, 14000.00, 'held', NULL, 'deposit', 'KBZ Pay', 'KBZ Pay', 'Yadanar', '09455658079', 17500.00, '2026-06-24 09:41:43', 'pending', NULL, 'TXN-12345678', 'public/uploads/payment-slips/2026/06/slip-20260624094143-54015a3a.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-24 03:11:43'),
(62, NULL, 132, 50000.00, 50000.00, 0.00, NULL, NULL, 'supplier_fee', 'Wave Money', 'Wave Money', 'Dana Htun', '09750625628', 60000.00, '2026-06-24 11:21:00', 'success', 'Member Fees', 'TXN-123456', NULL, 1, '2026-06-24 05:38:41', NULL, NULL, NULL, '2026-06-24 04:52:36'),
(63, NULL, 134, 50000.00, 50000.00, 0.00, NULL, NULL, 'supplier_fee', 'Wave Money', 'Wave Money', 'Dana Htun', '09750625628', 50000.00, '2026-06-25 11:43:00', 'success', 'Member Fees', 'TXN-123456', 'public/uploads/payment-slips/2026/06/slip-20260625114340-63d1cc21.png', 1, '2026-06-25 05:14:08', NULL, NULL, NULL, '2026-06-25 05:13:40'),
(64, 303, 134, 150000.00, 7500.00, 142500.00, NULL, NULL, 'full', 'AYA Pay', NULL, NULL, NULL, NULL, '2026-06-10 11:00:00', 'success', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-10 04:00:00'),
(65, 304, 134, 150000.00, 7500.00, 142500.00, NULL, NULL, 'deposit', 'KBZ Pay', NULL, NULL, NULL, NULL, '2026-06-15 15:00:00', 'success', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-15 08:00:00'),
(66, 305, 134, 150000.00, 7500.00, 142500.00, NULL, NULL, 'full', 'Wave Money', NULL, NULL, NULL, NULL, '2026-06-20 10:00:00', 'success', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-20 03:00:00'),
(67, 310, 134, 500000.00, 25000.00, 475000.00, NULL, NULL, 'deposit', 'AYA Pay', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-22 05:00:00'),
(68, 303, 134, 142500.00, NULL, NULL, NULL, NULL, 'payout', 'Bank Transfer', 'AYA Bank', 'Shwe Phoo Sar', NULL, NULL, '2026-06-14 10:00:00', 'success', NULL, 'PO-20260612-AUTO', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-12 02:30:00'),
(69, 304, 134, 142500.00, NULL, NULL, NULL, NULL, 'payout', 'Bank Transfer', 'KBZ Bank', 'Shwe Phoo Sar', NULL, NULL, NULL, 'success', NULL, 'PO-20260617-AUTO', NULL, 1, '2026-06-25 14:51:34', 'Transfer via 1234', NULL, NULL, '2026-06-17 02:30:00'),
(70, 305, 134, 142500.00, NULL, NULL, NULL, NULL, 'payout', 'Bank Transfer', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-22 02:30:00'),
(71, 334, NULL, 225000.00, 45000.00, 180000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'ko kyaw zin', '09123456789', 225000.00, '2026-06-25 23:26:57', 'pending', NULL, 'TXN-12345678', 'public/uploads/payment-slips/2026/06/slip-20260625232657-cad931b9.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-25 16:56:57'),
(72, NULL, 135, 50000.00, 50000.00, 0.00, NULL, NULL, 'supplier_fee', 'Wave Money', 'Wave Money', 'Kaung Min Khant', '09781344861', 50000.00, '2026-06-26 09:28:00', 'success', 'payment', '123456', NULL, 1, '2026-06-26 03:24:53', NULL, NULL, NULL, '2026-06-26 02:59:22'),
(73, 310, NULL, 3320520.00, NULL, NULL, 'held', NULL, 'remaining', 'KBZ Pay', 'KBZ Pay', 'U Zaw Moe', '09123456789', 3320520.00, '2026-06-27 11:18:09', 'pending', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627111809-5c1edb9e.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-27 04:48:09'),
(74, 316, NULL, 1079169.00, 249039.00, 830130.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Zaww Zaw', '09123456789', 1079169.00, '2026-06-27 13:32:18', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627133218-76202efe.jpg', 1, '2026-06-27 07:02:32', '', NULL, NULL, '2026-06-27 07:02:18'),
(75, 338, NULL, 780000.00, 180000.00, 600000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 780000.00, '2026-06-27 14:38:32', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627143832-a4fe32df.jpg', 1, '2026-06-27 08:08:57', '', NULL, NULL, '2026-06-27 08:08:32'),
(76, 338, NULL, 2220000.00, NULL, NULL, 'held', NULL, 'remaining', 'KBZ Pay', 'KBZ Pay', 'Ko Kyaw Zin', '09123456789', 2220000.00, '2026-06-27 15:34:13', 'pending', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627153413-f42728c5.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-27 09:04:13'),
(77, 339, NULL, 546000.00, 126000.00, 420000.00, 'refunded', 1, 'deposit', 'AYA Pay', 'AYA Pay', 'Aye Aye', '09123456789', 546000.00, '2026-06-27 16:27:44', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627162744-23edcdae.jpg', 1, '2026-06-27 09:57:54', '', NULL, NULL, '2026-06-27 09:57:44'),
(78, 340, NULL, 546000.00, 126000.00, 420000.00, 'refunded', 2, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 546000.00, '2026-06-27 16:47:50', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627164750-c47eb7ff.jpg', 1, '2026-06-27 10:25:53', '', NULL, NULL, '2026-06-27 10:17:50'),
(79, 340, NULL, 1680000.00, NULL, NULL, 'held', NULL, 'remaining', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 1680000.00, '2026-06-27 16:56:27', 'pending', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627165627-b4b470fe.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-27 10:26:27'),
(80, 341, NULL, 546000.00, 126000.00, 420000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'NN', '09123456789', 546000.00, '2026-06-27 17:10:57', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627171057-c9f4383a.jpg', 1, '2026-06-27 10:41:04', '', NULL, NULL, '2026-06-27 10:40:57'),
(81, 341, NULL, 1680000.00, NULL, NULL, 'held', NULL, 'remaining', 'AYA Pay', 'AYA Pay', 'NN', '09123456789', 1680000.00, '2026-06-27 17:11:54', 'pending', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627171154-41445cc9.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-27 10:41:54'),
(82, 342, NULL, 546000.00, 126000.00, 420000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 546000.00, '2026-06-27 17:24:01', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627172401-d6845d03.jpg', 1, '2026-06-27 10:54:09', '', NULL, NULL, '2026-06-27 10:54:01'),
(83, 342, NULL, 1680000.00, NULL, NULL, 'held', NULL, 'remaining', 'AYA Pay', 'AYA Pay', 'U Zaw Moe', '09123456789', 1680000.00, '2026-06-27 17:33:15', 'pending', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627173315-794782da.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-27 11:03:15'),
(84, 343, NULL, 546000.00, 126000.00, 420000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'HH', '09123456789', 546000.00, '2026-06-27 17:35:06', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627173506-72bec7e8.jpg', 1, '2026-06-27 11:05:15', '', NULL, NULL, '2026-06-27 11:05:06'),
(85, 343, NULL, 1680000.00, NULL, NULL, 'held', NULL, 'remaining', 'Wave Money', 'Wave Money', 'HH', '09123456789', 1680000.00, '2026-06-27 17:35:49', 'pending', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627173549-8f73ffa8.jpg', NULL, NULL, NULL, NULL, NULL, '2026-06-27 11:05:49'),
(86, 344, NULL, 546000.00, 126000.00, 420000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'NN', '09123456789', 546000.00, '2026-06-27 17:41:04', 'success', NULL, 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260627174104-eba0db9d.jpg', 1, '2026-06-27 11:11:17', '', NULL, NULL, '2026-06-27 11:11:04'),
(87, 345, NULL, 546000.00, 126000.00, 420000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'ko kyaw zin', '09123456789', 546000.00, '2026-06-27 18:10:40', 'success', NULL, 'TXN-12345678', 'public/uploads/payment-slips/2026/06/slip-20260627181040-b27de2fc.jpg', 1, '2026-06-27 11:40:45', '', NULL, NULL, '2026-06-27 11:40:40'),
(88, 345, NULL, 1680000.00, NULL, NULL, 'held', NULL, 'remaining', 'AYA Pay', 'AYA Pay', 'ko kyaw zin', '09123456789', 1680000.00, '2026-06-27 18:11:09', 'success', NULL, 'TXN-12345678', 'public/uploads/payment-slips/2026/06/slip-20260627181109-f5835473.jpg', 1, '2026-06-27 11:56:37', '', NULL, NULL, '2026-06-27 11:41:09'),
(89, 345, 20, 1974000.00, NULL, NULL, 'released', NULL, 'payout', NULL, NULL, NULL, NULL, NULL, NULL, 'success', NULL, NULL, NULL, 1, '2026-06-27 14:17:29', 'Paid via manual bank transfer', 'MANUAL-20-20260627183759', '2026-06-27 12:07:59', '2026-06-27 11:58:14'),
(90, 346, NULL, 780000.00, 180000.00, 600000.00, 'held', NULL, 'deposit', 'AYA Pay', 'AYA Pay', 'Mg Saen', '09123456789', 780000.00, '2026-06-27 18:32:53', 'success', NULL, 'TXN-12345678', 'public/uploads/payment-slips/2026/06/slip-20260627183253-c6ce3faa.jpg', 1, '2026-06-27 12:03:08', '', NULL, NULL, '2026-06-27 12:02:53');

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

CREATE TABLE `platform_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('platform_fee_percent', '6.00', '2026-06-27 04:11:43'),
('supplier_membership_fee', '50000.00', '2026-06-27 04:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `payment_id` bigint(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text NOT NULL,
  `policy_reason` varchar(255) DEFAULT NULL,
  `method` varchar(50) DEFAULT 'manual_bank_transfer',
  `status` enum('pending','processing','completed','rejected') DEFAULT 'pending',
  `refund_slip_path` varchar(500) DEFAULT NULL,
  `refund_transaction_ref` varchar(255) DEFAULT NULL,
  `refund_bank_name` varchar(100) DEFAULT NULL,
  `requested_by` bigint(20) DEFAULT NULL,
  `processed_by` bigint(20) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds` (`id`, `booking_id`, `payment_id`, `amount`, `reason`, `policy_reason`, `method`, `status`, `refund_slip_path`, `refund_transaction_ref`, `refund_bank_name`, `requested_by`, `processed_by`, `requested_at`, `processed_at`, `completed_at`, `note`) VALUES
(1, 339, NULL, 273000.00, 'cancle ချင်လို့ကွာ', '50% refund - cancelled 2-7 days before event', 'manual_bank_transfer', 'completed', 'public/uploads/payment-slips/2026/06/slip-20260627170741-8cd4c042.jpg', 'TXN-12345678', 'AYA Pay', 1, 1, '2026-06-27 09:59:09', '2026-06-27 10:37:41', '2026-06-27 10:38:01', ''),
(2, 340, NULL, 0.00, 'the customer sent the canclelation request', 'No refund - cancelled less than 2 days before event', 'manual_bank_transfer', 'completed', 'public/uploads/payment-slips/2026/06/slip-20260627170638-0865cabb.jpg', 'TXN-12345678', 'AYA Pay', 1, 1, '2026-06-27 10:36:22', '2026-06-27 10:36:38', '2026-06-27 10:37:19', 'abc'),
(3, 52, NULL, 0.00, 'refund', 'No refund - cancelled less than 2 days before event', 'manual_bank_transfer', 'completed', 'public/uploads/payment-slips/2026/06/slip-20260627171859-ee4f1368.jpg', 'TXN-12345678', 'AYA Pay', 1, 1, '2026-06-27 10:48:41', '2026-06-27 10:48:59', '2026-06-27 10:49:09', ''),
(4, 344, NULL, 273000.00, 'supplier request to cancle', '50% refund - cancelled 2-7 days before event', 'manual_bank_transfer', 'processing', 'public/uploads/payment-slips/2026/06/slip-20260627180650-622c33cc.jpg', 'TXN-12345678', 'AYA Pay', 1, 1, '2026-06-27 11:36:37', '2026-06-27 11:36:50', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `booking_item_id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `rating` tinyint(1) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `booking_item_id`, `service_id`, `customer_id`, `supplier_id`, `rating`, `comment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(182, 126, 179, 59, 1, 23, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(183, 127, 180, 59, 24, 23, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(184, 128, 181, 60, 24, 24, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(185, 129, 182, 60, 27, 24, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(186, 130, 183, 60, 29, 24, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(187, 131, 184, 61, 27, 25, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(188, 132, 185, 61, 29, 25, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(189, 133, 186, 62, 29, 26, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(190, 134, 187, 62, 30, 26, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(191, 135, 188, 62, 1, 26, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(192, 136, 189, 63, 30, 27, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(193, 137, 190, 63, 1, 27, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(194, 138, 191, 64, 1, 28, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(195, 139, 192, 64, 24, 28, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(196, 140, 193, 64, 27, 28, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(197, 141, 194, 65, 24, 29, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(198, 142, 195, 65, 27, 29, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(199, 143, 196, 66, 27, 30, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(200, 144, 197, 66, 29, 30, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(201, 145, 198, 66, 30, 30, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(202, 146, 199, 67, 29, 31, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(203, 147, 200, 67, 30, 31, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(204, 148, 201, 68, 30, 32, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(205, 149, 202, 68, 1, 32, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(206, 150, 203, 68, 24, 32, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(207, 151, 204, 69, 1, 33, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(208, 152, 205, 69, 24, 33, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(209, 153, 206, 70, 24, 34, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(210, 154, 207, 70, 27, 34, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(211, 155, 208, 70, 29, 34, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(212, 156, 209, 71, 27, 35, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(213, 157, 210, 71, 29, 35, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(214, 158, 211, 103, 29, 68, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(215, 159, 212, 103, 30, 68, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(216, 160, 213, 103, 1, 68, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(217, 161, 214, 104, 30, 69, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(218, 162, 215, 104, 1, 69, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(219, 163, 216, 105, 1, 70, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(220, 164, 217, 105, 24, 70, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(221, 165, 218, 105, 27, 70, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(222, 166, 219, 106, 24, 71, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(223, 167, 220, 106, 27, 71, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(224, 168, 221, 107, 27, 72, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(225, 169, 222, 107, 29, 72, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(226, 170, 223, 107, 30, 72, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(227, 171, 224, 108, 29, 73, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(228, 172, 225, 108, 30, 73, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(229, 173, 226, 109, 30, 74, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(230, 174, 227, 109, 1, 74, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(231, 175, 228, 109, 24, 74, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(232, 176, 229, 110, 1, 75, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(233, 177, 230, 110, 24, 75, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(234, 178, 231, 111, 24, 76, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(235, 179, 232, 111, 27, 76, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(236, 180, 233, 111, 29, 76, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(237, 181, 234, 112, 27, 77, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(238, 182, 235, 112, 29, 77, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(239, 183, 236, 113, 29, 78, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(240, 184, 237, 113, 30, 78, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(241, 185, 238, 113, 1, 78, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(242, 186, 239, 118, 30, 83, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(243, 187, 240, 118, 1, 83, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(244, 188, 241, 119, 1, 84, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(245, 189, 242, 119, 24, 84, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(246, 190, 243, 119, 27, 84, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(247, 191, 244, 120, 24, 85, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(248, 192, 245, 120, 27, 85, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(249, 193, 246, 121, 27, 86, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(250, 194, 247, 121, 29, 86, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(251, 195, 248, 121, 30, 86, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(252, 196, 249, 122, 29, 87, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(253, 197, 250, 122, 30, 87, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(254, 198, 251, 123, 30, 88, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(255, 199, 252, 123, 1, 88, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(256, 200, 253, 123, 24, 88, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(257, 201, 254, 124, 1, 89, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(258, 202, 255, 124, 24, 89, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(259, 203, 256, 125, 24, 90, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(260, 204, 257, 125, 27, 90, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(261, 205, 258, 125, 29, 90, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(262, 206, 259, 126, 27, 91, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(263, 207, 260, 126, 29, 91, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(264, 208, 261, 127, 29, 92, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(265, 209, 262, 127, 30, 92, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(266, 210, 263, 127, 1, 92, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(267, 211, 264, 128, 30, 93, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(268, 212, 265, 128, 1, 93, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(269, 213, 266, 129, 1, 94, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(270, 214, 267, 129, 24, 94, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(271, 215, 268, 129, 27, 94, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(272, 216, 269, 130, 24, 95, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(273, 217, 270, 130, 27, 95, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(274, 218, 271, 131, 27, 96, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(275, 219, 272, 131, 29, 96, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(276, 220, 273, 131, 30, 96, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(277, 221, 274, 132, 29, 97, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(278, 222, 275, 132, 30, 97, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(279, 223, 276, 133, 30, 98, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(280, 224, 277, 133, 1, 98, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(281, 225, 278, 133, 24, 98, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(282, 226, 279, 134, 1, 99, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(283, 227, 280, 134, 24, 99, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(284, 228, 281, 135, 24, 100, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(285, 229, 282, 135, 27, 100, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(286, 230, 283, 135, 29, 100, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(287, 231, 284, 136, 27, 101, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(288, 232, 285, 136, 29, 101, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(289, 233, 286, 137, 29, 102, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(290, 234, 287, 137, 30, 102, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(291, 235, 288, 137, 1, 102, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(292, 236, 289, 138, 30, 103, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(293, 237, 290, 138, 1, 103, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(294, 238, 291, 139, 1, 104, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(295, 239, 292, 139, 24, 104, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(296, 240, 293, 139, 27, 104, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(297, 241, 294, 140, 24, 105, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(298, 242, 295, 140, 27, 105, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(299, 243, 296, 141, 27, 106, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(300, 244, 297, 141, 29, 106, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(301, 245, 298, 141, 30, 106, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(302, 246, 299, 142, 29, 107, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(303, 247, 300, 142, 30, 107, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(304, 248, 301, 143, 30, 108, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(305, 249, 302, 143, 1, 108, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(306, 250, 303, 143, 24, 108, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(307, 251, 304, 144, 1, 109, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(308, 252, 305, 144, 24, 109, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(309, 253, 306, 145, 24, 110, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(310, 254, 307, 145, 27, 110, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(311, 255, 308, 145, 29, 110, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(312, 256, 309, 146, 27, 111, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(313, 257, 310, 146, 29, 111, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(314, 258, 311, 147, 29, 112, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(315, 259, 312, 147, 30, 112, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(316, 260, 313, 147, 1, 112, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(317, 261, 314, 148, 30, 113, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(318, 262, 315, 148, 1, 113, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(319, 263, 316, 149, 1, 114, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(320, 264, 317, 149, 24, 114, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(321, 265, 318, 149, 27, 114, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(322, 266, 319, 150, 24, 115, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(323, 267, 320, 150, 27, 115, 4, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(324, 268, 321, 151, 27, 116, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(325, 269, 322, 151, 29, 116, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(326, 270, 323, 151, 30, 116, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(327, 271, 324, 152, 29, 117, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(328, 272, 325, 152, 30, 117, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(329, 273, 326, 153, 30, 118, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(330, 274, 327, 153, 1, 118, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(331, 275, 328, 153, 24, 118, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(332, 276, 329, 154, 1, 119, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(333, 277, 330, 154, 24, 119, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(334, 278, 331, 155, 24, 120, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(335, 279, 332, 155, 27, 120, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(336, 280, 333, 155, 29, 120, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(337, 281, 334, 156, 27, 121, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(338, 282, 335, 156, 29, 121, 5, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(339, 283, 336, 157, 29, 122, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(340, 284, 337, 157, 30, 122, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(341, 285, 338, 157, 1, 122, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(342, 286, 339, 158, 30, 123, 4, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(343, 287, 340, 158, 1, 123, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(344, 288, 341, 159, 1, 124, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(345, 289, 342, 159, 24, 124, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(346, 290, 343, 159, 27, 124, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(347, 291, 344, 160, 24, 125, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(348, 292, 345, 160, 27, 125, 4, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(349, 293, 346, 161, 27, 126, 4, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(350, 294, 347, 161, 29, 126, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(351, 295, 348, 161, 30, 126, 4, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(352, 296, 349, 162, 29, 127, 5, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(353, 297, 350, 162, 30, 127, 4, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(354, 298, 351, 163, 30, 128, 4, 'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။', '2026-06-20 14:19:11', NULL, NULL),
(355, 299, 352, 163, 1, 128, 5, 'Lovely experience, would book again for sure.', '2026-06-20 14:19:11', NULL, NULL),
(356, 300, 353, 163, 24, 128, 5, 'Good service and right on time. Recommended for weddings.', '2026-06-20 14:19:11', NULL, NULL),
(357, 301, 354, 164, 1, 129, 5, 'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။', '2026-06-20 14:19:11', NULL, NULL),
(358, 302, 355, 164, 24, 129, 5, 'Beautiful work and very friendly service. Highly recommend!', '2026-06-20 14:19:11', NULL, NULL),
(359, 303, 356, 165, 24, 130, 5, 'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။', '2026-06-20 14:19:11', NULL, NULL),
(360, 304, 357, 165, 27, 130, 4, 'Everything was perfect on our wedding day. Thank you so much!', '2026-06-20 14:19:11', NULL, NULL),
(361, 305, 358, 165, 29, 130, 5, 'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐', '2026-06-20 14:19:11', NULL, NULL),
(362, 345, 404, 50, 29, 20, 5, 'yess this service is good', '2026-06-27 11:58:41', '2026-06-27 11:58:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'customer', 'Customer role', '2026-05-13 07:58:09', '2026-05-13 07:58:09'),
(2, 'supplier', 'Supplier role', '2026-05-13 07:58:09', '2026-05-13 07:58:09'),
(4, 'admin', 'Administrator role', '2026-05-13 07:58:09', '2026-05-13 07:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `category_id` bigint(20) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_min` decimal(10,2) DEFAULT NULL,
  `price_max` decimal(10,2) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `booking_type` enum('fullday','slot','flexible') NOT NULL DEFAULT 'fullday',
  `duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `pricing_unit` enum('per_session','per_hour') DEFAULT 'per_session',
  `buffer_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `max_concurrent` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `max_concurrent_package` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `max_concurrent_customize` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `min_lead_days` int(11) DEFAULT 0 COMMENT 'Minimum days in advance customer must book (0 = same day allowed)',
  `default_start_time` time DEFAULT NULL,
  `default_end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `supplier_id`, `category_id`, `name`, `description`, `price`, `price_min`, `price_max`, `thumbnail_url`, `is_active`, `booking_type`, `duration_minutes`, `pricing_unit`, `buffer_minutes`, `max_concurrent`, `max_concurrent_package`, `max_concurrent_customize`, `created_at`, `min_lead_days`, `default_start_time`, `default_end_time`) VALUES
(42, 21, 6, 'Governor\'s Residence', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', 70000.00, 70000.00, 600000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260618102917-41aebacc.jpg', 1, 'slot', 720, 'per_session', 0, 300, 0, 300, '2026-06-18 08:29:17', 90, NULL, NULL),
(43, 21, 12, 'Aphrodite Wedding Planning & Decoration', 'မိမိတိုရဲ့ အလှပဆုံး မင်္ဂလာအချိန်လေးကို လစ်ဟာမှုတွေ၊ လိုအပ်ချက်တွေမရှိဘဲ အချိုမြိန်ဆုံးအခိုက်အတန့်တွေကိုသာ အမှတ်တရဖြစ် နေစေဖို ကျွမ်းကျင်တဲ့ Wedding Professional တွေနဲ့အတူ မိမိတိုရဲ့ မင်္ဂလာနေ့ရက်လေးကို အပြည့်အစုံဆုံး ပုံဖော်လိုက်ပါ။\nမိမိတိုရဲ့ တစ်သက်မှတစ်ခါ ရင်အခုန်ရဆုံးနဲ့ အလှပဆုံး နေ့ရက် လေးအတွက် အကောင်းဆုံး Service အကောင်းဆုံး Quality တွေအပြင် ကျွမ်းကျင် Professional Wedding Planner တွေနဲ့ မိမိတိုရဲ့ ပွဲ ကို စိတ်အေးရချင်တယ်ဆိုရင်တော့ Aphrodite Wedding Planning and Decoration ကို အခုပဲရွေးချယ်လိုက်ပါ။', 3400000.00, 3400000.00, 3400000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260618104902-604f0759.jpg', 1, 'fullday', 60, 'per_session', 0, 1, 0, 1, '2026-06-18 08:49:02', 3, NULL, NULL),
(48, 20, 12, 'H&H Floral and Wedding Service', 'H&H floral မှာဈေးနှုန်း ချိုချိုသာသာလေးတွေနဲ့\nအလှဆုံးတွေပြင်ဆင်ပေးမှာပါနော်\nလိုချင်တဲ့ရက်လေးရဖို booking လေးတွေ\nကြိုယူထားဖိုလိုပါမယ်ရှင်', 1800000.00, 1800000.00, 2100000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181047-6c1da8a5.jpg', 1, 'slot', 240, 'per_session', 0, 1, 0, 0, '2026-06-18 19:02:22', 3, NULL, NULL),
(49, 20, 6, 'Zephyr Sein Lann So pyay', 'Zephyr (Sein Lann So Pyay Garden)ကရန်ကုန်မြို့အတွင်းတည်ရှိတဲ့အေးချမ်းပြီးသဘာဝပတ်ဝန်းကျင်နဲ့ကိုက်ညီတဲ့ fine dining & event venue တစ်ခုဖြစ်ပါတယ်။Sein Lann So Pyay Gardenအနားမှာရှိလို့ မိသားစုစားသောက်မှု၊ မင်္ဂလာပွဲ၊ အခမ်းအနားများအတွက်လူကြိုက်များပါတယ်။သဘာဝအလှနဲ့ လှပနဲ့background ကြောင့်pre-weeding/ event-photo ရိုက်ရအဆင်ပြေစေပါတယ်။outdoor garden weeding နဲ့ အေးချမ်းတဲ့weeding လုပ်ချင်သူများ Decoration+ food+ Serviceကိုတစ်နေရာထဲမှာpackageလိုချင်သူများအတွက်အဆင်ပြေပြီး ရွေးချယ်ဖို့သင့်တော်တဲ့နေရာတစ်ခုဖြစ်ပါတယ်။', 900000.00, 900000.00, 3000000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618212654-323d369a.jpg', 1, 'slot', 480, 'per_session', 0, 700, 0, 0, '2026-06-18 19:26:54', 8, NULL, NULL),
(50, 20, 5, 'H & H Wedding Studio', 'Capturing your the most meaningful moments with elegance & style             H&H Photo Studio ကို ယုံကြည်ပြီးအရေးကြီးတဲ့ အမှတ်တရနေ့ရက်တွေကို အပ်နှံပေးတဲ့ client တိုင်းကို အထူးကျေးဇူးတင်ရှိပါတယ် 💛ရိုက်ကူးမှုတိုင်းမှာcomfortable experience, clear communication, pose guidance နဲ့quality result ကို အရေးထားပြီး detail ကျကျ ဂရုစိုက်ဆောင်ရွက်ပေးနေပါတယ် ✨', 200000.00, 200000.00, 2100000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260626181009-90bb4ca5.jpg', 1, 'slot', 480, 'per_session', 0, 3, 0, 0, '2026-06-19 02:08:41', 1, NULL, NULL),
(55, 21, 2, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'ဝတ်စုံနှင့် ဝန်ဆောင်မှုများစုံလင်သော ဝတ်စုံဒီဇိုင်းများ: Wedding Gowns, Mermaid Dresses, Evening Dresses နဲ့ Pre-Wedding အတွက် ဝတ်စုံလှလှလေးများကို စိတ်ကြိုက်ငှားရမ်းနိုင်ပါတယ်။\n\nနောက်ဆုံးပေါ် ဒီဇိုင်းသစ်များ: နိုင်ငံခြား Wedding Dress Industry ရှိ စက်ရုံကြီးများမှ နောက်ဆုံးပေါ် Dress များကို မိမိကိုယ်တိုင်း၊ မိမိစိတ်ကြိုက် ရွေးချယ်ပြီး အငှား/အဝယ် မှာယူနိုင်ပါတယ်။\n\nအမှတ်တရ သိမ်းဆည်းလိုသူများအတွက်: အသစ်စက်စက် Dress များကို Studio မှာ ကိုယ်တိုင်ဝတ်ကြည့်ပြီး ဝယ်ယူနိုင်သလို၊ Bridal Veil (သတို့သမီးခေါင်းခြုံပုဝါ) များကိုလည်း မိမိစိတ်ကြိုက် Customized မှာယူနိုင်ပါတယ်ရှင်။\n\n🌸 မြန်မာ့ရိုးရာ ဝတ်စုံဝန်ဆောင်မှုခေတ်မီဝတ်စုံများသာမက ရိုးရာထိုင်မသိမ်း၊ တောင်ရှည်ဝတ်စုံများကိုလည်း အငှား/အရောင်းအပြင် အသစ်ချုပ်အငှား ဝန်ဆောင်မှုပါ ရရှိနိုင်ပါတယ်။ (အသားအရောင်နှင့် ကိုယ်လုံးအချိုးအစားပေါ်မူတည်၍ ဒီဇိုင်းသီးသန့် ဆွဲပေးပါတယ်ရှင်)\n\n💐 ပြီးပြည့်စုံသော Wedding Packagesဝတ်စုံများအပြင် Floral Decoration၊ လက်ကိုင်ပန်း၊ Hotel & Makeup Booking နှင့် မင်္ဂလာကားအလှဆင်ခြင်းအထိ အစုံအလင် ဝန်ဆောင်မှုပေးနေတာကြောင့် Dear Brides ကို ယုံကြည်စွာ လှမ်းလာခဲ့ဖို့ ဖိတ်ခေါ်လိုက်ပါတယ်ရှင်။', 40000.00, 40000.00, 500000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260619054309-45b53c74.jpg', 1, 'slot', 60, 'per_session', 0, 1, 0, 0, '2026-06-19 03:42:00', 3, NULL, NULL),
(56, 21, 10, 'Lin Lin', 'မိတ်ကပ်ပညာကို စနစ်တကျ သင်ယူချင်သူများအတွက် Lin Lin Makeup Academy ရှိသလို၊ ထူးခြားဆန်းသစ်တဲ့ Look တွေကို ပိုင်ဆိုင်ချင်တဲ့ ပွဲတက်သတို့သမီးများအတွက်လည်း Lin Lin က အနီးကပ် ရှိနေမှာပါ။ Color Theory နှင့် Face Anatomy အခြေခံကာ လူတစ်ဦးချင်းစီနဲ့ အလိုက်ဖက်ဆုံး အလှတရားတွေကို ဖန်တီးပေးနေသည့် သူမ၏ လက်ရာများကို Lin Lin Facebook Page တွင် လေ့လာနိုင်ပါသည်။', 73000.00, 73000.00, 75000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260620065739-732ff480.jpg', 1, 'slot', 180, 'per_session', 0, 2, 0, 0, '2026-06-20 04:57:39', 3, '04:00:00', '17:00:00'),
(57, 21, 2, 'Dear Brides Wedding Dress Studio', 'ဝတ်စုံနှင့် ဝန်ဆောင်မှုများစုံလင်သော ဝတ်စုံဒီဇိုင်းများ: Wedding Gowns, Mermaid Dresses, Evening Dresses နဲ့ Pre-Wedding အတွက် ဝတ်စုံလှလှလေးများကို စိတ်ကြိုက်ငှားရမ်းနိုင်ပါတယ်။\n\nနောက်ဆုံးပေါ် ဒီဇိုင်းသစ်များ: နိုင်ငံခြား Wedding Dress Industry ရှိ စက်ရုံကြီးများမှ နောက်ဆုံးပေါ် Dress များကို မိမိကိုယ်တိုင်း၊ မိမိစိတ်ကြိုက် ရွေးချယ်ပြီး အငှား/အဝယ် မှာယူနိုင်ပါတယ်။\n\nအမှတ်တရ သိမ်းဆည်းလိုသူများအတွက်: အသစ်စက်စက် Dress များကို Studio မှာ ကိုယ်တိုင်ဝတ်ကြည့်ပြီး ဝယ်ယူနိုင်သလို၊ Bridal Veil (သတို့သမီးခေါင်းခြုံပုဝါ) များကိုလည်း မိမိစိတ်ကြိုက် Customized မှာယူနိုင်ပါတယ်ရှင်။\n\n🌸 မြန်မာ့ရိုးရာ ဝတ်စုံဝန်ဆောင်မှုခေတ်မီဝတ်စုံများသာမက ရိုးရာထိုင်မသိမ်း၊ တောင်ရှည်ဝတ်စုံများကိုလည်း အငှား/အရောင်းအပြင် အသစ်ချုပ်အငှား ဝန်ဆောင်မှုပါ ရရှိနိုင်ပါတယ်။ (အသားအရောင်နှင့် ကိုယ်လုံးအချိုးအစားပေါ်မူတည်၍ ဒီဇိုင်းသီးသန့် ဆွဲပေးပါတယ်ရှင်)\n\n💐 ပြီးပြည့်စုံသော Wedding Packagesဝတ်စုံများအပြင် Floral Decoration၊ လက်ကိုင်ပန်း၊ Hotel & Makeup Booking နှင့် မင်္ဂလာကားအလှဆင်ခြင်းအထိ အစုံအလင် ဝန်ဆောင်မှုပေးနေတာကြောင့် Dear Brides ကို ယုံကြည်စွာ လှမ်းလာခဲ့ဖို့ ဖိတ်ခေါ်လိုက်ပါတယ်ရှင်။', 750000.00, 750000.00, 1000000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260620090310-439efa63.jpg', 1, 'slot', 60, 'per_session', 0, 1, 0, 0, '2026-06-20 07:02:52', 7, NULL, NULL),
(59, 23, 12, 'Excel Jade Hall — Grand Wedding Decoration', 'Stage decoration, floral arrangement, table & chair setup and theme colour design. Premium package.', 2900000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 5, 3, 0, '2026-06-20 07:13:56', 0, NULL, NULL),
(60, 24, 6, 'Golden Inya - Lakeside Wedding Venue', 'Indoor/outdoor lakeside venue (outdoor up to 700-800 guests). Grass-lawn usage, buffet lunch/dinner 22,000-35,000 per head.', 2000000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:22:45', 0, NULL, NULL),
(61, 25, 6, 'Western Park Ruby - Garden Wedding Venue', 'Upper lawn usage 500,000 / lower lawn 200,000. Set menus 400,000-500,000 per table (10 pax), 10-pax table from 190,000.', 500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:22:45', 0, NULL, NULL),
(62, 26, 6, 'Zephyr - Garden Wedding Venue', 'Lawn usage 900,000 (200+ guests) / 1,000,000 (under 200). Set menus 325,000-365,000 per table. Decoration, MC, photographer add-ons on request.', 900000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:22:45', 0, NULL, NULL),
(63, 27, 6, 'The White Cottage - Garden & Lounge Venue', 'Indoor lounge and garden venue (outdoor 100-150). Asian/Western buffet and set menus, decoration and planning arranged by the couple.', 800000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:22:45', 0, NULL, NULL),
(64, 28, 2, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ - Wedding Attire', 'Traditional Myanmar bridal wear — htaing-ma-theim, offering/registration outfits, taik-pon and taung-shay for the couple and parents, in various silk weaves. Rental and sale, plus custom-made rental (book 3-6 months ahead). Htaing-ma-theim rental approx 350,000 to 2,000,000, offering outfits from approx 200,000. Add-ons: floral decoration, hand bouquets, hotel/makeup booking and wedding car decoration.', 750000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 0, 'slot', 60, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(65, 29, 2, 'Dear Brides Wedding Dress Studio - Wedding Attire', 'Western and traditional bridal wear — wedding gowns, mermaid dresses, evening dresses and pre-wedding outfits. Latest imported designs for rent or sale, customised bridal veils, and custom-made rental. Spacious studio with parking, in-house photo studio and experienced stylists. Range approx 800,000 to 3,000,000 depending on dress.', 800000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(66, 30, 2, 'The Vow Wedding Studio Myanmar - Wedding Attire', 'Women\'s bridal studio with finely tailored gowns, quality fabrics and detailed finishing for each bride. Rental and sale; crowns and bridal shoes also available. Event-day rental approx 1,500,000 to 6,000,000+.', 1500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(67, 31, 2, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN - Wedding Attire', 'Wedding suits and dresses for men and women. Reliable remote/line ordering with good fit. Price approx 200,000 to 500,000+. Booking required.', 200000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(68, 32, 2, 'T&T Bridal Collection - Wedding Attire', 'Western wedding dresses with hundreds of new pieces. Rental approx 400,000 to 1,500,000; wholesale purchase from 230,000. 10+ years wedding-industry founder advises on current trends, body-fit styling, matching makeup look and accessories. New stock monthly plus resale of older pieces.', 400000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(69, 33, 2, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး - Wedding Attire', 'Men\'s ceremony wear — \"Gon\" taik-pon (M/L/XL/XXL) at 300,000 and pasoe (longyi) from 43,000 to 420,000. Detailed sizing help and sharp cutting for a smart, dignified look.', 300000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(70, 34, 2, 'Peter\'s Bridal Garden - Studio - Wedding Attire', 'Pre-wedding outfit and photography studio. Indoor and outdoor pre-wedding packages (e.g. 3-outfit indoor package), traditional looks, makeup and full-team support with raw photos provided. Highly recommended for pre-wedding shoots.', 500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(71, 35, 2, 'My Everything Wedding Dresses - Wedding Attire', 'Bridal dress rental for brides. Rental price range approx 480,000 to 1,860,000. Rental only.', 480000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:44:16', 0, NULL, NULL),
(103, 68, 5, 'Forever One Stop Wedding Studio - Studio', 'Forever One Stop Wedding Service & Planning was established in 2006 and has earned a name in bridal industries for wedding photography and wedding gowns collection. Our motto is to capture your most precious memories while still maintain customer satisfaction, innovation leadership and continuing operation, leaving customers happy dreams and memories.                   Forever One Stop Wedding Service & Planning ကို 2006 ခုနှစ်တွင် စတင်တည်ထောင်ခဲ့ပြီး မင်္ဂလာဆောင်ဓာတ်ပုံရိုက်ကူးခြင်းနှင့် မင်္ဂလာဝတ်စုံ (Wedding Gown) များအတွက် သတို့သမီးလုပ်ငန်းနယ်ပယ်တွင် နာမည်ကောင်းရရှိထားသော လုပ်ငန်းတစ်ခုဖြစ်ပါသည်။\n\nကျွန်ုပ်တို့၏ ရည်မှန်းချက်မှာ သင့်ဘဝ၏ အဖိုးတန်ဆုံး အမှတ်တရများကို အကောင်းဆုံးမှတ်တမ်းတင်ပေးနိုင်ရန်ဖြစ်ပြီး၊ ထိုသို့ဆောင်ရွက်ရာတွင် ဖောက်သည်စိတ်ကျေနပ်မှု၊ ဆန်းသစ်တီထွင်မှုဆိုင်ရာ ဦးဆောင်နိုင်မှုနှင့် ရေရှည်တည်တံ့သော လုပ်ငန်းလည်ပတ်မှုတို့ကို အမြဲတမ်း ထိန်းသိမ်းထားပါသည်။\n\nဖောက်သည်များအတွက် ပျော်ရွှင်ဖွယ် အိပ်မက်များနှင့် မမေ့နိုင်သော အမှတ်တရများကို ချန်ထားပေးနိုင်ရန် ကျွန်ုပ်တို့ အစဉ်ကြိုးပမ်းလျက်ရှိပါသည်။\n\nPricing: 445000', 445000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(104, 69, 5, 'H & H Photo Studio - Studio', 'Capturing your the most meaningful moments with elegance & style             H&H Photo Studio ကို ယုံကြည်ပြီးအရေးကြီးတဲ့ အမှတ်တရနေ့ရက်တွေကို အပ်နှံပေးတဲ့ client တိုင်းကို အထူးကျေးဇူးတင်ရှိပါတယ် 💛ရိုက်ကူးမှုတိုင်းမှာcomfortable experience, clear communication, pose guidance နဲ့quality result ကို အရေးထားပြီး detail ကျကျ ဂရုစိုက်ဆောင်ရွက်ပေးနေပါတယ် ✨\n\nPricing: Photo only package-430000ks     Photo+Video packageA-780000ks', 430000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(105, 70, 5, 'Venus Wedding Studio - Studio', '𝑽𝒆𝒏𝒖𝒔 𝑾𝒆𝒅𝒅𝒊𝒏𝒈 𝑺𝒕𝒖𝒅𝒊𝒐 မှာ Prewedding package အတွက်သာမကပဲ Couple Photo / Family Photo/Friendship Photo/ Solo Beauty Photo / Pregnancy Photo /Baby Photo / Graduation Photo လေးတွေအားလုံးအတွက်လဲ ဝန်ဆောင်မှုပေးနေတာမို ချစ်သောCustomerများအားလုံးကိုလဲ လာရောက်ခဲ့ကြဖိုဖိတ်ခေါ်လိုက်ရပါတယ်ရှင့်🫶', 400000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(106, 71, 5, 'PNA’S Wedding Studio - Studio', 'အခုလိုအစစအရာရာ ဈေးနှုန်းတွေတက်နေလို မင်္ဂလာပွဲလေးအတွက် အကောင်းဆုံးနဲ့ အသက်သာဆုံးဖြစ်အောင် ဘယ်လိုစီစဉ်ရင်ကောင်းမလဲ ဆိုတဲ့ မင်္ဂလာမောင်နှံလေးတွေအတွက် One Stop Service by PNA\'s Wedding Studio ကအဆင်သင့်ရှိနေပါတယ်နော် ပွဲအစမှအဆုံး သတိုသားသတိုသမီးတို စိတ်မရှုပ်ရလေအောင် အကောင်းဆုံးတာဝန်ယူဆောင်ရွက်ပေးမယ့် One Stop Service ဖြစ်ပါတယ်One Stop Service အပြင် အခြားအခြားသော Pre-Wedding , Wedding Photo & Video , Wedding Dress Rental , Floral Decoration Service များအားလုံးလည်းရရှိနိုင်ပါတယ်။Phone : 09959501111, 09881822200, 09881844400The Choice for your Wedding and the balance of Price & Quality.\" PNA\'s Wedding Studio', 400000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(107, 72, 5, 'Together Wedding Studio - Studio', '📸✨အမှတ်တရများစွာနဲ့ စိတ်ကူးထဲကအတိုင်း ပျော်ရွင်လှပခြင်းတွေကို မှတ်တမ်းတင်ကာ ရိုက်ကူးပေးတဲ့  Together Wedding Studio မှာဆိုရင်ဖြင့် Pre Wedding အပြင် Beauty, Family, Baby, Pregnancy စတဲ့ အမှတ်တရ အခိုက်အတန့်တွေကို ရိုက်ကူးပေးနေပြီဖြစ်လို လူကြီးမင်းတိုရဲ့ အမှတ်တရများကို လှပစွာ ထာဝရ တည်ဆောက်လိုက်ရအောင် 📸✨', 400000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(108, 73, 6, 'Western Park Ruby – People’s Park - Venue', 'မြို့အလယ်မှာရှိပေမဲ့ပန်းခြံဖြစ်လို့ ရှုပ်ထွေးမှုမရှိ၊မြက်ခင်းပြင်ကျယ် သဘာဝစိမ်းလန်းမှူများ၊နေရာကျယ်ဝန်းလို့ weeding, event venue အဖြစ်လူကြိုက်များပြီးဧည့်သည်အရေအတွက်များတဲ့eventများတွက်အဆင်   ပြေအောင်ဆောင်ရွက်ပေးနေပြီဖြစ်ပါတယ်။\n\nOther services: အခမ်းအနားအပြင်အဆင်ကို ကိုယ်တိုင်လည်းရသလို ဆိုင်မှခေါ်ယူပေးခြင်းလည်းရ ၊ weeding ကိုလာရောက်တဲ့ဧည့်သည်များ ပန်းခြံဝင်ကြေးပေးစရာမလို။          weeding dinner ကိုreception buffet systemအဖြစ်resquetနိုင်\n\nPricing: အပေါ်ဘက်မြက်ခင်းပြင်မှာ မြက်ခင်းအသုံးပြုခ- 500,000 ကျပ်ဖြစ်ပါတယ်။အောက်ဘက်မြက်ခင်းပြင်မှာ မြက်ခင်းအသုံးပြုခ -200,000ကျပ် ဖြစ်ပါတယ်။  Set Menu -400,000/ 450,000/ 500,000 ကျပ် (တစ်ဝိုင်းနှုန်း) /10ယောက်ဝိုင်း 1 ဝိုင်းကို190,000', 500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(109, 74, 9, 'MG & J Jewelry - Jewelry', 'Since 2015, MG&J has been redefining fine jewelry craftsmanship in Myanmar, blending time-honored tradition with contemporary elegance. We source the finest rubies, sapphires, and other precious gemstones directly from Myanmar, bringing exceptional value to every piece we create.\nOur mission is to offer more than adornment — we provide a refined, versatile collection that empowers individuals to express their identity, style, and story through jewelry.\n\nWith years of experience crafting exquisite pieces and delivering seamless service, MG&J has become a trusted name in fine jewelry.\n\nNow, we’re proud to introduce MG&J to the international stage. We invite discerning collectors and connoisseurs around the world to discover our creations and make them a meaningful part of their personal collection.\n\n၂၀၁၅ ခုနှစ်မှစ၍ MG&J သည် မြန်မာနိုင်ငံရှိ အထူးတန်ဖိုးမြင့် ရတနာလက်ဝတ်ရတနာလုပ်ငန်းကို အသစ်တဖန် အဓိပ္ပါယ်ဖော်ပြကာ ရိုးရာလက်ရာအမွေအနှစ်များနှင့် ခေတ်မီအလှတရားကို လိုက်ဖက်စွာ ပေါင်းစပ်ဖန်တီးလျက်ရှိပါသည်။\nကျွန်ုပ်တို့သည် မြန်မာနိုင်ငံမှ ထွက်ရှိသော အရည်အသွေးမြင့် ပတ္တမြား၊ နီလာနှင့် အခြားအဖိုးတန် ရတနာများကို တိုက်ရိုက် ရွေးချယ်ရရှိကာ ဖန်တီးသည့် လက်ရာတိုင်းတွင် ထူးချွန်သည့် တန်ဖိုးကို ပေးအပ်လျက်ရှိပါသည်။\n\nကျွန်ုပ်တို့၏ ရည်မှန်းချက်မှာ လက်ဝတ်ရတနာအလှတရားကိုသာ ပေးစွမ်းခြင်းမကဘဲ မိမိ၏ ကိုယ်ပိုင်အမှတ်အသား၊ စတိုင်နှင့် ဇာတ်လမ်းကို ဖော်ပြနိုင်ရန် အရည်အသွေးမြင့်၊ မျိုးစုံအသုံးပြုနိုင်သည့် စုဆောင်းမှုများကို ပံ့ပိုးပေးခြင်း ဖြစ်ပါသည်။\n\nနှစ်ပေါင်းများစွာ အလှတရားပြည့်စုံသော လက်ရာများကို ဖန်တီးထုတ်လုပ်ပြီး ဝန်ဆောင်မှုကိုလည်း အဆင်ပြေချောမွေ့စွာ ပေးဆောင်လာခဲ့သဖြင့် MG&J သည် အထူးတန်ဖိုးမြင့် လက်ဝတ်ရတနာလုပ်ငန်းတွင် ယုံကြည်စိတ်ချရသော အမှတ်တံဆိပ်တစ်ခုအဖြစ် ရပ်တည်လာနိုင်ခဲ့ပါသည်။\n\nယခုအခါတွင် MG&J ကို အပြည်ပြည်ဆိုင်ရာစျေးကွက်သို့ မိတ်ဆက်ပေးရန် ဂုဏ်ယူစွာ ကြေညာလိုပါသည်။ ကမ္ဘာတစ်ဝှမ်းရှိ ရတနာစုဆောင်းသူများနှင့် အလှတရားကို လေးစားမြတ်နိုးသူများအား ကျွန်ုပ်တို့၏ လက်ရာများကို လေ့လာရှာဖွေပြီး ကိုယ်ပိုင်စုဆောင်းမှုတွင် အဓိပ္ပါယ်ရှိသော အစိတ်အပိုင်းတစ်ခုအဖြစ် ထည့်သွင်းနိုင်ရန် ဖိတ်ခေါ်အပ်ပါသည်။\n\nPricing: 1580 USD for one\n\n850 USD for one', 1000000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(110, 75, 9, 'U Hton - Jewelry', 'ဦးထုံရွှေပန်းထိမ်နှင့် ရတနာရွှေဆိုင်တွင် ရွှေအရည်အသွေးကို စံချိန်မှီရွှေသားနှင့် ပြုလုပ်ထားပြီး ဝယ်သူများစိတ်ကျေနပ်မှုရရှိရန်အတွက် ဦးစားပေးဆောင်ရွက်လျက်ရှိပါသည်။ ရွှေထည်၊ ကျောက်ထည်နှင့် စိန်ထည်များကို ရွှေရည်အာမခံပြီး ပစ္စည်းမှန်၊ ဈေးနှုန်းတိကျမှန်ကန်စွာဖြင့် ရောင်းချပေးလျှက်ရှိပါသည်။ ရွှေထည်၏လက်ရာဒီဇိုင်းများမှာ သေသပ်လှပပြီး ဆန်းသစ်သောဒီဇိုင်းမျိုးစုံကို တစ်နေရာတည်းမှာ ရရှိနိုင်သည့်အပြင် ရွှေအရည်အသွေးမှာလည်း စံချိန်မှီ ထုတ်လုပ်ထားပါသည်။ ကိုယ်ပိုင်ပန်းထိမ်ရှိသည့်အတွက် အလျော့တွက်လက်ခများမှာ အသက်သာဆုံးဖြစ်ပြီး ရွှေအရည်အသွေးအလိုက် ခိုင်ခန့်မှုရှိအောင် ပြုလုပ်ထားပါသည်။ ဝါရင့်ပန်းထိမ်ဆရာများနှင့် ခေတ်မှီစက်ပစ္စည်းများအသုံးပြု၍ ကျွမ်းကျင်စွာ ပြုလုပ်ဖန်တီးပေးထားသောကြောင့် လက်ရာဒီဇိုင်းများမှာ သေသပ်လှပလျက်ရှိပါသည်။\n\nဦးထုံရွှေဆိုင်မှ ဝယ်ယူထားသော Customer များအားလုံး ငွေဖော်လိုသည့်အခါ အချိန်မရွေး ရွှေထည်ရတနာပစ္စည်းများကို ပြန်လဲပြန်သွင်း ပြုလုပ်နိုင်သည့်အပြင် ပြန်လဲပြန်သွင်းပြုလုပ်ရာတွင်လည်း ရွှေဈေးနှုန်းကို တက်လျှင်တက်ဈေး၊ ကျလျှင်ကျဈေးဖြင့် ရွှေအလေးချိန်ကို အရှိရွှေချိန်တိုင်း တိကျမှန်ကန်စွာဖြင့် ပြန်လည်ဆောင်ရွက်ပေးပါသည်။ Online မှ မှာယူသော Customer များကိုလည်း အကောင်းမွန်ဆုံးသော ဝန်ဆောင်မှုများဖြင့် ဆောင်ရွက်ပေးလျှက် ရှိသည့်အပြင် ပြည်တွင်း၊ ပြည်ပနိုင်ငံများကိုလည်း ပို့ဆောင်ပေးလျက် ရှိပါသည်။\n\n“ရွှေဆိုလျှင် ဦးထုံကိုအထူးယုံလိုက်ပါ” ဆိုသည့်စကားအတိုင်း ရွှေထည်နှင့်ရတနာပစ္စည်းများကို ရွှေအရည်အသွေးကောင်းမွန်ပြီး ရွှေရည်ပြည့်မှီသည့် ခေတ်မှီလက်ရာဒီဇိုင်းမျိုးစုံကို ဦးထုံရွှေပန်းထိမ်နှင့်ရတနာရွှေဆိုင်တွင် လာရောက်ဝယ်ယူရန် မိတ်ဟောင်းမိတ်သစ် Customer များအားလုံးကို နွေးထွေးစွာ ဖိတ်ခေါ်အပ်ပါသည်။', 1000000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(111, 76, 9, 'Myat Pan Tha Zin Diamond and Jewelry - Jewelry', 'Welcome to Myat Pan Thazin Diamonds & Jewellery, where elegance meets excellence! We specialize in crafting stunning diamond and emerald jewelry designed to celebrate the beauty and individuality in Myanmar and beyond.\nAt the heart of our creations is a commitment to quality. We use only the finest diamonds and emeralds, ensuring every piece is as timeless and unique as the person who wears it. Whether you\'re looking for a statement piece for a special occasion or a timeless treasure to cherish forever, we’re here to help you find the perfect match.\nOur mission is simple: to bring joy and confidence to our customers by offering the best quality jewelry with a personal touch. Let us be a part of your story and help you shine brighter every day.\n\nMyat Pan Thazin Diamonds & Jewellery မှ ကြိုဆိုပါသည်။ လှပမှုနှင့် ထူးချွန်မှု ပေါင်းစပ်ထားသော လက်ဝတ်ရတနာများကို ဖန်တီးထုတ်လုပ်ပေးနေသော အမှတ်တံဆိပ်တစ်ခုဖြစ်ပါသည်။ မြန်မာနိုင်ငံနှင့် နိုင်ငံရပ်ခြားများတွင်ပါ လူတစ်ဦးချင်းစီ၏ လှပမှုနှင့် ကိုယ်ပိုင်စရိုက်ကို အထူးအလေးထား၍ ဒီဇိုင်းထုတ်ထားသော စိန်နှင့် မြကွင်း လက်ဝတ်ရတနာများကို အထူးပြု ဆောင်ရွက်ပါသည်။\n\nကျွန်ုပ်တို့၏ ဖန်တီးမှုများ၏ အဓိကအခြေခံမှာ အရည်အသွေးမြင့်မားမှုဖြစ်ပါသည်။ အကောင်းဆုံးအရည်အသွေးရှိသော စိန်များနှင့် မြကွင်းများကိုသာ အသုံးပြုကာ ဝတ်ဆင်သူတစ်ဦးချင်းစီ၏ ထူးခြားမှုနှင့် ကိုက်ညီသည့် အမြဲတမ်းတန်ဖိုးရှိသော လက်ဝတ်ရတနာများကို ဖန်တီးပေးပါသည်။ အထူးအခမ်းအနားများအတွက် မျက်စိကျစရာ လက်ဝတ်ရတနာတစ်ခုကို ရှာဖွေနေပါသလား၊ သို့မဟုတ် အချိန်ကြာရှည်ထိန်းသိမ်းနိုင်မည့် အဖိုးတန်အမှတ်တရတစ်ခုကို လိုအပ်ပါသလား—သင့်အတွက် အကောင်းဆုံးရွေးချယ်မှုကို ရရှိစေရန် ကျွန်ုပ်တို့ အမြဲတမ်း အဆင်သင့်ရှိပါသည်။\n\nကျွန်ုပ်တို့၏ မစ်ရှင်မှာ ရိုးရှင်းပါသည်—အရည်အသွေးအကောင်းဆုံး လက်ဝတ်ရတနာများကို ကိုယ်ပိုင်စိတ်ပါဝင်မှုဖြင့် ပေးအပ်ကာ ဖောက်သည်များအား ပျော်ရွှင်မှုနှင့် ယုံကြည်မှုကို ပေးဆောင်ခြင်းဖြစ်ပါသည်။ သင့်ဘဝအမှတ်တရများ၏ အစိတ်အပိုင်းတစ်ခုဖြစ်ခွင့်ရပြီး သင့်အား နေ့စဉ် ပိုမိုတောက်ပစေရန် ကူညီပေးပါစေ။\n\nPricing: 5400000 Ks for one\n\n7900000 Ks for one', 5400000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(112, 77, 9, 'Vivian Diamond Jewellery - Jewelry', 'Pricing: 2800000Ks to 3500000Ks', 2800000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(113, 78, 9, 'Theingi Moe Jewelry - Jewelry', 'Pricing: 1000000Ks to\n7800000Ks', 1000000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(118, 83, 3, 'Parisian Cake&Cafe - Cake', '**Parisian Cake & Coffee** သည် မြန်မာနိုင်ငံ၊ ရန်ကုန်မြို့တွင် နာမည်ကြီးသော ဘိတ်ကရီနှင့် ကာဖေးဆိုင်ဖြစ်ပြီး **ပြင်သစ်စတိုင်မုန့်ပေါင်းများနှင့် ကိတ်မုန့်များ** အတွက်လူကြိုက်များသည်။ ဆိုင်သည် အပျော်အပါးဖြစ်စေရန် အဆင့်မြင့်ဖန်တီးထားသော ပတ်ဝန်းကျင်နှင့်အတူ အရည်အသွေးမြင့် မုန့်ဖုတ်ပစ္စည်းများကို ပံ့ပိုးသည်။**ပြင်သစ်စတိုင် ကိတ်မုန့်နှင့် ပတ်ရီဆီယန် မုန့်များ**\nCheesecake, Chocolate Cake, Fruit Cake နှင့် အခြား အထူးဒီဇိုင်းပါဝင်သည့် မုန့်ပေါင်းများကို ရောင်းချသည်။order **အလိုက် လိုချင်သည့် ကိတ်များ**\nမွေးနေ့၊ နှစ်ပတ်လည်နှင့် လက်ထပ်ပွဲအတွက် order **အလိုက်ကိတ်** ရနိုင်သည်။ လက်ထပ်ပွဲကိတ်များကို **မှာအလိုက် ထုတ်လုပ်သည်(Drinks)များ**\nကိတ်နှင့် အရသာကောင်းစွာ လိုက်ဖက်သော ကော်ဖီ၊ လက်ဖက်ရည်နှင့် အပူ/အအေးသောက်စရာများ ရောင်းချသည်။**ပတ်ဝန်းကျင် (Ambiance)**\nဆိုင်များသည် ပြင်သစ်စတိုင်ကာဖေးဆိုင်အတိုင်း နေရာချထားထားပြီး သက်တောင့်သက်သာ နေထိုင်နိုင်သော ပတ်ဝန်းကျင်ကို ပံ့ပိုးသည်။💍 လက်ထပ်ပွဲကိတ်ဝန်ဆောင်မှု**အလိုက် လက်ထပ်ပွဲကိတ်များ** (1-tier ကနေ Luxury multi-tier designs) ရနိုင်သည်။အရသာ၊ အရွယ်အစား၊ ဒီဇိုင်း စိတ်ကြိုက် ထုတ်လုပ်သည်။ကြိုတင်မှာယူရန် အကြံပြုသည်၊ အထူးသဖြင့် အထူးဒီဇိုင်းများအတွက် အရေးကြီးသည်။📌 လိပ်စာနှင့် ဖုန်းနံပါတ်Parisian Cake & Coffee သည် ရန်ကုန်မြို့တွင် အခွင့်အရေးအမျိုးမျိုးရှိသည် –**446 Lower Kyeemyindaing Rd, Yangon** – +95 1 230 1512**No.169, Corner of Maha Bandula Rd & 38th St, Yangon** – Facebook ဖြင့်ဆက်သွယ်နိုင်သည်**Gamone Pwint Shopping Center, Bagaya St, Yangon** – +95 976 5250017ကိတ်အရသာ၊ ဒီဇိုင်းနှင့် ဝန်ဆောင်မှုအရ လူကြိုက်များသောကြောင့် **နေ့စဉ်စားသောက်မှုနှင့် အထူးပွဲများ (လက်ထပ်ပွဲ) အတွက်လည်း** ရွေးချယ်သင့်သော ဆိုင်ဖြစ်သည်။\n\nPricing: 1-**Price:** **200,000 – 300,000 MMK 2-Price:** **500,000 – 800,000 MMK 3-Price:** **800,000 – 1,500,000 MMK 4-1,500,000 MMK and above**', 200000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(119, 84, 3, 'Season - Cake', '**Seasons Bakery** သည် ရန်ကုန်မြို့တွင် နာမည်ကြီးသော ဘိတ်ကရီဆိုင်ဖြစ်ပြီး **အရည်အသွေးမြင့် ကိတ်မုန့်များ၊ မုန့်ပေါင်းများနှင့် အချိုပစ္စည်းများ** ရောင်းချသည်။ ဆိုင်သည် အရသာကောင်းပြီး အလှဆင်ထားသော ဒီဇိုင်းများနှင့် ပေါင်းစပ်ထားကာ၊ **မွေးနေ့၊ နှစ်ပတ်လည်နှင့် လက်ထပ်ပွဲအတွက် order ကိတ်များ** အပါအဝင် အမျိုးမျိုးသော ကိတ်များကို ပံ့ပိုးသည်။🍰 အင်္ဂါရပ်များ**ကိတ်နှင့် မုန့်ပေါင်းအမျိုးမျိုး**\nCream Cake, Fruit Cake, Chocolate Cake, Cupcake နှင့် အထူးရာသီအရသာပါဝင်သည့် မုန့်များပါဝင်သည်။order **ကိတ်**\nလက်ထပ်ပွဲ၊ မွေးနေ့ သို့မဟုတ် အထူးပွဲအတွက် **အမိန့်အလိုက်ကိတ်** ရနိုင်ပြီး၊ အရွယ်အစား၊ tier အရေအတွက်၊ အရသာနှင့် ဒီဇိုင်းကို သင်ရွေးချယ်နိုင်သည်။**ဘီဗရိေ့များနှင့် အခြားစားစရာများ**\nအချို့သောခွဲခြားဆိုင်များတွင် ကော်ဖီ၊ လက်ဖက်ရည် နှင့် အလှဆင်ထားသည့် အပြည့်အစုံအစားအစာများ ရောင်းချသည်။**ပတ်ဝန်းကျင်**\nအချို့သောခွဲခြားဆိုင်များတွင် **သက်တောင့်သက်သာသော နေရာချထားမှု** ရှိပြီး၊ မိတ်ဆွေတွေ၊ မိသားစု၊ အထူးပွဲများအတွက်သင့်တော်သည်။💍 လက်ထပ်ပွဲကိတ်ဝန်ဆောင်မှု**အမျိုးအစားများစွာ** ရရှိနိုင်သည် – Basic, Standard, Premium, Luxury/Custom Design**အမိန့်အလိုက်သာ ထုတ်လုပ်သည်** – လက်ထပ်ပွဲကိတ်များကို အဆင့်လိုက် အရွယ်အစား၊ ဒီဇိုင်းနှင့် အရသာအရ ထုတ်လုပ်သည်။ကြိုတင်မှာယူခြင်းကို အကြံပြုသည်၊ အထူးဒီဇိုင်းနှင့် Multi-tier ကိတ်များအတွက် အထူးအရေးကြီးသည်။📌 လိပ်စာနှင့် ဖုန်းနံပါတ် (ဥပမာ)**Myanmar Plaza Branch:** Level 1, Myanmar Plaza, Kabar Aye Pagoda Rd, Yangon – +95 973 020 754**City Mart Pyay Rd Branch:** City Mart, Pyay Rd, Yangon – +95 1 650 771**Junction City Branch:** 4th Floor, Junction City, Bo Gyoke Aung San Rd, Yangon – +95 9 9708 37654Seasons Bakery သည် **အရည်အသွေး၊ အရသာနှင့် ဝန်ဆောင်မှုကောင်းမွန်မှု** ကြောင့် **လက်ထပ်ပွဲကိတ်နှင့် အထူးပွဲများအတွက်** လူကြိုက်များသော ဆိုင်ဖြစ်သည်။\n\nPricing: 1-**Price:** **200,000 – 300,000 MMK 2-Price:** **500,000 – 800,000 MMK 3-Price:** **800,000 – 1,500,000 MMK 4-1,500,000 MMK and above**', 200000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(120, 85, 3, 'Kudo’s - Cake', '**Kudos Bakery** သည် ရန်ကုန်မြို့တွင် နာမည်ကြီးသော ဘိတ်ကရီနှင့် ကာဖေးဆိုင်ဖြစ်ပြီး **အရည်အသွေးမြင့် ကိတ်မုန့်များ၊ ပေါင်မုန့်များနှင့် အချိုပစ္စည်းများ** ရောင်းချသည်။ ဆိုင်သည် **အရသာကောင်းခြင်း၊ သန့်ရှင်းမှုနှင့် လှပသော ဒီဇိုင်းများ** ပေါင်းစပ်ထားကာ, နေ့စဉ်စားသောက်မှုအတွက်သာမက **မွေးနေ့၊ နှစ်ပတ်လည်နှင့် လက်ထပ်ပွဲအတွက်**လည်း လူကြိုက်များသည်။**ကိတ်နှင့် မုန့်ပေါင်းအမျိုးမျိုး**\nCream Cake, Fruit Cake, Chocolate Cake, Cupcake နှင့် ရာသီအရသာပါဝင်သည့် မုန့်များ ပါဝင်သည်။order **ကိတ်** **အလိုက်** \nမွေးနေ့၊ နှစ်ပတ်လည်၊ လက်ထပ်ပွဲအတွက်ရနိုင်ပြီး၊ အရွယ်အစား၊ tier အရေအတွက်၊ အရသာနှင့် ဒီဇိုင်းကို ရွေးချယ်နိုင်သည်။**ဘီဗရိေ့များနှင့် အခြားစားစရာများ**\nအချို့ခွဲခြားဆိုင်များတွင် ကော်ဖီ၊ လက်ဖက်ရည်နှင့် အလှဆင်ထားသော အချိုအစာများ ရောင်းချသည်။**ပတ်ဝန်းကျင်**\nဆိုင်များသည် **သက်တောင့်သက်သာရှိပြီး စိတ်လှုပ်ရှားဖွယ်ကောင်းသော နေရာ** ပံ့ပိုးကာ, မိတ်ဆွေတွေ၊ မိသားစု၊ အထူးပွဲများအတွက် သင့်တော်သည်။💍 လက်ထပ်ပွဲကိတ်ဝန်ဆောင်မှု**အမျိုးအစားများ:** Basic, Standard, Premium, Luxury / Custom Design**အမိန့်အလိုက် ထုတ်လုပ်သည်** – လက်ထပ်ပွဲကိတ်များကို အရွယ်အစား၊ tier အရေအတွက်၊ အရသာနှင့် ဒီဇိုင်းအရ ထုတ်လုပ်သည်။ကြိုတင်မှာယူခြင်းကို အကြံပြုသည်၊ အထူးဒီဇိုင်းနှင့် Multi-tier ကိတ်များအတွက် အရေးကြီးသည်။📌 လိပ်စာနှင့် ဖုန်းနံပါတ် (ဥပမာ)**Main Branch:** Anawrahta Rd, Yangon – +95 9 422 886 667**Moe Kaung Rd Branch:** Corner of Sonlonngu Kyaung St & Moe Kaung Rd, Yangon – +95 9 422 886 664**Myanmar Plaza Branch:** Level 1, Myanmar Plaza, Kabar Aye Pagoda Rd, Yangon – +95 973 020 754Kudos Bakery သည် **အရည်အသွေး၊ အရသာနှင့် ဝန်ဆောင်မှုကောင်းမွန်မှု** ကြောင့် **လက်ထပ်ပွဲကိတ်နှင့် အထူးပွဲများအတွက်** လူကြိုက်များသော ဆိုင်ဖြစ်သည်။\n\nPricing: 1-**Price:** **200,000 – 300,000 MMK 2-Price:** **500,000 – 800,000 MMK 3-Price:** **800,000 – 1,500,000 MMK 4-1,500,000 MMK and above**', 200000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(121, 86, 3, 'Shwe Pu Zun - Cake', 'ရွှေပုဇွန်သည် ရန်ကုန်မြို့တွင် လူကြိုက်များသော Bakery & Café တစ်ခုဖြစ်ပြီး ကိတ်မုန့်များ၊ မုန့်ဖုတ်ပစ္စည်းများ၊ ပေါင်မုန့်များနှင့် အအေးအနွေးအဖျော်ယမကာများကို အရသာကောင်းမွန်စွာ ထုတ်လုပ်ရောင်းချလျက်ရှိပါသည်။သန့်ရှင်းသပ်ရပ်သော ဆိုင်ပတ်ဝန်းကျင်နှင့် သက်တောင့်သက်သာ နေထိုင်နိုင်သော အစားအသောက်ဆိုင်ပုံစံကြောင့် မိသားစုတွေ၊ သူငယ်ချင်းတွေ တွေ့ဆုံစားသောက်ရန် အထူးသင့်တော်ပါသည်။🎂 ထူးခြားချက်များမွေးနေ့၊ မင်္ဂလာပွဲ စသည့် အခမ်းအနားများအတွက် Custom Cake များ လက်ခံပြုလုပ်ပေးခြင်းနေ့စဉ်လတ်ဆတ်သော Pastry နှင့် Bread များလူကြိုက်များသော Faluda နှင့် အခြားအဖျော်ယမကာများမြို့တွင်း တည်နေရာများစွာ ရှိခြင်းရွှေပုဇွန်သည် အရသာနှင့် အရည်အသွေးကောင်းမွန်မှုကြောင့် ယုံကြည်စိတ်ချရသော Bakery Brand တစ်ခုအဖြစ် လူသိများပါသည်။\n\nPricing: **Basic small customized cake** (1–2 tiers): about **60,000 – 120,000 MMK+**\n💰 **Medium wedding cake** (2–3 tiers): about **120,000 – 250,000 MMK+**\n💰 **Large or highly decorated cake** (3+ tiers, fully custom): **250,000 MMK**', 60000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(122, 87, 3, '77 Cake - Cake', '77 Cakes သည် ရန်ကုန်မြို့တွင် လူကြိုက်များသော **Bakery & Cake Shop** တစ်ခုဖြစ်ပြီး ကိတ်မုန့်များ၊ မုန့်ဖုတ်ပစ္စည်းများ၊ ပေါင်မုန့်များနှင့် အဖျော်ယမကာများကို ရောင်းချလျက်ရှိပါသည်။🎂 **ထူးခြားချက်များ**မွေးနေ့၊ မင်္ဂလာအခမ်းအနားများနှင့် အခြား အခမ်းအနားများအတွက် Custom Cake များ လက်ခံပြုလုပ်သည်။နေ့စဉ်လတ်ဆတ်သော Pastry၊ Bread များနှင့် Specialty Cakes များရှိသည်။Cafe-style ဆိုင်များအချို့တွင် ကော်ဖီ၊ အအေးအနွေးအဖျော်ယမကာများကိုလည်း ရရှိနိုင်သည်။မြို့တွင်း အဆင့်မြင့်သော အရသာနှင့် ဝန်ဆောင်မှုကောင်းမွန်မှုကြောင့် လူကြိုက်များသော Bakery Brand ဖြစ်သည်။💡 77 Cakes သည် အရသာကောင်းမွန်ပြီး အနည်းဆုံးနှစ်လစဉ်အတွက် Customer များထံမှ အကြိုက်ဆုံး Bakery အဖြစ် သတ်မှတ်ခံထားရသည်။\n\nPricing: **Basic small customized cake** (1–2 tiers): about **60,000 – 120,000 MMK+**\n💰 **Medium wedding cake** (2–3 tiers): about **120,000 – 250,000 MMK+**\n💰 **Large or highly decorated cake** (3+ tiers, fully custom): **250,000 MMK**', 60000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(123, 88, 3, 'El Dorado - Cake', 'El Dorado (အဲလ်ဒိုရာဒို) အကြောင်းEl Dorado သည် ရန်ကုန်မြို့တွင် လူကြိုက်များသော **Bakery & Cake Shop** တစ်ခုဖြစ်ပြီး **နေ့စဉ်လတ်ဆတ်သော ကိတ်မုန့်များ၊ မုန့်ဖုတ်ပစ္စည်းများနှင့် ပေါင်မုန့်များ**ကို ရောင်းချလျက်ရှိသည်။🎂 **ထူးခြားချက်များ**မွေးနေ့၊ မင်္ဂလာပွဲ၊ နှစ်ပတ်လည် အခမ်းအနားများအတွက် **Custom Cake များ** လက်ခံပြုလုပ်သည်နေ့စဉ်ထုတ်လျက်ရှိသော **Pastry၊ Bread၊ Specialty Cakes**ဝန်ဆောင်မှုကောင်းမွန်ပြီး သန့်ရှင်းသပ်ရပ်သော ဆိုင်ပတ်ဝန်းကျင်အရသာကောင်းမွန်မှုနှင့် လူကြိုက်များမှုကြောင့် **Yangon တွင် အကြိုက်ဆုံး Bakery** အဖြစ် သိရှိခံရသည်🍰 El Dorado သည် အမှန်တကယ် **အရသာနှင့် အရည်အသွေးကို အားထားသူများအတွက် သင့်တော်သည့် Bakery** ဖြစ်ပြီး၊ သင့် မွေးနေ့၊ မင်္ဂလာပွဲ နှင့် အခြား အခမ်းအနားများအတွက် အကောင်းဆုံးရွေးချယ်မှုတစ်ခုဖြစ်သည်။\n\nPricing: **Basic small customized cake** (1–2 tiers): about **60,000 – 120,000 MMK+**\n💰 **Medium wedding cake** (2–3 tiers): about **120,000 – 250,000 MMK+**\n💰 **Large or highly decorated cake** (3+ tiers, fully custom): **250,000 MMK**', 60000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(124, 89, 3, 'Shan Yoe Yar Restaurant - Catering', 'ရှမ်းရိုးရာ အစားအစာစစ်စစ်များကို Fine Dining ပုံစံဖြင့် နှစ်ပေါင်း (၁၀) နှစ်ကျော်ကြာ လည်ပတ်နေသည့် ဆိုင်ခွဲများရှိပြီး အစားအသောက်အရည်အသွေးနှင့် ဝန်ဆောင်မှုကို အလေးထား ဆောင်ရွက်လျက်ရှိပါသည်။မင်္ဂလာပွဲ၊ ကုသိုလ်ပွဲ၊ မွေးနေ့ပွဲ၊ ဆွမ်းကပ်လှူပွဲ၊ Staff Party နှင့် Company Anniversary ပွဲများအတွက် Outdoor Catering Service ပေးအပ်နိုင်ပြီး Buffet, Set Menu နှင့် A la carte စနစ်များဖြင့် မီနူးအမျိုးပေါင်းရာကျော်ထဲမှ နှစ်သက်သလို စီစဉ်ပေးပါသည်။မနော်ဟရီ၊ ရန်ကင်း နှင့် ဆူးလေ ဆိုင်ခွဲများတွင် ဝန်ဆောင်မှုပေးလျက်ရှိပြီး Event Sales မှတစ်ဆင့်လည်း ဆက်သွယ်နိုင်ပါသည်။\n\nPricing: 🍽 Buffet (တစ်ဦးချင်း)\n➡️ 18,000 – 35,000 ကျပ် / လူတစ်ယောက်\n\n🍱 Set Menu (တစ်ဦးချင်း Set)\n➡️ 15,000 – 30,000 ကျပ် / လူတစ်ယောက်\n\n🥘 A la carte (တစ်ပွဲချင်း)\n➡️ 5,000 – 25,000 ကျပ် / ပွဲ', 18000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(125, 90, 3, 'KSS နတ်သုဒ္ဓါဒံပေါက် - Catering', '🍗 အခါမွရ်ဂီး (Aka Murgi) – ကြက်တစ်ကောင်လုံးကို သီဟိုဠ်စေ့၊ စပျစ်ခြောက်၊ အမွှေးအကြိုင်များနှင့် နှပ်ပြီး ဗိုက်ထဲတွင် ကြက်ဥပြုတ်ကြော်နှင့် အာလူးထည့်၍ နူးအိမွှေးကြိုင်အောင် ချက်ထားသော ကြက်ကောင်လုံးကြော်ချက်။\n\n🍲 ဟာလင်း (Haleem) – ကြက်သားကို ပဲမျိုးစုံ၊ ဂျုံတို့နှင့် အချိန်ကြာမြင့်စွာ နူးအိအောင် မွှေချက်ထားသော အသားပဲစွပ်ပြုတ်ပျစ်ပျစ်။\n\n🥣 စမိုင် (Samai) – နွားနို့စိမ့်စိမ့်ထဲ ကြာဆံအသေးများ၊ သီဟိုဠ်စေ့၊ စပျစ်ခြောက်တို့နှင့် အချိုတည်းထားသော နို့ကြာဆံအချိုပွဲ။\nထို့အပြင် —\n• နတ်သုဒ္ဓါဒံပေါက် (ကြက်၊ ဆိတ်၊ မြေအိုး)\n• ကြက်/ဆိတ် ကပ်ဘတ် (Kebab)\n• ကြက်သား ချပ်ပ် (Chaap)\n• Grilled Masala Chicken (မဆလာကြက်ကင်)\n• ကုလားပဲသုပ်\n• ဖာလူဒါ၊ နို့သစ်ခွ၊ ဒိန်ချဉ်မျိုးစုံ၊ ကျောက်ကျောသံပရာ စသည့် အချိုပွဲနှင့် အအေးမျိုးစုံတို့လည်း ရရှိနိုင်ပါသည်။\nတစ်နှစ်တစ်ခါသာ ရနိုင်သော လက်ရာစစ်စစ် Ramzan Special Menu များဖြစ်ပါသည်။ ✨\n\nPricing: 🍗 အခါမွရ်ဂီး (Aka Murgi)➡️ 30,000 – 45,000 ကျပ် (ကြက်တစ်ကောင်လုံး)\n\n🍲 ဟာလင်း (Haleem)➡️ 8,000 – 15,000 ကျပ် (ပန်းကန်တစ်ခွက်)\n\n🥣 စမိုင် (Samai)➡️ 5,000 – 10,000 ကျပ်🍚 နတ်သုဒ္ဓါဒံပေါက်➡️ 7,000 – 15,000 ကျပ် (တစ်ပွဲ)\n\n🍢 Kebab / Chaap➡️ 6,000 – 12,000 ကျပ်\n\n🍗 Grilled Masala Chicken➡️ 10,000 – 20,000 ကျပ်\n\n🍧 ဖာလူဒါ / အချိုပွဲ➡️ 3,000 – 8,000 ကျပ်📌 \n\n📌 မှတ်ချက် –Ramzan Special ဖြစ်လို့ Limited Time Menu ဖြစ်ပြီး ဈေးနှုန်းက ပစ္စည်းအရည်အသွေးနဲ့ Portion ပေါ်မူတည်ပြီး ပြောင်းလဲနိုင်ပါတယ်။', 30000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(126, 91, 3, 'ထူး ရေခဲမုန့် - Catering', '❤️ အချစ်တွေလာအောင် အချိုတွေစားပါ။😋🍰\nဒီလိုရာသီဥတုလေးမှာ နွေးနွေးထွေးထွေး အချစ်ဓာတ်လေးနဲ့ ချိုချိုအီအီ မုန့်လေးတွေက အလိုက်ဖက်ဆုံးပါပဲနော်။ 🍨🧀🍦🍰\nအပြင်မှာ ဘယ်လောက်ပဲ အေးနေပါစေ၊ ချိုမြိန်တဲ့ Dessert တစ်ခုရဲ့ အရသာက နွေးထွေးတဲ့ စိတ်ခံစားမှု တွေကို ယူဆောင်လာပေးပါလိမ့်မယ်။ 😋😋\n🧀 နူးနူးညံ့ညံ့ ကိတ် အိအိလေး တစ်ဖဲ့🍦ထူး Ice Cream ချိုချိုလေး တစ်ကိုက်နဲ့ဆို နေ့ရက်တိုင်းမှာ စိတ်ကို ကြည်လင်လန်းဆန်းသွားစေမှာပါ။\n🍰🍦🍨🧀 ကိုယ့်ဘေးနားက ချစ်ရတဲ့သူကို ဒီလိုမုန့်လေးတွေ ဝယ်ကျွေးပြီး ချစ်ခြင်းမေတ္တာတွေပြမလား၊ ဒါမှမဟုတ် တစ်ယောက်တည်း အေးအေးဆေးဆေး အရသာခံစားပြီး Self-Love လုပ်ကြမလား။😚❤️\n\nPricing: 🍦 Ice Cream (တစ်ခွက် / တစ်လုံး)➡️ 3,000 – 8,000 ကျပ်\n\n🍨 Ice Cream Cup (Premium / Special)➡️ 5,000 – 12,000 ကျပ်\n\n🍰 Cake Slice (တစ်ဖဲ့)➡️ 4,000 – 10,000 ကျပ်\n\n🎂 Whole Cake (ကိတ်တစ်လုံး)➡️ 35,000 – 120,000 ကျပ်(Size နှင့် Design ပေါ်မူတည်)', 3000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(127, 92, 3, 'The Hundred -Grilled Chicken - Catering', 'THE HUNDRED မှာ ထူးခြားကောင်းမွန်တဲ့အရသာနဲ့အတူ သန့်ရှင်းသပ်ရပ်တဲ့ Packaging ၊ ပျူငှာတဲဲ့ဝန်ဆောင်မှုတွေအပြင် လက်ဆောင်ပေးချင်သူများအတွက်လည်း မေတ္တာစကားလေးတွေ ကိုယ်စားရေးပေးတဲ့  Free Postcard Service လည်းရှိသေးတယ်နော် 🥰\n\nPricing: ♨️ရိုးရိုး Plain ကြက်ကင်      Whole ▪️ 28000 Ks / Half ▪️ 15000 Ks\n♨️ဂေါ်ရဖား အစာသွပ်ကြက်ကင်      Whole ▪️ 35000 Ks / Half ▪️ 18500 Ks\n♨️ရို့စ်မေရီ အစာသွပ်ကြက်ကင်      Whole ▪️ 35000 Ks / Half ▪️ 18500 Ks\n♨️နှစ်တစ်ရာသီး အစာသွပ်ကြက်ကင်      Whole ▪️ 37000 Ks / Half ▪️ 19500 Ks', 28000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(128, 93, 3, 'Royal Chef - Catering', 'Royal Chefမှ Event & Catering Service အဖြစ် အကောင်းဆုံး ချက်ပြုတ်တည်ခင်းဧည့်ခံမှုများဖြင့် Customer များကို Professional ဝန်ဆောင်မှုပေးလျက်ရှိပါသည်။ မိတ်ဟောင်းမိတ်သစ်များ၏ အားပေးမှုအတွက်လည်း အထူးကျေးဇူးတင်ရှိပါသည်။\n\nPricing: 🍽 Buffet Set (A-1 to C-3)➡️ 18,000 – 35,000 ကျပ် / လူတစ်ယောက်(Menu အမျိုးအစားပေါ်မူတည်ပြီး ကွာနိုင်သည်)\n\n🥢 Set Menu (Group Order)➡️ 15,000 – 30,000 ကျပ် / လူတစ်ယောက်📌 ပွဲအရေအတွက်များလျှင် Discount Package ရနိုင်နိုင်ပါသည်။', 18000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(129, 94, 3, 'Rice Box - Catering', '•မြန်မာ၊ တရုတ်၊ ယိုးဒယား အစားအစာများနဲ့ နှုတ်မြိန်စာ အစုံအလင် .အကင် ၊ မုန့် ၊ ကော်ဖီကအစ \n• စားပွဲ၊ ပန်းကန်၊ ထိုင်ခုံ အစအဆုံး အခင်းအကျင်း။ ဧည့်ခံပေးမယ် Waiter Service\n\nPricing: 💕 CP ကြက်သား Set — တစ်ခါစား (၁၁,၀၀၀) ကျပ်\n💕 မြန်မာဒန်ပေါက် ကြက်သား Set — တစ်ခါစား (၁၃,၀၀၀) ကျပ်', 11000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(130, 95, 3, 'Boke & Bee - Catering', 'စတုဒီသာကျွေးမလား? ဝန်ထမ်းတွေအတွက် Staff Party လား?💍 မင်္ဂလာပွဲ၊ 🏠 အိမ်တက်ပွဲကနေ 🎬 Shooting နဲ့ 🏭 စက်ရုံကင်တင်းတွေအထိ အတွေ့အကြုံရင့် ကျွမ်းကျင်စွာ တာဝန်ယူပေးနေပါပြီ။\n\nPricing: တစ်ဦးလျှင် ၁၂,၀၀၀ ကျပ် ကနေစရှိတဲ့Package များ။\n\nသပ်ရပ်လှပတဲ့ Table Setup (ပန်းကန် + ကော်ဖီခွက်) ကို တစ်စုံမှ ၁,၄၀၀ ကျပ် ထဲနဲ့ အပြီးအစီး ငှားရမ်းနိုင်ပါပြီ။\n\n☕ Coffee & Drinks – 5,000 – 12,000 ကျပ်🍰 Dessert – 6,000 – 15,000 ကျပ်🍽️ Light Meal – 8,000 – 18,000 ကျပ်', 12000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(131, 96, 3, 'နှင်းသီရိ - Catering', '🍗🍚 နှင်းသီရိရဲ့ကြက်ဆီထမင်းမှာက ဆီသန့်သန့်၊ ပါဝင်ပစ္စည်းတွေအားလုံးကို သန့်ရှင်းလတ်ဆတ်စွာနဲ့ ချက်ပြုတ်ပေးထားလို့ ခန္ဓာကိုယ်အတွက် လိုအပ်တဲ့ အာဟာရများစွာကို ရရှိစေပါတယ်။\n\nPricing: 🍽️ ကြက်ဆီထမင်း (ပွဲကြီး) - 4,200 Ks\n🍽️ ကြက်ဆီထမင်း (လိုက်ပွဲ) - 2,500 Ks\n🍗 ကြက်ကင်တစ်ကောင် (ရှယ်အရသာ) - 30,000 Ks\n 🍽️ ဒံပေါက် - 7,200 Ks \n🍽️ ထောပတ်ထမင်း - 7,200 Ks \n🍽️ ပဲထမင်း / နှမ်းထမင်း - 7,200 Ks\n🍨 နှင်းပုလဲ ရေခဲမုန့် - 1,200 Ks\n🍨 စတော်ဘယ်ရီ / နို့ / ပိန်းဥ - 1,100 Ks\n🍨 ရိုးရိုးရေခဲမုန့် - 1,100 Ks', 4200.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(132, 97, 12, 'H&H Floral and Wedding Service - Decoration', 'H&H floral မှာဈေးနှုန်း ချိုချိုသာသာလေးတွေနဲ့\nအလှဆုံးတွေပြင်ဆင်ပေးမှာပါနော်\nလိုချင်တဲ့ရက်လေးရဖို booking လေးတွေ\nကြိုယူထားဖိုလိုပါမယ်ရှင် ☺️\n\nOther services: ပန်းစည်းအမျိုးမျိုး / လက်ကိုင်ပန်း / surprise box များ မှာယူနိုင်ပါသည်။\n\nPricing: ပိတ်စ Photobooth ဈေးနှုန်းများ 8x8 ပေ - 330000 (စလုံးတို)         8x8 ပေ - 350000 (စလုံးရှည်)     8x10 ပေ - 380000 (စလုံးတို)     8x10 ပေ - 400000 (စလုံးရှည်)   8x12 ပေ - 430000 (စလုံးတို)     8x12 ပေ - 450000 (စလုံးရှည်)           ဗီနိုင်း Photobooth ဈေးနှုန်းများ  8x8 ပေ - 400000                         8x10 ပေ- 450000                        8x12 ပေ - 500000                     10x12 ပေ - 600000                     8x16 ပေ - 900000', 330000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(133, 98, 12, 'Eternal Flowers - Decoration', 'မင်္ဂလာပွဲ၊ လက်မှတ်ရေးထိုးပွဲ၊ စေ့စပ်ပွဲ၊ bridal shower၊ မွေးနေ့ပွဲ၊ ကုမ္ပဏီပွဲ အပြင်အဆင်တိုအတွက် ဆွေးနွေးမေးမြန်းလိုပါက appointment ယူပြီးလာရောက်ဆွေးနွေးနိုင်ပါ တယ်ရှင်။', 500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(134, 99, 12, 'Aphrodite Wedding Planning & Decoration - Decoration', 'မိမိတိုရဲ့ အလှပဆုံး မင်္ဂလာအချိန်လေးကို လစ်ဟာမှုတွေ၊ လိုအပ်ချက်တွေမရှိဘဲ အချိုမြိန်ဆုံးအခိုက်အတန့်တွေကိုသာ အမှတ်တရဖြစ် နေစေဖို ကျွမ်းကျင်တဲ့ Wedding Professional တွေနဲ့အတူ မိမိတိုရဲ့ မင်္ဂလာနေ့ရက်လေးကို အပြည့်အစုံဆုံး ပုံဖော်လိုက်ပါ။\nမိမိတိုရဲ့ တစ်သက်မှတစ်ခါ ရင်အခုန်ရဆုံးနဲ့ အလှပဆုံး နေ့ရက် လေးအတွက် အကောင်းဆုံး Service အကောင်းဆုံး Quality တွေအပြင် ကျွမ်းကျင် Professional Wedding Planner တွေနဲ့ မိမိတိုရဲ့ ပွဲ ကို စိတ်အေးရချင်တယ်ဆိုရင်တော့ Aphrodite Wedding Planning and Decoration ကို အခုပဲရွေးချယ်လိုက်ပါ။\n\nPricing: ဈေးနှုန်းအကြမ်းဖျင်းအားဖြင့်\n- structure\n- floral decoration\n- lighting\n- sound system\n- ဘိသိက်ခွင် တွေ အတွက်ကို သိန်း 400 နဲ့ 500 ဝန်းကျင်ကြားလောက်မှာဆိုရင် ပုံမှန်မြိုင်တယ်ဆိုတဲ့ design ပုံစံမျိုးလေးတွေရနိုင်ပါတယ်ရှင့်။\n\nအကယ်၍ ကိုယ်ကပွဲအတွက် လုံးဝထည်ချင်တယ်၊ မြိုင်ချင်တယ်ဆိုရင်တော့ လုပ်မယ့် design ပေါ်လိုက်ပြီး ကုန်ကျစရိတ်လေးတွေရှိနိုင်ပါတယ်ရှင့်။\n\nတကယ်လို လျာထားတဲ့ budget ရှိတယ်ဆိုရင်လည်း အဲ့ budget ပေါ်မှာလိုက်ပြီး ရနိုင်မယ့် design လေးတွေ ပြန်လုပ်ပေးလိုရပါတယ်ရှင့်။\n\nအခု ဈေးနှုန်းလေးကတော့ အကြမ်းဖျင်းဈေးလေးဖြစ်တဲ့အတွက် ကိုယ်လုပ်ချင်တဲ့ design တွေပေါ်၊ သုံးရတဲ့ material တွေပေါ်မူတည်ပြီး ဈေးလေးတွေကတော့ အတိုး၊အလျှော့ ရှိနိုင်ပါတယ်ရှင့်။                                                                                                         Decoration အတွက် ရွေးချယ်ထားတဲ့ designပေါ်မှာ ထွက်လာတဲ့ ဈေးနှုန်းလေးတွေကိုလည်း ကိုယ့်စိတ်ကြိုက် အတိုးအလျှော့လုပ်‌လိုရပါတယ်ရှင့်။\n\nကျန်တဲ့ ပွဲအတွက် လိုအပ်‌တဲ့ Vendor တွေအတွက်ကတော့ Extra charges တွေအနေနဲ့ ဖြစ်မှာပါရှင့်။', 500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(135, 100, 12, 'Elysian Floral Art & Events Planning - Decoration', 'မင်္ဂလာပွဲအတွက် မောင်နှံလေးတွေ သိချင်တာ သိသင့်တာ လိုအပ်တာတွေကို\nလဲ Free Consultation ပြုလုပ်ပေးသွားဦးမှာပါ\nအပ်နှံကြတဲ့ မင်္ဂလာမောင်နှံလေးတွေကို Special Lucky Draw နဲ့\nGifts တွေလဲ ထည့်ပေးဦးမှာဆိုတော့ လာဖြစ်အောင်လာသင့်ပါတယ်ရှင်🥰\nဘဝရဲ့ အမှတ်တရ နေ့ရက်လေးမှာ ပြန်တွေးကြည့်တိုင်း ပျော်ရွှင်နေဖိုဆို မင်\nမင်တို Elysian Team က တာဝန်ယူပါရစေရှင့်. ❤️❤️❤️❤️\n\nPricing: ပန်းအလှဆင် Package ဈေးလေးတွေက \nလက်မှတ်ထိုး ဆွမ်းကျွေးပွဲလေး တွေ အတွက် 30သိန်း ၊ \nHotel Wedding Reception  ကို သိန်း 60 ကနေ မှစပြီးတော့ အမျိုးမျိုး ရှိပါတယ်ရှင့်..လုပ်မည့် နေရာ၊ လုပ်ချင်တဲ့ ပုံစံအပေါ် မူတည်ပြီး ဈေးနှုန်းလေးတွေက အမျိုးမျိုးရှိပါတယ်ရှင့် ၊ \nလုပ်ချင်တဲ့ ဒီဇိုင်း ၊ သုံးချင်တဲ့ budget အပေါ် မူတည်ပြီး ညှိနှိုင်းဆောင်ရွက်ပေးပါတယ်..', 3000000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(136, 101, 12, 'S&S Events and Floral - Decoration', 'S&S (Events & Floral) လိုကြားလိုက်တာနဲ့ ပန်းအလှဆင်ဝန်ဆောင်မှုပဲရှိတယ် ထင်ရင်မှားနေပြီနော်။\nမင်္ဂလာပွဲနဲ့ အခမ်းအနားအမျိုးမျိုးအတွက် ပန်းအလှဆင်ခြင်းဝန်ဆောင်မှု အပြင်\n-ပွဲအခမ်းအနားကျင်းပဖို နေရာရွေးချယ်တာကစလို\n-ပွဲတစ်ခုလုံး အောင်အောင်မြင်မြင်ပြီးမြောက်အောင် အသေးစိတ်ကအစ တာဝန်ယူစီစဉ်ဆောင်ရွက်ပေးနေတာပါ။\nဒါကြောင့် S&S (Events & Floral) မှာ ပန်းအပြင်အဆင်တစ်ခုအတွက်ပဲ မဟုတ်ဘဲ ပွဲတစ်ခုလုံးအတွက် ယုံကြည်စိတ်ချစွာ အပ်နှံနိုင်ပါတယ်ရှင့်။\nS&S (Events & Floral)\nသင့်တော်တဲ့စျေးနှုန်း၊ သာလွန်တဲ့ ဝန်ဆောင်မှုနဲ့ အတူ စေတနာတွေအပိုဆောင်းပြီး အခမ်းအနားအလှဆင်ပေးနေတာ S&S ပါနော်။\n\nPricing: အကြမ်းဖျင်းက သိန်း ၄၀ ကစပြီး ပြင်ပေးလေ့ရှိပါတယ်..လုပ်မည့် နေရာ၊ လုပ်ချင်တဲ့ ပုံစံအပေါ် မူတည်ပြီး ဈေးနှုန်းလေးတွေက အမျိုးမျိုးရှိပါတယ်ရှင့် ၊ \nလုပ်ချင်တဲ့ ဒီဇိုင်း ၊ သုံးချင်တဲ့ budget အပေါ် မူတည်ပြီး ညှိနှိုင်းဆောင်ရွက်ပေးပါတယ်..', 500000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(137, 102, 12, 'His & Hers Events and Wedding Studio - Decoration', 'His & Hers Events Wedding Company Event ပွဲတော်များ : Opening Ceremony Product Launch Birthday Bridal Shower, Baby Shower Anniversary, Staff Party, Gathering, Private Dinner စတဲ့ ပွဲလေးတွေ စီစဉ်လိုလျှင် ဖြစ်ဖြစ် အမှတ်တရကောင်းတွေ ဖန်တီးဖို ပွဲများကို Event Design ဆွဲပေးခြင်း အပြင် အသေးစိတ်ဆွေးနွေးပေးခြင်း ၊ one stop planning service များပါ အသေးစိတ်ဆောင်ရွက်ပေးနေပြီဖြစ်ပါတယ်\nSigning Package, Wedding Package One Stop Planning Services အစီအစဉ်များနှင့်အတူ တစ်သက်မှာတစ်ခါ ကျင်းပမည့် မင်္ဂလာပွဲအတွက် ပွဲစီစဉ်မှုအတွေ့အကြုံများစွာရှိသည့် Event Organizers များနှင့်အတူညှိနှိင်း၍အကောင်းဆုံး ဝန်ဆောင်မှုများအား ရယူနိုင်ပါပြီနော်.\n\nPricing: WEDDING PACKAGE -A (2026) 4,300,000MMK\nBACKDROP (20X9FT) 2,300,000MMK\nPHOTO BOOTH(12X9FT) 650,000MMK\nENTRANCE FLORAL ARCH 450, 000MMK\nSTAGE FLORAL TRAY 550,000MMK\nWELCOME BOARD 180, 000 MMK\nBRIDAL BOUQUET & TWO180, 00OMMK CORSAGES                        \nWEDDING PACKAGE -B (2026) 5,880,000MMK\nBACKDROP (24X9FT) 2,600,000MMK\nPHOTO BOOTH(12X9FT) 650,000MMK\nENTRANCE FLORAL ARCH 500, 000MMK\nSTAGE FLORAL TRAY 700,000MMK\nWELCOME BOARD 200, 000 MMK\nBRIDAL BOUQUET & TWO180, 00OMMK CORSAGES', 4300000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(138, 103, 6, 'Governor’s Residence - Venue', 'Standard Indoor reception Cost: \n110$ to 150$ per a guest\nAdditional Services\n-Venue hall use (few hours)\n-Standard table & chair setup\n-Basic decorations \n-Buffet or plated menu(Standard-110$/Premium-130$to180$ per a guest)\n-Wedding coordinator\n\nOther services: ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။\n\nPricing: Indoor and outdoor hotel', 800000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(139, 104, 6, 'Novotel Yangon Max - Venue', 'A delightful experience awaits you when booking your wedding venue at Novotel Yangon Max. Whether indoor or outdoor, a large-scale banquet, high profile event with personalized butler, or intimate ceremonial gathering, we’ll be at your service to assist and provide helpful suggestions for personalizing this extremely important occasion.\n\nOther services: Yangon Ballroom (Ground floor) မှာ ဧည့်သည် 500 မှ 750 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\nPyay Ballroom (Level 4) မှာ ဧည့်သည် 200 မှ 250 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\nPathein Ballroom (Ground floor) မှာ ဧည့်သည် 100 မှ 180 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\n\nPricing: Pearl Package: မုန့် (၃) မျိုး၊ ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 22\nRuby Package: မုန့် (၄) မျိုး၊ ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 24\nDiamond Package: မုန့် (၅) မျိုး၊ ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 26\nThe package includes:\n-Choice of Snacks, Coffee Tea & Ice Cream\n-4 x Unique Romantic Floral Stand on Stage\n-8 x Romantic Floral Stands Along the Red Carpet\n-Floral Decoration on all the Table\n-Decorated Wedding Cake\n-Red Carpet to the bridal walkway to the wedding stage\n-Usage of one fully setup bridal dressing room\n-1 night stay in a Deluxe Suite including breakfast\n-1 bottle of wine  in the room\n-Backdrop to grace the occasion\n-Food tasting for booking\n-VIP Parking for Bridal Car\n\nBreakfast buffet: 28$ per a person\n\nDinner set package :Chinese Set Menu – at least US$ 40\n(Additional services are the same)', 99000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL);
INSERT INTO `services` (`id`, `supplier_id`, `category_id`, `name`, `description`, `price`, `price_min`, `price_max`, `thumbnail_url`, `is_active`, `booking_type`, `duration_minutes`, `pricing_unit`, `buffer_minutes`, `max_concurrent`, `max_concurrent_package`, `max_concurrent_customize`, `created_at`, `min_lead_days`, `default_start_time`, `default_end_time`) VALUES
(140, 105, 6, 'Sedona Hotel Yangon - Venue', 'Sitting majestically on eight acres of beautifully landscaped gardens, Sedona Hotel Yangon is a 20minute drive away from Yangon International Airport and the bustling city centre. Conveniently located across from Yangon’s first international retail shopping centre, Myanmar Plaza, Sedona 5-star hotel in Yangon is close to iconic attractions such as the Shwedagon Pagoda and Inya Lake.\n\nOther services: Grand Ballroom မှာ ဧည့်သည် 350 ကနေ 600 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\nYankin Room မှာ ဧည့်သည် 200 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\nMindon Room မှာ ဧည့်သည် 150 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\nInya Room မှာ ဧည့်သည် 140 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။', 800000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(141, 106, 6, 'Inya Lake Hotel - Venue', 'Dreaming of a spectacular location for your wedding? The Inya Lake Hotel has a dedicated and professional team. We will help you with the planning, decorations, catering and hostess services, and more to make your wedding truly special.\n\nOther services: Mingalar Hall မှာ ဧည့်သည် 200 ကနေ 500 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\nOutdoor Wedding အတွက် Sunset Terrace, Lake Side Lawn, Pool Side Lawn တို့မှာကျင်းပနိုင်ပြီး ဧည့်သည် 1000 အထိ တည်ခင်းနိုင်ပါတယ်။\n\nPricing: Promise Package: မုန့် (၃) မျိုး၊ ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 14\nJoyous Package: မုန့် (၄) မျိုး၊ ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 15\nRomance Package: မုန့် (၅) မျိုး၊ ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 16', 63000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(142, 107, 6, 'Meliá Yangon - Venue', 'A contemporary hotel with an avant-garde feel located alongside Lake Inya. The hotel offers the finest hospitality with a passion for detail. A first-class hotel in one of the most vibrant cities in Asia, ideal for weddings.\n\nOther services: Grand Ballroom မှာ ဧည့်သည် 350 ကနေ 600 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။Inya Ballroom မှာ ဧည့်သည် 180 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\n\nPricing: မုန့် (၃) မျိုး:  ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 19\nမုန့် (၄) မျိုး:  ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 20\nမုန့် (၅) မျိုး:  ကော်ဖီ၊ လက်ဖက်ရည်၊ ရေခဲမုန့် – US$ 21\nChinese/Asian/Western Set Menu – US$ 35 per a person', 85500.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(143, 108, 6, 'Hotel Yangon - Venue', '**Hotel Yangon, a luxurious business as well as leisure hotel sits majesticallyon a beautifully landscaped garden with a panoramic view of Yangon City.It is strategically located at 8th Mile junction area which is situated with many businessand commercial offices. Our hotel is close to Junction 8 Shopping Center and , just 10 minutes drivefrom Yangon International Airport & 30 minutes driveto famous landmark of Yangon, Myanmar, Shwedagon Pagoda.**\n\nOther services: Royal Ballroom (Level-2) မှာ ဧည့်သည် 220 အထိ တည်ခင်းဧည့်ခံနိုင်ပါတယ်။\n\nPricing: မုန့် (၃) မျိုး၊ ကော်ဖီ/လက်ဖက်ရည်၊ ရေခဲမုန့် – ၁၉,၅ဝဝ ကျပ်\nမုန့် (၄) မျိုး၊ ကော်ဖီ/လက်ဖက်ရည်၊ ရေခဲမုန့် – ၂၁,၅ဝဝ ကျပ်\nမုန့် (၅) မျိုး၊ ကော်ဖီ/လက်ဖက်ရည်၊ ရေခဲမုန့် – ၂၃,၅ဝဝ ကျပ်\n\nLunch package: ထောပတ်ထမင်း(သို့) ဒံပေါက်၊ ကြက်သားဟင်း၊ အသုတ်၊ ဘာလချောင်ကြော်၊ ချဉ်ပေါင်ဟင်းရည်၊ ရေခဲမုန့် – ၂၅,ဝဝဝ ကျပ်', 800000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(144, 109, 11, 'Myanmar Car Rental - Car Rental', 'We,Myanmar Car Rental provides the best Myanmar car rental services with very reasonable prices. Myanmar car rental services offer car hire services in Yangon, Mandalay, Bagan,Taunggyi Inle lake regions and also for other areas. We offer myanmar car rental services with Saloon cars, Vans, Hiaces & Buses.Myanmar car rental vehicles are very safe and chauffeurs very good experienced. Don\'t wait to book Myanmar car rentals with Us.\n\nPricing: Half day $30-50 \nFull day $50-70', 135000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(145, 110, 11, 'The Experience Rent A Car - Car Rental', 'The Experience Rent A Car Automobile leasing & Limousine\nCompany’s one of leading companies with fleet services in Myanmar,\nfounded in 2014 and it provides vehicle leasing to personal and business customers throughout Myanmar including limo services.Our company offers thousands of competitive leasing deals on brand\nnew vehicles across a wide range of manufacturers and models. We’re part of a group with a proven track record of excellence customer service\nand a reputation for providing friendly, efficient and professional\nservice to personal and business customers alike. Our head office is located in Yangon, we’re part of the multi\naward-winning Fleet Alliance. Collectively, we manage over 400 vehicles\nfor all customer types from private all the way up to large\nmultinational companies in Myanmar which includes Airlines, NGOs and\nEmbassies for the services of leasing including Limo & FIT/GIT\ntours.**“Live in journeys for your splendid Life with us”**\n\nPricing: 3 hour 180k', 180000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(146, 111, 11, 'AVIS MYANMAR - Car Rental', 'Avis is a leading global car rental brand recognized over 5,000 locations in more than 170 countries around the world. “We stand ready to tailor the perfect corporate travel package for you.” With years of experience in the car rental, lease and chauffeur-drive\nservice industry, we know what is needed to make your trip smooth,\neffortless and enjoyable. We specialise in multiple travel services, making us yourone-stop preferred partner in**Corporate Short-term Car Rental Avis Lease – Long-term Car RentalChauffeur-drive ServiceAvis PrestigeCommercial Vehicle Rental and LeaseGlobal Mobility Service**\n\nPricing: 24h-80.75USD     24h-60.50USD                    24h-90.25USD', 100000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(147, 112, 11, 'inoventure - Car Rental', 'INOVenture is a premier car rental service based in Yangon, Myanmar. We are committed to providing a seamless, convenient, and enjoyable transportation experience for all our clients. We offer a diverse fleet of well-maintained cars ranging from compact sedans and SUVs to luxury vehicles.\n\nWe have flexible rental plans and competitive pricing to match your needs. Our chauffeurs are professionally trained and equipped with a strong sense of safety to ensure quality travel experience for you.\n\nPricing: Half day $30-60\nFull day $60-80', 135000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(148, 113, 11, 'Concierge Business Limousine - Car Rental', '**W**hether you’re traveling for business, getting ready for your special day, or planning a big night out on the town, the last thing you want to worry about is ground transportation. One of Golden land city’s oldest and most respected names in luxury transportation, Concierge business Limousines is the premier provider of ground transportation for corporate, wedding and leisure activities in Golden land city.**S**tarted in 2009, Concierge business Limousines has one of the most diverse fleets of chauffeured limousines, Town Cars, and buses, So if you need ground transportation to wherever of Golden land Myanmar , a luxurious limousine for your wedding day, or leisure day to celebrate a special time with your friends, concierge business Limousines will take care of all the details so you can focus on other things.\n\nPricing: Half Day 55k                       Full Day 90k', 55000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(149, 114, 8, 'Elegant Star (Recommended) - Invitation & Gifts', 'မင်္ဂလာပါရှင့် 𝗘𝗹𝗲𝗴𝗮𝗻𝘁 𝗦𝘁𝗮𝗿 𝗪𝗲𝗱𝗱𝗶𝗻𝗴 𝗦𝘁𝗮𝘁𝗶𝗼𝗻𝗲𝗿𝘆 𝗦𝗲𝗿𝘃𝗶𝗰𝗲 မှ ကြိုဆိုပါတယ်။\nMarriage Certificates ၊လက်ထပ်စာချုပ် ၊\nInvitation cards ဖိတ်စာ ၊\nWedding Gift Box ငွေသား ၊  လက်ဖွဲ့ပုံး ၊\nမင်္ဂလာပြန်ကမ်း ၊\nWedding Guest Book ၊\nVows Books ၊\nSigning pens၊ \nCanvas Fingerprint Tree ၊(Customization avaliable) ၊\nAcrylic Photobooth & Welcomeboard Services များကို Customized အပ်နှံနိုင်ပါတယ်။ \nOpening hours 9:00 AM - 5:30 PM\n\nOther services: wedding guest book, Vows Books, Signing pens, Canvas Fingerprint Tree, Acrylic Photobooth & Welcomeboard\n\nPricing: - start from 3200 Kyats above 300 invitations.       -   can order from 100 invitations \n-  prices can be different based on design and orders', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(150, 115, 8, 'Memory Memory Handmade invitation cards and gifts (Recommended) - Invitation & Gifts', 'သူမတူတဲ့ ဖန်တီးမှုလေးတွေနဲ့ အတူ\nမင်မင်တို့ပေးနိုင်တာ အကောင်းဆုံးဝန်ဆောင်မှုပါ\n❤️ စိတ်တိုင်းကျတဲ့အထိ ဒီဇိုင်းလေးတွေ font လေးတွေကိုပြင်ပေးပါတယ်\n❤️ စိတ်တိုင်းကျဖြစ်ပြီဆို sample တစ်စောင်ထုတ်ပြပြီး ဖဲပြားအရောင်လေးနဲ့တိုက်ပေးပါတယ်် အားလုံးစိတ်တိုင်းကျပြီဆိုမှ မင်မင်တို့က ဖိတ်စာလေးကိုထုတ်ပေးပါတယ်\n❤️ မင်မင်တို့က offset မဟုတ်ပဲ printing service ပဲမို့ အစောင်ရေ အနဲလည်းလက်ခံဆောင်ရွက်ပေးပါတယ် အစောင်ရေ ၁၀၀ အောက်တော့ design fees လေး ၅၀၀၀ ယူပါတယ်\n❤️ online ကနေ စိတ်တိုင်းကျမှာယူနိုင်သလို အိမ်မှာဘာရောက်ဆွေးနွေးချင်လည်း လှိုင်မြို့နယ်မှာပါရှင့်\n❤️ လာကြည့်မယ်ဆို ဖုန်းလေးကြိုဆက်ပေးဖို့တော့ မေတ္တာရပ်ခံပါတယ်ရှင်\n\nPricing: design fees : 5000 below 100 invitations\n\nfees can be varied on design.', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(151, 116, 8, 'Moe Kaung Kin - Invitation & Gifts', 'Pricing: Invitation Cards & Gifts', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(152, 117, 8, 'Y Collection - Invitation & Gifts', 'Celebrate your Special occasions with us! We’ve been creating Invitation cards with love for more than 10 years!\n\nOpening Hours : 10:00am to 5:00pm (Mon - Sat)\nCloses every Sunday and Gazette Holidays.…', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(153, 118, 8, 'Paperie Tale (Recommended) - Invitation & Gifts', 'တစ်သက်မှာ တစ်ခါပဲ ပြုလုပ်ခွင့်ရမယ့် မင်္ဂလာပွဲလေးအတွက် ကိုကို သဲသဲ ဖေဖေ မေမေတို့ စိတ်တိုင်းကျ အသေးစိတ်ထိ လိုချင်တဲ့ design နဲ့အတူ မိမိ wedding ပွဲရဲ့ theme နဲ့ လိုက်ဖက်မယ့်  စိတ်ကူးထဲက အိမ်မက်လေးတွေကို အပြင်မှာ လက်တွေ့ ဖန်တီးပေးဖို့အတွက် Paperie team က ready ဖြစ်နေပါပြီ 🪄✨\n\nPage မှာလဲ Tale တို့ လုပ်ထားပြီးသား creation တွေ ဖြစ်တဲ့ စာချုပ်တွေ နဲ့ 💌 ဖိတ်စာဒီဇိုင်းလေးတွေ, 𝑷𝒆𝒓𝒔𝒐𝒏𝒂𝒍𝒊𝒛𝒆𝒅 𝑴𝒐𝒏𝒐𝒈𝒓𝒂𝒎, 𝑪𝒂𝒍𝒍𝒊𝒈𝒓𝒂𝒑𝒉𝒚, နဲ့ အချား 𝑾𝒆𝒅𝒅𝒊𝒏𝒈 𝑺𝒕𝒂𝒕𝒊𝒐𝒏𝒆𝒓𝒚 မျိုးစုံကိုဝင်ကြည့်ပြီး စတင်ရွေးချယ်နိုင်ပါပြီ 💕\n\nလက်ထပ်စာချုပ် နဲ့  ဖိတ်စာလေးတွေမှာယူချင်တယ်ဆိုရင်တော့ 𝑪𝒖𝒔𝒕𝒐𝒎 𝑫𝒆𝒔𝒊𝒈𝒏 ဆို အနည်းဆုံး 1 လခွဲမှ 2လထိ ကြိုတင် book ပေးဖို့လိုအပ်မှာ ဖြစ်ပြီး ရှိပြီး ဒီဇိုင်းတွေထဲကရွေးချယ်မယ်ဆိုရင်တော့  1 လ ဝန်းကျင်တော့ ကြိုတင် booking ယူပေးဖို့ မေတ္တာရပ်ခံပါတယ်ရှင့်! 🙌🏼\n\nWedding Season ကြီးကို ရောက်လာပြီမို့ Taleတို့ design နဲ့မှ စိတ်ချမ်းသာမယ့် မောင်နှံတို့‌ကတော့ စောစော ကြိုပြီး slot တွေ မကုန်ခင် reserve လုပ်ထားဖို့ လိုပါမယ်နော်။\n\nDesign team နဲ့ တစ်ခါထဲဆွေးနွေးရင်း ကိုယ်တိုင်လာကြည့်ပြီး စိတ်ကြိုက်ရွေးချယ်ချင်တဲ့ customer တွေအတွက် Showroom မှာ Appointment Only နဲ့ စတင် လက်ခံပေးနေတာမို့လို့ Page Messenger ကနေ အရင်ဆုံး Appointment ယူပြီး လာခဲ့ပေးနော်! 💖\n\nOther services: Marriage Certification Folder, Wedding Monogram design, Vow Book,\nPlace cards,\nMenu cards,\nSignage Board,\nSigning Pens,\nRing box,\nWax Seal & Wax stamps\n\nPricing: appointment', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(154, 119, 8, 'THIRI Handmade Invatation - Invitation & Gifts', 'Thiri Handmade ကို ယုံကြည်စွာ ရွေးချယ်ပေးတဲ့အတွက် မဂ်လာမောင်နှံ ကိုအထူးကျေးဇူးတင်ပါတယ်ရှင်\n\nမဂ်လာမောင်နှံ တို့သည်လည်း မဂ်လာရက်မြတ်မှစ နှစ်တရာတိုင် ပျော်ရွင်ချမ်းမြေ့ကြပါစေ..\n\nတရားရုံး + လက်ထပ်စာချုပ် အသေးစိတ်စုံစမ်းလိုပါက\n09798949195/ 09772244608(viber) သို့ဆက်သွယ်စုံစမ်းနိုင်ပါတယ်\n\nOther services: တရားရုံး', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(155, 120, 8, 'Pyan Kann - Invitation & Gifts', 'စိတ်ကူး အိပ်မက်ထဲကအတိုင်း ပြီးပြည့်စုံတဲ့ မင်္ဂလာပွဲလေးဖြစ်ဖို့ အသေးစိတ်ပုံဖော်ချင်တဲ့ မင်္ဂလာမောင်နှံတိုင်းအတွက် Team Pyan Kann ကအမြဲအဆင်သင့်ရှိပါတယ်ရှင့်ပြန်ကမ်းလက်ဆောင်လေးတွေဆိုတာ မင်္ဂလာမောင်နှံ၂ဦးနှင့် ပွဲကို အချိန်ပေးတက်ရောက် ဂုဏ်ပြုမင်္ဂလာပေးကြတဲ့ မိတ်သဟာတွေအတွက် ချစ်ခြင်းမေတ္တာတွေကိုဖော်ပြပေးတဲ့ အကောင်းဆုံး ကျေးဇူးတုံ့ပြန်ရတဲ့ ကြားခံမေတ္တာလက်ဆောင်လေးတွေပါ သေချာပြင်ဆင်ထားတဲ့ ပြန်ကမ်းလေးတွေ ရတဲ့သူတိုင်းက အသေးစိတ်ကအစ ဂရုစိုက်ပြင်ဆင်တက်တဲ့မောင်နှံတွေကို အလွန် အထင်ကြီးလေးစားကြပါတယ် ဒါ့ကြောင့် မင်္ဂလာပွဲတပွဲမှာ ပြန်ကမ်းလက်ဆောင်လေးတွေဟာ သတို့သား သတို့အမီးတို့ရဲ့ မေတ္တာ၊စေတနာကို ဖော်ပြတဲ့ အမှတ်တရလက်ဆောင်တခုအဖြစ် ပွဲတက်မိတ်သဟာတွေစီကို တိုက်ရိုက်ရောက်ရှိသွားမယ့် First Impression တခုဖြစ်တာကြောင့် အရေးကြီးတဲ့အခန်းကဏ္ဍ ကပါဝင်နေပါတယ်  Pyan Kann မှာ Best Seller အဖြစ်ဆုံး products Menu လေးတွေတင်ပေးလိုက်ပါတယ်ရှင့် Team Pyan Kann က Quantity ထက် Quality ကိုတန်ဖိုးထားတဲ့ team တခုဖြစ်တာကြောင့် လူကြီးမင်းတို့ရဲ့ အဖိုးတန်မင်္ဂလာပွဲမှာ အလှဆုံးပြန်ကမ်းလက်ဆောင်လေးတွေကို စေတနာအပြည့်ဖြင့် ပွဲအတွေ့အကြုံများစွာရှိတဲ့လက်မှုုပညာရှင်များကိုယ်တိုင် လက်ရာအထူးလှပသေသပ်စွာ တာဝန်ယူပေးပါရစေရှင့် Direct Message Us for more details 𝐄𝐯𝐞𝐧𝐭 𝐆𝐢𝐟𝐭 တွေအတွက် အလှဆုံးလေး ဖန်တီးပေးမယ့် \" ပြန်ကမ်း \"𝐄𝐯𝐞𝐧𝐭 တွေပြုလုပ်တဲ့အခါ ပြန်လည်ရရှိခဲ့တဲ့ 𝐄𝐯𝐞𝐧𝐭 𝐆𝐢𝐟𝐭 လေးတွေကလည်း ဒီအချိန်ကာလကို အမြဲတမ်းတမိနေစေတဲ့ အမှတ်တရပစ္စည်းလေးတွေပါပဲ တကယ်လို့ သင်ဟာ 𝐄𝐯𝐞𝐧𝐭 အတွက်စီစဥ်ရင်း.... အမှတ်တရလည်းဖြစ်ရမယ်  Budget လည်း သက်သာရမယ်  အရည်အသွေးလည်း ကောင်းရမယ် ဒီဇိုင်းကလည်း အများနဲ့မတူ တမူထူးခြားခြားနေရမယ် ဒီလို အချက်လေးတွေနဲ့ ကိုက်ညီတဲ့ 𝐄𝐯𝐞𝐧𝐭 𝐆𝐢𝐟𝐭 လေးတွေကိုမှ ရရှိလိုတယ်ဆိုရင်တော့...ဒါတွေအားလုံးကို တစ်နေရာထဲမှာ ပြီးပြည့်စုံစွာ ရရှိနိုင်မယ့် \" ပြန်ကမ်း \" ရှိတယ်နော် \" ပြန်ကမ်း \" နဲ့သာဆို 𝐄𝐯𝐞𝐧𝐭 𝐆𝐢𝐟𝐭 လှလှလေးတွေနဲ့အတူ ပျော်ရွှင်စရာ အမှတ်တရကောင်းတွေကို ဖန်တီးနိုင်မှာ အသေအချာပါပဲ\n\nOther services: invitation cards, wedding vows\n\nPricing: appointment', 50000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(156, 121, 10, 'SORA - Makeup & Hair', 'Makeup SORA သည် မြန်မာနိုင်ငံရှိ ထိပ်တန်း Celebrity မိတ်ကပ်ပညာရှင်တစ်ဦးသာမကသူမသည် မိတ်ကပ်ပညာရပ်ကို ထိုင်းနိုင်ငံ တွင် စနစ်တကျ သွားရောက်သင်ယူခဲ့သူဖြစ်သည်မြန်မာနိုင်ငံ၏ \"အကောင်းဆုံး Celebrity မိတ်ကပ်ပညာရှင်\" (Best Celebrity Makeup Artist) ဆုကို ရရှိထားသူဖြစ်ပြီး၊ အဓိကအားဖြင့် သတို့သမီး မိတ်ကပ် (Bridal Makeup) ပြင်ဆင်မှုတွင် အလွန်နာမည်ကြီးသောကြောင့်သူမကိုယ်တိုင် တည်ထောင်ထားသော SORA Professional Makeup Academy သည် မိတ်ကပ်ပညာဖြင့် အသက်မွေးဝမ်းကြောင်းပြုလိုသူ အများအပြားကို မွေးထုတ်ပေးနေပါသည်။\n\nPricing: 89or90', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(157, 122, 10, 'ကိုသာဂိ - Makeup & Hair', 'ကိုသာဂိ (Ko Thar Gi) ကမြန်မာနိုင်ငံ မိတ်ကပ်လောကတွင် ဆယ်စုနှစ် ၂ ခုကျော်တိုင် အောင်မြင်မှု ထိန်းသိမ်းထားနိုင်သော ဝါရင့် Master ပညာရှင်ကြီး တစ်ဦး ဖြစ်တာကြောင့်သူသည် Star Way (Ko Thar Gi) အမည်ဖြင့် မိတ်ကပ်စတူဒီယိုနှင့် သင်တန်းကျောင်းကို ဦးဆောင်နေသူ ဖြစ်ပြီးအနုပညာရှင်များ၏ အားကိုးရာမြန်မာနိုင်ငံ၏ ထိပ်တန်းမင်းသမီးများဖြစ်သော အိန္ဒြာကျော်ဇော်၊ သက်မွန်မြင့်၊ ရွှေမှုံရတီ နှင့် အခြား အနုပညာရှင်များစွာကို အရေးကြီးသော ပွဲတက်မိတ်ကပ်နှင့် အကယ်ဒမီမိတ်ကပ်များ ဖန်တီးပေးလေ့ရှိသူ ဖြစ်သည်။ ကိုသာဂိသည် \"Classic & Elegant\" ဖြစ်သော မိတ်ကပ်စတိုင်တွင် အထူးကျွမ်းကျင်သည်။ မျက်နှာသွင်ပြင်ကို ရင့်ရော်မသွားစေဘဲ နုပျိုသန့်ရှင်းပြီး တင့်တယ်သော (Sophisticated Look) ကို ဖန်တီးပေးနိုင်ခြင်းမှာ သူရဲ့Signature ဖြစ်သည်။\n\nPricing: 50or60', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(158, 123, 10, 'Ma Htet-pop soul - Makeup & Hair', 'မြန်မာနိုင်ငံ၏ ထိပ်တန်းမင်းသမီးများ၊ အဆိုတော်များနှင့် မော်ဒယ်လ်များစွာ၏ မိတ်ကပ်ကို တာဝန်ယူပြင်ဆင်ပေးရင်း \"Ma Htet - Pop Soul\" ဆိုလျှင် လူမသိသူမရှိအောင် နာမည်ကြီးလာကာသူမသည် မြန်မာနိုင်ငံတွင် Transwoman တစ်ဦးအဖြစ် ပွင့်လင်းမြင်သာစွာ ရပ်တည်ပြီး အောင်မြင်မှုရရှိနိုင်ကြောင်း သက်သေပြခဲ့သူဖြစ်သဖြင့် LGBTQ+ အသိုင်းအဝိုင်းအတွက် Role Model တစ်ဦးလည်း ဖြစ်သည်။\n\nPricing: 85or90', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(159, 124, 10, 'Lin Lin - Makeup & Hair', 'မိတ်ကပ်ပညာကို စနစ်တကျ သင်ယူချင်သူများအတွက် Lin Lin Makeup Academy ရှိသလို၊ ထူးခြားဆန်းသစ်တဲ့ Look တွေကို ပိုင်ဆိုင်ချင်တဲ့ ပွဲတက်သတို့သမီးများအတွက်လည်း Lin Lin က အနီးကပ် ရှိနေမှာပါ။ Color Theory နှင့် Face Anatomy အခြေခံကာ လူတစ်ဦးချင်းစီနဲ့ အလိုက်ဖက်ဆုံး အလှတရားတွေကို ဖန်တီးပေးနေသည့် သူမ၏ လက်ရာများကို Lin Lin Facebook Page တွင် လေ့လာနိုင်ပါသည်။\n\nPricing: 50or75', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(160, 125, 10, 'make up Kin San Win - Makeup & Hair', 'ခင်စန်းဝင်း (Kin San Win) ကမြန်မာနိုင်ငံ မိတ်ကပ်မှာဆိုဂန္ထဝင် (Legendary) မိတ်ကပ်ပညာရှင်ကြီးတစ်ဦး ဖြစ်တာကြောင့်မြန်မာ့အလှအပရေးရာ နယ်ပယ်ကို ခေတ်မီလာအောင် ပြောင်းလဲပေးခဲ့တယ်မိတ်ကပ်ပညာရှင်သာမကLGBTQ+ Icon: ပွင့်လင်းမြင်သာစွာ ရပ်တည်နေသည့် Transwoman တစ်ဦးအဖြစ် သူမ၏ ကျွမ်းကျင်မှုနှင့် ပတ်သက်၍ လူအများ၏ လေးစားမှုကို အပြည့်အဝ ရရှိထားသူ ဖြစ်သည်။ ကျွမ်းကျင်မှုနှင့် စတိုင်လ် Signature Look: မြန်မာ့ရိုးရာ သတို့သမီး မိတ်ကပ် (Traditional Myanmar Bridal) နှင့် အဆင့်မြင့် ပွဲတက်မိတ်ကပ် (Heavy Glamour) ပြင်ဆင်မှုတွင် ဆရာတစ်ဆူ ဖြစ်သည်။ မိတ်ကပ်လိမ်းခြယ်မှုသာမက ဆံပင်ပုံစံဖန်တီးမှု (Hair Styling) နှင့် ဖက်ရှင်အကြံပေးမှုများတွင်လည်း ထိပ်တန်းမင်းသမီးများ၏ အားကိုးရာ ဖြစ်သည်။\n\nPricing: 96or99', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(161, 126, 10, 'Magic Touch Beauty Boutique - Makeup & Hair', 'Pricing: 10', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(162, 127, 10, 'Chi Chi’s Touch - Makeup & Hair', 'Pricing: 13', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(163, 128, 10, 'Makeup Hazel - Makeup & Hair', 'Pricing: 10', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero1.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(164, 129, 10, 'Makeup Non Thit San - Makeup & Hair', 'Pricing: 10', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero2.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(165, 130, 10, 'Sweet Hair& Make up - Makeup & Hair', 'Pricing: 15', 150000.00, NULL, NULL, 'http://localhost/GP/public/uploads/serviceHero3.png', 1, 'fullday', NULL, 'per_session', 0, 2, 1, 0, '2026-06-20 07:52:35', 0, NULL, NULL),
(166, 131, 2, 'Y\'s dress', 'We provide accessories', 250000.00, 250000.00, 850000.00, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/suppliers/131/service-management/service/20260624085706-a41e8881.jpg', 1, 'slot', 240, 'per_session', 0, 3, 2, 2, '2026-06-24 02:27:06', 3, NULL, NULL),
(167, 134, 2, 'Grand', 'Hello', 450000.00, 450000.00, 2500000.00, '', 1, 'slot', 60, 'per_session', 0, 1, 0, 0, '2026-06-26 03:36:14', 12, '10:05:00', '13:05:00'),
(170, 20, 3, 'Eldora', 'El Dorado (အဲလ်ဒိုရာဒို) အကြောင်းEl Dorado သည် ရန်ကုန်မြို့တွင် လူကြိုက်များသော Bakery & Cake Shop တစ်ခုဖြစ်ပြီး နေ့စဉ်လတ်ဆတ်သော ကိတ်မုန့်များ၊ မုန့်ဖုတ်ပစ္စည်းများနှင့် ပေါင်မုန့်များကို ရောင်းချလျက်ရှိသည်။🎂 ထူးခြားချက်များမွေးနေ့၊ မင်္ဂလာပွဲ၊ နှစ်ပတ်လည် အခမ်းအနားများအတွက် Custom Cake များ လက်ခံပြုလုပ်သည်နေ့စဉ်ထုတ်လျက်ရှိသော Pastry၊ Bread၊ Specialty Cakesဝန်ဆောင်မှုကောင်းမွန်ပြီး သန့်ရှင်းသပ်ရပ်သော ဆိုင်ပတ်ဝန်းကျင်အရသာကောင်းမွန်မှုနှင့် လူကြိုက်များမှုကြောင့် Yangon တွင် အကြိုက်ဆုံး Bakery အဖြစ် သိရှိခံရသည်🍰 El Dorado သည် အမှန်တကယ် အရသာနှင့် အရည်အသွေးကို အားထားသူများအတွက် သင့်တော်သည့် Bakery ဖြစ်ပြီး၊ သင့် မွေးနေ့၊ မင်္ဂလာပွဲ နှင့် အခြား အခမ်းအနားများအတွက် အကောင်းဆုံးရွေးချယ်မှုတစ်ခုဖြစ်သည်။', 0.00, 0.00, 0.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260627150931-3840189c.jpg', 1, 'slot', 480, 'per_session', 0, 1, 1, 1, '2026-06-27 08:39:31', 4, NULL, NULL),
(171, 20, 2, 'Dear Brides Wedding Dress Studio', 'ကျွန်မတို့ Studio ရဲ့ ထူးခြားချက်များကျယ်ဝန်းသော Studio နှင့် ကားပါကင်: စိတ်အေးချမ်းသာစွာ ဝတ်စုံရွေးချယ်နိုင်ဖို့ ကျယ်ဝန်းသပ်ရပ်တဲ့ Studio အပြင်၊ လာရောက်သူတိုင်းအတွက် အဆင်ပြေစေမယ့် ကိုယ်ပိုင်ကားပါကင်လည်း စီစဉ်ပေးထားပါတယ်။ကျွမ်းကျင် Stylist များ: သတို့သမီးတစ်ဦးချင်းစီရဲ့ အလှတရားကို ပေါ်လွင်စေဖို့ အတွေ့အကြုံရှိ Stylist များက ဝတ်စုံရွေးချယ်မှုမှသည် ပုံဖော်ပေးမှုအထိ အနီးကပ်အကြံပြုပေးမှာပါရှင်။Photo Studio နှင့် တစ်ဆက်တည်း: Studio အတွင်းမှာတင် Photo Studio ပါရှိလို့ အချိန်မကုန် လူမပင်ပန်းဘဲ တစ်ခါတည်း Photoshoot နိုင်တာက ကျွန်မတို့ရဲ့ အားသာချက်ပါပဲရှင်။', 1000000.00, 1000000.00, 3000000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260627151756-619c2298.jpg', 0, 'fullday', 60, 'per_session', 0, 1, 0, 0, '2026-06-27 08:47:56', 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_availability`
--

CREATE TABLE `service_availability` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `type` enum('available','unavailable','custom_hours') NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_availability`
--

INSERT INTO `service_availability` (`id`, `service_id`, `date`, `type`, `open_time`, `close_time`, `reason`, `created_at`) VALUES
(11, 56, '2026-06-21', 'unavailable', NULL, NULL, NULL, '2026-06-21 07:46:22'),
(12, 166, '2026-07-01', 'unavailable', NULL, NULL, 'owner\'s birthday', '2026-06-24 02:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `service_media`
--

CREATE TABLE `service_media` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `type` enum('image','video') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_media`
--

INSERT INTO `service_media` (`id`, `service_id`, `file_url`, `type`) VALUES
(72, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103236-1661e8a3.jpg', 'image'),
(73, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103239-fb232b73.jpg', 'image'),
(74, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103243-cf567784.jpg', 'image'),
(75, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103247-23bbfb51.jpg', 'image'),
(76, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103251-c8b843d7.jpg', 'image'),
(77, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103259-e7cfcd2b.jpg', 'image'),
(78, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103303-62e2f364.jpg', 'image'),
(79, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103307-951e5978.jpg', 'image'),
(100, 48, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618210337-4e038754.png', 'image'),
(101, 48, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618210344-2247d48e.png', 'image'),
(102, 48, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618210350-59732434.png', 'image'),
(103, 48, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618210357-8b987eca.png', 'image'),
(104, 48, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618210403-3e860638.png', 'image'),
(105, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618212704-d7d4e53b.jpg', 'image'),
(106, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618212709-91883f58.jpg', 'image'),
(107, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618212715-3abe2509.jpg', 'image'),
(108, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618212720-d0fedf42.jpg', 'image'),
(109, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618212725-9a4a310e.jpg', 'image'),
(110, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618212733-ec9f14fe.jpg', 'image'),
(111, 50, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260619040856-93083e43.png', 'image'),
(112, 50, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260619040902-07a42ba0.png', 'image'),
(113, 50, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260619040905-d182399e.png', 'image'),
(114, 50, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260619040910-212336b9.png', 'image'),
(115, 50, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260619040914-812823b7.png', 'image'),
(116, 50, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260619040918-03a03cbd.png', 'image'),
(117, 55, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054320-6df321ce.jpg', 'image'),
(118, 55, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054327-2aac792c.jpg', 'image'),
(119, 55, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054332-c6aa260e.jpg', 'image'),
(120, 55, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054340-cd5874be.jpg', 'image'),
(121, 43, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054737-38e0d332.png', 'image'),
(122, 43, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054741-3dc27fc0.png', 'image'),
(123, 43, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054746-16c69297.png', 'image'),
(124, 43, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054750-b1039d9c.png', 'image'),
(125, 43, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054754-91f4eb68.png', 'image'),
(126, 56, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620071704-82f4bd85.jpg', 'image'),
(127, 56, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620071708-9ef6f551.jpg', 'image'),
(128, 56, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620071712-637bbf46.jpg', 'image'),
(129, 56, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620071716-83feb39e.jpg', 'image'),
(130, 57, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620090335-8798b241.jpg', 'image'),
(131, 57, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620090338-6482a308.jpg', 'image'),
(132, 57, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620090342-ec734ac6.jpg', 'image'),
(133, 57, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260620090346-8ca5ca98.jpg', 'image'),
(134, 105, 'http://localhost/GP/public/uploads/suppliers/70/service-management/media/20260620170527-ab5f9505.png', 'image'),
(135, 105, 'http://localhost/GP/public/uploads/suppliers/70/service-management/media/20260620170533-557c294a.png', 'image'),
(136, 105, 'http://localhost/GP/public/uploads/suppliers/70/service-management/media/20260620170538-7efe2c09.png', 'image'),
(137, 105, 'http://localhost/GP/public/uploads/suppliers/70/service-management/media/20260620170542-56bad5ef.png', 'image'),
(138, 105, 'http://localhost/GP/public/uploads/suppliers/70/service-management/media/20260620170548-2cb0c732.png', 'image'),
(139, 105, 'http://localhost/GP/public/uploads/suppliers/70/service-management/media/20260620170552-de2f6d92.png', 'image'),
(141, 166, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/suppliers/131/service-management/media/20260624110739-5c1b9ea5.jpg', 'image'),
(142, 166, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/suppliers/131/service-management/media/20260624110752-f37bb4b2.jpg', 'image'),
(143, 166, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/suppliers/131/service-management/media/20260624110830-3bd7de88.jpg', 'image'),
(144, 167, 'https://4b2b50ea8de75a5e-204-157-173-127.serveousercontent.com/GP/public/uploads/suppliers/134/service-management/media/20260626104300-56c10968.png', 'image'),
(145, 49, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627134704-d1f5737f.jpg', 'image'),
(149, 170, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627150940-a53e07d3.png', 'image'),
(150, 170, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627150943-86b04556.png', 'image'),
(151, 170, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627150946-5875f662.png', 'image'),
(152, 170, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627150952-fe48c78b.png', 'image'),
(153, 171, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627151803-299a3be7.jpg', 'image'),
(154, 171, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627151806-fbdddfd0.jpg', 'image'),
(155, 171, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627151809-63068888.jpg', 'image'),
(156, 171, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260627151813-6690a1dd.jpg', 'image');

-- --------------------------------------------------------

--
-- Table structure for table `service_rental_pricing`
--

CREATE TABLE `service_rental_pricing` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `borrow_package_price` decimal(12,2) DEFAULT NULL,
  `borrow_customize_price` decimal(12,2) DEFAULT NULL,
  `borrow_price` decimal(12,2) DEFAULT NULL,
  `return_days` int(11) DEFAULT NULL,
  `buy_package_price` decimal(12,2) DEFAULT NULL,
  `buy_customize_price` decimal(12,2) DEFAULT NULL,
  `buy_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_rental_pricing`
--

INSERT INTO `service_rental_pricing` (`id`, `service_id`, `borrow_package_price`, `borrow_customize_price`, `borrow_price`, `return_days`, `buy_package_price`, `buy_customize_price`, `buy_price`, `created_at`) VALUES
(9, 55, 400000.00, 410000.00, 400000.00, 1, 500000.00, 500000.00, 500000.00, '2026-06-19 03:42:00'),
(10, 57, 750000.00, 790000.00, 750000.00, 3, 1000000.00, 1000000.00, 1000000.00, '2026-06-20 07:02:52'),
(11, 166, 150000.00, 220000.00, 150000.00, 3, 850000.00, 1200000.00, 850000.00, '2026-06-24 02:27:06'),
(12, 167, 150000.00, 220000.00, 150000.00, 2, 230000.00, 230000.00, 230000.00, '2026-06-26 03:36:14'),
(14, 171, 1000000.00, 1100000.00, 1000000.00, 3, 1500000.00, 1500000.00, 1500000.00, '2026-06-27 08:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `service_schedules`
--

CREATE TABLE `service_schedules` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_schedules`
--

INSERT INTO `service_schedules` (`id`, `service_id`, `day_of_week`, `open_time`, `close_time`, `is_available`, `created_at`) VALUES
(365, 42, 1, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(366, 42, 2, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(367, 42, 3, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(368, 42, 4, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(369, 42, 5, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(370, 42, 6, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(371, 42, 7, '09:00:00', '17:00:00', 1, '2026-06-18 08:33:11'),
(421, 48, 1, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(422, 48, 2, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(423, 48, 3, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(424, 48, 4, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(425, 48, 5, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(426, 48, 6, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(427, 48, 7, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(456, 55, 1, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(457, 55, 2, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(458, 55, 3, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(459, 55, 4, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(460, 55, 5, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(461, 55, 6, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(462, 55, 7, '09:00:00', '17:00:00', 1, '2026-06-19 13:00:43'),
(512, 57, 1, '09:00:00', '17:00:00', 1, '2026-06-20 07:03:54'),
(513, 57, 2, '09:00:00', '17:00:00', 1, '2026-06-20 07:03:54'),
(514, 57, 3, '09:00:00', '17:00:00', 1, '2026-06-20 07:03:54'),
(515, 57, 4, '09:00:00', '17:00:00', 1, '2026-06-20 07:03:54'),
(516, 57, 5, '09:00:00', '17:00:00', 1, '2026-06-20 07:03:54'),
(517, 57, 6, '09:00:00', '17:00:00', 0, '2026-06-20 07:03:54'),
(518, 57, 7, '09:00:00', '17:00:00', 0, '2026-06-20 07:03:54'),
(526, 59, 1, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(527, 59, 2, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(528, 59, 3, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(529, 59, 4, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(530, 59, 5, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(531, 59, 6, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(532, 59, 7, '09:00:00', '17:00:00', 1, '2026-06-20 07:13:56'),
(533, 60, 1, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(534, 60, 2, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(535, 60, 3, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(536, 60, 4, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(537, 60, 5, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(538, 60, 6, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(539, 60, 7, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(540, 61, 1, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(541, 61, 2, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(542, 61, 3, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(543, 61, 4, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(544, 61, 5, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(545, 61, 6, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(546, 61, 7, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(547, 62, 1, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(548, 62, 2, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(549, 62, 3, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(550, 62, 4, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(551, 62, 5, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(552, 62, 6, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(553, 62, 7, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(554, 63, 1, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(555, 63, 2, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(556, 63, 3, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(557, 63, 4, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(558, 63, 5, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(559, 63, 6, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(560, 63, 7, '09:00:00', '22:00:00', 1, '2026-06-20 07:22:45'),
(571, 65, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(572, 65, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(573, 65, 3, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(574, 65, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(575, 65, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(576, 65, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(577, 65, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(578, 66, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(579, 66, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(580, 66, 3, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(581, 66, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(582, 66, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(583, 66, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(584, 66, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(585, 67, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(586, 67, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(587, 67, 3, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(588, 67, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(589, 67, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(590, 67, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(591, 67, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(592, 68, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(593, 68, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(594, 68, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(595, 68, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(596, 68, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(597, 68, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(598, 69, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(599, 69, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(600, 69, 3, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(601, 69, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(602, 69, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(603, 69, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(604, 69, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(605, 70, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(606, 70, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(607, 70, 3, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(608, 70, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(609, 70, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(610, 70, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(611, 70, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(612, 71, 1, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(613, 71, 2, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(614, 71, 3, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(615, 71, 4, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(616, 71, 5, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(617, 71, 6, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(618, 71, 7, '10:00:00', '18:00:00', 1, '2026-06-20 07:44:16'),
(836, 103, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(837, 103, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(838, 103, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(839, 103, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(840, 103, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(841, 103, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(842, 103, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(843, 104, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(844, 104, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(845, 104, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(846, 104, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(847, 104, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(848, 104, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(849, 104, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(850, 105, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(851, 105, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(852, 105, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(853, 105, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(854, 105, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(855, 105, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(856, 105, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(857, 106, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(858, 106, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(859, 106, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(860, 106, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(861, 106, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(862, 106, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(863, 106, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(864, 107, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(865, 107, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(866, 107, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(867, 107, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(868, 107, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(869, 107, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(870, 107, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(871, 108, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(872, 108, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(873, 108, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(874, 108, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(875, 108, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(876, 108, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(877, 108, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(878, 109, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(879, 109, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(880, 109, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(881, 109, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(882, 109, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(883, 109, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(884, 109, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(885, 110, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(886, 110, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(887, 110, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(888, 110, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(889, 110, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(890, 110, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(891, 110, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(892, 111, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(893, 111, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(894, 111, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(895, 111, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(896, 111, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(897, 111, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(898, 111, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(899, 112, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(900, 112, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(901, 112, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(902, 112, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(903, 112, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(904, 112, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(905, 112, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(906, 113, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(907, 113, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(908, 113, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(909, 113, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(910, 113, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(911, 113, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(912, 113, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(941, 118, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(942, 118, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(943, 118, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(944, 118, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(945, 118, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(946, 118, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(947, 118, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(948, 119, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(949, 119, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(950, 119, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(951, 119, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(952, 119, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(953, 119, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(954, 119, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(955, 120, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(956, 120, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(957, 120, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(958, 120, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(959, 120, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(960, 120, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(961, 120, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(962, 121, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(963, 121, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(964, 121, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(965, 121, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(966, 121, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(967, 121, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(968, 121, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(969, 122, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(970, 122, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(971, 122, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(972, 122, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(973, 122, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(974, 122, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(975, 122, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(976, 123, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(977, 123, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(978, 123, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(979, 123, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(980, 123, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(981, 123, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(982, 123, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(983, 124, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(984, 124, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(985, 124, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(986, 124, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(987, 124, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(988, 124, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(989, 124, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(990, 125, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(991, 125, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(992, 125, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(993, 125, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(994, 125, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(995, 125, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(996, 125, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(997, 126, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(998, 126, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(999, 126, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1000, 126, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1001, 126, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1002, 126, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1003, 126, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1004, 127, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1005, 127, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1006, 127, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1007, 127, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1008, 127, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1009, 127, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1010, 127, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1011, 128, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1012, 128, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1013, 128, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1014, 128, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1015, 128, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1016, 128, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1017, 128, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1018, 129, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1019, 129, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1020, 129, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1021, 129, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1022, 129, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1023, 129, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1024, 129, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1025, 130, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1026, 130, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1027, 130, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1028, 130, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1029, 130, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1030, 130, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1031, 130, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1032, 131, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1033, 131, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1034, 131, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1035, 131, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1036, 131, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1037, 131, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1038, 131, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1039, 132, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1040, 132, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1041, 132, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1042, 132, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1043, 132, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1044, 132, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1045, 132, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1046, 133, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1047, 133, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1048, 133, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1049, 133, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1050, 133, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1051, 133, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1052, 133, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1053, 134, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1054, 134, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1055, 134, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1056, 134, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1057, 134, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1058, 134, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1059, 134, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1060, 135, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1061, 135, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1062, 135, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1063, 135, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1064, 135, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1065, 135, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1066, 135, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1067, 136, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1068, 136, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1069, 136, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1070, 136, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1071, 136, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1072, 136, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1073, 136, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1074, 137, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1075, 137, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1076, 137, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1077, 137, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1078, 137, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1079, 137, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1080, 137, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1081, 138, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1082, 138, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1083, 138, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1084, 138, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1085, 138, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1086, 138, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1087, 138, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1088, 139, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1089, 139, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1090, 139, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1091, 139, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1092, 139, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1093, 139, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1094, 139, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1095, 140, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1096, 140, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1097, 140, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1098, 140, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1099, 140, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1100, 140, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1101, 140, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1102, 141, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1103, 141, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1104, 141, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1105, 141, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1106, 141, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1107, 141, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1108, 141, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1109, 142, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1110, 142, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1111, 142, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1112, 142, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1113, 142, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1114, 142, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1115, 142, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1116, 143, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1117, 143, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1118, 143, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1119, 143, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1120, 143, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1121, 143, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1122, 143, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1123, 144, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1124, 144, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1125, 144, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1126, 144, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1127, 144, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1128, 144, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1129, 144, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1130, 145, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1131, 145, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1132, 145, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1133, 145, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1134, 145, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1135, 145, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1136, 145, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1137, 146, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1138, 146, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1139, 146, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1140, 146, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1141, 146, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1142, 146, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1143, 146, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1144, 147, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1145, 147, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1146, 147, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1147, 147, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1148, 147, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1149, 147, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1150, 147, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1151, 148, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1152, 148, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1153, 148, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1154, 148, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1155, 148, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1156, 148, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1157, 148, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1158, 149, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1159, 149, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1160, 149, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1161, 149, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1162, 149, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1163, 149, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1164, 149, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1165, 150, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1166, 150, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1167, 150, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1168, 150, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1169, 150, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1170, 150, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1171, 150, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1172, 151, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1173, 151, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1174, 151, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1175, 151, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1176, 151, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1177, 151, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1178, 151, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1179, 152, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1180, 152, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1181, 152, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1182, 152, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1183, 152, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1184, 152, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1185, 152, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1186, 153, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1187, 153, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1188, 153, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1189, 153, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1190, 153, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1191, 153, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1192, 153, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1193, 154, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1194, 154, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1195, 154, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1196, 154, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1197, 154, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1198, 154, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1199, 154, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1200, 155, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1201, 155, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1202, 155, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1203, 155, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1204, 155, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1205, 155, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1206, 155, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1207, 156, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1208, 156, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1209, 156, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1210, 156, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1211, 156, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1212, 156, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1213, 156, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1214, 157, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1215, 157, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1216, 157, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1217, 157, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1218, 157, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1219, 157, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1220, 157, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1221, 158, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1222, 158, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1223, 158, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1224, 158, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1225, 158, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1226, 158, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1227, 158, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1228, 159, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1229, 159, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1230, 159, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1231, 159, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1232, 159, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1233, 159, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1234, 159, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1235, 160, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1236, 160, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1237, 160, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1238, 160, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1239, 160, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1240, 160, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1241, 160, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1242, 161, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1243, 161, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1244, 161, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1245, 161, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1246, 161, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1247, 161, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1248, 161, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1249, 162, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1250, 162, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1251, 162, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1252, 162, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1253, 162, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1254, 162, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1255, 162, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1256, 163, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1257, 163, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1258, 163, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1259, 163, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1260, 163, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1261, 163, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1262, 163, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1263, 164, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1264, 164, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1265, 164, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1266, 164, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1267, 164, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1268, 164, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1269, 164, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1270, 165, 1, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1271, 165, 2, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1272, 165, 3, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1273, 165, 4, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1274, 165, 5, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1275, 165, 6, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1276, 165, 7, '09:00:00', '18:00:00', 1, '2026-06-20 07:52:35'),
(1284, 64, 1, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1285, 64, 2, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1286, 64, 3, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1287, 64, 4, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1288, 64, 5, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1289, 64, 6, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1290, 64, 7, '10:00:00', '18:00:00', 1, '2026-06-21 07:27:47'),
(1312, 56, 1, '04:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1313, 56, 2, '04:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1314, 56, 3, '04:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1315, 56, 4, '04:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1316, 56, 5, '04:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1317, 56, 6, '09:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1318, 56, 7, '11:00:00', '17:00:00', 1, '2026-06-24 02:25:15'),
(1382, 166, 1, '10:00:00', '17:00:00', 1, '2026-06-24 05:16:39'),
(1383, 166, 2, '10:00:00', '17:00:00', 1, '2026-06-24 05:16:39'),
(1384, 166, 3, '10:00:00', '17:00:00', 1, '2026-06-24 05:16:39'),
(1385, 166, 4, '10:00:00', '17:00:00', 1, '2026-06-24 05:16:39'),
(1386, 166, 5, '10:00:00', '17:00:00', 1, '2026-06-24 05:16:39'),
(1387, 166, 6, '09:00:00', '17:00:00', 0, '2026-06-24 05:16:39'),
(1388, 166, 7, '09:00:00', '17:00:00', 0, '2026-06-24 05:16:39'),
(1396, 167, 1, '09:00:00', '17:00:00', 1, '2026-06-26 04:35:28'),
(1397, 167, 2, '09:00:00', '17:00:00', 1, '2026-06-26 04:35:28'),
(1398, 167, 3, '09:00:00', '17:00:00', 1, '2026-06-26 04:35:28'),
(1399, 167, 4, '09:00:00', '17:00:00', 1, '2026-06-26 04:35:28'),
(1400, 167, 5, '09:00:00', '17:00:00', 1, '2026-06-26 04:35:28'),
(1401, 167, 6, '09:00:00', '17:00:00', 1, '2026-06-26 04:35:28'),
(1402, 167, 7, '09:00:00', '17:00:00', 0, '2026-06-26 04:35:28'),
(1473, 49, 1, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1474, 49, 2, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1475, 49, 3, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1476, 49, 4, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1477, 49, 5, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1478, 49, 6, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1479, 49, 7, '09:00:00', '17:00:00', 1, '2026-06-27 07:26:05'),
(1494, 170, 1, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1495, 170, 2, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1496, 170, 3, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1497, 170, 4, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1498, 170, 5, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1499, 170, 6, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1500, 170, 7, '09:00:00', '17:00:00', 1, '2026-06-27 08:40:31'),
(1501, 50, 1, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16'),
(1502, 50, 2, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16'),
(1503, 50, 3, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16'),
(1504, 50, 4, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16'),
(1505, 50, 5, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16'),
(1506, 50, 6, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16'),
(1507, 50, 7, '09:00:00', '17:00:00', 1, '2026-06-27 10:16:16');

-- --------------------------------------------------------

--
-- Table structure for table `service_time_slots`
--

CREATE TABLE `service_time_slots` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `confirmed_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `confirmed_package_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `confirmed_customize_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `max_concurrent` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `max_concurrent_package` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `max_concurrent_customize` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('available','full','blocked') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_time_slots`
--

INSERT INTO `service_time_slots` (`id`, `service_id`, `date`, `start_time`, `end_time`, `confirmed_count`, `confirmed_package_count`, `confirmed_customize_count`, `max_concurrent`, `max_concurrent_package`, `max_concurrent_customize`, `status`, `created_at`) VALUES
(4, 42, '2026-09-16', '09:00:00', '17:00:00', 1, 0, 1, 1, 0, 1, 'full', '2026-06-18 11:10:23'),
(6, 42, '2026-07-18', '09:00:00', '17:00:00', 1, 0, 1, 1, 0, 1, 'full', '2026-06-18 14:36:16'),
(10, 48, '2026-07-19', '09:00:00', '17:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-19 15:48:47'),
(11, 50, '2026-07-19', '09:00:00', '17:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-19 15:48:47'),
(12, 49, '2026-07-19', '09:00:00', '17:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-19 15:48:47'),
(14, 55, '2026-06-27', '09:00:00', '17:00:00', 1, 1, 0, 1, 1, 1, 'full', '2026-06-20 01:33:55'),
(15, 48, '2026-06-27', '09:00:00', '17:00:00', 0, 0, 0, 1, 0, 1, 'available', '2026-06-20 01:33:55'),
(16, 50, '2026-06-27', '09:00:00', '17:00:00', 2, 2, 0, 3, 0, 3, 'full', '2026-06-20 01:33:55'),
(17, 55, '2026-06-21', '09:00:00', '10:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(18, 55, '2026-06-21', '10:00:00', '11:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(19, 55, '2026-06-21', '11:00:00', '12:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(20, 55, '2026-06-21', '12:00:00', '13:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(21, 55, '2026-06-21', '13:00:00', '14:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(22, 55, '2026-06-21', '14:00:00', '15:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(23, 55, '2026-06-21', '15:00:00', '16:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(24, 55, '2026-06-21', '16:00:00', '17:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-20 03:51:17'),
(25, 59, '2026-06-27', '09:00:00', '10:00:00', 1, 1, 0, 5, 3, 0, 'available', '2026-06-20 07:33:09'),
(26, 67, '2026-06-27', '09:00:00', '10:00:00', 1, 1, 0, 2, 1, 0, 'full', '2026-06-20 09:24:06'),
(27, 105, '2026-06-27', '09:00:00', '10:00:00', 0, 0, 0, 2, 1, 0, 'available', '2026-06-20 14:30:35'),
(29, 67, '2026-06-03', '10:00:00', '11:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(30, 67, '2026-06-03', '11:00:00', '12:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(31, 67, '2026-06-03', '12:00:00', '13:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(32, 67, '2026-06-03', '13:00:00', '14:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(33, 67, '2026-06-03', '14:00:00', '15:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(34, 67, '2026-06-03', '15:00:00', '16:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(35, 67, '2026-06-03', '16:00:00', '17:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(36, 67, '2026-06-03', '17:00:00', '18:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:21:53'),
(37, 67, '2026-06-20', '10:00:00', '11:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(38, 67, '2026-06-20', '11:00:00', '12:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(39, 67, '2026-06-20', '12:00:00', '13:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(40, 67, '2026-06-20', '13:00:00', '14:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(41, 67, '2026-06-20', '14:00:00', '15:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(42, 67, '2026-06-20', '15:00:00', '16:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(43, 67, '2026-06-20', '16:00:00', '17:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(44, 67, '2026-06-20', '17:00:00', '18:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:12'),
(45, 67, '2026-06-27', '10:00:00', '11:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(46, 67, '2026-06-27', '11:00:00', '12:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(47, 67, '2026-06-27', '12:00:00', '13:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(48, 67, '2026-06-27', '13:00:00', '14:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(49, 67, '2026-06-27', '14:00:00', '15:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(50, 67, '2026-06-27', '15:00:00', '16:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(51, 67, '2026-06-27', '16:00:00', '17:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(52, 67, '2026-06-27', '17:00:00', '18:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-20 18:22:16'),
(84, 56, '2026-06-28', '04:00:00', '17:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-21 06:25:06'),
(86, 55, '2026-06-28', '09:00:00', '17:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-21 06:25:06'),
(87, 48, '2026-06-28', '09:00:00', '17:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-21 06:25:06'),
(88, 50, '2026-06-28', '09:00:00', '17:00:00', 2, 1, 1, 3, 0, 0, 'full', '2026-06-21 06:25:06'),
(89, 50, '2026-06-21', '09:00:00', '17:00:00', 0, 0, 0, 3, 0, 0, 'available', '2026-06-21 06:57:57'),
(92, 64, '2026-06-21', '10:00:00', '11:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(93, 64, '2026-06-21', '11:00:00', '12:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(94, 64, '2026-06-21', '12:00:00', '13:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(95, 64, '2026-06-21', '13:00:00', '14:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(96, 64, '2026-06-21', '14:00:00', '15:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(97, 64, '2026-06-21', '15:00:00', '16:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(98, 64, '2026-06-21', '16:00:00', '17:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(99, 64, '2026-06-21', '17:00:00', '18:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:13:47'),
(100, 56, '2026-06-21', '11:00:00', '14:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:40:15'),
(101, 56, '2026-06-21', '14:00:00', '17:00:00', 0, 0, 0, 2, 0, 0, 'available', '2026-06-21 07:40:15'),
(102, 56, '2026-06-29', '04:00:00', '17:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-22 09:49:24'),
(104, 55, '2026-06-29', '09:00:00', '17:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-22 09:49:24'),
(105, 48, '2026-06-29', '09:00:00', '17:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-22 09:49:24'),
(106, 50, '2026-06-29', '09:00:00', '17:00:00', 2, 1, 1, 3, 0, 3, 'full', '2026-06-22 09:49:24'),
(108, 56, '2026-07-01', '04:00:00', '07:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:07:04'),
(109, 56, '2026-07-01', '07:00:00', '10:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:07:04'),
(111, 55, '2026-07-01', '09:00:00', '12:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-23 05:07:04'),
(112, 48, '2026-07-01', '09:00:00', '12:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-23 05:07:04'),
(113, 50, '2026-07-01', '09:00:00', '12:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-23 05:07:04'),
(114, 56, '2026-07-01', '10:00:00', '13:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:07:04'),
(116, 55, '2026-07-01', '12:00:00', '15:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-23 05:07:04'),
(117, 48, '2026-07-01', '12:00:00', '15:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-23 05:07:04'),
(118, 50, '2026-07-01', '12:00:00', '15:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-23 05:07:04'),
(119, 56, '2026-07-01', '13:00:00', '16:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:07:04'),
(120, 56, '2027-08-04', '04:00:00', '07:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:15:13'),
(121, 56, '2027-08-04', '07:00:00', '10:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:15:13'),
(123, 55, '2027-08-04', '09:00:00', '12:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-23 05:15:13'),
(124, 48, '2027-08-04', '09:00:00', '12:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-23 05:15:13'),
(125, 50, '2027-08-04', '09:00:00', '12:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-23 05:15:13'),
(126, 56, '2027-08-04', '10:00:00', '13:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:15:13'),
(128, 55, '2027-08-04', '12:00:00', '15:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-23 05:15:13'),
(129, 48, '2027-08-04', '12:00:00', '15:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-23 05:15:13'),
(130, 50, '2027-08-04', '12:00:00', '15:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-23 05:15:13'),
(131, 56, '2027-08-04', '13:00:00', '16:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-23 05:15:13'),
(132, 56, '2026-07-02', '04:00:00', '07:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-24 02:53:58'),
(133, 56, '2026-07-02', '07:00:00', '10:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-24 02:53:58'),
(135, 55, '2026-07-02', '09:00:00', '12:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-24 02:53:58'),
(136, 48, '2026-07-02', '09:00:00', '12:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-24 02:53:58'),
(137, 50, '2026-07-02', '09:00:00', '12:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-24 02:53:58'),
(138, 56, '2026-07-02', '10:00:00', '13:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-24 02:53:58'),
(140, 55, '2026-07-02', '12:00:00', '15:00:00', 1, 1, 0, 1, 1, 0, 'full', '2026-06-24 02:53:58'),
(141, 48, '2026-07-02', '12:00:00', '15:00:00', 1, 1, 0, 1, 0, 1, 'full', '2026-06-24 02:53:58'),
(142, 50, '2026-07-02', '12:00:00', '15:00:00', 1, 1, 0, 3, 0, 3, 'available', '2026-06-24 02:53:58'),
(143, 56, '2026-07-02', '13:00:00', '16:00:00', 1, 1, 0, 2, 2, 0, 'full', '2026-06-24 02:53:58'),
(144, 42, '2026-07-24', '09:00:00', '12:00:00', 1, 1, 0, 300, 0, 300, 'available', '2026-06-24 03:09:11'),
(145, 42, '2026-07-24', '12:00:00', '15:00:00', 1, 1, 0, 300, 0, 300, 'available', '2026-06-24 03:09:11'),
(146, 50, '2026-06-26', '09:00:00', '17:00:00', 0, 0, 0, 3, 0, 0, 'available', '2026-06-26 06:33:05'),
(147, 49, '2026-06-27', '09:00:00', '17:00:00', 0, 0, 0, 1, 0, 0, 'available', '2026-06-26 13:54:33');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `shop_name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','verified','approved','rejected','banned') DEFAULT NULL,
  `verified_by` bigint(20) DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `verify_url` varchar(255) DEFAULT NULL,
  `agreement_accepted` tinyint(1) DEFAULT 0,
  `agreement_accepted_at` timestamp NULL DEFAULT NULL,
  `agreement_version` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','paid') DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 0,
  `auto_accept_bookings` tinyint(1) NOT NULL DEFAULT 0,
  `min_advance_days` int(11) NOT NULL DEFAULT 0,
  `cancellation_policy` text DEFAULT NULL,
  `warning_level` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=none, 1=warning, 2=final_warning',
  `missed_response_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of bookings auto-cancelled due to supplier non-response',
  `last_warning_at` timestamp NULL DEFAULT NULL COMMENT 'When the supplier was last issued a system warning for missed responses',
  `admin_note` text DEFAULT NULL,
  `bank_code` varchar(50) DEFAULT NULL,
  `notification_prefs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_prefs`)),
  `bank_account` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `user_id`, `shop_name`, `description`, `status`, `verified_by`, `approved_by`, `verify_url`, `agreement_accepted`, `agreement_accepted_at`, `agreement_version`, `payment_status`, `is_available`, `auto_accept_bookings`, `min_advance_days`, `cancellation_policy`, `warning_level`, `missed_response_count`, `last_warning_at`, `admin_note`, `bank_code`, `notification_prefs`, `bank_account`, `created_at`, `deleted_at`) VALUES
(20, 24, 'JV', 'we sell dress', 'verified', NULL, 1, 'https://www.facebook.com/jv230', 1, '2026-06-10 02:08:51', 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, 'KBZ', NULL, '123456789', '2026-06-10 06:38:51', NULL),
(21, 29, 'Wyndham Grand Yangon Hotel', 'ဝင်ဒမ်ဂရန်းရန်ကုန်ဟိုတယ်ရဲ့ Wedding Tea Package များကို US$ 7 တောင် လျော့ပေးမယ့်အပြင် မိမိရွေး ချယ်တဲ့ Package ပေါ် မူတည်၍ Walkway နဲ့ LED အသုံးပြုခွင့်များပါ ရရှိနိုင်မှာပဲဖြစ်ပါတယ်...\r\n\r\nဒါ့အပြင် Wedding Dinner Packages ဝယ်ယူသူတိုင်းအတွက် အခမဲ့ Complimentary Table များ (သိုမဟုတ်) Walkway အသုံးပြုခွင့် (သိုမဟုတ်)  LED အသုံးပြုခွင့်ဆိုပြီး မိမိ နှစ်သက်ရာ အကျိုးခံစားခွင့်ကို ရွေးချယ်ရယူနိုင်မှာပါ...\r\n\r\n သင့်စိတ်ကူးထဲကအတိုင်း ကြီးကျယ်ခမ်းနားလှပတဲ့ Wedding ပွဲကြီးကို စိတ်တိုင်းကျဖန်တီးနိုင်ဖိုအတွက် ဝင်ဒမ်ဂရန်းရန်ကုန်ဟိုတယ်ရဲ့ Wedding Venue Area များက အသင့်တော်ဆုံးရွေးချယ်မှုဖြစ်စေမှာပါ...Wedding Period ကိုလည်း ၂၀၂၇ ခုနှစ် နှစ်ကုန်အထိ ပေးထားတာမို တအားတန်တဲ့ ဒီအခွင့်အရေးကို လက်မလွတ်ရလေအောင် အမိအရဖမ်းဆုပ်လိုက်တော့နော်...🤍', 'verified', 1, 1, 'htpps://www.wyndhamgrandyangon.com', 1, '2026-06-11 00:31:15', 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-11 05:01:15', NULL),
(23, 32, 'Excel River View Hotel & Resort', 'Riverside hotel on the Bago River offering stage decoration, table & chair arrangement, floral decoration and theme colour design for weddings.', 'approved', NULL, NULL, 'excelriverview@gmail.com', 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:13:56', NULL),
(24, 33, 'Golden Inya Restaurant', 'Lakeside fine-dining restaurant on Inya Lake with indoor and outdoor space (outdoor seats 700-800). Popular for weddings, engagements and receptions, buffet and set/custom menus available.', 'approved', NULL, NULL, 'golden-inya-restaurant.business.site', 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:22:45', NULL),
(25, 34, 'Western Park Ruby - People\'s Park', 'Garden venue inside People\'s Park, Dagon Township. Indoor (100-200) and outdoor (200-800) wedding space, guests skip the park entrance fee. Reception buffet on request.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:22:45', NULL),
(26, 35, 'Zephyr (Sein Lann So Pyay Garden)', 'Calm garden fine-dining and event venue beside Inya Lake. Outdoor lawn seats up to 400. Offers stage decoration, floral arrangement and theme-based decoration, Asian & Western set/buffet menus.', 'approved', NULL, NULL, 'zephyrcafe2018@gmail.com', 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:22:45', NULL),
(27, 36, 'The White Cottage Restaurant & Lounge', 'European cottage-style restaurant and lounge in Shwe Taung Kyar, Bahan. Romantic indoor space and green garden (outdoor 100-150), suited to Western-style civil weddings. Decor/planner/MC not included.', 'approved', NULL, NULL, 'thewhitecottageyangon.com', 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:22:45', NULL),
(28, 37, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'No.991, Thu Mingalar Road, Thingangyun Township, Yangon. Tel 09 250 500 809\n\nTraditional Myanmar bridal wear — htaing-ma-theim, offering/registration outfits, taik-pon and taung-shay for the couple and parents, in various silk weaves. Rental and sale, plus custom-made rental (book 3-6 months ahead). Htaing-ma-theim rental approx 350,000 to 2,000,000, offering outfits from approx 200,000. Add-ons: floral decoration, hand bouquets, hotel/makeup booking and wedding car decoration.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(29, 38, 'Dear Brides Wedding Dress Studio', 'Karaweik Garden, near Myaw Sin Kyun entry, Mingalar Taung Nyunt, Yangon. Tel 09 771471462. Open 10:00-18:00 daily\n\nWestern and traditional bridal wear — wedding gowns, mermaid dresses, evening dresses and pre-wedding outfits. Latest imported designs for rent or sale, customised bridal veils, and custom-made rental. Spacious studio with parking, in-house photo studio and experienced stylists. Range approx 800,000 to 3,000,000 depending on dress.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(30, 39, 'The Vow Wedding Studio Myanmar', 'No.789, 47 ward, Bohmu Ba Htoo Road, North Dagon, Yangon. Tel 09 451355553, 09 791580503. Open 09:00-17:00\n\nWomen\'s bridal studio with finely tailored gowns, quality fabrics and detailed finishing for each bride. Rental and sale; crowns and bridal shoes also available. Event-day rental approx 1,500,000 to 6,000,000+.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(31, 40, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'Thu Mingalar main road (between Sa Taik and Inn Wa bus stops), South Okkalapa, Yangon — above Khit Pyaing toy shop, next to CB Bank. Tel 09 777775512\n\nWedding suits and dresses for men and women. Reliable remote/line ordering with good fit. Price approx 200,000 to 500,000+. Booking required.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(32, 41, 'T&T Bridal Collection', 'No.666, Thudamar Road (near Eaindra bus stop), North Okkalapa, Yangon. Tel 09 799515633, 09 799515622. Open 10:00-17:30, closed Wednesdays\n\nWestern wedding dresses with hundreds of new pieces. Rental approx 400,000 to 1,500,000; wholesale purchase from 230,000. 10+ years wedding-industry founder advises on current trends, body-fit styling, matching makeup look and accessories. New stock monthly plus resale of older pieces.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(33, 42, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး', 'No.293, Brahmaso 4/6 Street, South Okkalapa Township, Yangon. Tel 09 422999929, 09 985808800\n\nMen\'s ceremony wear — \"Gon\" taik-pon (M/L/XL/XXL) at 300,000 and pasoe (longyi) from 43,000 to 420,000. Detailed sizing help and sharp cutting for a smart, dignified look.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(34, 43, 'Peter\'s Bridal Garden - Studio', 'No.542, Ou Zanar Street, ward 11, Mya Thidar Housing, South Okkalapa, Yangon. Tel 09 777 595010\n\nPre-wedding outfit and photography studio. Indoor and outdoor pre-wedding packages (e.g. 3-outfit indoor package), traditional looks, makeup and full-team support with raw photos provided. Highly recommended for pre-wedding shoots.', 'approved', NULL, NULL, 'peterbridalgarden@gmail.com', 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(35, 44, 'My Everything Wedding Dresses', 'No.1253, 13 ward, Ratana main road, South Okkalapa Township, Yangon. Tel 09 776040862, 09 760396053. Open 09:00-17:00\n\nBridal dress rental for brides. Rental price range approx 480,000 to 1,860,000. Rental only.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:44:16', NULL),
(68, 45, 'Forever One Stop Wedding Studio', 'No. 108, Phone Gyi Road, Lanmadaw Township, Yangon, Myanmar. +95 9 777 299 466 , +95 9 776 275 302 foreverstudio.mm@gmail.com              Garden Studio;\nNo.619, Padagyi - Thilawa Rd,\nShwe Pyout, Kyauktan, Yangon.\nPh : 09-777299477', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(69, 46, 'H & H Photo Studio', '🏨 - No.968, Thiri Zayar 18 A Street, 7 Ward, South Oakkalapa, Yangon.                 09770837838', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(70, 47, 'Venus Wedding Studio', 'Yangon Add：အမှတ်(A+B), မေဥယျာဥ်အိမ်ယာ၊ ရတနာလမ်းမပေါ်၊ သင်္ဃန်းကျွန်းမြို့နယ်၊ ရန်ကုန်မြို့။️Hotline：09957373666 /09957373999', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(71, 48, 'PNA’S Wedding Studio', 'Main Branch Address   No.93, သရက်တောလမ်း၊ ကမ်းနားလမ်းမပေါ် ၊ ကြည့်မြင်တိုင်မြိုနယ် ၊ ရန်ကုန်မြို ။\nNorth Dagon Branch Address ( Opening Soon )\nအမှတ် ၉၀၄ ၊ ဒဂုံသီရိလမ်း ၊ ၄၃ ရပ်ကွက် ၊ မြောက်ဒဂုံမြိုနယ် (ဗိုလ်ဗထူး အိမ်ရာ အနီး) ။', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(72, 49, 'Together Wedding Studio', 'YANGON\nAdd : No.1242, ရတနာလမ်းမလိခအိမ်ရာအရှေ့ / တောင်ဥက္ကလာပမြိုနယ် / ရန်ကုန်မြို။\nHotline : 09 787 888 818 , 09 7679 10070, 09 967 888 818, 09 778 617 797, 09 974 468 884, 09 766 208 568, 09 785 255 890, 09 420 003 031', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(73, 50, 'Western Park Ruby – People’s Park', 'ပြည်သူ့ရင်ပြင်ဝန်းအတွင်း၊ ဒဂုံမြို့နယ်၊ ရန်ကုန်မြို့။                                                                                                      09-444437223,09-444437226,09-444437225', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(74, 51, 'MG & J Jewelry', 'No(80/A), Kanbawza Lane(2), Bahan, Yangon.(closed on Wednesday)\n\nTel : +95 9 762510251, Viber : +95 9 762510251, Whatsapp: +95 9 762510251 Email:info@mgjmyanmar.com\n\n**Facebook**: @MG&J Jewelry International, **Instagram:** @mgjjewelryco, **Youtube**:@mgjmyanmar, **Tiktok**: @mgjmyanmar', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(75, 52, 'U Hton', 'အခန်း (B) 01-05 (မြေညီထပ်)၊ လမ်းမတော်ပလာဇာ (သံစျေးအနီး)၊ လမ်းမတော်လမ်း၊လသာမြို့နယ်၊ ရန်ကုန်မြို့။\n\n01-701390, 09-790609656\nViber Number: +959 965152335\n\nတနင်္ဂနွေနေ့တိုင်းဆိုင်ပိတ်သည်။ဆိုင်ဖွင့်ချိန် မနက် ၉ နာရီ မှ ည ၅ နာရီခွဲ အထိဖွင့်လှစ်ပါသည်။', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(76, 53, 'Myat Pan Tha Zin Diamond and Jewelry', '**Located in:** Salween Institute for Public Policy office**Address:** Times City, A 313, Level 3, Jewellery Mall, Kyun Taw Rd, Yangon 11041\n\n**Phone:** 09 890 006320', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(77, 54, 'Vivian Diamond Jewellery', 'No.22 Pyi Thu Kwat Thit 1st Street, Yangon 11111\n\n09 44313 6572+wedding+rings&oq=vivian&gs_lcrp=EgZjaHJvbWUqCAgAEEUYJxg7MggIABBFGCcYOzIHCAEQLhiABDIHCAIQLhiABDIGCAMQRRg7MgYIBBBFGDsyBwgFEC4YgAQyBwgGEAAYgAQyBwgHEC4YgAQyBwgIEC4YgAQyBwgJEC4YgATSAQgyMjAwajBqN6gCALACAA&sourceid=chrome&ie=UTF-8#)', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(78, 55, 'Theingi Moe Jewelry', 'No5 Mahar, Myint Mo St, Yangon 11201\n\n09 42009 7809', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(83, 56, 'Parisian Cake&Cafe', 'Yangon. **Parisian Cake & Coffee**\n📍 446 Lower Kyeemyindaing Rd, Yangon\n📞 +95 1 230 1512\n➡️ Classic large branch where you can order custom cakes (including wedding cakes).', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(84, 57, 'Season', '**Seasons Bakery**\n📍 City Mart, Pyay Rd, Yangon, Myanmar\n☎️ +95 1 650 771\n✅ Popular bakery & cake shop — good for daily cakes and custom orders.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(85, 58, 'Kudo’s', '**Kudos Bakery** – Anawrahta Rd, Yangon\n📞 **+95 9 422 886 667**\nOne of the main bakery locations with a wide selection of baked goods', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(86, 59, 'Shwe Pu Zun', '**Main Bakery & Cafe – Dawbon BranchShwe Pu Zun Cafeteria & Bakery House**\n📌 Address: No.14/A Min Nandar Rd, Dawbon Township, Yangon, Myanmar (Burma)\n📞 Phone: +95 1 553062', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(87, 60, '77 Cake', '**77 Cakes – Kyauk Kone, Tamwe** 🎂\n📍 Address: No. 1 သမ္မာဓိလမ်း, ကျောက်ကုန်း, Yangon\n📞 Phone: +95 9 799558070\n🕒 Hours: ~08:00 AM – 05:00 PM', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(88, 61, 'El Dorado', '**El Dorado**\n📌 Address: No. 4 Wai Za Yan Tar Rd, Yangon, Myanmar (Thingangyun)\n📞 Phone: +95 9 9788 46073\n⭐ Rating: ~4.1 ★ (300+ reviews)\n💰 Price: $$\n🕒 Hours: 07:30 AM – 09:00 PM (daily)', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(89, 62, 'Shan Yoe Yar Restaurant', 'Event sales-09255166608မနော်ဟရီဆိုင်ခွဲ\n\n 📌အမှတ် ၅၄၊ မနော်ဟရီလမ်း၊ ဒဂုံမြို့နယ်၊ ရန်ကုန်မြို့။ (ခရေပင်လမ်းနှင့် တော်ဝင်လမ်းကြား) 09-250566695, 09-255166655opening hour - 6am to 10pmရန်ကင်းဆိုင်ခွဲ \n\n📌အမှတ်7 အောင်ဇေယျလမ်း နှင့် မင်းရဲကျော်စွာလမ်းထောင့် ရန်ကင်းမြို့နယ်09 255 166 604, 09 255 166 605opening hour - 7:00 am to 10pmဆူးလေဆိုင်ခွဲ \n\n📌Sule Square Mall ပထမထပ် ကျောက်တံတားမြို့နယ်09 258 777 070', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(90, 63, 'KSS နတ်သုဒ္ဓါဒံပေါက်', 'ရုံးချုပ်📍 အမှတ် (၁၃၃)(C)/၀၆ မြို့တော်လမ်း သာကေတမြို့နယ် ရန်ကုန်မြို့📞 09 4222 333 35, 09 45 453 5858\n\nမင်္ဂလာတောင်ညွန့်📍 တိုက် (၃) အခန်း (၁၀၀) ကန်လမ်း မင်္ဂလာတောင်ညွန့်မြို့နယ် ရန်ကုန်မြို့📞 09 88 335 4411, 09 4222 333 35 (Manager)\n\nပုဇွန်တောင် (၆)လမ်းမ)📍 အမှတ် (၂၅) ၆လမ်းမ ပုဇွန်တောင်မြို့နယ် ရန်ကုန်မြို့📞 09 88 335 4400, 09 4222 333 36 (Manager)\n\nလှိုင်📍 တိုက် (၂) အခန်း (၀၆) အင်းစိန်လမ်းမကြီး လှိုင်မြို့နယ် ရန်ကုန်မြို့📞 09 420 4477 33\n\nစမ်းချောင်း (ပြည်လမ်း)📍 အမှတ် (၁၁) (C-1) ပြည်လမ်း စမ်းချောင်းမြို့နယ် ရန်ကုန်မြို့📞 09 420 4477 66\n\nလမ်းမတော်📍 အမှတ် (၇၄/၇၆) အနော်ရထာလမ်း လမ်းမတော်မြို့နယ် ရန်ကုန်မြို့📞 09 420 4477 11\n\nကျောက်တံတား📍 အမှတ် (၆၁) ပန်းဆိုးတန်းလမ်း ကျောက်တံတားမြို့နယ် ရန်ကုန်မြို့📞 09 420 4477 99\n\nအင်းစိန်📍 အမှတ် (၁၉၆) ကမ်းနားလမ်း အင်းစိန်မြို့နယ် ရန်ကုန်မြို့📞 09 89 244 0044\n\nပန်းဆိုးတန်း (ဗိုလ်ချုပ်အောင်ဆန်း)📍 အမှတ် (၇၀/၇၂) ဗိုလ်ချုပ်အောင်ဆန်းလမ်း ပန်းဆိုးတန်းမြို့နယ် ရန်ကုန်မြို့📞 09 8833 544 33', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(91, 64, 'ထူး ရေခဲမုန့်', 'အမှတ်(၂၂)၊ အထက်ပုဇွန်တောင်လမ်းမကြီး၊ ပုဇွန်တောင်မြို့နယ်။အမှတ်(၁၂၇)၊ လူညီတန်း - အင်းစိန်လမ်းမကြီး၊ ကမာရွတ်မြို့နယ် (၁)ရပ်ကွက်၊ ဆင်ရေတွင်းမှတ်တိုင်၊ ဂမုန်းပွင့်စံရိပ်ငြိမ်အနီး။\n\n0969598333809975285954', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(92, 65, 'The Hundred -Grilled Chicken', 'စံရိပ်ငြိမ် ဂမုန်းပွင့် Shopping Mall(ရှေ့မျက်နှာစာ)စံရိပ်ငြိမ်မှတ်တိုင်၊ အင်းစိန်လမ်းမ၊ ကမာရွတ်မြို့နယ်။‌\n\n0995444520009753628843', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(93, 66, 'Royal Chef', 'No. 15/17, Nantha Phyu Street,Pazundaung Township, Yangon,Myanmar, 11171\n\n09762225667', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(94, 67, 'Rice Box', 'No. 668, 4/6 Byamaso Street, 4th Ward, South Okkalapa, Yangon\n\n09-765-2030-17\n09-7933-7472-6', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(95, 68, 'Boke & Bee', 'အမှတ်(၄၀)၊ အောင်သိဒ္ဓိလမ်း (၁) ၊ (၃)ရပ်ကွက် ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်။\n\n📞 09 791992746 , 09 404916066', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(96, 69, 'နှင်းသီရိ', 'ရန်ကုန်၊ မြောက်ဥက္ကလာပ၊ သုနန္ဒာ (၁၂) လမ်း၊ (ဆ) ရပ်ကွက်။ \n📞 မှာယူရန် ဖုန်း: 09-456666422', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(97, 70, 'H&H Floral and Wedding Service', 'Add 1 - 35B, 69-70 Maharaungmyay Township.\nAdd 2 - Ta Kon Taing, Pyigyitagon\nTownship, Mandalay, Mandalay, Myanmar                                        09 977 819738\nhaymanoo3111995@gmail.com', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(98, 71, 'Eternal Flowers', 'အမှတ်(449-A)၊ တက္ကသိုလ်ရိပ်သာလမ်းသစ်၊ ဗဟန်းမြို့နယ်၊ ရန်ကုန်မြို့။ (The Link Hotel ဘေး)           **Phone:** 01 9541217, 01 9559011, 09 404014512,  09 421017797 eternalflowers99@gmail.com', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(99, 72, 'Aphrodite Wedding Planning & Decoration', 'အမှတ် ၄၈၊ ၃ရပ်ကွက် ၄ လမ်း၊ ငွေကြာရံ၊ တောင်ဥက္ကလာပမြိုနယ်၊ Yangon, Myanmar,11091             09 975 288653\ninfo@aphroditeweddingplanning.com', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(100, 73, 'Elysian Floral Art & Events Planning', 'အမှတ်(352A/353B)၊ ကျန်စစ်သား(1)လမ်း၊ မြောက်ကြီးပွားရေးရပ်ကွက်၊ သင်္ဃန်းကျွန်းမြို့နယ်၊ ရန်ကုန်မြို့။ 09 5086711, 09 775086711,  09 965085711  elysian.floral.art.mm@gmail.com', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(101, 74, 'S&S Events and Floral', 'အမှတ် 5/46(A2)၊ အောင်ဇေယျလမ်း၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။ 09 254886898, 09 779922703', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(102, 75, 'His & Hers Events and Wedding Studio', 'အမှတ်(560)၊ မစိုးရိမ်လမ်းသွယ်(3)၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။ 09 250188137, 09 256795792 hnhbridal@gmail.com', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(103, 76, 'Governor’s Residence', 'Governor’s Residence', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(104, 77, 'Novotel Yangon Max', '**Novotel Yangon Max**', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(105, 78, 'Sedona Hotel Yangon', '**Sedona Hotel Yangon**', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(106, 79, 'Inya Lake Hotel', '**Inya Lake Hotel**', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(107, 80, 'Meliá Yangon', '**Meliá Yangon**', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(108, 81, 'Hotel Yangon', 'Hotel Yangon', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(109, 82, 'Myanmar Car Rental', 'No. 741, Ground Floor, 3rd Street, 1st Ward,Mayangone Township,Yangon, Myanmar.', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(110, 83, 'The Experience Rent A Car', 'No.1 , Kaba Aye Pagoda Road , Sedona Hotel , Yankin Township , Yangon , Myanmar +95 9880034504', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(111, 84, 'AVIS MYANMAR', '+959977875099       Unit 15, M Tower, No.527 Pyay Road, 04 15th Floor, 11041, မြန်မာ', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(112, 85, 'inoventure', 'No. 631, Pyay Road, Kamayut Township, Yangon, Myanmar.+959897308080', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(113, 86, 'Concierge Business Limousine', 'Room (302) Tower A, Shwe Zabu Deik Condo, Strand Road, Ahlone Township, Yangon, Myanmar +959 450061110 , +959 960760732', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(114, 87, 'Elegant Star (Recommended)', '3C, Shwe Kinnari Estate, Nar Nat Taw Street, Kamayut, Yangon, Myanmar    \n\n+95 9421736316,\n +95 9678884898', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(115, 88, 'Memory Memory Handmade invitation cards and gifts (Recommended)', 'Hlaing, Kamayut, Myanmar\n\n09740016907 or 095501302 for Viber', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(116, 89, 'Moe Kaung Kin', '62(A)29x30ကြား', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(117, 90, 'Y Collection', '၁၀၆၊ ၄၉ လမ်း (အနော်ရထာလမ်း နှင့် မဟာဗန္ဓုလလမ်းကြား)၊ ပုဇွန်တောင်မြို့နယ်၊ ရန်ကုန်။\n\n099 8484 8787, 09 78 666 2998', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(118, 91, 'Paperie Tale (Recommended)', '09-251158839', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(119, 92, 'THIRI Handmade Invatation', '+95 9 772 244608\n\nအမှတ် 122(2)လွှာ အောင်ဇေယျလမ်း  လွတ်လပ်ရေးရပ်ကွက်  အလုံမြို့နယ်\nရန်ကုန်မြို့။', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(120, 93, 'Pyan Kann', '၁၄၂၊၂ ကျိုက်ဝိုင်းဘုရားလမ်း၊ မရမ်းကုန်း၊ ရဲရန်အောင်မှတ်တိုင်အနီး နေဝင်းမျက်မှန် အပေါ်(ပ)ထပ်\n\n09783945706,09446986613', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(121, 94, 'SORA', 'အမှတ် ၆၉၄၊ ဘုရင့်နောင်လမ်း၊ ၃၂ ရပ်ကွက်၊ မြောက်ဒဂုံမြို့နယ်၊ ရန်ကုန်မြို့ (Kaung Htue စားသောက်ဆိုင် မျက်နှာချင်းဆိုင်)Yangon,Myanmar\n09882233765', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(122, 95, 'ကိုသာဂိ', 'အမှတ်(၂၈)၊၆ရက်ကွက်၊တောင်ဉက္ကလာပမြို့နယ်၊သစ္စာလမ်း၊ရန်ကုန်\n09894881122', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(123, 96, 'Ma Htet-pop soul', 'No.3\nMa Har Myint Mo street (u chit mg rood )\nSayarsan ( south)Quartar\nBahan township, Yangon, Myanmar,\n095166069\n09765166069', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(124, 97, 'Lin Lin', 'Yangon\n095163167\n0973132666', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(125, 98, 'make up Kin San Win', 'အမှတ် (၂၅၉)၊ ပထမထပ်၊ ၃၅ လမ်း (အထက်ဘလောက်)၊ သွင်ရုပ်ရှင်ရုံအနီး၊ ကျောက်တံတားမြို့နယ်၊ ရန်ကုန်မြို့။\n095101144\n095012581', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(126, 99, 'Magic Touch Beauty Boutique', 'Mandaly\n09444700382', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(127, 100, 'Chi Chi’s Touch', '77/34-35Mandalay\n09758646836', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(128, 101, 'Makeup Hazel', 'No.14, Yandanar streets , Kamayut\nYangon\n09779922564', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(129, 102, 'Makeup Non Thit San', 'Yangon,Myanmar\n09796217995', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(130, 103, 'Sweet Hair& Make up', 'No.52, First Floor, 157 Road 9, Tamwe, Yangon.\n09791157650', 'approved', NULL, NULL, NULL, 1, NULL, 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-20 07:52:35', NULL),
(131, 105, 'Chanel', 'we provide accessories', 'verified', 1, 1, 'https://dribble.com', 1, '2026-06-23 04:23:48', 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-23 04:23:48', NULL),
(132, 114, 'Forever Wedding Studio', 'Forever One Stop Wedding Service & Planning was established in 2006 and has earned a name in bridal industries for wedding photography and wedding gowns collection. Our motto is to capture your most precious memories while still maintain customer satisfaction, innovation leadership and continuing operation, leaving customers happy dreams and memories.', 'verified', 1, 1, 'https://www.foreverweddingstudio.com/', 1, '2026-06-23 05:50:02', 'supplier-v1', 'paid', 1, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-23 05:50:02', NULL),
(133, 124, 'Yadanar', 'We rent bridal dresses, accessories, and provide pre-wedding studio photos.', 'approved', NULL, 1, 'https://t.me/nawpanydn', 1, '2026-06-24 04:20:55', 'supplier-v1', 'unpaid', 0, 0, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-24 04:20:55', NULL),
(134, 27, 'Shwe Phoo Sar', 'မင်္ဂလာရက်အထူးစိတ်တက်ကြွစေမယ့် ရွှေဖူးစာ (shwephoosar) ပါဝင်သော wedding dress အကြောင်းဖြစ်သည်။ သာမာန်ဝတ်စုံမဟုတ်ပါ၊ ဂါဝန် (dress) ကိုဘက်တစ်ဖက်စီလှည့်ပြီး လှုပ်လိုက်ခြင်း၊ လက်နှစ်ဖက်ချထားသည့် စတိုင်ဖြင့် မင်္ဂလာနေ့မှာ အထင်ကရ ပေါ်လာစေနိုင်သည်။', 'verified', 1, 1, 'https://www.tiktok.com/@shwephoosar22', 1, '2026-06-25 04:43:57', 'supplier-v1', 'paid', 1, 0, 0, NULL, 1, 0, NULL, '\nWARN L1 (admin #1): Do the work properly — 2026-06-26 11:18:17', NULL, NULL, NULL, '2026-06-25 04:43:57', NULL),
(135, 133, 'Cake', 'Hi', 'approved', 1, 1, 'https://kudosmm.com/', 1, '2026-06-26 02:36:13', 'supplier-v1', 'paid', 1, 0, 0, NULL, 1, 0, NULL, 'BANNED: I want to ban\nUnbanned by admin #1 at 2026-06-26 21:19:54', NULL, NULL, NULL, '2026-06-26 02:36:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_bans`
--

CREATE TABLE `supplier_bans` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `banned_by` bigint(20) NOT NULL,
  `reason` text NOT NULL,
  `warning_count` int(11) NOT NULL DEFAULT 0,
  `is_permanent` tinyint(1) NOT NULL DEFAULT 0,
  `banned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lifted_by` bigint(20) DEFAULT NULL,
  `lifted_at` timestamp NULL DEFAULT NULL,
  `lift_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_categories`
--

CREATE TABLE `supplier_categories` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `category_id` bigint(20) NOT NULL,
  `source` enum('ai','manual','admin') NOT NULL DEFAULT 'manual',
  `confidence` decimal(5,4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_categories`
--

INSERT INTO `supplier_categories` (`id`, `supplier_id`, `category_id`, `source`, `confidence`, `created_at`) VALUES
(4, 20, 2, 'manual', NULL, '2026-06-10 06:38:51'),
(5, 21, 3, 'manual', NULL, '2026-06-11 05:01:15'),
(6, 21, 4, 'manual', NULL, '2026-06-11 05:01:15'),
(7, 21, 6, 'manual', NULL, '2026-06-11 05:01:15'),
(8, 131, 2, 'manual', NULL, '2026-06-23 04:23:48'),
(9, 132, 5, 'manual', NULL, '2026-06-23 05:50:02'),
(10, 133, 2, 'manual', NULL, '2026-06-24 04:20:55'),
(11, 133, 5, 'manual', NULL, '2026-06-24 04:20:55'),
(12, 134, 2, 'manual', NULL, '2026-06-25 04:43:57'),
(13, 135, 3, 'manual', NULL, '2026-06-26 02:36:13');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_documents`
--

CREATE TABLE `supplier_documents` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_documents`
--

INSERT INTO `supplier_documents` (`id`, `supplier_id`, `file_url`, `type`, `created_at`) VALUES
(8, 20, 'http://localhost/GP/public/uploads/suppliers/20-jv/documents/cover-photo-20260610083851-a953579d.jpg', 'cover_photo', '2026-06-10 06:38:51'),
(9, 20, 'http://localhost/GP/public/uploads/suppliers/20-jv/documents/business-license-20260610083851-8ff92fb4.jpg', 'business_license', '2026-06-10 06:38:51'),
(10, 21, 'http://localhost/GP/public/uploads/suppliers/21-wyndham-grand-yangon-hotel/documents/cover-photo-20260611070115-9e2abb41.jpg', 'cover_photo', '2026-06-11 05:01:15'),
(11, 21, 'http://localhost/GP/public/uploads/suppliers/21-wyndham-grand-yangon-hotel/documents/business-license-20260611070115-21f65a81.pdf', 'business_license', '2026-06-11 05:01:15'),
(12, 131, 'https://7234de2136702a21-204-157-173-242.serveousercontent.com/GP/public/uploads/suppliers/131-chanel/documents/cover-photo-20260623105348-13d2f1a5.webp', 'cover_photo', '2026-06-23 04:23:48'),
(13, 131, 'https://7234de2136702a21-204-157-173-242.serveousercontent.com/GP/public/uploads/suppliers/131-chanel/documents/business-license-20260623105348-5237b5cd.jpg', 'business_license', '2026-06-23 04:23:48'),
(14, 132, 'https://7234de2136702a21-204-157-173-242.serveousercontent.com/GP/public/uploads/suppliers/132-forever-wedding-studio/documents/cover-photo-20260623122002-5bfdf9ed.png', 'cover_photo', '2026-06-23 05:50:02'),
(15, 132, 'https://7234de2136702a21-204-157-173-242.serveousercontent.com/GP/public/uploads/suppliers/132-forever-wedding-studio/documents/business-license-20260623122002-891fd2fc.jpg', 'business_license', '2026-06-23 05:50:02'),
(16, 133, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/suppliers/133-yadanar/documents/cover-photo-20260624105055-df889d83.jpg', 'cover_photo', '2026-06-24 04:20:55'),
(17, 133, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/suppliers/133-yadanar/documents/business-license-20260624105055-d7543d79.jpg', 'business_license', '2026-06-24 04:20:55'),
(18, 134, 'https://9b5bcca332b0e95c-136-228-172-224.serveousercontent.com/GP/public/uploads/suppliers/134-shwe-phoo-sar/documents/cover-photo-20260625111357-94b6cc23.jpg', 'cover_photo', '2026-06-25 04:43:57'),
(19, 134, 'https://9b5bcca332b0e95c-136-228-172-224.serveousercontent.com/GP/public/uploads/suppliers/134-shwe-phoo-sar/documents/business-license-20260625111357-2a026943.jpg', 'business_license', '2026-06-25 04:43:57'),
(20, 135, 'https://4b2b50ea8de75a5e-204-157-173-127.serveousercontent.com/GP/public/uploads/suppliers/135-cake/documents/cover-photo-20260626090613-457f4545.jpg', 'cover_photo', '2026-06-26 02:36:13'),
(21, 135, 'https://4b2b50ea8de75a5e-204-157-173-127.serveousercontent.com/GP/public/uploads/suppliers/135-cake/documents/business-license-20260626090613-65bfbf9f.png', 'business_license', '2026-06-26 02:36:13');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_warnings`
--

CREATE TABLE `supplier_warnings` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `issued_by` bigint(20) NOT NULL,
  `reason` text NOT NULL,
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `source` enum('customer_report','staff_review','system') NOT NULL DEFAULT 'customer_report',
  `booking_id` bigint(20) DEFAULT NULL,
  `review_id` bigint(20) DEFAULT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `resolved_by` bigint(20) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `login_time`, `last_active`, `logout_time`, `created_at`) VALUES
(4, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-22 02:20:18', '2026-05-22 02:20:18', NULL, '2026-05-22 02:20:18'),
(5, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-22 02:20:50', '2026-05-22 02:20:50', NULL, '2026-05-22 02:20:50'),
(6, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-22 02:21:01', '2026-05-22 02:21:01', NULL, '2026-05-22 02:21:01'),
(7, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-22 02:21:23', '2026-05-22 02:21:23', NULL, '2026-05-22 02:21:23'),
(12, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-23 07:40:45', '2026-05-23 07:40:45', NULL, '2026-05-23 07:40:45'),
(13, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-23 07:41:26', '2026-05-23 07:41:26', NULL, '2026-05-23 07:41:26'),
(14, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-23 07:41:43', '2026-05-23 07:41:43', NULL, '2026-05-23 07:41:43'),
(15, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:32:28', '2026-05-24 02:32:28', NULL, '2026-05-24 02:32:28'),
(16, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:32:42', '2026-05-24 02:32:42', NULL, '2026-05-24 02:32:42'),
(17, 1, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:33:13', '2026-05-24 02:33:13', NULL, '2026-05-24 02:33:13'),
(18, 1, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:33:24', '2026-05-24 02:33:24', NULL, '2026-05-24 02:33:24'),
(19, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:33:33', '2026-05-24 02:33:33', NULL, '2026-05-24 02:33:33'),
(20, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:33:50', '2026-05-24 02:33:50', NULL, '2026-05-24 02:33:50'),
(21, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-05-24 02:33:57', '2026-05-24 02:33:57', NULL, '2026-05-24 02:33:57'),
(50, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 14:07:35', '2026-06-03 14:07:35', '2026-06-03 14:07:35', '2026-06-03 14:07:35'),
(53, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-03 14:38:07', '2026-06-03 14:38:07', '2026-06-03 14:38:07', '2026-06-03 14:38:07'),
(57, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:54:05', '2026-06-10 02:54:05', '2026-06-10 02:54:05', '2026-06-10 02:54:05'),
(58, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 02:54:35', '2026-06-10 02:54:35', NULL, '2026-06-10 02:54:35'),
(59, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 04:40:15', '2026-06-10 04:40:15', '2026-06-10 04:40:15', '2026-06-10 04:40:15'),
(61, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 04:44:32', '2026-06-10 04:44:32', '2026-06-10 04:44:32', '2026-06-10 04:44:32'),
(62, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 06:39:07', '2026-06-10 06:39:07', '2026-06-10 06:39:07', '2026-06-10 06:39:07'),
(63, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 06:39:43', '2026-06-10 06:39:43', '2026-06-10 06:39:43', '2026-06-10 06:39:43'),
(65, 27, 'register_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 02:32:31', '2026-06-11 02:32:31', NULL, '2026-06-11 02:32:31'),
(66, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 04:10:46', '2026-06-11 04:10:46', NULL, '2026-06-11 04:10:46'),
(67, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 04:11:38', '2026-06-11 04:11:38', NULL, '2026-06-11 04:11:38'),
(68, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 04:11:48', '2026-06-11 04:11:48', NULL, '2026-06-11 04:11:48'),
(70, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 05:01:26', '2026-06-11 05:01:26', '2026-06-11 05:01:26', '2026-06-11 05:01:26'),
(71, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 05:01:34', '2026-06-11 05:01:34', NULL, '2026-06-11 05:01:34'),
(72, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 05:01:42', '2026-06-11 05:01:42', NULL, '2026-06-11 05:01:42'),
(73, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 05:06:45', '2026-06-11 05:06:45', NULL, '2026-06-11 05:06:45'),
(74, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 05:06:54', '2026-06-11 05:06:54', NULL, '2026-06-11 05:06:54'),
(75, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 12:40:57', '2026-06-11 12:40:57', NULL, '2026-06-11 12:40:57'),
(76, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 12:41:16', '2026-06-11 12:41:16', NULL, '2026-06-11 12:41:16'),
(77, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 12:41:37', '2026-06-11 12:41:37', NULL, '2026-06-11 12:41:37'),
(78, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 14:07:57', '2026-06-11 14:07:57', NULL, '2026-06-11 14:07:57'),
(79, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 14:08:04', '2026-06-11 14:08:04', NULL, '2026-06-11 14:08:04'),
(80, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 14:19:17', '2026-06-11 14:19:17', NULL, '2026-06-11 14:19:17'),
(81, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-11 14:19:26', '2026-06-11 14:19:26', '2026-06-11 14:19:26', '2026-06-11 14:19:26'),
(82, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 05:50:16', '2026-06-13 05:50:16', '2026-06-13 05:50:16', '2026-06-13 05:50:16'),
(83, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-13 05:50:22', '2026-06-13 05:50:22', NULL, '2026-06-13 05:50:22'),
(84, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:17:34', '2026-06-17 04:17:34', NULL, '2026-06-17 04:17:34'),
(85, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:17:54', '2026-06-17 04:17:54', NULL, '2026-06-17 04:17:54'),
(86, 1, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:18:10', '2026-06-17 04:18:10', NULL, '2026-06-17 04:18:10'),
(87, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:18:26', '2026-06-17 04:18:26', NULL, '2026-06-17 04:18:26'),
(88, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:18:39', '2026-06-17 04:18:39', NULL, '2026-06-17 04:18:39'),
(89, 29, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-18 05:07:54', '2026-06-18 05:07:54', '2026-06-18 05:07:54', '2026-06-18 05:07:54'),
(90, 27, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-18 09:43:17', '2026-06-18 09:43:17', '2026-06-18 09:43:17', '2026-06-18 09:43:17'),
(91, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-19 07:16:21', '2026-06-19 07:16:21', '2026-06-19 07:16:21', '2026-06-19 07:16:21'),
(92, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-19 14:35:25', '2026-06-19 14:35:25', '2026-06-19 14:35:25', '2026-06-19 14:35:25'),
(93, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-19 14:35:34', '2026-06-19 14:35:34', '2026-06-19 14:35:34', '2026-06-19 14:35:34'),
(94, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-19 14:36:01', '2026-06-19 14:36:01', '2026-06-19 14:36:01', '2026-06-19 14:36:01'),
(95, 29, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 14:05:18', '2026-06-20 14:05:18', '2026-06-20 14:05:18', '2026-06-20 14:05:18'),
(96, 29, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 14:34:07', '2026-06-20 14:34:07', '2026-06-20 14:34:07', '2026-06-20 14:34:07'),
(97, 47, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 14:34:15', '2026-06-20 14:34:15', NULL, '2026-06-20 14:34:15'),
(98, 47, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 14:57:38', '2026-06-20 14:57:38', NULL, '2026-06-20 14:57:38'),
(99, 47, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 14:57:57', '2026-06-20 14:57:57', NULL, '2026-06-20 14:57:57'),
(100, 47, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 14:58:16', '2026-06-20 14:58:16', NULL, '2026-06-20 14:58:16'),
(101, 47, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 15:21:07', '2026-06-20 15:21:07', '2026-06-20 15:21:07', '2026-06-20 15:21:07'),
(102, 40, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 15:23:25', '2026-06-20 15:23:25', NULL, '2026-06-20 15:23:25'),
(103, 40, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 15:23:36', '2026-06-20 15:23:36', NULL, '2026-06-20 15:23:36'),
(104, 40, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-20 15:24:02', '2026-06-20 15:24:02', NULL, '2026-06-20 15:24:02'),
(106, 37, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 06:29:53', '2026-06-21 06:29:53', NULL, '2026-06-21 06:29:53'),
(107, 37, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 06:30:00', '2026-06-21 06:30:00', NULL, '2026-06-21 06:30:00'),
(108, 37, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 06:30:34', '2026-06-21 06:30:34', NULL, '2026-06-21 06:30:34'),
(109, 37, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 07:18:28', '2026-06-21 07:18:28', NULL, '2026-06-21 07:18:28'),
(110, 37, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 07:19:01', '2026-06-21 07:19:01', NULL, '2026-06-21 07:19:01'),
(111, 37, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 07:21:17', '2026-06-21 07:21:17', NULL, '2026-06-21 07:21:17'),
(112, 37, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-21 07:21:31', '2026-06-21 07:21:31', NULL, '2026-06-21 07:21:31'),
(113, 30, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 07:31:17', '2026-06-22 07:31:17', '2026-06-22 07:31:17', '2026-06-22 07:31:17'),
(114, 102, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 07:45:59', '2026-06-22 07:45:59', NULL, '2026-06-22 07:45:59'),
(115, 102, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 07:46:04', '2026-06-22 07:46:04', NULL, '2026-06-22 07:46:04'),
(116, 102, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-22 07:46:46', '2026-06-22 07:46:46', NULL, '2026-06-22 07:46:46'),
(117, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 03:14:11', '2026-06-23 03:14:11', NULL, '2026-06-23 03:14:11'),
(118, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 03:15:13', '2026-06-23 03:15:13', NULL, '2026-06-23 03:15:13'),
(119, 105, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 03:15:16', '2026-06-23 03:15:16', NULL, '2026-06-23 03:15:16'),
(120, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 03:15:28', '2026-06-23 03:15:28', NULL, '2026-06-23 03:15:28'),
(121, 106, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 03:20:07', '2026-06-23 03:20:07', NULL, '2026-06-23 03:20:07'),
(122, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:01:48', '2026-06-23 04:01:48', NULL, '2026-06-23 04:01:48'),
(123, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:01:57', '2026-06-23 04:01:57', NULL, '2026-06-23 04:01:57'),
(124, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:02:22', '2026-06-23 04:02:22', NULL, '2026-06-23 04:02:22'),
(125, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:03:28', '2026-06-23 04:03:28', NULL, '2026-06-23 04:03:28'),
(126, 107, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:07:45', '2026-06-23 04:07:45', NULL, '2026-06-23 04:07:45'),
(128, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:15:39', '2026-06-23 04:15:39', '2026-06-23 04:15:39', '2026-06-23 04:15:39'),
(129, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:20:38', '2026-06-23 04:20:38', NULL, '2026-06-23 04:20:38'),
(130, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:20:48', '2026-06-23 04:20:48', NULL, '2026-06-23 04:20:48'),
(131, 105, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:21:13', '2026-06-23 04:21:13', NULL, '2026-06-23 04:21:13'),
(132, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:21:47', '2026-06-23 04:21:47', NULL, '2026-06-23 04:21:47'),
(133, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:22:14', '2026-06-23 04:22:14', NULL, '2026-06-23 04:22:14'),
(134, 109, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:23:00', '2026-06-23 04:23:00', NULL, '2026-06-23 04:23:00'),
(135, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:24:47', '2026-06-23 04:24:47', NULL, '2026-06-23 04:24:47'),
(136, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:25:09', '2026-06-23 04:25:09', NULL, '2026-06-23 04:25:09'),
(137, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:25:24', '2026-06-23 04:25:24', NULL, '2026-06-23 04:25:24'),
(138, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:25:37', '2026-06-23 04:25:37', NULL, '2026-06-23 04:25:37'),
(139, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:29:05', '2026-06-23 04:29:05', NULL, '2026-06-23 04:29:05'),
(140, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:30:07', '2026-06-23 04:30:07', NULL, '2026-06-23 04:30:07'),
(141, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:30:16', '2026-06-23 04:30:16', NULL, '2026-06-23 04:30:16'),
(142, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:30:29', '2026-06-23 04:30:29', NULL, '2026-06-23 04:30:29'),
(143, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:31:03', '2026-06-23 04:31:03', NULL, '2026-06-23 04:31:03'),
(145, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:32:28', '2026-06-23 04:32:28', NULL, '2026-06-23 04:32:28'),
(146, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:33:02', '2026-06-23 04:33:02', NULL, '2026-06-23 04:33:02'),
(147, 105, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:34:16', '2026-06-23 04:34:16', '2026-06-23 04:34:16', '2026-06-23 04:34:16'),
(148, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:38:13', '2026-06-23 04:38:13', NULL, '2026-06-23 04:38:13'),
(150, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:38:22', '2026-06-23 04:38:22', NULL, '2026-06-23 04:38:22'),
(151, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:38:46', '2026-06-23 04:38:46', NULL, '2026-06-23 04:38:46'),
(161, 111, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 04:54:27', '2026-06-23 04:54:27', NULL, '2026-06-23 04:54:27'),
(165, 112, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 04:58:22', '2026-06-23 04:58:22', NULL, '2026-06-23 04:58:22'),
(166, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:00:21', '2026-06-23 05:00:21', NULL, '2026-06-23 05:00:21'),
(167, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:01:03', '2026-06-23 05:01:03', NULL, '2026-06-23 05:01:03'),
(168, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:01:18', '2026-06-23 05:01:18', NULL, '2026-06-23 05:01:18'),
(169, 113, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:02:36', '2026-06-23 05:02:36', NULL, '2026-06-23 05:02:36'),
(170, 113, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:04:24', '2026-06-23 05:04:24', NULL, '2026-06-23 05:04:24'),
(171, 113, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:04:34', '2026-06-23 05:04:34', NULL, '2026-06-23 05:04:34'),
(172, 113, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:04:47', '2026-06-23 05:04:47', NULL, '2026-06-23 05:04:47'),
(173, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:08:58', '2026-06-23 05:08:58', NULL, '2026-06-23 05:08:58'),
(174, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:09:05', '2026-06-23 05:09:05', NULL, '2026-06-23 05:09:05'),
(175, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:09:23', '2026-06-23 05:09:23', NULL, '2026-06-23 05:09:23'),
(176, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:16:41', '2026-06-23 05:16:41', NULL, '2026-06-23 05:16:41'),
(177, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:16:49', '2026-06-23 05:16:49', NULL, '2026-06-23 05:16:49'),
(178, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:17:25', '2026-06-23 05:17:25', NULL, '2026-06-23 05:17:25'),
(179, 24, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:20:27', '2026-06-23 05:20:27', NULL, '2026-06-23 05:20:27'),
(180, 24, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:20:32', '2026-06-23 05:20:32', NULL, '2026-06-23 05:20:32'),
(181, 24, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 05:20:47', '2026-06-23 05:20:47', NULL, '2026-06-23 05:20:47'),
(182, 114, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:27:33', '2026-06-23 05:27:33', NULL, '2026-06-23 05:27:33'),
(183, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:30:36', '2026-06-23 05:30:36', NULL, '2026-06-23 05:30:36'),
(184, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:30:44', '2026-06-23 05:30:44', NULL, '2026-06-23 05:30:44'),
(185, 114, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 05:31:17', '2026-06-23 05:31:17', NULL, '2026-06-23 05:31:17'),
(186, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 12:58:11', '2026-06-23 12:58:11', NULL, '2026-06-23 12:58:11'),
(187, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 12:58:55', '2026-06-23 12:58:55', NULL, '2026-06-23 12:58:55'),
(188, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 12:58:59', '2026-06-23 12:58:59', NULL, '2026-06-23 12:58:59'),
(189, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:00:16', '2026-06-23 13:00:16', NULL, '2026-06-23 13:00:16'),
(190, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:00:21', '2026-06-23 13:00:21', NULL, '2026-06-23 13:00:21'),
(191, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:01:32', '2026-06-23 13:01:32', NULL, '2026-06-23 13:01:32'),
(192, 109, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:02:16', '2026-06-23 13:02:16', NULL, '2026-06-23 13:02:16'),
(193, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:02:42', '2026-06-23 13:02:42', NULL, '2026-06-23 13:02:42'),
(194, 109, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:03:24', '2026-06-23 13:03:24', NULL, '2026-06-23 13:03:24'),
(195, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:04:03', '2026-06-23 13:04:03', NULL, '2026-06-23 13:04:03'),
(196, 109, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:04:32', '2026-06-23 13:04:32', NULL, '2026-06-23 13:04:32'),
(197, 109, 'lock_account', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:04:36', '2026-06-23 13:04:36', NULL, '2026-06-23 13:04:36'),
(198, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 13:05:41', '2026-06-23 13:05:41', NULL, '2026-06-23 13:05:41'),
(199, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:06:20', '2026-06-23 13:06:20', NULL, '2026-06-23 13:06:20'),
(200, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:06:40', '2026-06-23 13:06:40', NULL, '2026-06-23 13:06:40'),
(201, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 13:09:53', '2026-06-23 13:09:53', NULL, '2026-06-23 13:09:53'),
(202, 29, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:10:51', '2026-06-23 13:10:51', NULL, '2026-06-23 13:10:51'),
(203, 29, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:11:49', '2026-06-23 13:11:49', '2026-06-23 13:11:49', '2026-06-23 13:11:49'),
(204, 115, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 13:15:22', '2026-06-23 13:15:22', NULL, '2026-06-23 13:15:22'),
(205, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:39:40', '2026-06-23 13:39:40', NULL, '2026-06-23 13:39:40'),
(206, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:40:29', '2026-06-23 13:40:29', NULL, '2026-06-23 13:40:29'),
(207, 109, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:41:16', '2026-06-23 13:41:16', NULL, '2026-06-23 13:41:16'),
(208, 109, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:41:54', '2026-06-23 13:41:54', NULL, '2026-06-23 13:41:54'),
(209, 109, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:42:13', '2026-06-23 13:42:13', NULL, '2026-06-23 13:42:13'),
(210, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:43:09', '2026-06-23 13:43:09', NULL, '2026-06-23 13:43:09'),
(211, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:43:26', '2026-06-23 13:43:26', NULL, '2026-06-23 13:43:26'),
(212, 109, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:43:41', '2026-06-23 13:43:41', NULL, '2026-06-23 13:43:41'),
(213, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:44:44', '2026-06-23 13:44:44', NULL, '2026-06-23 13:44:44'),
(214, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 13:45:18', '2026-06-23 13:45:18', NULL, '2026-06-23 13:45:18'),
(215, 116, 'register_success', '::1', 'curl/8.7.1', '2026-06-23 14:25:48', '2026-06-23 14:25:48', NULL, '2026-06-23 14:25:48'),
(216, 117, 'register_success', '::1', 'curl/8.7.1', '2026-06-23 14:26:03', '2026-06-23 14:26:03', NULL, '2026-06-23 14:26:03'),
(217, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 14:57:17', '2026-06-23 14:57:17', NULL, '2026-06-23 14:57:17'),
(218, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 14:57:48', '2026-06-23 14:57:48', NULL, '2026-06-23 14:57:48'),
(219, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 14:58:11', '2026-06-23 14:58:11', NULL, '2026-06-23 14:58:11'),
(220, 109, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 14:59:09', '2026-06-23 14:59:09', '2026-06-23 14:59:09', '2026-06-23 14:59:09'),
(221, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 14:59:39', '2026-06-23 14:59:39', NULL, '2026-06-23 14:59:39'),
(222, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 14:59:48', '2026-06-23 14:59:48', NULL, '2026-06-23 14:59:48'),
(223, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:00:02', '2026-06-23 15:00:02', NULL, '2026-06-23 15:00:02'),
(224, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:00:37', '2026-06-23 15:00:37', NULL, '2026-06-23 15:00:37'),
(225, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:02:45', '2026-06-23 15:02:45', NULL, '2026-06-23 15:02:45'),
(226, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:03:00', '2026-06-23 15:03:00', NULL, '2026-06-23 15:03:00'),
(227, 109, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:03:26', '2026-06-23 15:03:26', NULL, '2026-06-23 15:03:26'),
(228, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:04:14', '2026-06-23 15:04:14', NULL, '2026-06-23 15:04:14'),
(229, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:04:34', '2026-06-23 15:04:34', NULL, '2026-06-23 15:04:34'),
(230, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:12:17', '2026-06-23 15:12:17', NULL, '2026-06-23 15:12:17'),
(231, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:13:34', '2026-06-23 15:13:34', NULL, '2026-06-23 15:13:34'),
(232, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:13:48', '2026-06-23 15:13:48', NULL, '2026-06-23 15:13:48'),
(233, 114, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:14:15', '2026-06-23 15:14:15', NULL, '2026-06-23 15:14:15'),
(234, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:16:11', '2026-06-23 15:16:11', NULL, '2026-06-23 15:16:11'),
(235, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:16:30', '2026-06-23 15:16:30', NULL, '2026-06-23 15:16:30'),
(236, 109, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:23:47', '2026-06-23 15:23:47', '2026-06-23 15:23:47', '2026-06-23 15:23:47'),
(237, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:24:17', '2026-06-23 15:24:17', NULL, '2026-06-23 15:24:17'),
(238, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:24:25', '2026-06-23 15:24:25', NULL, '2026-06-23 15:24:25'),
(239, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:24:48', '2026-06-23 15:24:48', NULL, '2026-06-23 15:24:48'),
(240, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-23 15:28:32', '2026-06-23 15:28:32', NULL, '2026-06-23 15:28:32'),
(241, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:35:11', '2026-06-23 18:35:11', NULL, '2026-06-23 18:35:11'),
(242, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:37:49', '2026-06-23 18:37:49', NULL, '2026-06-23 18:37:49'),
(243, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:38:47', '2026-06-23 18:38:47', NULL, '2026-06-23 18:38:47'),
(244, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:40:35', '2026-06-23 18:40:35', NULL, '2026-06-23 18:40:35'),
(245, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:41:33', '2026-06-23 18:41:33', NULL, '2026-06-23 18:41:33'),
(246, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:41:37', '2026-06-23 18:41:37', NULL, '2026-06-23 18:41:37'),
(247, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:46:39', '2026-06-23 18:46:39', NULL, '2026-06-23 18:46:39'),
(248, 29, 'login_information_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-23 18:48:46', '2026-06-23 18:48:46', NULL, '2026-06-23 18:48:46'),
(249, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:04:13', '2026-06-24 02:04:13', NULL, '2026-06-24 02:04:13'),
(250, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:04:29', '2026-06-24 02:04:29', NULL, '2026-06-24 02:04:29'),
(251, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:04:38', '2026-06-24 02:04:38', NULL, '2026-06-24 02:04:38'),
(252, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:05:43', '2026-06-24 02:05:43', NULL, '2026-06-24 02:05:43'),
(253, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:06:18', '2026-06-24 02:06:18', NULL, '2026-06-24 02:06:18'),
(254, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:07:43', '2026-06-24 02:07:43', NULL, '2026-06-24 02:07:43'),
(255, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:07:50', '2026-06-24 02:07:50', NULL, '2026-06-24 02:07:50'),
(256, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:07:58', '2026-06-24 02:07:58', NULL, '2026-06-24 02:07:58'),
(257, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:08:21', '2026-06-24 02:08:21', NULL, '2026-06-24 02:08:21'),
(258, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:08:30', '2026-06-24 02:08:30', NULL, '2026-06-24 02:08:30'),
(259, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:08:48', '2026-06-24 02:08:48', NULL, '2026-06-24 02:08:48'),
(260, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:09:06', '2026-06-24 02:09:06', NULL, '2026-06-24 02:09:06'),
(261, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:19:11', '2026-06-24 02:19:11', NULL, '2026-06-24 02:19:11'),
(262, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:19:23', '2026-06-24 02:19:23', NULL, '2026-06-24 02:19:23'),
(263, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:19:38', '2026-06-24 02:19:38', NULL, '2026-06-24 02:19:38'),
(264, 118, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 02:23:58', '2026-06-24 02:23:58', NULL, '2026-06-24 02:23:58'),
(265, 119, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:07:57', '2026-06-24 03:07:57', NULL, '2026-06-24 03:07:57'),
(266, 105, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:11:13', '2026-06-24 03:11:13', '2026-06-24 03:11:13', '2026-06-24 03:11:13'),
(267, 120, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:12:13', '2026-06-24 03:12:13', NULL, '2026-06-24 03:12:13');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `login_time`, `last_active`, `logout_time`, `created_at`) VALUES
(268, 121, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:15:03', '2026-06-24 03:15:03', NULL, '2026-06-24 03:15:03'),
(269, 112, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:16:26', '2026-06-24 03:16:26', '2026-06-24 03:16:26', '2026-06-24 03:16:26'),
(270, 122, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:17:26', '2026-06-24 03:17:26', NULL, '2026-06-24 03:17:26'),
(271, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:18:02', '2026-06-24 03:18:02', NULL, '2026-06-24 03:18:02'),
(272, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:18:09', '2026-06-24 03:18:09', NULL, '2026-06-24 03:18:09'),
(273, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:18:23', '2026-06-24 03:18:23', NULL, '2026-06-24 03:18:23'),
(274, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:19:19', '2026-06-24 03:19:19', NULL, '2026-06-24 03:19:19'),
(275, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:19:27', '2026-06-24 03:19:27', NULL, '2026-06-24 03:19:27'),
(276, 112, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:19:45', '2026-06-24 03:19:45', NULL, '2026-06-24 03:19:45'),
(277, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:19:59', '2026-06-24 03:19:59', NULL, '2026-06-24 03:19:59'),
(278, 109, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:20:03', '2026-06-24 03:20:03', NULL, '2026-06-24 03:20:03'),
(279, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:20:13', '2026-06-24 03:20:13', NULL, '2026-06-24 03:20:13'),
(280, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:20:34', '2026-06-24 03:20:34', NULL, '2026-06-24 03:20:34'),
(281, 122, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:20:50', '2026-06-24 03:20:50', NULL, '2026-06-24 03:20:50'),
(282, 120, 'ResetToken_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:21:12', '2026-06-24 03:21:12', NULL, '2026-06-24 03:21:12'),
(283, 122, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:21:28', '2026-06-24 03:21:28', NULL, '2026-06-24 03:21:28'),
(284, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:21:47', '2026-06-24 03:21:47', NULL, '2026-06-24 03:21:47'),
(285, 122, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:21:47', '2026-06-24 03:21:47', NULL, '2026-06-24 03:21:47'),
(286, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:22:12', '2026-06-24 03:22:12', NULL, '2026-06-24 03:22:12'),
(287, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:22:21', '2026-06-24 03:22:21', NULL, '2026-06-24 03:22:21'),
(288, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:22:38', '2026-06-24 03:22:38', NULL, '2026-06-24 03:22:38'),
(289, 121, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:22:40', '2026-06-24 03:22:40', NULL, '2026-06-24 03:22:40'),
(290, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:22:41', '2026-06-24 03:22:41', NULL, '2026-06-24 03:22:41'),
(291, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:22:46', '2026-06-24 03:22:46', NULL, '2026-06-24 03:22:46'),
(292, 121, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:22:55', '2026-06-24 03:22:55', NULL, '2026-06-24 03:22:55'),
(293, 121, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:23:02', '2026-06-24 03:23:02', NULL, '2026-06-24 03:23:02'),
(294, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:23:02', '2026-06-24 03:23:02', NULL, '2026-06-24 03:23:02'),
(295, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:23:06', '2026-06-24 03:23:06', NULL, '2026-06-24 03:23:06'),
(296, 121, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:23:12', '2026-06-24 03:23:12', NULL, '2026-06-24 03:23:12'),
(297, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:23:23', '2026-06-24 03:23:23', NULL, '2026-06-24 03:23:23'),
(298, 121, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:23:33', '2026-06-24 03:23:33', NULL, '2026-06-24 03:23:33'),
(299, 123, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:24:38', '2026-06-24 03:24:38', NULL, '2026-06-24 03:24:38'),
(300, 123, 'ResetToken_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:25:44', '2026-06-24 03:25:44', NULL, '2026-06-24 03:25:44'),
(301, 124, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:28:35', '2026-06-24 03:28:35', NULL, '2026-06-24 03:28:35'),
(302, 121, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:31:27', '2026-06-24 03:31:27', '2026-06-24 03:31:27', '2026-06-24 03:31:27'),
(303, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:31:53', '2026-06-24 03:31:53', NULL, '2026-06-24 03:31:53'),
(304, 124, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:52:46', '2026-06-24 03:52:46', NULL, '2026-06-24 03:52:46'),
(305, 122, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:53:14', '2026-06-24 03:53:14', '2026-06-24 03:53:14', '2026-06-24 03:53:14'),
(306, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:53:14', '2026-06-24 03:53:14', NULL, '2026-06-24 03:53:14'),
(307, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:53:35', '2026-06-24 03:53:35', NULL, '2026-06-24 03:53:35'),
(308, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:53:55', '2026-06-24 03:53:55', NULL, '2026-06-24 03:53:55'),
(309, 125, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:55:30', '2026-06-24 03:55:30', NULL, '2026-06-24 03:55:30'),
(310, 123, 'login_information_correct', '::1', 'Mozilla/5.0 (Linux; U; Android 16; en-us; 2312DRA50C Build/BP2A.250605.031.A3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/20.21.1050617', '2026-06-24 03:57:18', '2026-06-24 03:57:18', NULL, '2026-06-24 03:57:18'),
(311, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:57:29', '2026-06-24 03:57:29', NULL, '2026-06-24 03:57:29'),
(312, 126, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 03:58:34', '2026-06-24 03:58:34', NULL, '2026-06-24 03:58:34'),
(313, 123, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Linux; U; Android 16; en-us; 2312DRA50C Build/BP2A.250605.031.A3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/20.21.1050617', '2026-06-24 03:58:58', '2026-06-24 03:58:58', NULL, '2026-06-24 03:58:58'),
(314, 123, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Linux; U; Android 16; en-us; 2312DRA50C Build/BP2A.250605.031.A3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/20.21.1050617', '2026-06-24 03:59:17', '2026-06-24 03:59:17', NULL, '2026-06-24 03:59:17'),
(315, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 03:59:54', '2026-06-24 03:59:54', NULL, '2026-06-24 03:59:54'),
(316, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:00:16', '2026-06-24 04:00:16', NULL, '2026-06-24 04:00:16'),
(317, 123, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Linux; U; Android 16; en-us; 2312DRA50C Build/BP2A.250605.031.A3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/20.21.1050617', '2026-06-24 04:00:35', '2026-06-24 04:00:35', NULL, '2026-06-24 04:00:35'),
(318, 123, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Linux; U; Android 16; en-us; 2312DRA50C Build/BP2A.250605.031.A3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.7049.79 Mobile Safari/537.36 XiaoMi/MiuiBrowser/20.21.1050617', '2026-06-24 04:00:58', '2026-06-24 04:00:58', NULL, '2026-06-24 04:00:58'),
(319, 124, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:04:17', '2026-06-24 04:04:17', NULL, '2026-06-24 04:04:17'),
(320, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:04:31', '2026-06-24 04:04:31', NULL, '2026-06-24 04:04:31'),
(321, 113, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:05:29', '2026-06-24 04:05:29', NULL, '2026-06-24 04:05:29'),
(322, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:05:48', '2026-06-24 04:05:48', NULL, '2026-06-24 04:05:48'),
(323, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:06:15', '2026-06-24 04:06:15', NULL, '2026-06-24 04:06:15'),
(324, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:06:42', '2026-06-24 04:06:42', NULL, '2026-06-24 04:06:42'),
(325, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:07:03', '2026-06-24 04:07:03', NULL, '2026-06-24 04:07:03'),
(326, 113, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:07:25', '2026-06-24 04:07:25', NULL, '2026-06-24 04:07:25'),
(327, 113, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:07:51', '2026-06-24 04:07:51', NULL, '2026-06-24 04:07:51'),
(328, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:08:39', '2026-06-24 04:08:39', NULL, '2026-06-24 04:08:39'),
(329, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:08:58', '2026-06-24 04:08:58', NULL, '2026-06-24 04:08:58'),
(330, 124, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:15:35', '2026-06-24 04:15:35', '2026-06-24 04:15:35', '2026-06-24 04:15:35'),
(331, 124, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:16:35', '2026-06-24 04:16:35', NULL, '2026-06-24 04:16:35'),
(332, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:17:36', '2026-06-24 04:17:36', NULL, '2026-06-24 04:17:36'),
(333, 124, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:17:49', '2026-06-24 04:17:49', NULL, '2026-06-24 04:17:49'),
(334, 112, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:17:59', '2026-06-24 04:17:59', '2026-06-24 04:17:59', '2026-06-24 04:17:59'),
(335, 112, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:18:11', '2026-06-24 04:18:11', NULL, '2026-06-24 04:18:11'),
(336, 112, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:18:18', '2026-06-24 04:18:18', NULL, '2026-06-24 04:18:18'),
(337, 112, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:18:37', '2026-06-24 04:18:37', NULL, '2026-06-24 04:18:37'),
(338, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:18:53', '2026-06-24 04:18:53', NULL, '2026-06-24 04:18:53'),
(339, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:19:18', '2026-06-24 04:19:18', NULL, '2026-06-24 04:19:18'),
(340, 24, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:19:46', '2026-06-24 04:19:46', NULL, '2026-06-24 04:19:46'),
(341, 24, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:20:18', '2026-06-24 04:20:18', NULL, '2026-06-24 04:20:18'),
(342, 121, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:21:33', '2026-06-24 04:21:33', NULL, '2026-06-24 04:21:33'),
(343, 24, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:21:43', '2026-06-24 04:21:43', NULL, '2026-06-24 04:21:43'),
(344, 121, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:21:47', '2026-06-24 04:21:47', NULL, '2026-06-24 04:21:47'),
(345, 24, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:22:06', '2026-06-24 04:22:06', NULL, '2026-06-24 04:22:06'),
(346, 121, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:22:09', '2026-06-24 04:22:09', NULL, '2026-06-24 04:22:09'),
(347, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:28:32', '2026-06-24 04:28:32', NULL, '2026-06-24 04:28:32'),
(348, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:28:40', '2026-06-24 04:28:40', NULL, '2026-06-24 04:28:40'),
(349, 114, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:29:31', '2026-06-24 04:29:31', NULL, '2026-06-24 04:29:31'),
(350, 124, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:29:38', '2026-06-24 04:29:38', NULL, '2026-06-24 04:29:38'),
(351, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:29:46', '2026-06-24 04:29:46', NULL, '2026-06-24 04:29:46'),
(352, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:30:00', '2026-06-24 04:30:00', NULL, '2026-06-24 04:30:00'),
(353, 127, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:34:08', '2026-06-24 04:34:08', NULL, '2026-06-24 04:34:08'),
(354, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:35:01', '2026-06-24 04:35:01', NULL, '2026-06-24 04:35:01'),
(355, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:35:58', '2026-06-24 04:35:58', NULL, '2026-06-24 04:35:58'),
(356, 114, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:36:43', '2026-06-24 04:36:43', '2026-06-24 04:36:43', '2026-06-24 04:36:43'),
(357, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:36:59', '2026-06-24 04:36:59', NULL, '2026-06-24 04:36:59'),
(358, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:37:07', '2026-06-24 04:37:07', NULL, '2026-06-24 04:37:07'),
(359, 114, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:37:27', '2026-06-24 04:37:27', NULL, '2026-06-24 04:37:27'),
(360, 113, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:37:52', '2026-06-24 04:37:52', '2026-06-24 04:37:52', '2026-06-24 04:37:52'),
(361, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:38:33', '2026-06-24 04:38:33', NULL, '2026-06-24 04:38:33'),
(362, 114, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:38:54', '2026-06-24 04:38:54', NULL, '2026-06-24 04:38:54'),
(363, 111, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:49:02', '2026-06-24 04:49:02', NULL, '2026-06-24 04:49:02'),
(364, 111, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:49:10', '2026-06-24 04:49:10', NULL, '2026-06-24 04:49:10'),
(365, 111, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 04:49:35', '2026-06-24 04:49:35', NULL, '2026-06-24 04:49:35'),
(366, 124, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 04:57:00', '2026-06-24 04:57:00', '2026-06-24 04:57:00', '2026-06-24 04:57:00'),
(367, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:03:38', '2026-06-24 05:03:38', NULL, '2026-06-24 05:03:38'),
(368, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:03:59', '2026-06-24 05:03:59', NULL, '2026-06-24 05:03:59'),
(369, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:04:17', '2026-06-24 05:04:17', NULL, '2026-06-24 05:04:17'),
(370, 114, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:26:11', '2026-06-24 05:26:11', '2026-06-24 05:26:11', '2026-06-24 05:26:11'),
(371, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:26:38', '2026-06-24 05:26:38', NULL, '2026-06-24 05:26:38'),
(372, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:26:53', '2026-06-24 05:26:53', NULL, '2026-06-24 05:26:53'),
(373, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:28:21', '2026-06-24 05:28:21', NULL, '2026-06-24 05:28:21'),
(374, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:28:28', '2026-06-24 05:28:28', NULL, '2026-06-24 05:28:28'),
(375, 114, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:28:47', '2026-06-24 05:28:47', NULL, '2026-06-24 05:28:47'),
(376, 114, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:31:51', '2026-06-24 05:31:51', '2026-06-24 05:31:51', '2026-06-24 05:31:51'),
(377, 29, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:32:47', '2026-06-24 05:32:47', NULL, '2026-06-24 05:32:47'),
(378, 29, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:34:14', '2026-06-24 05:34:14', NULL, '2026-06-24 05:34:14'),
(379, 29, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:35:49', '2026-06-24 05:35:49', NULL, '2026-06-24 05:35:49'),
(380, 29, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:36:04', '2026-06-24 05:36:04', NULL, '2026-06-24 05:36:04'),
(381, 114, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:41:09', '2026-06-24 05:41:09', NULL, '2026-06-24 05:41:09'),
(382, 114, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:41:37', '2026-06-24 05:41:37', NULL, '2026-06-24 05:41:37'),
(383, 114, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-24 05:41:59', '2026-06-24 05:41:59', NULL, '2026-06-24 05:41:59'),
(384, 27, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 11:09:10', '2026-06-24 11:09:10', '2026-06-24 11:09:10', '2026-06-24 11:09:10'),
(385, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:14:36', '2026-06-24 13:14:36', NULL, '2026-06-24 13:14:36'),
(386, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:15:17', '2026-06-24 13:15:17', NULL, '2026-06-24 13:15:17'),
(387, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:15:33', '2026-06-24 13:15:33', NULL, '2026-06-24 13:15:33'),
(388, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:15:49', '2026-06-24 13:15:49', NULL, '2026-06-24 13:15:49'),
(389, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:17:20', '2026-06-24 13:17:20', NULL, '2026-06-24 13:17:20'),
(390, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:17:27', '2026-06-24 13:17:27', NULL, '2026-06-24 13:17:27'),
(391, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:17:46', '2026-06-24 13:17:46', NULL, '2026-06-24 13:17:46'),
(392, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:18:42', '2026-06-24 13:18:42', NULL, '2026-06-24 13:18:42'),
(393, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:18:49', '2026-06-24 13:18:49', NULL, '2026-06-24 13:18:49'),
(394, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-24 13:19:09', '2026-06-24 13:19:09', NULL, '2026-06-24 13:19:09'),
(395, 126, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:27:58', '2026-06-25 02:27:58', '2026-06-25 02:27:58', '2026-06-25 02:27:58'),
(396, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:28:05', '2026-06-25 02:28:05', NULL, '2026-06-25 02:28:05'),
(397, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:28:15', '2026-06-25 02:28:15', NULL, '2026-06-25 02:28:15'),
(398, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:30:16', '2026-06-25 02:30:16', NULL, '2026-06-25 02:30:16'),
(399, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:30:36', '2026-06-25 02:30:36', NULL, '2026-06-25 02:30:36'),
(400, 126, 'ResetToken_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:30:59', '2026-06-25 02:30:59', NULL, '2026-06-25 02:30:59'),
(401, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:31:06', '2026-06-25 02:31:06', NULL, '2026-06-25 02:31:06'),
(402, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:31:30', '2026-06-25 02:31:30', NULL, '2026-06-25 02:31:30'),
(403, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:31:48', '2026-06-25 02:31:48', NULL, '2026-06-25 02:31:48'),
(404, 126, 'ResetToken_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:32:12', '2026-06-25 02:32:12', NULL, '2026-06-25 02:32:12'),
(405, NULL, 'ResetPw_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:32:34', '2026-06-25 02:32:34', NULL, '2026-06-25 02:32:34'),
(406, 126, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:33:09', '2026-06-25 02:33:09', NULL, '2026-06-25 02:33:09'),
(407, 126, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:33:23', '2026-06-25 02:33:23', NULL, '2026-06-25 02:33:23'),
(408, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:33:53', '2026-06-25 02:33:53', NULL, '2026-06-25 02:33:53'),
(409, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:34:05', '2026-06-25 02:34:05', NULL, '2026-06-25 02:34:05'),
(410, 126, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:34:09', '2026-06-25 02:34:09', NULL, '2026-06-25 02:34:09'),
(411, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:34:19', '2026-06-25 02:34:19', NULL, '2026-06-25 02:34:19'),
(412, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:34:49', '2026-06-25 02:34:49', NULL, '2026-06-25 02:34:49'),
(413, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:41:33', '2026-06-25 02:41:33', NULL, '2026-06-25 02:41:33'),
(414, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:41:42', '2026-06-25 02:41:42', NULL, '2026-06-25 02:41:42'),
(415, 105, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:42:00', '2026-06-25 02:42:00', NULL, '2026-06-25 02:42:00'),
(416, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:42:15', '2026-06-25 02:42:15', NULL, '2026-06-25 02:42:15'),
(417, 126, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:42:31', '2026-06-25 02:42:31', NULL, '2026-06-25 02:42:31'),
(418, 126, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:43:08', '2026-06-25 02:43:08', NULL, '2026-06-25 02:43:08'),
(419, 109, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:43:18', '2026-06-25 02:43:18', NULL, '2026-06-25 02:43:18'),
(420, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:43:26', '2026-06-25 02:43:26', NULL, '2026-06-25 02:43:26'),
(421, 126, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:43:54', '2026-06-25 02:43:54', NULL, '2026-06-25 02:43:54'),
(422, 105, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:44:08', '2026-06-25 02:44:08', '2026-06-25 02:44:08', '2026-06-25 02:44:08'),
(423, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:44:35', '2026-06-25 02:44:35', NULL, '2026-06-25 02:44:35'),
(424, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:44:42', '2026-06-25 02:44:42', NULL, '2026-06-25 02:44:42'),
(425, 109, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:44:54', '2026-06-25 02:44:54', NULL, '2026-06-25 02:44:54'),
(426, 109, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:45:06', '2026-06-25 02:45:06', NULL, '2026-06-25 02:45:06'),
(427, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:45:11', '2026-06-25 02:45:11', NULL, '2026-06-25 02:45:11'),
(428, 111, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:45:31', '2026-06-25 02:45:31', NULL, '2026-06-25 02:45:31'),
(429, 128, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:48:38', '2026-06-25 02:48:38', NULL, '2026-06-25 02:48:38'),
(430, 109, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:50:38', '2026-06-25 02:50:38', '2026-06-25 02:50:38', '2026-06-25 02:50:38'),
(431, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:51:55', '2026-06-25 02:51:55', NULL, '2026-06-25 02:51:55'),
(432, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:51:58', '2026-06-25 02:51:58', NULL, '2026-06-25 02:51:58'),
(433, 129, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 02:52:21', '2026-06-25 02:52:21', NULL, '2026-06-25 02:52:21'),
(434, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:52:59', '2026-06-25 02:52:59', NULL, '2026-06-25 02:52:59'),
(435, 124, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 02:59:57', '2026-06-25 02:59:57', NULL, '2026-06-25 02:59:57'),
(436, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:00:06', '2026-06-25 03:00:06', NULL, '2026-06-25 03:00:06'),
(437, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:00:30', '2026-06-25 03:00:30', NULL, '2026-06-25 03:00:30'),
(438, 111, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:01:24', '2026-06-25 03:01:24', '2026-06-25 03:01:24', '2026-06-25 03:01:24'),
(439, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:02:10', '2026-06-25 03:02:10', NULL, '2026-06-25 03:02:10'),
(440, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:02:25', '2026-06-25 03:02:25', NULL, '2026-06-25 03:02:25'),
(441, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:02:39', '2026-06-25 03:02:39', NULL, '2026-06-25 03:02:39'),
(442, 130, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:03:02', '2026-06-25 03:03:02', NULL, '2026-06-25 03:03:02'),
(443, 124, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:04:15', '2026-06-25 03:04:15', NULL, '2026-06-25 03:04:15'),
(444, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:09:23', '2026-06-25 03:09:23', '2026-06-25 03:09:23', '2026-06-25 03:09:23'),
(445, 131, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:13:18', '2026-06-25 03:13:18', NULL, '2026-06-25 03:13:18'),
(446, 124, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:13:25', '2026-06-25 03:13:25', NULL, '2026-06-25 03:13:25'),
(447, 124, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:13:36', '2026-06-25 03:13:36', NULL, '2026-06-25 03:13:36'),
(448, 124, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:13:42', '2026-06-25 03:13:42', NULL, '2026-06-25 03:13:42'),
(449, 124, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:14:04', '2026-06-25 03:14:04', NULL, '2026-06-25 03:14:04'),
(450, 124, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:14:11', '2026-06-25 03:14:11', NULL, '2026-06-25 03:14:11'),
(451, 124, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:14:30', '2026-06-25 03:14:30', NULL, '2026-06-25 03:14:30'),
(452, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:15:54', '2026-06-25 03:15:54', NULL, '2026-06-25 03:15:54'),
(453, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:16:12', '2026-06-25 03:16:12', NULL, '2026-06-25 03:16:12'),
(454, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:16:41', '2026-06-25 03:16:41', NULL, '2026-06-25 03:16:41'),
(455, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:22:47', '2026-06-25 03:22:47', '2026-06-25 03:22:47', '2026-06-25 03:22:47'),
(456, 105, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:23:06', '2026-06-25 03:23:06', '2026-06-25 03:23:06', '2026-06-25 03:23:06'),
(457, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:24:21', '2026-06-25 03:24:21', NULL, '2026-06-25 03:24:21'),
(458, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:24:35', '2026-06-25 03:24:35', NULL, '2026-06-25 03:24:35'),
(459, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:24:46', '2026-06-25 03:24:46', NULL, '2026-06-25 03:24:46'),
(460, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:25:38', '2026-06-25 03:25:38', '2026-06-25 03:25:38', '2026-06-25 03:25:38'),
(461, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:28:53', '2026-06-25 03:28:53', NULL, '2026-06-25 03:28:53'),
(462, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:29:01', '2026-06-25 03:29:01', NULL, '2026-06-25 03:29:01'),
(463, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:29:12', '2026-06-25 03:29:12', NULL, '2026-06-25 03:29:12'),
(464, 1, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:29:45', '2026-06-25 03:29:45', '2026-06-25 03:29:45', '2026-06-25 03:29:45'),
(465, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:30:38', '2026-06-25 03:30:38', NULL, '2026-06-25 03:30:38'),
(466, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:30:50', '2026-06-25 03:30:50', NULL, '2026-06-25 03:30:50'),
(467, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 03:31:23', '2026-06-25 03:31:23', NULL, '2026-06-25 03:31:23'),
(468, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:32:36', '2026-06-25 03:32:36', NULL, '2026-06-25 03:32:36'),
(469, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:33:45', '2026-06-25 03:33:45', NULL, '2026-06-25 03:33:45'),
(470, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:34:35', '2026-06-25 03:34:35', NULL, '2026-06-25 03:34:35'),
(471, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:35:53', '2026-06-25 03:35:53', NULL, '2026-06-25 03:35:53'),
(472, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:41:09', '2026-06-25 03:41:09', NULL, '2026-06-25 03:41:09'),
(473, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:41:17', '2026-06-25 03:41:17', NULL, '2026-06-25 03:41:17');
INSERT INTO `system_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `login_time`, `last_active`, `logout_time`, `created_at`) VALUES
(474, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:41:29', '2026-06-25 03:41:29', NULL, '2026-06-25 03:41:29'),
(475, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:44:52', '2026-06-25 03:44:52', '2026-06-25 03:44:52', '2026-06-25 03:44:52'),
(476, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:45:09', '2026-06-25 03:45:09', '2026-06-25 03:45:09', '2026-06-25 03:45:09'),
(477, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:45:42', '2026-06-25 03:45:42', NULL, '2026-06-25 03:45:42'),
(478, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:46:22', '2026-06-25 03:46:22', NULL, '2026-06-25 03:46:22'),
(479, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:46:32', '2026-06-25 03:46:32', NULL, '2026-06-25 03:46:32'),
(480, 131, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:47:06', '2026-06-25 03:47:06', NULL, '2026-06-25 03:47:06'),
(481, 131, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:47:37', '2026-06-25 03:47:37', NULL, '2026-06-25 03:47:37'),
(482, 131, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:47:58', '2026-06-25 03:47:58', NULL, '2026-06-25 03:47:58'),
(483, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:50:12', '2026-06-25 03:50:12', NULL, '2026-06-25 03:50:12'),
(484, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:50:19', '2026-06-25 03:50:19', NULL, '2026-06-25 03:50:19'),
(485, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 03:50:37', '2026-06-25 03:50:37', NULL, '2026-06-25 03:50:37'),
(486, 105, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:26:23', '2026-06-25 04:26:23', NULL, '2026-06-25 04:26:23'),
(487, 105, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:26:31', '2026-06-25 04:26:31', NULL, '2026-06-25 04:26:31'),
(488, 105, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:26:43', '2026-06-25 04:26:43', NULL, '2026-06-25 04:26:43'),
(489, 126, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:30:33', '2026-06-25 04:30:33', NULL, '2026-06-25 04:30:33'),
(490, 126, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:31:01', '2026-06-25 04:31:01', NULL, '2026-06-25 04:31:01'),
(491, 126, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:32:20', '2026-06-25 04:32:20', NULL, '2026-06-25 04:32:20'),
(492, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:32:25', '2026-06-25 04:32:25', NULL, '2026-06-25 04:32:25'),
(493, 126, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:32:37', '2026-06-25 04:32:37', NULL, '2026-06-25 04:32:37'),
(494, 27, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:32:44', '2026-06-25 04:32:44', NULL, '2026-06-25 04:32:44'),
(495, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:33:07', '2026-06-25 04:33:07', NULL, '2026-06-25 04:33:07'),
(496, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:33:18', '2026-06-25 04:33:18', NULL, '2026-06-25 04:33:18'),
(497, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:33:54', '2026-06-25 04:33:54', NULL, '2026-06-25 04:33:54'),
(498, 131, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:39:37', '2026-06-25 04:39:37', NULL, '2026-06-25 04:39:37'),
(499, 131, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:40:22', '2026-06-25 04:40:22', NULL, '2026-06-25 04:40:22'),
(500, 131, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:40:38', '2026-06-25 04:40:38', NULL, '2026-06-25 04:40:38'),
(501, 131, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:41:49', '2026-06-25 04:41:49', '2026-06-25 04:41:49', '2026-06-25 04:41:49'),
(502, 105, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:42:04', '2026-06-25 04:42:04', '2026-06-25 04:42:04', '2026-06-25 04:42:04'),
(503, 132, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:43:53', '2026-06-25 04:43:53', NULL, '2026-06-25 04:43:53'),
(504, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:44:30', '2026-06-25 04:44:30', NULL, '2026-06-25 04:44:30'),
(505, 131, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:44:44', '2026-06-25 04:44:44', NULL, '2026-06-25 04:44:44'),
(506, 131, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:44:57', '2026-06-25 04:44:57', NULL, '2026-06-25 04:44:57'),
(507, 131, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 04:45:23', '2026-06-25 04:45:23', NULL, '2026-06-25 04:45:23'),
(508, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:48:04', '2026-06-25 04:48:04', NULL, '2026-06-25 04:48:04'),
(509, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:48:15', '2026-06-25 04:48:15', NULL, '2026-06-25 04:48:15'),
(510, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 04:48:46', '2026-06-25 04:48:46', NULL, '2026-06-25 04:48:46'),
(511, 126, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 05:05:38', '2026-06-25 05:05:38', '2026-06-25 05:05:38', '2026-06-25 05:05:38'),
(512, 132, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 05:16:41', '2026-06-25 05:16:41', NULL, '2026-06-25 05:16:41'),
(513, 132, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-25 05:16:50', '2026-06-25 05:16:50', NULL, '2026-06-25 05:16:50'),
(514, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 06:45:18', '2026-06-25 06:45:18', NULL, '2026-06-25 06:45:18'),
(515, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 06:45:25', '2026-06-25 06:45:25', NULL, '2026-06-25 06:45:25'),
(516, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 06:45:46', '2026-06-25 06:45:46', NULL, '2026-06-25 06:45:46'),
(517, 27, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-25 13:11:02', '2026-06-25 13:11:02', '2026-06-25 13:11:02', '2026-06-25 13:11:02'),
(518, 133, 'register_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:25:10', '2026-06-26 02:25:10', NULL, '2026-06-26 02:25:10'),
(519, 133, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:28:47', '2026-06-26 02:28:47', NULL, '2026-06-26 02:28:47'),
(520, 133, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:29:44', '2026-06-26 02:29:44', NULL, '2026-06-26 02:29:44'),
(521, 133, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:29:51', '2026-06-26 02:29:51', NULL, '2026-06-26 02:29:51'),
(522, 133, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:30:03', '2026-06-26 02:30:03', NULL, '2026-06-26 02:30:03'),
(523, 133, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:30:39', '2026-06-26 02:30:39', NULL, '2026-06-26 02:30:39'),
(524, 133, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:31:09', '2026-06-26 02:31:09', NULL, '2026-06-26 02:31:09'),
(525, 133, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:31:22', '2026-06-26 02:31:22', NULL, '2026-06-26 02:31:22'),
(526, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:43:59', '2026-06-26 02:43:59', NULL, '2026-06-26 02:43:59'),
(527, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:44:05', '2026-06-26 02:44:05', NULL, '2026-06-26 02:44:05'),
(528, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 02:44:21', '2026-06-26 02:44:21', NULL, '2026-06-26 02:44:21'),
(529, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 02:54:46', '2026-06-26 02:54:46', '2026-06-26 02:54:46', '2026-06-26 02:54:46'),
(530, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 03:09:55', '2026-06-26 03:09:55', NULL, '2026-06-26 03:09:55'),
(531, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 03:10:11', '2026-06-26 03:10:11', NULL, '2026-06-26 03:10:11'),
(532, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 03:10:33', '2026-06-26 03:10:33', NULL, '2026-06-26 03:10:33'),
(533, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 03:11:15', '2026-06-26 03:11:15', NULL, '2026-06-26 03:11:15'),
(534, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 03:21:01', '2026-06-26 03:21:01', NULL, '2026-06-26 03:21:01'),
(535, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 03:21:07', '2026-06-26 03:21:07', NULL, '2026-06-26 03:21:07'),
(536, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 03:21:20', '2026-06-26 03:21:20', NULL, '2026-06-26 03:21:20'),
(537, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:08:52', '2026-06-26 04:08:52', NULL, '2026-06-26 04:08:52'),
(538, NULL, 'login_information_fail', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:09:05', '2026-06-26 04:09:05', NULL, '2026-06-26 04:09:05'),
(539, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:09:23', '2026-06-26 04:09:23', NULL, '2026-06-26 04:09:23'),
(540, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:09:33', '2026-06-26 04:09:33', NULL, '2026-06-26 04:09:33'),
(541, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:10:42', '2026-06-26 04:10:42', NULL, '2026-06-26 04:10:42'),
(542, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:10:51', '2026-06-26 04:10:51', NULL, '2026-06-26 04:10:51'),
(543, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 04:11:06', '2026-06-26 04:11:06', NULL, '2026-06-26 04:11:06'),
(544, 126, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 04:23:13', '2026-06-26 04:23:13', NULL, '2026-06-26 04:23:13'),
(545, 126, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 04:24:23', '2026-06-26 04:24:23', NULL, '2026-06-26 04:24:23'),
(546, 126, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 04:24:37', '2026-06-26 04:24:37', NULL, '2026-06-26 04:24:37'),
(547, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 05:00:29', '2026-06-26 05:00:29', NULL, '2026-06-26 05:00:29'),
(548, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 05:00:38', '2026-06-26 05:00:38', NULL, '2026-06-26 05:00:38'),
(549, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-26 05:00:51', '2026-06-26 05:00:51', NULL, '2026-06-26 05:00:51'),
(550, 29, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-26 06:52:59', '2026-06-26 06:52:59', '2026-06-26 06:52:59', '2026-06-26 06:52:59'),
(551, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 03:58:16', '2026-06-27 03:58:16', NULL, '2026-06-27 03:58:16'),
(552, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 03:58:22', '2026-06-27 03:58:22', NULL, '2026-06-27 03:58:22'),
(553, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 03:58:44', '2026-06-27 03:58:44', NULL, '2026-06-27 03:58:44'),
(554, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 03:58:56', '2026-06-27 03:58:56', NULL, '2026-06-27 03:58:56'),
(555, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 03:59:05', '2026-06-27 03:59:05', NULL, '2026-06-27 03:59:05'),
(556, 1, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:00:28', '2026-06-27 04:00:28', NULL, '2026-06-27 04:00:28'),
(557, 1, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:00:34', '2026-06-27 04:00:34', NULL, '2026-06-27 04:00:34'),
(558, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:00:42', '2026-06-27 04:00:42', NULL, '2026-06-27 04:00:42'),
(559, 1, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:00:51', '2026-06-27 04:00:51', '2026-06-27 04:00:51', '2026-06-27 04:00:51'),
(560, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:01:02', '2026-06-27 04:01:02', NULL, '2026-06-27 04:01:02'),
(561, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:01:07', '2026-06-27 04:01:07', NULL, '2026-06-27 04:01:07'),
(562, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:01:25', '2026-06-27 04:01:25', NULL, '2026-06-27 04:01:25'),
(563, 27, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:02:28', '2026-06-27 04:02:28', '2026-06-27 04:02:28', '2026-06-27 04:02:28'),
(564, 24, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:03:10', '2026-06-27 04:03:10', NULL, '2026-06-27 04:03:10'),
(565, 24, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:03:15', '2026-06-27 04:03:15', NULL, '2026-06-27 04:03:15'),
(566, 24, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:03:28', '2026-06-27 04:03:28', NULL, '2026-06-27 04:03:28'),
(567, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:11:56', '2026-06-27 04:11:56', '2026-06-27 04:11:56', '2026-06-27 04:11:56'),
(568, 27, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:56:13', '2026-06-27 04:56:13', NULL, '2026-06-27 04:56:13'),
(569, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:56:18', '2026-06-27 04:56:18', NULL, '2026-06-27 04:56:18'),
(570, 27, 'verifyOTP_fail', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:56:30', '2026-06-27 04:56:30', NULL, '2026-06-27 04:56:30'),
(571, 27, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:56:53', '2026-06-27 04:56:53', NULL, '2026-06-27 04:56:53'),
(572, 27, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 04:57:21', '2026-06-27 04:57:21', NULL, '2026-06-27 04:57:21'),
(573, 29, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 06:51:05', '2026-06-27 06:51:05', '2026-06-27 06:51:05', '2026-06-27 06:51:05'),
(574, 27, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 07:13:36', '2026-06-27 07:13:36', '2026-06-27 07:13:36', '2026-06-27 07:13:36'),
(575, 24, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 07:26:11', '2026-06-27 07:26:11', '2026-06-27 07:26:11', '2026-06-27 07:26:11'),
(576, 24, 'login_information_correct', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 07:27:49', '2026-06-27 07:27:49', NULL, '2026-06-27 07:27:49'),
(577, 24, 'sendingOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 07:27:55', '2026-06-27 07:27:55', NULL, '2026-06-27 07:27:55'),
(578, 24, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-27 07:28:09', '2026-06-27 07:28:09', NULL, '2026-06-27 07:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','suspended','banned','locked') DEFAULT 'active',
  `lock_reason` enum('password_attempts','otp_attempts') DEFAULT NULL,
  `locked_until` timestamp NULL DEFAULT NULL,
  `failed_password_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `failed_otp_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `last_failed_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `notification_prefs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_prefs`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `address`, `status`, `lock_reason`, `locked_until`, `failed_password_attempts`, `failed_otp_attempts`, `last_failed_at`, `last_login`, `is_online`, `created_at`, `deleted_at`, `google_id`, `avatar`, `facebook_id`, `updated_at`, `email_verified_at`, `remember_token`, `notification_prefs`) VALUES
(1, 'Hsu Myat Moe', 'hsumyatm7308@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-27 04:00:28', 0, '2026-05-21 17:36:05', NULL, '108175427434445055275', 'https://lh3.googleusercontent.com/a/ACg8ocJe2tVcu-OZRevJWFdEJzRQYM7rUvS-PP7VTfvv54W2K70gmX2v=s96-c', NULL, '2026-06-27 04:00:51', '2026-06-23 19:18:53', NULL, NULL),
(24, 'J V', 'mhsu537@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', '09771471462', 'ကန်တော်ကြီး ကရဝိတ်၊ မျှော်စင်ကျွန်းဝင်ပေါက်အနီး၊ မင်္ဂလာတောင်ညွန့်မြို့နယ်၊ ရန်ကုန်မြို့။ ', 'active', NULL, NULL, 0, 0, NULL, '2026-06-27 07:27:49', 1, '2026-06-10 06:38:38', NULL, '112808788643014027786', 'https://lh3.googleusercontent.com/a/ACg8ocIXClMfEn5duPuil8ov2K8LCsnUDcK7DYKGSo2DuULXo1tqaHi2=s96-c', NULL, '2026-06-27 07:28:09', '2026-06-27 07:27:30', '8a146541d271249763bfbec2a30ed03b4b484f0db1b3b04a6112247da4621a65', NULL),
(27, 'HsuHive', 'hsuhive38@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', '09750625628', 'Yangon', 'active', NULL, NULL, 0, 0, NULL, '2026-06-27 04:56:13', 0, '2026-06-11 02:32:31', NULL, '106937788818804252855', 'http://localhost/GP/public/uploads/supplier/avatars/avatar-27-20260625181338-db6c9db2.jpg', NULL, '2026-06-27 07:13:36', '2026-06-24 14:08:40', NULL, NULL),
(29, 'Saen', 'saenintiktok@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', '09451777705', 'no.11, corner of Kan Yeik Thar Road &amp; U Aung Myat Road, Mingalar Taung township', 'active', NULL, NULL, 0, 0, NULL, '2026-06-24 05:32:47', 0, '2026-06-11 04:43:46', NULL, '113883451541620508706', 'https://lh3.googleusercontent.com/a/ACg8ocKa0OVagjb-Z034lNGR1feDM9cWYi9krO4byxaDck2Fzyjv1w=s96-c', NULL, '2026-06-27 11:39:01', '2026-06-27 11:39:01', NULL, NULL),
(30, 'zaw moe', '7zawzawmoe8@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 2, 0, '2026-06-23 13:09:53', NULL, 0, '2026-06-18 09:44:03', NULL, '105962240867007474645', 'https://lh3.googleusercontent.com/a/ACg8ocJ3JrvFxn1cRzuotErkuS0lsXh9eb2rdG8kLIL3S3pQEJYCGg=s96-c', NULL, '2026-06-27 04:12:07', '2026-06-27 04:12:07', NULL, NULL),
(31, 'Naw Paw', 'nawpawtarmalar20@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 1, 0, '2026-06-23 04:03:28', NULL, 0, '2026-06-19 15:07:43', NULL, '114585182535071373461', 'https://lh3.googleusercontent.com/a/ACg8ocLxtArBhTcl9Vsk7CgrCP2_uGTcD2ejVrBVEajJWmYxSaaTdg=s96-c', NULL, '2026-06-23 04:03:28', '2026-06-19 15:07:43', NULL, NULL),
(32, 'Excel River View Hotel & Resort', 'supplier23@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(33, 'Golden Inya Restaurant', 'supplier24@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(34, 'Western Park Ruby - People\'s Park', 'supplier25@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(35, 'Zephyr (Sein Lann So Pyay Garden)', 'supplier26@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(36, 'The White Cottage Restaurant & Lounge', 'supplier27@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(37, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'supplier129@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-21 07:18:28', 1, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-22 07:44:31', '2026-06-20 08:05:08', NULL, NULL),
(38, 'Dear Brides Wedding Dress Studio', 'supplier29@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(39, 'The Vow Wedding Studio Myanmar', 'supplier30@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(40, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'sixfriendseightjune@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-20 15:23:25', 1, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 15:23:25', '2026-06-20 08:05:08', NULL, NULL),
(41, 'T&T Bridal Collection', 'supplier32@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(42, 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး', 'supplier33@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(43, 'Peter\'s Bridal Garden - Studio', 'supplier34@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(44, 'My Everything Wedding Dresses', 'supplier35@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(45, 'Forever One Stop Wedding Studio', 'foreverstudio.mm@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(46, 'H & H Photo Studio', 'supplier69@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(47, 'Venus Wedding Studio', 'supplier47@goldenpromise.test\r\n', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-20 14:57:38', 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 15:21:07', '2026-06-20 08:05:08', NULL, NULL),
(48, 'PNA’S Wedding Studio', 'supplier71@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(49, 'Together Wedding Studio', 'supplier72@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(50, 'Western Park Ruby – People’s Park', 'supplier73@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(51, 'MG & J Jewelry', 'info@mgjmyanmar.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(52, 'U Hton', 'supplier75@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(53, 'Myat Pan Tha Zin Diamond and Jewelry', 'supplier76@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(54, 'Vivian Diamond Jewellery', 'supplier77@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(55, 'Theingi Moe Jewelry', 'supplier78@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(56, 'Parisian Cake&Cafe', 'supplier83@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(57, 'Season', 'supplier84@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(58, 'Kudo’s', 'supplier85@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(59, 'Shwe Pu Zun', 'supplier86@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(60, '77 Cake', 'supplier87@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(61, 'El Dorado', 'supplier88@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(62, 'Shan Yoe Yar Restaurant', 'supplier89@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(63, 'KSS နတ်သုဒ္ဓါဒံပေါက်', 'supplier90@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(64, 'ထူး ရေခဲမုန့်', 'supplier91@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(65, 'The Hundred -Grilled Chicken', 'supplier92@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(66, 'Royal Chef', 'supplier93@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(67, 'Rice Box', 'supplier94@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(68, 'Boke & Bee', 'supplier95@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(69, 'နှင်းသီရိ', 'supplier96@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(70, 'H&H Floral and Wedding Service', 'haymanoo3111995@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(71, 'Eternal Flowers', 'eternalflowers99@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(72, 'Aphrodite Wedding Planning & Decoration', 'info@aphroditeweddingplanning.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(73, 'Elysian Floral Art & Events Planning', 'elysian.floral.art.mm@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(74, 'S&S Events and Floral', 'supplier101@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(75, 'His & Hers Events and Wedding Studio', 'hnhbridal@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(76, 'Governor’s Residence', 'supplier103@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(77, 'Novotel Yangon Max', 'supplier104@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(78, 'Sedona Hotel Yangon', 'supplier105@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(79, 'Inya Lake Hotel', 'supplier106@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(80, 'Meliá Yangon', 'supplier107@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(81, 'Hotel Yangon', 'supplier108@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(82, 'Myanmar Car Rental', 'supplier109@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(83, 'The Experience Rent A Car', 'supplier110@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(84, 'AVIS MYANMAR', 'supplier111@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(85, 'inoventure', 'supplier112@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(86, 'Concierge Business Limousine', 'supplier113@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(87, 'Elegant Star (Recommended)', 'supplier114@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(88, 'Memory Memory Handmade invitation cards and gifts (Recommended)', 'supplier115@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(89, 'Moe Kaung Kin', 'supplier116@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(90, 'Y Collection', 'supplier117@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(91, 'Paperie Tale (Recommended)', 'supplier118@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(92, 'THIRI Handmade Invatation', 'supplier119@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(93, 'Pyan Kann', 'supplier120@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(94, 'SORA', 'supplier121@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(95, 'ကိုသာဂိ', 'supplier122@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(96, 'Ma Htet-pop soul', 'supplier123@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(97, 'Lin Lin', 'supplier124@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(98, 'make up Kin San Win', 'supplier125@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(99, 'Magic Touch Beauty Boutique', 'supplier126@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(100, 'Chi Chi’s Touch', 'supplier127@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(101, 'Makeup Hazel', 'supplier128@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(102, 'Makeup Non Thit San', 'hsumyatrain@gmail.com', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-22 07:45:59', 1, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-22 07:45:59', '2026-06-20 08:05:08', NULL, NULL),
(103, 'Sweet Hair& Make up', 'supplier130@goldenpromise.test', '$2y$10$ZzdxXJsCIAmN53Emla3zCOhHkQhckxDhQ0KNrM42PHEi6/jR7H3rm', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-20 08:05:08', NULL, NULL, NULL, NULL, '2026-06-20 14:08:13', '2026-06-20 08:05:08', NULL, NULL),
(105, 'Hnin Yu Yu Hlaing Tun', 'hyuyuhlaingtun@gmail.com', '$2y$10$NvZiWjoTWwCsiKs/cG2KguqWlCXQJjgqvIqHB.iszZkE3O98xpxSG', '09252208158', 'Yangon', 'active', NULL, NULL, 0, 0, NULL, '2026-06-25 04:26:23', 0, '2026-06-23 03:15:16', NULL, NULL, 'https://ecb606aaa67580a9-204-157-173-163.serveousercontent.com/GP/public/uploads/supplier/avatars/avatar-105-20260624084025-7ebc9717.jpg', NULL, '2026-06-25 04:42:04', '2026-06-23 03:16:48', NULL, NULL),
(106, 'Naw Pan', 'ydn5157@gmail.com', '$2y$10$8Lx7Y4rW0ARRdokkMJhshO5LYuqrFEpEkpIUQr21pG1PqgL7x8IFG', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-23 03:20:07', NULL, NULL, NULL, NULL, '2026-06-23 03:20:07', NULL, NULL, NULL),
(107, 'Zaw Min Thein', 'ookinkin6@gmai.com', '$2y$10$mVJIiRwvE3I0mIAIlCx6MehDIaxGYypsEqASTRK9ToWP.la6WXJ3a', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-23 04:07:45', NULL, NULL, NULL, NULL, '2026-06-23 04:07:45', NULL, NULL, NULL),
(109, 'Yadanar', 'nawpan321@gmail.com', '$2y$10$8e0zuhDtA8uhxEPxOUA9d.tBgtudK1WNKUp9zlgBQDPUIIMVR1FAq', '09455658079', NULL, 'active', NULL, NULL, 3, 0, '2026-06-25 02:52:59', '2026-06-25 02:43:18', 0, '2026-06-23 04:23:00', NULL, NULL, NULL, NULL, '2026-06-25 02:52:59', '2026-06-23 04:24:57', NULL, NULL),
(111, 'NuNu', 'shihiroamedio@gmail.com', '$2y$10$eHyWQ7uEHUDAkvwRsvnAPeUwKGFp8B6rjxFFZIEX6CszlacLzsWpy', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-25 02:45:31', 0, '2026-06-23 04:54:27', NULL, NULL, NULL, NULL, '2026-06-25 03:01:24', '2026-06-24 04:41:50', NULL, NULL),
(112, 'Deleted User', 'deleted_112_1782278603@deleted.invalid', '', '', '', 'banned', NULL, NULL, 0, 0, NULL, '2026-06-24 04:18:11', 1, '2026-06-23 04:58:22', '2026-06-24 05:23:23', NULL, NULL, NULL, '2026-06-24 05:23:23', '2026-06-23 05:00:11', NULL, NULL),
(113, 'AmKiio', 'amediochihiro@gmail.com', '$2y$10$Eb8gkzRP3vTWUHFVnk9XduP/MH0uy/Ndl7Q5Z6yCMajXwlY/pe0UC', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-24 04:05:29', 0, '2026-06-23 05:02:36', NULL, NULL, NULL, NULL, '2026-06-24 04:37:52', '2026-06-23 05:03:11', NULL, NULL),
(114, 'Dana Htun', 'danahtun220@gmail.com', '$2y$10$ll2/WEjEXN9YKb9wk1M.AO9RfUpyYl.PbdoqAERvgb1V4tGpKV6kO', '09750625628', 'Yangon', 'active', NULL, NULL, 0, 0, NULL, '2026-06-24 05:41:09', 1, '2026-06-23 05:27:33', NULL, NULL, NULL, NULL, '2026-06-24 05:41:09', '2026-06-23 05:30:11', NULL, NULL),
(115, 'Zaw Moe', '7zawmoe7@gmail.com', '$2y$10$vCMdMow58hXLbiGtjiIqGujxVk3n2AwRzab93Qg0hRtvsJxi1PHBO', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-23 13:15:22', NULL, NULL, NULL, NULL, '2026-06-23 13:17:50', '2026-06-23 13:17:50', NULL, NULL),
(116, 'Test User', 'test-strong@example.com', '$2y$10$LwIXIWTfxSKJ8/BZg5R1NOSEKjAL2NJPwuGsSBbErztLBwUYcRQGq', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-23 14:25:48', NULL, NULL, NULL, NULL, '2026-06-23 14:25:48', NULL, NULL, NULL),
(117, 'Test User2', 'test-fair@example.com', '$2y$10$nPjta7APkjUC90RhnnDvm.RMKpFB.zn3aAtreYTQguBkrUBSvi3hG', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-23 14:26:03', NULL, NULL, NULL, NULL, '2026-06-23 14:26:03', NULL, NULL, NULL),
(118, 'Lae Yati Htun', 'nw97346@gmail.com', '$2y$10$/JUCZENmPtJmztTjx70vQuoa7Wq1VuBk2JTkhTSjpXo.z.W8zJO0G', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-24 02:23:58', NULL, NULL, NULL, NULL, '2026-06-24 02:23:58', NULL, NULL, NULL),
(119, 'Lae Yati Htun', 'laeyatihtun1@gmail.com', '$2y$10$4ELuvsJtRwSjvmEeIP5Nte1.1W6hTb3KOCsL4A5yBG/DJVVsQhkCi', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-24 03:07:57', NULL, NULL, NULL, NULL, '2026-06-24 03:07:57', NULL, NULL, NULL),
(120, 'Lae Yati Htun', 'laeyatihtun11@gmail.com', '$2y$10$lnp.6ft0DWo3tSuAlmVepOcOcMzNpaWIC5V7FYAPAVyJOMBv/8Eju', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-24 03:12:13', NULL, NULL, NULL, NULL, '2026-06-24 03:12:13', NULL, NULL, NULL),
(121, 'Deleted User', 'deleted_121_1782278577@deleted.invalid', '', '', '', 'banned', NULL, NULL, 0, 0, NULL, '2026-06-24 04:21:33', 1, '2026-06-24 03:15:03', '2026-06-24 05:22:57', NULL, NULL, NULL, '2026-06-24 05:22:57', '2026-06-24 03:18:58', NULL, NULL),
(122, 'Avary', 'ava07940500@gmail.com', '$2y$10$qFF8Hg9klkFV0QbbwHoDq.NQ9S9xnAaoGT/4Zk0pRlUi/9EJFJ1si', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-24 03:20:50', 0, '2026-06-24 03:17:26', NULL, NULL, NULL, NULL, '2026-06-24 03:53:14', '2026-06-24 03:19:59', NULL, NULL),
(123, 'Lae Yati Htun', 'laeyatihtun313@gmail.com', '$2y$10$hNC9v/D/75BAL.s0zbqet.71V5MewxGl3dDBpi6ngqRVSO8c.45gy', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-24 03:57:18', 1, '2026-06-24 03:24:38', NULL, NULL, NULL, NULL, '2026-06-24 03:57:18', '2026-06-24 03:29:09', NULL, NULL),
(124, 'Yadanar', 'panyadanar210@gmail.com', '$2y$10$4dUdJ8TmKlPH30DkfL2wLeXCGLZ1kymM64LtPNrJ9xP9c7pHXVEDe', '09455658079', 'Tharkayta', 'active', NULL, NULL, 0, 0, NULL, '2026-06-25 03:14:04', 1, '2026-06-24 03:28:35', NULL, NULL, NULL, NULL, '2026-06-25 03:14:04', '2026-06-24 03:51:36', NULL, NULL),
(125, 'Shin Thant', 'shinthant38292@gmail.com', '$2y$10$14Zcx./JK9AQyBOk2Pmaoem3dRLfI6wNtVuzCzgDAyM6K6z1.q6YW', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-24 03:55:30', NULL, NULL, NULL, NULL, '2026-06-24 03:55:30', NULL, NULL, NULL),
(126, 'Avary', 'ayethuzarnwe80@gmail.com', '$2y$10$1HRKDxQ5PyCvD8os.9bs0.5TBES7DNijLEk7vpSWsbXqgUK/bccZK', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-26 04:23:13', 1, '2026-06-24 03:58:34', NULL, NULL, NULL, NULL, '2026-06-26 04:23:13', '2026-06-25 02:27:08', NULL, NULL),
(127, 'Zaw MIn Thein', 'amediochihiro@gmsil.com', '$2y$10$ArLJFNspPeZv.nDWPYDqReuZWBUIiLDkw.Q.WyvMBzkW7Qa7cysgq', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-24 04:34:08', NULL, NULL, NULL, NULL, '2026-06-24 04:34:08', NULL, NULL, NULL),
(128, 'Hans', 'zawminthein5314@gmail.com', '$2y$10$cFbKun3whLOCUUSLxrP/O.z6KLq6XtAqvgmSwpcwAr6n88c9hntRK', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-25 02:48:38', NULL, NULL, NULL, NULL, '2026-06-25 02:48:38', NULL, NULL, NULL),
(129, 'Hans', 'undeadmonstes2006@gmail.com', '$2y$10$eriEZbmAsaetOzGAs8QFHurX5Rjlg.l5bmFlzx8prtsCuwEBHdlVe', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-25 02:52:21', NULL, NULL, NULL, NULL, '2026-06-25 02:52:21', NULL, NULL, NULL),
(130, 'hans', 'undeadmonster2006@gmail.com', '$2y$10$GVqFOT.XA5r6InNDo7cY9eDSkGAJ.72Qp.8JGvlq4T9ztE3cKmtya', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-25 03:03:02', NULL, NULL, NULL, NULL, '2026-06-25 03:03:02', NULL, NULL, NULL),
(131, 'Hans', 'hkoke56@gmail.com', '$2y$10$MRQCSKQUt3J6vv8O/faZ5e8bnme.CrGt2teI8XQOL/RJOso9py/HW', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-25 04:44:44', 1, '2026-06-25 03:13:18', NULL, NULL, NULL, NULL, '2026-06-25 04:44:44', '2026-06-25 03:21:40', NULL, NULL),
(132, 'Tina', 'tinaforger3@gmail.com', '$2y$10$cIht5d1CVdRQGEYTLj1GuODQlJaBJsvy5Ci6ScjRxhlDsjE1tt/cC', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-25 05:16:41', 1, '2026-06-25 04:43:53', NULL, NULL, NULL, NULL, '2026-06-25 05:16:41', '2026-06-25 04:46:41', NULL, NULL),
(133, 'Kaung Min Khant', 'kk8264483@gmail.com', '$2y$10$KxmkzU9h9ES64e1ikdJ1eOBur.PyYlGz4iAcTzp79MpuprZyUN96W', '09781344861', 'Yangon', 'active', NULL, NULL, 0, 0, NULL, '2026-06-26 02:29:44', 1, '2026-06-26 02:25:10', NULL, NULL, NULL, NULL, '2026-06-26 02:36:13', '2026-06-26 02:28:29', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(3, 1, 4, '2026-05-21 17:36:05'),
(12, 1, 1, '2026-06-03 02:54:30'),
(15, 1, 2, '2026-06-03 08:51:29'),
(24, 24, 2, '2026-06-10 06:38:38'),
(25, 24, 1, '2026-06-10 06:39:50'),
(28, 27, 1, '2026-06-11 02:32:31'),
(30, 29, 2, '2026-06-11 04:43:46'),
(31, 29, 1, '2026-06-13 17:31:21'),
(32, 30, 1, '2026-06-18 09:44:03'),
(33, 31, 2, '2026-06-19 15:07:43'),
(34, 32, 2, '2026-06-20 08:05:08'),
(35, 33, 2, '2026-06-20 08:05:08'),
(36, 34, 2, '2026-06-20 08:05:08'),
(37, 35, 2, '2026-06-20 08:05:08'),
(38, 36, 2, '2026-06-20 08:05:08'),
(39, 37, 2, '2026-06-20 08:05:08'),
(40, 38, 2, '2026-06-20 08:05:08'),
(41, 39, 2, '2026-06-20 08:05:08'),
(42, 40, 2, '2026-06-20 08:05:08'),
(43, 41, 2, '2026-06-20 08:05:08'),
(44, 42, 2, '2026-06-20 08:05:08'),
(45, 43, 2, '2026-06-20 08:05:08'),
(46, 44, 2, '2026-06-20 08:05:08'),
(47, 45, 2, '2026-06-20 08:05:08'),
(48, 46, 2, '2026-06-20 08:05:08'),
(49, 47, 2, '2026-06-20 08:05:08'),
(50, 48, 2, '2026-06-20 08:05:08'),
(51, 49, 2, '2026-06-20 08:05:08'),
(52, 50, 2, '2026-06-20 08:05:08'),
(53, 51, 2, '2026-06-20 08:05:08'),
(54, 52, 2, '2026-06-20 08:05:08'),
(55, 53, 2, '2026-06-20 08:05:08'),
(56, 54, 2, '2026-06-20 08:05:08'),
(57, 55, 2, '2026-06-20 08:05:08'),
(58, 56, 2, '2026-06-20 08:05:08'),
(59, 57, 2, '2026-06-20 08:05:08'),
(60, 58, 2, '2026-06-20 08:05:08'),
(61, 59, 2, '2026-06-20 08:05:08'),
(62, 60, 2, '2026-06-20 08:05:08'),
(63, 61, 2, '2026-06-20 08:05:08'),
(64, 62, 2, '2026-06-20 08:05:08'),
(65, 63, 2, '2026-06-20 08:05:08'),
(66, 64, 2, '2026-06-20 08:05:08'),
(67, 65, 2, '2026-06-20 08:05:08'),
(68, 66, 2, '2026-06-20 08:05:08'),
(69, 67, 2, '2026-06-20 08:05:08'),
(70, 68, 2, '2026-06-20 08:05:08'),
(71, 69, 2, '2026-06-20 08:05:08'),
(72, 70, 2, '2026-06-20 08:05:08'),
(73, 71, 2, '2026-06-20 08:05:08'),
(74, 72, 2, '2026-06-20 08:05:08'),
(75, 73, 2, '2026-06-20 08:05:08'),
(76, 74, 2, '2026-06-20 08:05:08'),
(77, 75, 2, '2026-06-20 08:05:08'),
(78, 76, 2, '2026-06-20 08:05:08'),
(79, 77, 2, '2026-06-20 08:05:08'),
(80, 78, 2, '2026-06-20 08:05:08'),
(81, 79, 2, '2026-06-20 08:05:08'),
(82, 80, 2, '2026-06-20 08:05:08'),
(83, 81, 2, '2026-06-20 08:05:08'),
(84, 82, 2, '2026-06-20 08:05:08'),
(85, 83, 2, '2026-06-20 08:05:08'),
(86, 84, 2, '2026-06-20 08:05:08'),
(87, 85, 2, '2026-06-20 08:05:08'),
(88, 86, 2, '2026-06-20 08:05:08'),
(89, 87, 2, '2026-06-20 08:05:08'),
(90, 88, 2, '2026-06-20 08:05:08'),
(91, 89, 2, '2026-06-20 08:05:08'),
(92, 90, 2, '2026-06-20 08:05:08'),
(93, 91, 2, '2026-06-20 08:05:08'),
(94, 92, 2, '2026-06-20 08:05:08'),
(95, 93, 2, '2026-06-20 08:05:08'),
(96, 94, 2, '2026-06-20 08:05:08'),
(97, 95, 2, '2026-06-20 08:05:08'),
(98, 96, 2, '2026-06-20 08:05:08'),
(99, 97, 2, '2026-06-20 08:05:08'),
(100, 98, 2, '2026-06-20 08:05:08'),
(101, 99, 2, '2026-06-20 08:05:08'),
(102, 100, 2, '2026-06-20 08:05:08'),
(103, 101, 2, '2026-06-20 08:05:08'),
(104, 102, 2, '2026-06-20 08:05:08'),
(105, 103, 2, '2026-06-20 08:05:08'),
(107, 105, 2, '2026-06-23 03:15:16'),
(108, 106, 1, '2026-06-23 03:20:07'),
(109, 107, 1, '2026-06-23 04:07:45'),
(111, 109, 1, '2026-06-23 04:23:00'),
(113, 111, 1, '2026-06-23 04:54:27'),
(115, 113, 1, '2026-06-23 05:02:36'),
(116, 114, 2, '2026-06-23 05:27:33'),
(117, 115, 1, '2026-06-23 13:15:22'),
(118, 116, 1, '2026-06-23 14:25:48'),
(119, 117, 1, '2026-06-23 14:26:03'),
(120, 118, 1, '2026-06-24 02:23:58'),
(121, 119, 1, '2026-06-24 03:07:57'),
(122, 120, 1, '2026-06-24 03:12:13'),
(124, 122, 1, '2026-06-24 03:17:26'),
(125, 123, 1, '2026-06-24 03:24:38'),
(126, 124, 2, '2026-06-24 03:28:35'),
(127, 125, 1, '2026-06-24 03:55:30'),
(128, 126, 1, '2026-06-24 03:58:34'),
(129, 127, 2, '2026-06-24 04:34:08'),
(130, 27, 2, '2026-06-24 14:08:40'),
(131, 128, 1, '2026-06-25 02:48:38'),
(132, 129, 1, '2026-06-25 02:52:21'),
(133, 130, 1, '2026-06-25 03:03:02'),
(134, 131, 1, '2026-06-25 03:13:18'),
(135, 132, 1, '2026-06-25 04:43:53'),
(136, 133, 2, '2026-06-26 02:25:10');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `supplier_id`, `service_id`, `name`, `location`, `description`, `created_at`) VALUES
(1, 21, NULL, 'Golden Inya', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Golden Inya Restaurant ကအမြင်လှ အဆင့်မြင့်စားသောက်ဆိုင်တစ်ခုဖြစ်ပြီး weeding, Engagement, Reception, Corporate Eventတွေအတွက်လူကြိုက်များ ပြီးအင်ယားကန်ကိုတိုင်ရိုက်မြင်ရလို့  Evening weeding အတွက် ကိုက်ညီတယ် indoor outdoor ပေါင်းသုံးလို့ရတဲ့အဆင့်မြင့်စား သောက် ဆိုင်တစ်ခုဖြစ်သည်။', '2026-06-11 06:45:46'),
(2, 21, NULL, 'Golden Inya', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Golden Inya Restaurant ကအမြင်လှ အဆင့်မြင့်စားသောက်ဆိုင်တစ်ခုဖြစ်ပြီး weeding, Engagement, Reception, Corporate Eventတွေအတွက်လူကြိုက်များ ပြီးအင်ယားကန်ကိုတိုင်ရိုက်မြင်ရလို့  Evening weeding အတွက် ကိုက်ညီတယ် indoor outdoor ပေါင်းသုံးလို့ရတဲ့အဆင့်မြင့်စား သောက် ဆိုင်တစ်ခုဖြစ်သည်။', '2026-06-11 06:54:59'),
(3, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-11 08:20:20'),
(4, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-11 08:32:32'),
(5, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-11 08:39:21'),
(6, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-11 08:52:55'),
(7, 21, NULL, 'Nobotel', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Novotel', '2026-06-12 03:27:20'),
(8, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-14 05:36:09'),
(9, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'governere', '2026-06-14 07:11:01'),
(10, 20, NULL, 'govender', NULL, 'blah blah', '2026-06-14 07:25:22'),
(11, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-14 09:28:17'),
(12, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'blah blah', '2026-06-14 09:48:10'),
(13, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'blah blah', '2026-06-14 09:48:15'),
(14, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-14 12:28:41'),
(15, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-14 12:49:18'),
(16, 21, NULL, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'governor', '2026-06-14 12:53:44'),
(17, 20, NULL, 'Golden Inya Restaurant', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Golden Inya Restaurant ကအမြင်လှ အဆင့်မြင့်စားသောက်ဆိုင်တစ်ခုဖြစ်ပြီး weeding, Engagement, Reception, Corporate Eventတွေအတွက်လူကြိုက်များ ပြီးအင်ယားကန်ကိုတိုင်ရိုက်မြင်ရလို့  Evening weeding အတွက် ကိုက်ညီတယ် indoor outdoor ပေါင်းသုံးလို့ရတဲ့အဆင့်မြင့်စား သောက် ဆိုင်တစ်ခုဖြစ်သည်။', '2026-06-15 18:15:07'),
(18, 20, NULL, 'Hotel Yangon', 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', 'Hotel Yangon, a luxurious business as well as leisure hotel sits majesticallyon a beautifully landscaped garden with a panoramic view of Yangon City.It is strategically located at 8th Mile junction area which is situated with many businessand commercial offices. Our hotel is close to Junction 8 Shopping Center and , just 10 minutes drivefrom Yangon International Airport &amp; 30 minutes driveto famous landmark of Yangon, Myanmar, Shwedagon Pagoda.', '2026-06-17 08:29:07'),
(19, 20, NULL, 'Hotel Yangon', 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', 'Hotel Yangon, a luxurious business as well as leisure hotel sits majesticallyon a beautifully landscaped garden with a panoramic view of Yangon City.It is strategically located at 8th Mile junction area which is situated with many businessand commercial offices. Our hotel is close to Junction 8 Shopping Center and , just 10 minutes drivefrom Yangon International Airport &amp;amp;amp;amp;amp; 30 minutes driveto famous landmark of Yangon, Myanmar, Shwedagon Pagoda.', '2026-06-17 08:31:38'),
(20, 21, 42, 'Governor\'s Residence', '35, Taw Win Road, Dagon Township, Yangon', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', '2026-06-18 08:29:17'),
(21, 20, 49, 'Zephyr Sein Lann So Pyay Garden', 'အမှတ်-(28) စိမ်းလန်းစိုပြေပန်းခြံ၊ အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Zephyr (Sein Lann So Pyay Garden)ကရန်ကုန်မြို့အတွင်းတည်ရှိတဲ့အေးချမ်းပြီးသဘာဝပတ်ဝန်းကျင်နဲ့ကိုက်ညီတဲ့ fine dining & event venue တစ်ခုဖြစ်ပါတယ်။Sein Lann So Pyay Gardenအနားမှာရှိလို့ မိသားစုစားသောက်မှု၊ မင်္ဂလာပွဲ၊ အခမ်းအနားများအတွက်လူကြိုက်များပါတယ်။သဘာဝအလှနဲ့ လှပနဲ့background ကြောင့်pre-weeding/ event-photo ရိုက်ရအဆင်ပြေစေပါတယ်။outdoor garden weeding နဲ့ အေးချမ်းတဲ့weeding လုပ်ချင်သူများ Decoration+ food+ Serviceကိုတစ်နေရာထဲမှာpackageလိုချင်သူများအတွက်အဆင်ပြေပြီး ရွေးချယ်ဖို့သင့်တော်တဲ့နေရာတစ်ခုဖြစ်ပါတယ်။', '2026-06-18 19:26:54'),
(22, 20, NULL, 'Western Park Ruby – People’s Park', 'ပြည်သူ့ရင်ပြင်ဝန်းအတွင်း၊ ဒဂုံမြို့နယ်၊ ရန်ကုန်မြို့။', 'မြို့အလယ်မှာရှိပေမဲ့ပန်းခြံဖြစ်လို့ ရှုပ်ထွေးမှုမရှိ၊မြက်ခင်းပြင်ကျယ် သဘာဝစိမ်းလန်းမှူများ၊နေရာကျယ်ဝန်းလို့ weeding, event venue အဖြစ်လူကြိုက်များပြီးဧည့်သည်အရေအတွက်များတဲ့eventများတွက်အဆင်   ပြေအောင်ဆောင်ရွက်ပေးနေပြီဖြစ်ပါတယ်။', '2026-06-19 03:18:58'),
(23, 24, 60, 'Golden Inya - Lakeside Wedding Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(24, 25, 61, 'Western Park Ruby - Garden Wedding Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(25, 26, 62, 'Zephyr - Garden Wedding Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(26, 27, 63, 'The White Cottage - Garden & Lounge Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(27, 73, 108, 'Western Park Ruby – People’s Park - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(28, 103, 138, 'Governor’s Residence - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(29, 104, 139, 'Novotel Yangon Max - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(30, 105, 140, 'Sedona Hotel Yangon - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(31, 106, 141, 'Inya Lake Hotel - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(32, 107, 142, 'Meliá Yangon - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32'),
(33, 108, 143, 'Hotel Yangon - Venue', 'Yangon', 'Wedding venue', '2026-06-20 08:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `venue_rooms`
--

CREATE TABLE `venue_rooms` (
  `id` bigint(20) NOT NULL,
  `venue_id` bigint(20) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `min_lead_days` int(11) DEFAULT NULL COMMENT 'Room-specific override. NULL = inherit from parent service.',
  `photo_url` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_rooms`
--

INSERT INTO `venue_rooms` (`id`, `venue_id`, `name`, `capacity`, `price`, `created_at`, `min_lead_days`, `photo_url`) VALUES
(20, 20, 'Grand Hall', 300, 600000.00, '2026-06-18 08:29:17', 30, 'http://localhost/GP/public/uploads/suppliers/21/service-management/hall/20260618102917-1657b0e1.jpg'),
(21, 20, 'Ball Room', 250, 70000.00, '2026-06-18 08:32:14', 30, 'http://localhost/GP/public/uploads/suppliers/21/service-management/hall/20260618103214-ba082147.jpg'),
(22, 21, 'Grass Room', 400, 900000.00, '2026-06-18 19:26:54', 30, 'http://localhost/GP/public/uploads/suppliers/20/service-management/hall/20260618212654-af34618f.jpg'),
(23, 22, 'Grand Hall', 300, 500000.00, '2026-06-19 03:18:58', 4, 'http://localhost/GP/public/uploads/suppliers/20/service-management/hall/20260619051858-41a7dc9a.jpg'),
(35, 23, 'Grand Ballroom (Indoor)', 400, 2000000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(36, 23, 'Garden Lawn (Outdoor)', 250, 1600000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(37, 24, 'Grand Ballroom (Indoor)', 400, 500000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(38, 24, 'Garden Lawn (Outdoor)', 250, 400000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(39, 25, 'Grand Ballroom (Indoor)', 400, 900000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(40, 25, 'Garden Lawn (Outdoor)', 250, 720000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(41, 26, 'Grand Ballroom (Indoor)', 400, 800000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(42, 26, 'Garden Lawn (Outdoor)', 250, 640000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(43, 27, 'Grand Ballroom (Indoor)', 400, 500000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(44, 27, 'Garden Lawn (Outdoor)', 250, 400000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(45, 28, 'Grand Ballroom (Indoor)', 400, 800000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(46, 28, 'Garden Lawn (Outdoor)', 250, 640000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(47, 29, 'Grand Ballroom (Indoor)', 400, 300000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(48, 29, 'Garden Lawn (Outdoor)', 250, 240000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(49, 30, 'Grand Ballroom (Indoor)', 400, 800000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(50, 30, 'Garden Lawn (Outdoor)', 250, 640000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(51, 31, 'Grand Ballroom (Indoor)', 400, 300000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(52, 31, 'Garden Lawn (Outdoor)', 250, 240000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(53, 32, 'Grand Ballroom (Indoor)', 400, 300000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero1.png'),
(54, 32, 'Garden Lawn (Outdoor)', 250, 240000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(55, 33, 'Grand Ballroom (Indoor)', 400, 800000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero2.png'),
(56, 33, 'Garden Lawn (Outdoor)', 250, 640000.00, '2026-06-20 13:57:16', 0, 'http://localhost/GP/public/uploads/serviceHero3.png'),
(57, 21, 'Roof top', 700, 3000000.00, '2026-06-27 07:15:24', 1, 'http://localhost/GP/public/uploads/suppliers/20/service-management/hall/20260627134548-705d4ae9.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `venue_room_availability`
--

CREATE TABLE `venue_room_availability` (
  `id` bigint(20) NOT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_room_availability`
--

INSERT INTO `venue_room_availability` (`id`, `room_id`, `date`, `start_time`, `end_time`, `is_available`) VALUES
(44, 20, NULL, '06:00:00', '17:00:00', 1),
(45, 21, NULL, '09:00:00', '17:00:00', 1),
(49, 23, NULL, '09:00:00', '17:00:00', 1),
(64, 22, NULL, '09:00:00', '17:00:00', 1),
(65, 57, NULL, '09:00:00', '17:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_collections`
--

CREATE TABLE `wishlist_collections` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist_collections`
--

INSERT INTO `wishlist_collections` (`id`, `user_id`, `name`, `sort_order`, `created_at`) VALUES
(1, 30, 'Fav', 1, '2026-06-19 07:32:39'),
(2, 30, 'သားသမီးအတွက်', 2, '2026-06-22 03:24:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_lockout_logs`
--
ALTER TABLE `account_lockout_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event` (`event`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `all_ibfk_unlocked_by` (`unlocked_by`);

--
-- Indexes for table `attire_items`
--
ALTER TABLE `attire_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Indexes for table `attire_rental_bookings`
--
ALTER TABLE `attire_rental_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_item_id` (`booking_item_id`),
  ADD KEY `idx_attire_item_id` (`attire_item_id`),
  ADD KEY `idx_attire_dates` (`attire_item_id`,`borrow_date`,`buffer_until`,`status`);

--
-- Indexes for table `attire_rental_options`
--
ALTER TABLE `attire_rental_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attire_item_id` (`attire_item_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `booking_items`
--
ALTER TABLE `booking_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `venue_room_id` (`venue_room_id`),
  ADD KEY `bi_ibfk_slot` (`slot_id`),
  ADD KEY `idx_booking_package_addon` (`package_booking_item_id`);

--
-- Indexes for table `booking_slot_reservations`
--
ALTER TABLE `booking_slot_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_slot_active` (`booking_id`,`released_at`),
  ADD KEY `idx_booking_slot_service` (`booking_id`,`service_id`,`released_at`),
  ADD KEY `idx_booking_slot_slot` (`slot_id`,`released_at`);

--
-- Indexes for table `booking_status_logs`
--
ALTER TABLE `booking_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `booking_suppliers`
--
ALTER TABLE `booking_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_booking_pkg_item` (`booking_id`,`package_item_id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `booking_supplier_replacements`
--
ALTER TABLE `booking_supplier_replacements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_repl_booking` (`booking_id`),
  ADD KEY `idx_repl_booking_supp` (`booking_supplier_id`),
  ADD KEY `idx_repl_status` (`status`),
  ADD KEY `idx_repl_new_supplier` (`new_supplier_id`),
  ADD KEY `idx_replacement_proposed_status` (`status`,`proposed_at`);

--
-- Indexes for table `booking_vouchers`
--
ALTER TABLE `booking_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_number` (`voucher_number`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ci_ibfk_slot` (`slot_id`),
  ADD KEY `cart_items_venue_room_id` (`venue_room_id`),
  ADD KEY `idx_cart_package_addon` (`package_cart_item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_status_logs`
--
ALTER TABLE `customer_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_csl_user` (`user_id`),
  ADD KEY `idx_csl_changed_by` (`changed_by`);

--
-- Indexes for table `decoration_styles`
--
ALTER TABLE `decoration_styles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_decoration_styles_service` (`service_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_details`
--
ALTER TABLE `event_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_item` (`booking_item_id`),
  ADD KEY `idx_event_details_booking_id` (`booking_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`item_type`,`item_id`),
  ADD KEY `idx_favorites_collection` (`collection_id`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_food_items_service` (`service_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`package_id`),
  ADD UNIQUE KEY `uk_slug` (`slug`),
  ADD KEY `idx_packages_deleted_active_order` (`deleted_at`,`is_active`,`sort_order`,`package_id`),
  ADD KEY `idx_packages_slug` (`slug`),
  ADD KEY `idx_packages_category` (`category_id`);

--
-- Indexes for table `package_items`
--
ALTER TABLE `package_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `default_supplier_id` (`default_supplier_id`),
  ADD KEY `idx_package_items_package_category` (`package_id`,`category_id`),
  ADD KEY `idx_package_items_package_service` (`package_id`,`service_id`),
  ADD KEY `idx_package_items_venue_room` (`venue_room_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_password_resets_token_hash` (`token_hash`),
  ADD KEY `idx_password_resets_user_used` (`user_id`,`used`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_booking_supplier_payout` (`booking_id`,`supplier_id`,`type`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_payments_payout_batch` (`payout_batch_id`);

--
-- Indexes for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refunds_booking` (`booking_id`),
  ADD KEY `idx_refunds_status` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `booking_item_id` (`booking_item_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_name` (`name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_min_lead_days` (`min_lead_days`);

--
-- Indexes for table `service_availability`
--
ALTER TABLE `service_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_service_date` (`service_id`,`date`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `service_media`
--
ALTER TABLE `service_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `service_rental_pricing`
--
ALTER TABLE `service_rental_pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_service_rental` (`service_id`);

--
-- Indexes for table `service_schedules`
--
ALTER TABLE `service_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_service_day` (`service_id`,`day_of_week`),
  ADD KEY `idx_service_id` (`service_id`);

--
-- Indexes for table `service_time_slots`
--
ALTER TABLE `service_time_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`service_id`,`date`,`start_time`),
  ADD KEY `idx_service_date` (`service_id`,`date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `supplier_bans`
--
ALTER TABLE `supplier_bans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_banned_by` (`banned_by`),
  ADD KEY `sb_ibfk_lifted_by` (`lifted_by`);

--
-- Indexes for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_supplier_category` (`supplier_id`,`category_id`),
  ADD KEY `idx_supplier_categories_supplier` (`supplier_id`),
  ADD KEY `idx_supplier_categories_category` (`category_id`);

--
-- Indexes for table `supplier_documents`
--
ALTER TABLE `supplier_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_warnings`
--
ALTER TABLE `supplier_warnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_issued_by` (`issued_by`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_review_id` (`review_id`),
  ADD KEY `sw_ibfk_resolved` (`resolved_by`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `facebook_id` (`facebook_id`),
  ADD UNIQUE KEY `unique_google_id` (`google_id`),
  ADD UNIQUE KEY `unique_facebook_id` (`facebook_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_role_id` (`role_id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_venues_service_id` (`service_id`);

--
-- Indexes for table `venue_rooms`
--
ALTER TABLE `venue_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `idx_room_min_lead_days` (`min_lead_days`);

--
-- Indexes for table `venue_room_availability`
--
ALTER TABLE `venue_room_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_supplier_id` (`supplier_id`);

--
-- Indexes for table `wishlist_collections`
--
ALTER TABLE `wishlist_collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_lockout_logs`
--
ALTER TABLE `account_lockout_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attire_items`
--
ALTER TABLE `attire_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `attire_rental_bookings`
--
ALTER TABLE `attire_rental_bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attire_rental_options`
--
ALTER TABLE `attire_rental_options`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;

--
-- AUTO_INCREMENT for table `booking_items`
--
ALTER TABLE `booking_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=406;

--
-- AUTO_INCREMENT for table `booking_slot_reservations`
--
ALTER TABLE `booking_slot_reservations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `booking_status_logs`
--
ALTER TABLE `booking_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=268;

--
-- AUTO_INCREMENT for table `booking_suppliers`
--
ALTER TABLE `booking_suppliers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=334;

--
-- AUTO_INCREMENT for table `booking_supplier_replacements`
--
ALTER TABLE `booking_supplier_replacements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `booking_vouchers`
--
ALTER TABLE `booking_vouchers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `customer_status_logs`
--
ALTER TABLE `customer_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `decoration_styles`
--
ALTER TABLE `decoration_styles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `event_details`
--
ALTER TABLE `event_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=391;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `package_items`
--
ALTER TABLE `package_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `service_availability`
--
ALTER TABLE `service_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `service_media`
--
ALTER TABLE `service_media`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `service_rental_pricing`
--
ALTER TABLE `service_rental_pricing`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `service_schedules`
--
ALTER TABLE `service_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1508;

--
-- AUTO_INCREMENT for table `service_time_slots`
--
ALTER TABLE `service_time_slots`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `supplier_bans`
--
ALTER TABLE `supplier_bans`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `supplier_documents`
--
ALTER TABLE `supplier_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `supplier_warnings`
--
ALTER TABLE `supplier_warnings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=579;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `venue_rooms`
--
ALTER TABLE `venue_rooms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `venue_room_availability`
--
ALTER TABLE `venue_room_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist_collections`
--
ALTER TABLE `wishlist_collections`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_lockout_logs`
--
ALTER TABLE `account_lockout_logs`
  ADD CONSTRAINT `all_ibfk_unlocked_by` FOREIGN KEY (`unlocked_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `all_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `attire_rental_options`
--
ALTER TABLE `attire_rental_options`
  ADD CONSTRAINT `fk_rental_option_attire_item` FOREIGN KEY (`attire_item_id`) REFERENCES `attire_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `booking_items`
--
ALTER TABLE `booking_items`
  ADD CONSTRAINT `bi_ibfk_slot` FOREIGN KEY (`slot_id`) REFERENCES `service_time_slots` (`id`),
  ADD CONSTRAINT `booking_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_items_ibfk_2` FOREIGN KEY (`venue_room_id`) REFERENCES `venue_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_items_package_addon_fk` FOREIGN KEY (`package_booking_item_id`) REFERENCES `booking_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_status_logs`
--
ALTER TABLE `booking_status_logs`
  ADD CONSTRAINT `booking_status_logs_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `booking_status_logs_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `booking_suppliers`
--
ALTER TABLE `booking_suppliers`
  ADD CONSTRAINT `booking_suppliers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_suppliers_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_vouchers`
--
ALTER TABLE `booking_vouchers`
  ADD CONSTRAINT `booking_vouchers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `booking_vouchers_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `booking_vouchers_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_items_package_addon_fk` FOREIGN KEY (`package_cart_item_id`) REFERENCES `cart_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_venue_room_fk` FOREIGN KEY (`venue_room_id`) REFERENCES `venue_rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ci_ibfk_slot` FOREIGN KEY (`slot_id`) REFERENCES `service_time_slots` (`id`);

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_details`
--
ALTER TABLE `event_details`
  ADD CONSTRAINT `event_details_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_collection` FOREIGN KEY (`collection_id`) REFERENCES `wishlist_collections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `package_items`
--
ALTER TABLE `package_items`
  ADD CONSTRAINT `package_items_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`),
  ADD CONSTRAINT `package_items_ibfk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `package_items_ibfk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `package_items_ibfk_supplier` FOREIGN KEY (`default_supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`booking_item_id`) REFERENCES `booking_items` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `reviews_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_5` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `service_availability`
--
ALTER TABLE `service_availability`
  ADD CONSTRAINT `sav_ibfk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_media`
--
ALTER TABLE `service_media`
  ADD CONSTRAINT `service_media_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `service_schedules`
--
ALTER TABLE `service_schedules`
  ADD CONSTRAINT `ssc_ibfk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_time_slots`
--
ALTER TABLE `service_time_slots`
  ADD CONSTRAINT `sts_ibfk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `supplier_bans`
--
ALTER TABLE `supplier_bans`
  ADD CONSTRAINT `sb_ibfk_banned_by` FOREIGN KEY (`banned_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sb_ibfk_lifted_by` FOREIGN KEY (`lifted_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sb_ibfk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  ADD CONSTRAINT `supplier_categories_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_categories_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `supplier_documents`
--
ALTER TABLE `supplier_documents`
  ADD CONSTRAINT `supplier_documents_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `supplier_warnings`
--
ALTER TABLE `supplier_warnings`
  ADD CONSTRAINT `sw_ibfk_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `sw_ibfk_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sw_ibfk_resolved` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sw_ibfk_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`),
  ADD CONSTRAINT `sw_ibfk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `venues`
--
ALTER TABLE `venues`
  ADD CONSTRAINT `venues_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `venues_service_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `venue_rooms`
--
ALTER TABLE `venue_rooms`
  ADD CONSTRAINT `venue_rooms_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`);

--
-- Constraints for table `venue_room_availability`
--
ALTER TABLE `venue_room_availability`
  ADD CONSTRAINT `venue_room_availability_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `venue_rooms` (`id`);

--
-- Constraints for table `wishlist_collections`
--
ALTER TABLE `wishlist_collections`
  ADD CONSTRAINT `wishlist_collections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
