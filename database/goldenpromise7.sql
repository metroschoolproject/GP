-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 17, 2026 at 05:04 PM
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
  `status` enum('draft','pending_supplier_response','pending_payment','payment_submitted','payment_verified','paid','suppliers_responding','confirmed','pending_final_payment','finalized','completed','cancelled') NOT NULL DEFAULT 'draft',
  `supplier_response_deadline` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `cart_id`, `total_amount`, `paid_amount`, `payment_status`, `status`, `supplier_response_deadline`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 27, 2, 1755000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-14 17:39:09'),
(2, 27, 2, 1755000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 02:17:34'),
(3, 27, 2, 910000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 06:59:08'),
(4, 27, 2, 910000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 06:59:41'),
(5, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 08:04:48'),
(6, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 08:42:43'),
(7, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 12:56:06'),
(8, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 12:56:17'),
(9, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 12:56:19'),
(10, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 12:56:43'),
(11, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 13:03:27'),
(12, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 13:03:53'),
(13, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 13:13:36'),
(14, 27, 2, 105000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 13:13:59'),
(15, 27, 2, 75000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-15 13:14:34'),
(16, 27, 2, 75000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-15 13:22:22'),
(17, 27, 2, 75000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-15 13:43:28'),
(18, 27, 2, 880000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 15:22:12'),
(19, 27, 2, 880000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 15:22:18'),
(20, 27, 2, 880000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 15:23:10'),
(21, 27, 2, 880000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 15:23:31'),
(22, 27, 2, 880000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 15:28:20'),
(23, 27, 2, 255000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-15 15:29:19'),
(24, 27, 2, 255000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-15 15:40:56'),
(25, 27, 2, 255000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-16 03:01:28'),
(26, 27, 2, 150000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-16 07:34:59'),
(27, 27, 2, 1000000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-16 07:37:37'),
(28, 27, 2, 850000.00, 0.00, 'unpaid', 'pending_payment', NULL, NULL, NULL, '2026-06-16 13:51:01'),
(29, 27, 2, 2000000.00, 200000.00, 'partial', 'paid', NULL, NULL, NULL, '2026-06-17 01:52:06'),
(30, 27, 2, 600000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-17 09:18:49'),
(31, 27, 2, 600000.00, 60000.00, 'partial', 'confirmed', NULL, NULL, NULL, '2026-06-17 09:32:37'),
(32, 27, 2, 2000000.00, 0.00, 'unpaid', 'draft', NULL, NULL, NULL, '2026-06-17 09:35:40'),
(33, 27, 2, 2000000.00, 200000.00, 'partial', 'confirmed', NULL, NULL, NULL, '2026-06-17 09:36:25'),
(34, 27, 2, 600000.00, 60000.00, 'partial', 'confirmed', '2026-06-19 05:38:29', NULL, NULL, '2026-06-17 10:08:29'),
(35, 27, 2, 74999.98, 0.00, 'unpaid', 'cancelled', '2026-06-19 07:40:16', NULL, NULL, '2026-06-17 12:10:16'),
(36, 27, 2, 2000000.00, 0.00, 'unpaid', 'payment_submitted', '2026-06-19 07:56:59', NULL, NULL, '2026-06-17 12:26:59'),
(37, 27, 2, 2000000.00, 0.00, 'unpaid', 'pending_supplier_response', '2026-06-19 10:33:58', NULL, NULL, '2026-06-17 15:03:58');

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
  `status` enum('pending','accepted','completed','cancelled') DEFAULT NULL,
  `venue_room_id` bigint(20) DEFAULT NULL,
  `slot_id` bigint(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `booking_type` enum('fullday','slot','flexible') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_items`
--

INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `source`, `item_id`, `booking_date`, `price`, `status`, `venue_room_id`, `slot_id`, `start_time`, `end_time`, `booking_type`) VALUES
(1, 1, 'package', 'custom', 16, '2026-06-15 00:09:09', 880000.00, 'pending', NULL, NULL, NULL, NULL, 'fullday'),
(2, 1, 'service', 'custom', 34, '2026-06-17 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(3, 1, 'service', 'custom', 33, '2026-06-14 09:00:00', 800000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(4, 2, 'package', 'custom', 16, '2026-06-15 08:47:34', 880000.00, 'pending', NULL, NULL, NULL, NULL, 'fullday'),
(5, 2, 'service', 'custom', 34, '2026-06-17 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(6, 2, 'service', 'custom', 33, '2026-06-14 09:00:00', 800000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(7, 3, 'package', 'custom', 16, '2026-06-15 13:29:08', 880000.00, 'pending', NULL, NULL, NULL, NULL, 'fullday'),
(8, 3, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(10, 4, 'package', 'custom', 16, '2026-06-15 13:29:41', 880000.00, 'pending', NULL, NULL, NULL, NULL, 'fullday'),
(11, 4, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(13, 5, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(14, 5, 'service', 'custom', 34, '2026-06-17 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(16, 6, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(17, 6, 'service', 'custom', 34, '2026-06-17 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(19, 7, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(20, 7, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(22, 8, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(23, 8, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(25, 9, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(26, 9, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(28, 10, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(29, 10, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(31, 11, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(32, 11, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(34, 12, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(35, 12, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(37, 13, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(38, 13, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(40, 14, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', NULL, NULL, '09:00:00', '17:00:00', 'slot'),
(41, 14, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(43, 15, 'service', 'custom', 34, '2026-06-15 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(44, 16, 'service', 'custom', 34, '2026-06-17 15:00:00', 75000.00, 'pending', NULL, NULL, '15:00:00', '17:00:00', 'slot'),
(45, 17, 'service', 'custom', 34, '2026-06-16 09:00:00', 75000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(46, 18, 'service', 'custom', 33, '2026-06-16 09:00:00', 850000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(47, 18, 'service', 'custom', 32, '2026-06-17 09:00:00', 30000.00, 'pending', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(49, 19, 'service', 'custom', 33, '2026-06-16 09:00:00', 850000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(50, 19, 'service', 'custom', 32, '2026-06-17 09:00:00', 30000.00, 'pending', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(52, 20, 'service', 'custom', 33, '2026-06-16 09:00:00', 850000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(53, 20, 'service', 'custom', 32, '2026-06-17 09:00:00', 30000.00, 'pending', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(55, 21, 'service', 'custom', 33, '2026-06-16 09:00:00', 850000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(56, 21, 'service', 'custom', 32, '2026-06-17 09:00:00', 30000.00, 'pending', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(58, 22, 'service', 'custom', 33, '2026-06-16 09:00:00', 850000.00, 'pending', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(59, 22, 'service', 'custom', 32, '2026-06-17 09:00:00', 30000.00, 'pending', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(61, 23, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'pending', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(62, 23, 'service', 'custom', 34, '2026-06-16 09:00:00', 225000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(64, 24, 'service', 'custom', 32, '2026-06-16 09:00:00', 30000.00, 'accepted', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(65, 24, 'service', 'custom', 34, '2026-06-16 09:00:00', 225000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(67, 25, 'service', 'custom', 32, '2026-07-30 09:00:00', 30000.00, 'cancelled', 15, NULL, '09:00:00', '17:00:00', 'slot'),
(68, 25, 'service', 'custom', 34, '2026-06-16 09:00:00', 225000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(70, 26, 'service', 'custom', 34, '2026-06-20 09:00:00', 150000.00, 'pending', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(71, 27, 'service', 'custom', 34, '2026-06-20 09:00:00', 150000.00, 'accepted', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(72, 27, 'service', 'custom', 33, '2026-07-04 09:00:00', 850000.00, 'accepted', NULL, NULL, '09:00:00', '10:00:00', 'slot'),
(74, 28, 'service', 'custom', 33, '2026-07-04 12:00:00', 850000.00, 'pending', NULL, NULL, '12:00:00', '13:00:00', 'slot'),
(75, 29, 'service', 'custom', 36, '2026-11-25 07:00:00', 2000000.00, 'pending', 17, NULL, '07:00:00', '21:00:00', 'fullday'),
(76, 31, 'service', 'custom', 41, '2026-09-09 09:00:00', 600000.00, 'cancelled', 18, NULL, '09:00:00', '17:00:00', 'slot'),
(77, 32, 'service', 'custom', 36, '2026-12-22 07:00:00', 2000000.00, 'pending', 17, NULL, '07:00:00', '21:00:00', 'fullday'),
(78, 33, 'service', 'custom', 36, '2026-12-26 07:00:00', 2000000.00, 'pending', 17, NULL, '07:00:00', '21:00:00', 'fullday'),
(79, 34, 'service', 'custom', 41, '2027-01-09 09:00:00', 600000.00, 'accepted', 18, NULL, '09:00:00', '17:00:00', 'slot'),
(80, 35, 'service', 'custom', 34, '2026-06-20 09:00:00', 74999.98, 'cancelled', NULL, NULL, '09:00:00', '11:00:00', 'slot'),
(81, 36, 'service', 'custom', 36, '2026-12-17 07:00:00', 2000000.00, 'accepted', 17, NULL, '07:00:00', '21:00:00', 'fullday'),
(82, 37, 'service', 'custom', 36, '2026-09-23 07:00:00', 2000000.00, 'pending', 17, NULL, '07:00:00', '21:00:00', 'fullday');

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
(1, 15, NULL, 'draft', 27, NULL, '2026-06-15 13:14:34'),
(2, 15, 'draft', 'pending_payment', 27, NULL, '2026-06-15 13:14:35'),
(3, 16, NULL, 'draft', 27, NULL, '2026-06-15 13:22:22'),
(4, 16, 'draft', 'pending_payment', 27, NULL, '2026-06-15 13:22:22'),
(5, 17, NULL, 'draft', 27, NULL, '2026-06-15 13:43:28'),
(6, 17, 'draft', 'pending_payment', 27, NULL, '2026-06-15 13:43:28'),
(7, 24, NULL, 'draft', 27, NULL, '2026-06-15 15:40:56'),
(8, 24, 'draft', 'pending_payment', 27, NULL, '2026-06-15 15:40:56'),
(9, 25, NULL, 'draft', 27, NULL, '2026-06-16 03:01:28'),
(10, 25, 'draft', 'pending_payment', 27, NULL, '2026-06-16 03:01:28'),
(11, 25, NULL, 'supplier_rejected', NULL, 'Supplier declineed booking', '2026-06-16 03:33:12'),
(12, 26, NULL, 'draft', 27, NULL, '2026-06-16 07:34:59'),
(13, 26, 'draft', 'pending_payment', 27, NULL, '2026-06-16 07:34:59'),
(14, 27, NULL, 'draft', 27, NULL, '2026-06-16 07:37:37'),
(15, 27, 'draft', 'pending_payment', 27, NULL, '2026-06-16 07:37:37'),
(16, 27, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-16 07:42:39'),
(17, 27, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-16 10:05:56'),
(18, 24, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-16 10:48:38'),
(19, 28, NULL, 'draft', 27, NULL, '2026-06-16 13:51:01'),
(20, 28, 'draft', 'pending_payment', 27, NULL, '2026-06-16 13:51:01'),
(21, 29, NULL, 'draft', 27, NULL, '2026-06-17 01:52:06'),
(22, 29, 'draft', 'pending_payment', 27, NULL, '2026-06-17 01:52:06'),
(23, 29, 'pending_payment', 'paid', 27, NULL, '2026-06-17 02:49:58'),
(24, 31, NULL, 'draft', 27, NULL, '2026-06-17 09:32:37'),
(25, 32, NULL, 'draft', 27, NULL, '2026-06-17 09:35:41'),
(26, 33, NULL, 'draft', 27, NULL, '2026-06-17 09:36:25'),
(27, 34, NULL, 'draft', 27, NULL, '2026-06-17 10:08:29'),
(28, 34, 'draft', 'pending_supplier_response', 27, NULL, '2026-06-17 10:08:29'),
(29, 34, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-17 10:08:53'),
(30, 34, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-17 10:08:53'),
(31, 34, 'pending_payment', 'confirmed', 27, NULL, '2026-06-17 10:09:09'),
(32, 35, NULL, 'draft', 27, NULL, '2026-06-17 12:10:16'),
(33, 35, 'draft', 'pending_supplier_response', 27, NULL, '2026-06-17 12:10:16'),
(34, 33, 'draft', 'pending_payment', 27, NULL, '2026-06-17 12:11:48'),
(35, 33, 'pending_payment', 'confirmed', 27, NULL, '2026-06-17 12:11:55'),
(36, 31, 'draft', 'pending_payment', 27, NULL, '2026-06-17 12:12:07'),
(37, 31, 'pending_payment', 'confirmed', 27, NULL, '2026-06-17 12:12:11'),
(38, 31, NULL, 'supplier_rejected', NULL, 'Supplier declineed booking', '2026-06-17 12:25:50'),
(39, 35, NULL, 'supplier_rejected', NULL, 'Supplier declineed booking', '2026-06-17 12:25:57'),
(40, 35, 'pending_supplier_response', 'cancelled', NULL, 'Supplier declined', '2026-06-17 12:25:57'),
(41, 36, NULL, 'draft', 27, NULL, '2026-06-17 12:26:59'),
(42, 36, 'draft', 'pending_supplier_response', 27, NULL, '2026-06-17 12:26:59'),
(43, 36, NULL, 'supplier_confirmed', NULL, 'Supplier accepted booking', '2026-06-17 12:27:24'),
(44, 36, 'pending_supplier_response', 'pending_payment', NULL, 'All suppliers accepted', '2026-06-17 12:27:24'),
(45, 37, NULL, 'draft', 27, NULL, '2026-06-17 15:03:58'),
(46, 37, 'draft', 'pending_supplier_response', 27, NULL, '2026-06-17 15:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `booking_suppliers`
--

CREATE TABLE `booking_suppliers` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `payout_status` enum('unpaid','processing','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_suppliers`
--

INSERT INTO `booking_suppliers` (`id`, `booking_id`, `supplier_id`, `status`, `confirmed_at`, `completed_at`, `payout_status`, `created_at`, `updated_at`) VALUES
(1, 15, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-15 13:14:34', '2026-06-15 13:14:34'),
(2, 16, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-15 13:22:22', '2026-06-15 13:22:22'),
(3, 17, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-15 13:43:28', '2026-06-15 13:43:28'),
(4, 24, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-15 15:40:56', '2026-06-15 15:40:56'),
(5, 24, 21, 'confirmed', '2026-06-16 10:48:38', NULL, 'unpaid', '2026-06-15 15:40:56', '2026-06-16 10:48:38'),
(7, 25, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-16 03:01:28', '2026-06-16 03:01:28'),
(8, 25, 21, 'rejected', NULL, NULL, 'unpaid', '2026-06-16 03:01:28', '2026-06-16 03:33:12'),
(10, 26, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-16 07:34:59', '2026-06-16 07:34:59'),
(11, 27, 20, 'confirmed', '2026-06-16 07:42:39', NULL, 'unpaid', '2026-06-16 07:37:37', '2026-06-16 07:42:39'),
(12, 27, 21, 'confirmed', '2026-06-16 10:05:56', NULL, 'unpaid', '2026-06-16 07:37:37', '2026-06-16 10:05:56'),
(14, 28, 21, 'pending', NULL, NULL, 'unpaid', '2026-06-16 13:51:01', '2026-06-16 13:51:01'),
(15, 29, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-17 01:52:06', '2026-06-17 01:52:06'),
(16, 31, 20, 'rejected', NULL, NULL, 'unpaid', '2026-06-17 09:32:37', '2026-06-17 12:25:50'),
(17, 32, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-17 09:35:41', '2026-06-17 09:35:41'),
(18, 33, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-17 09:36:25', '2026-06-17 09:36:25'),
(19, 34, 20, 'confirmed', '2026-06-17 10:08:53', NULL, 'unpaid', '2026-06-17 10:08:29', '2026-06-17 10:08:53'),
(20, 35, 20, 'cancelled', NULL, NULL, 'unpaid', '2026-06-17 12:10:16', '2026-06-17 12:25:57'),
(21, 36, 20, 'confirmed', '2026-06-17 12:27:24', NULL, 'unpaid', '2026-06-17 12:26:59', '2026-06-17 12:27:24'),
(22, 37, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-17 15:03:58', '2026-06-17 15:03:58');

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
(1, 29, 'VCH-SRV-D70C8044', 36, 20, 'Golden Inya', 'Venue', '2026-11-25', '07:00:00', '21:00:00', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 2000000.00, 'active', '2026-06-17 02:49:58'),
(2, 34, 'VCH-SRV-58D0096C', 41, 20, 'Hotel Yangon', 'Venue', '2027-01-09', '09:00:00', '17:00:00', 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', 600000.00, 'active', '2026-06-17 10:09:09'),
(3, 33, 'VCH-SRV-65DA1EA7', 36, 20, 'Golden Inya', 'Venue', '2026-12-26', '07:00:00', '21:00:00', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 2000000.00, 'active', '2026-06-17 12:11:55'),
(4, 31, 'VCH-SRV-E4A049F4', 41, 20, 'Hotel Yangon', 'Venue', '2026-09-09', '09:00:00', '17:00:00', 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', 600000.00, 'active', '2026-06-17 12:12:11');

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
(2, 27, '2026-06-14 15:00:24');

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
  `venue_room_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Accessories', 'accessories', '2026-05-24 05:07:27'),
(2, 'Dress', 'dress', '2026-05-24 05:07:27'),
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
-- Table structure for table `decoration_styles`
--

CREATE TABLE `decoration_styles` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `decoration_styles`
--

INSERT INTO `decoration_styles` (`id`, `service_id`, `name`, `price`, `sort_order`, `created_at`) VALUES
(2, 37, 'Ballon Arch', 2000000.00, 0, '2026-06-17 09:27:00');

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
(6, 27, 'c9af74c98a6f1759d78a1c2143dd000b8e88e7f8533cac9ce74e69d25b25c6fb', '2026-06-11 02:39:48', 1, '2026-06-11 02:32:31');

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
(1, 1, NULL, '2026-06-14', '09:00:00', '10:00:00', 1, NULL, '', NULL, NULL, '', '', '', NULL, '2026-06-14 17:39:09'),
(3, 2, NULL, '2026-06-14', '09:00:00', '10:00:00', NULL, NULL, '', NULL, NULL, '', '', '', NULL, '2026-06-15 02:17:34'),
(5, 3, NULL, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, '', NULL, NULL, '', '', '', NULL, '2026-06-15 06:59:08'),
(7, 4, NULL, '2026-06-16', '09:00:00', '17:00:00', 8, NULL, 'venue location customize', NULL, NULL, '', '0912345678', 'venue location customize', NULL, '2026-06-15 06:59:41'),
(9, 5, NULL, '2026-06-17', '09:00:00', '11:00:00', NULL, NULL, '', NULL, NULL, '', '', '', NULL, '2026-06-15 08:04:48'),
(11, 6, NULL, '2026-06-17', '09:00:00', '11:00:00', NULL, NULL, '', NULL, NULL, '', '', '', NULL, '2026-06-15 08:42:43'),
(13, 7, 19, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 12:56:06'),
(15, 8, 22, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 12:56:17'),
(17, 9, 25, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 12:56:19'),
(19, 10, 28, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 12:56:43'),
(21, 11, 31, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:03:27'),
(23, 12, 34, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:03:53'),
(25, 13, 37, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:13:36'),
(27, 14, 40, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:13:59'),
(29, 15, 43, '2026-06-15', '09:00:00', '11:00:00', NULL, NULL, '', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:14:34'),
(30, 16, 44, '2026-06-17', '15:00:00', '17:00:00', NULL, NULL, '', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:22:22'),
(31, 17, 45, '2026-06-16', '09:00:00', '17:00:00', NULL, NULL, '', NULL, NULL, 'HsuHive', '', '', NULL, '2026-06-15 13:43:28'),
(32, 18, 46, '2026-06-16', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:22:12'),
(34, 19, 49, '2026-06-16', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:22:18'),
(36, 20, 52, '2026-06-16', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:23:10'),
(38, 21, 55, '2026-06-16', '09:00:00', '17:00:00', 3, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:23:31'),
(40, 22, 58, '2026-06-16', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:28:20'),
(42, 23, 61, '2026-06-16', '09:00:00', '21:00:00', 3, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:29:19'),
(44, 24, 64, '2026-06-16', '09:00:00', '21:00:00', 3, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:40:56'),
(45, 24, 65, '2026-06-16', '09:00:00', '17:00:00', 3, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-15 15:40:56'),
(46, 25, 67, '2026-07-30', '09:00:00', '21:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-16 03:01:28'),
(47, 25, 68, '2026-06-16', '09:00:00', '17:00:00', 3, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-16 03:01:28'),
(48, 26, 70, '2026-06-20', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-16 07:34:59'),
(49, 27, 71, '2026-06-20', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-16 07:37:37'),
(50, 27, 72, '2026-07-04', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-16 07:37:37'),
(51, 28, 74, '2026-07-04', '09:00:00', '17:00:00', 2, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-16 13:51:01'),
(52, 29, 75, '2026-11-25', '07:00:00', '21:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 01:52:06'),
(53, 31, 76, '2026-09-09', '09:00:00', '17:00:00', 220, NULL, 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 09:32:37'),
(54, 32, 77, '2026-12-22', '07:00:00', '21:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 09:35:41'),
(55, 33, 78, '2026-12-26', '07:00:00', '21:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 09:36:25'),
(56, 34, 79, '2027-01-09', '09:00:00', '17:00:00', 220, NULL, 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 10:08:29'),
(57, 35, 80, '2026-06-20', '09:00:00', '17:00:00', 1, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 12:10:16'),
(58, 36, 81, '2026-12-17', '07:00:00', '21:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 12:26:59'),
(59, 37, 82, '2026-09-23', '07:00:00', '21:00:00', 300, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-17 15:03:58');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `item_type` enum('service','package','supplier_package') NOT NULL,
  `item_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `type` enum('booking','payment','approval','system') DEFAULT NULL,
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
(49, 24, 'New Booking Request', 'A customer is requesting: Golden Inya. Please accept or decline within 48 hours.', 'booking', 'booking', 37, 0, '2026-06-17 15:03:58');

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
(17, 1, '471707', 'login', '2026-06-17 04:18:39', 1, 0, 3, '2026-06-17 04:18:22');

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
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`package_id`, `name`, `category_id`, `slug`, `type`, `description`, `tagline`, `base_price`, `image_url`, `is_active`, `sort_order`, `created_at`, `deleted_at`) VALUES
(17, 'Standard Complete Wedding Package', 4, 'standard-complete-wedding-package', 'curated', 'description', 'Every detail, every moment, perfectly planned', 2850000.00, 'http://localhost/GP/public/uploads/admin/packages/20260617065021-55c9cd8c.jpg', 1, 0, '2026-06-17 04:50:21', NULL),
(18, 'Premium Complete Wedding Package', 4, 'premium-complete-wedding-package', 'curated', 'this is complete premium wedding package', 'Every detail, every moment, perfectly planned', 4850000.00, 'http://localhost/GP/public/uploads/admin/packages/20260617065750-455de21a.png', 1, 0, '2026-06-17 04:57:50', NULL);

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
  `default_supplier_id` bigint(20) DEFAULT NULL,
  `default_price` decimal(10,2) DEFAULT NULL,
  `quantity_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_items`
--

INSERT INTO `package_items` (`id`, `package_id`, `category_id`, `service_id`, `venue_room_id`, `default_supplier_id`, `default_price`, `quantity_type`, `quantity`, `deleted_at`) VALUES
(53, 17, 10, 34, NULL, 20, 50000.00, 'fixed', 1, NULL),
(54, 17, 2, 33, NULL, 21, 800000.00, 'fixed', 1, NULL),
(55, 17, 6, 36, 17, 20, 2000000.00, 'fixed', 1, NULL),
(56, 18, 2, 33, NULL, 21, 800000.00, 'fixed', 1, NULL),
(57, 18, 10, 34, NULL, 20, 50000.00, 'fixed', 1, NULL),
(59, 18, 6, 32, 16, 21, 2000000.00, 'fixed', 1, NULL),
(60, 18, 6, 36, 17, 20, 2000000.00, 'fixed', 1, NULL);

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
  `type` enum('deposit','remaining','full','supplier_fee') DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `supplier_id`, `amount`, `platform_fee`, `supplier_amount`, `escrow_status`, `type`, `method`, `status`, `transaction_ref`, `verified_by`, `verified_at`, `created_at`) VALUES
(6, NULL, 20, 50000.00, 50000.00, 0.00, NULL, 'supplier_fee', 'KBZ Pay', 'failed', NULL, NULL, '2026-06-10 07:25:15', '2026-06-10 07:24:24'),
(7, NULL, 20, 50000.00, 50000.00, 0.00, NULL, 'supplier_fee', 'KBZ Pay', 'success', 'KBZ-DEMO-20260610094812-7', NULL, '2026-06-10 07:48:12', '2026-06-10 07:25:51'),
(8, NULL, 21, 50000.00, 50000.00, 0.00, NULL, 'supplier_fee', 'AYA Bank Transfer', 'success', 'http://localhost/GP/public/uploads/payments/supplier-fees/21-wyndham-grand-yangon-hotel/payment-slip-20260611070930-e6663891.jpg', 1, '2026-06-11 05:09:44', '2026-06-11 05:09:30'),
(9, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-16 15:56:56', '2026-06-16 15:56:56'),
(10, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-16 15:56:58', '2026-06-16 15:56:58'),
(11, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 01:21:25', '2026-06-17 01:21:24'),
(12, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 01:21:26', '2026-06-17 01:21:26'),
(13, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 01:21:28', '2026-06-17 01:21:27'),
(14, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_mmqr', 'failed', NULL, NULL, '2026-06-17 01:21:35', '2026-06-17 01:21:34'),
(15, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_mmqr', 'failed', NULL, NULL, '2026-06-17 01:21:36', '2026-06-17 01:21:36'),
(16, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_mmqr', 'failed', NULL, NULL, '2026-06-17 01:21:37', '2026-06-17 01:21:36'),
(17, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_mmqr', 'failed', NULL, NULL, '2026-06-17 01:39:32', '2026-06-17 01:39:32'),
(18, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 01:39:37', '2026-06-17 01:39:37'),
(19, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 01:39:39', '2026-06-17 01:39:38'),
(20, 28, NULL, 85000.00, 0.00, 85000.00, NULL, 'deposit', '2c2p_mmqr', 'failed', NULL, NULL, '2026-06-17 01:45:13', '2026-06-17 01:45:12'),
(21, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 01:52:16', '2026-06-17 01:52:16'),
(22, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:01:51', '2026-06-17 02:01:50'),
(23, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:36:00', '2026-06-17 02:35:59'),
(24, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:36:10', '2026-06-17 02:36:09'),
(25, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:38:53', '2026-06-17 02:38:53'),
(26, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:38:55', '2026-06-17 02:38:55'),
(27, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:38:56', '2026-06-17 02:38:55'),
(28, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:39:17', '2026-06-17 02:39:17'),
(29, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'failed', NULL, NULL, '2026-06-17 02:44:30', '2026-06-17 02:44:30'),
(30, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'success', 'SANDBOX-190D0AE7-30', NULL, '2026-06-17 02:46:07', '2026-06-17 02:46:07'),
(31, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'success', 'SANDBOX-B39A7A91-31', NULL, '2026-06-17 02:46:09', '2026-06-17 02:46:08'),
(32, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'success', 'SANDBOX-F0814EC4-32', NULL, '2026-06-17 02:46:09', '2026-06-17 02:46:09'),
(33, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_mmqr', 'success', 'SANDBOX-0FC714A3-33', NULL, '2026-06-17 02:47:17', '2026-06-17 02:47:17'),
(34, 29, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_card', 'success', 'SANDBOX-59DDC3A0-34', NULL, '2026-06-17 02:49:58', '2026-06-17 02:49:58'),
(35, 34, NULL, 60000.00, 0.00, 60000.00, NULL, 'deposit', '2c2p_card', 'success', 'SANDBOX-BA852CB1-35', NULL, '2026-06-17 10:09:09', '2026-06-17 10:09:08'),
(36, 33, NULL, 200000.00, 0.00, 200000.00, NULL, 'deposit', '2c2p_mmqr', 'success', 'SANDBOX-FA385585-36', NULL, '2026-06-17 12:11:54', '2026-06-17 12:11:54'),
(37, 31, NULL, 60000.00, 0.00, 60000.00, NULL, 'deposit', '2c2p_card', 'success', 'SANDBOX-8FA20EF9-37', NULL, '2026-06-17 12:12:11', '2026-06-17 12:12:10');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 'staff', 'Staff role', '2026-05-13 07:58:09', '2026-05-13 07:58:09'),
(4, 'admin', 'Administrator role', '2026-05-13 07:58:09', '2026-05-13 07:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL,
  `permission_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `min_lead_days` int(11) DEFAULT 0 COMMENT 'Minimum days in advance customer must book (0 = same day allowed)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `supplier_id`, `category_id`, `name`, `description`, `price`, `price_min`, `price_max`, `thumbnail_url`, `is_active`, `booking_type`, `duration_minutes`, `pricing_unit`, `buffer_minutes`, `max_concurrent`, `created_at`, `min_lead_days`) VALUES
(32, 21, 6, 'Governor&amp;amp;amp;amp;amp;#039;s Residence', 'governor', 30000.00, 30000.00, 2000000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260614145344-367d5efe.jpg', 1, 'slot', 720, 'per_session', 0, 700, '2026-06-14 12:53:44', 0),
(33, 21, 2, 'Dear Brides', 'ဝတ်စုံနှင့် ဝန်ဆောင်မှုများစုံလင်သော ဝတ်စုံဒီဇိုင်းများ: Wedding Gowns, Mermaid Dresses, Evening Dresses နဲ့ Pre-Wedding အတွက် ဝတ်စုံလှလှလေးများကို စိတ်ကြိုက်ငှားရမ်းနိုင်ပါတယ်။\n\nနောက်ဆုံးပေါ် ဒီဇိုင်းသစ်များ: နိုင်ငံခြား Wedding Dress Industry ရှိ စက်ရုံကြီးများမှ နောက်ဆုံးပေါ် Dress များကို မိမိကိုယ်တိုင်း၊ မိမိစိတ်ကြိုက် ရွေးချယ်ပြီး အငှား/အဝယ် မှာယူနိုင်ပါတယ်။\n\nအမှတ်တရ သိမ်းဆည်းလိုသူများအတွက်: အသစ်စက်စက် Dress များကို Studio မှာ ကိုယ်တိုင်ဝတ်ကြည့်ပြီး ဝယ်ယူနိုင်သလို၊ Bridal Veil (သတို့သမီးခေါင်းခြုံပုဝါ) များကိုလည်း မိမိစိတ်ကြိုက် Customized မှာယူနိုင်ပါတယ်ရှင်။\n\n🌸 မြန်မာ့ရိုးရာ ဝတ်စုံဝန်ဆောင်မှုခေတ်မီဝတ်စုံများသာမက ရိုးရာထိုင်မသိမ်း၊ တောင်ရှည်ဝတ်စုံများကိုလည်း အငှား/အရောင်းအပြင် အသစ်ချုပ်အငှား ဝန်ဆောင်မှုပါ ရရှိနိုင်ပါတယ်။ (အသားအရောင်နှင့် ကိုယ်လုံးအချိုးအစားပေါ်မူတည်၍ ဒီဇိုင်းသီးသန့် ဆွဲပေးပါတယ်ရှင်)\n\n💐 ပြီးပြည့်စုံသော Wedding Packagesဝတ်စုံများအပြင် Floral Decoration၊ လက်ကိုင်ပန်း၊ Hotel &amp; Makeup Booking နှင့် မင်္ဂလာကားအလှဆင်ခြင်းအထိ အစုံအလင် ဝန်ဆောင်မှုပေးနေတာကြောင့် Dear Brides ကို ယုံကြည်စွာ လှမ်းလာခဲ့ဖို့ ဖိတ်ခေါ်လိုက်ပါတယ်ရှင်။', 800000.00, 800000.00, 850000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260614145726-b953dd13.jpg', 1, 'slot', 60, 'per_session', 0, 3, '2026-06-14 12:57:26', 18),
(34, 20, 10, 'Lin Lin', 'မိတ်ကပ်ပညာကို စနစ်တကျ သင်ယူချင်သူများအတွက် Lin Lin Makeup Academy ရှိသလို၊ ထူးခြားဆန်းသစ်တဲ့ Look တွေကို ပိုင်ဆိုင်ချင်တဲ့ ပွဲတက်သတို့သမီးများအတွက်လည်း Lin Lin က အနီးကပ် ရှိနေမှာပါ။ Color Theory နှင့် Face Anatomy အခြေခံကာ လူတစ်ဦးချင်းစီနဲ့ အလိုက်ဖက်ဆုံး အလှတရားတွေကို ဖန်တီးပေးနေသည့် သူမ၏ လက်ရာများကို Lin Lin Facebook Page တွင် လေ့လာနိုင်ပါသည်။', 50000.00, 50000.00, 74999.98, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260617033015-703e56de.jpg', 1, 'slot', 120, 'per_session', 0, 1, '2026-06-14 14:09:35', 3),
(35, 21, 3, 'El Dorado', 'eldorado', 800.00, 800.00, 1000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260615111205-7951bb0a.jpg', 0, 'slot', 60, 'per_session', 0, 2, '2026-06-15 09:12:05', 0),
(36, 20, 6, 'Golden Inya', 'Golden Inya Restaurant ကအမြင်လှ အဆင့်မြင့်စားသောက်ဆိုင်တစ်ခုဖြစ်ပြီး weeding, Engagement, Reception, Corporate Eventတွေအတွက်လူကြိုက်များ ပြီးအင်ယားကန်ကိုတိုင်ရိုက်မြင်ရလို့  Evening weeding အတွက် ကိုက်ညီတယ် indoor outdoor ပေါင်းသုံးလို့ရတဲ့အဆင့်မြင့်စား သောက် ဆိုင်တစ်ခုဖြစ်သည်။', 2000000.00, 2000000.00, 2000000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260615201507-b1f2d185.jpg', 1, 'fullday', 60, 'per_session', 0, 300, '2026-06-15 18:15:07', 0),
(37, 20, 12, 'Aphrodite Wedding Planning &amp;amp;amp; Decoration', 'မိမိတိုရဲ့ အလှပဆုံး မင်္ဂလာအချိန်လေးကို လစ်ဟာမှုတွေ၊ လိုအပ်ချက်တွေမရှိဘဲ အချိုမြိန်ဆုံးအခိုက်အတန့်တွေကိုသာ အမှတ်တရဖြစ် နေစေဖို ကျွမ်းကျင်တဲ့ Wedding Professional တွေနဲ့အတူ မိမိတိုရဲ့ မင်္ဂလာနေ့ရက်လေးကို အပြည့်အစုံဆုံး ပုံဖော်လိုက်ပါ။\nမိမိတိုရဲ့ တစ်သက်မှတစ်ခါ ရင်အခုန်ရဆုံးနဲ့ အလှပဆုံး နေ့ရက် လေးအတွက် အကောင်းဆုံး Service အကောင်းဆုံး Quality တွေအပြင် ကျွမ်းကျင် Professional Wedding Planner တွေနဲ့ မိမိတိုရဲ့ ပွဲ ကို စိတ်အေးရချင်တယ်ဆိုရင်တော့ Aphrodite Wedding Planning and Decoration ကို အခုပဲရွေးချယ်လိုက်ပါ။', 2000000.00, 2000000.00, 2000000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260617092813-7c6b29f7.jpg', 0, 'fullday', 60, 'per_session', 0, 2, '2026-06-17 07:28:13', 15),
(39, 20, 2, 'T &amp;amp; T', 'ဆိုင်မှာ ရွေးချယ်စရာ အထည်ရေ အသစ်စက်စက် အထည် ရာ ကျော် ထားရှိပေးပါတယ်။ \nWedding Industry Experience (10) နှစ်အထက်အတွေ့အကြုံရှိတဲ့ founder မှ လက်ရှိ wedding trend များကို အကြံပေးရင်း သတို့သမီးလေးများရဲ့ ခန္ဓာကိုယ် lifestyle နဲ့လိုက်ဖက်တဲ့ ပုံစံကို သေချာရွေးချယ်ပေးပါတယ်။လိုက်ဖက်တဲ့ Make up look ,လက်ဝတ်ရတနာ accessory design ကအစ သေချာ တိုင်ပင်ပေးပါတယ်။\n အထည်အသစ်ရောက်လားလို့မေးစရာမလိုအောင်ကို အမြဲတမ်း လတိုင်းကို အထည်သစ်ရောက်တဲ့အပြင်အထည်ဟောင်းများကို ပြန်ရောင်းထုတ်တဲ့ဆိုင်မို့ တဆိုင်လုံးရွေးစရာအများကြီးနဲ့ လှပနေမှာပါ။Customer များကို ဝန်ဆောင်မှုအကောင်းဆုံး နဲ့ သေချာလေး service ပေးပါတယ်ရှင်။', 0.00, 0.00, 0.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260617093718-01a2615a.jpg', 1, 'slot', 60, 'per_session', 0, 1, '2026-06-17 07:37:18', 7),
(41, 20, 6, 'Hotel Yangon', 'Hotel Yangon, a luxurious business as well as leisure hotel sits majesticallyon a beautifully landscaped garden with a panoramic view of Yangon City.It is strategically located at 8th Mile junction area which is situated with many businessand commercial offices. Our hotel is close to Junction 8 Shopping Center and , just 10 minutes drivefrom Yangon International Airport &amp;amp;amp;amp;amp; 30 minutes driveto famous landmark of Yangon, Myanmar, Shwedagon Pagoda.', 600000.00, 600000.00, 850000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260617103138-dcb36324.jpg', 1, 'slot', 60, 'per_session', 0, 500, '2026-06-17 08:31:38', 30);

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
(37, 33, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260614145733-b15c484a.jpg', 'image'),
(38, 33, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260614145737-ba7fcc80.jpg', 'image'),
(39, 33, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260614145741-505ba5f6.jpg', 'image'),
(40, 33, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260614145744-af20692c.jpg', 'image'),
(49, 32, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260615054230-eb69fc55.jpg', 'image'),
(50, 32, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260615054233-d9e7887e.jpg', 'image'),
(51, 32, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260615054236-b1383655.jpg', 'image'),
(52, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033409-27fcbfee.jpg', 'image'),
(53, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033414-402bb0d0.jpg', 'image'),
(54, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033418-5872caa3.jpg', 'image'),
(55, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033423-f5eb4339.jpg', 'image'),
(56, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033427-c68b1ce7.jpg', 'image'),
(57, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033431-d9274148.jpg', 'image'),
(58, 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617033435-2fe3a729.jpg', 'image'),
(59, 34, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617034900-01606477.jpg', 'image'),
(60, 34, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617034903-ec8ceda2.jpg', 'image'),
(61, 34, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617034906-fb1bf13c.jpg', 'image'),
(62, 34, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617034910-da01ef19.jpg', 'image'),
(63, 39, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617100135-52a8173b.jpg', 'image'),
(64, 39, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617100139-f75fb441.jpg', 'image'),
(65, 39, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617100143-eb396dd9.jpg', 'image'),
(66, 39, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617100147-6d18c5de.jpg', 'image'),
(67, 41, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617105010-eb0c6403.jpg', 'image'),
(68, 41, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617105015-edd41b9c.jpg', 'image'),
(69, 41, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617105019-91a8839e.jpg', 'image'),
(70, 41, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617105025-1c8779fc.jpg', 'image'),
(71, 41, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260617105029-0cf2e0a2.png', 'image');

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
(1, 39, 1500000.00, 1500000.00, 1500000.00, 3, 230000.00, 230000.00, 230000.00, '2026-06-17 07:37:19');

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
(288, 32, 1, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(289, 32, 2, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(290, 32, 3, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(291, 32, 4, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(292, 32, 5, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(293, 32, 6, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(294, 32, 7, '09:00:00', '21:00:00', 1, '2026-06-14 17:46:30'),
(295, 35, 1, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(296, 35, 2, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(297, 35, 3, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(298, 35, 4, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(299, 35, 5, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(300, 35, 6, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(301, 35, 7, '09:00:00', '17:00:00', 1, '2026-06-15 09:12:15'),
(309, 33, 1, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(310, 33, 2, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(311, 33, 3, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(312, 33, 4, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(313, 33, 5, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(314, 33, 6, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(315, 33, 7, '09:00:00', '17:00:00', 1, '2026-06-16 07:10:03'),
(323, 34, 1, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(324, 34, 2, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(325, 34, 3, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(326, 34, 4, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(327, 34, 5, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(328, 34, 6, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(329, 34, 7, '09:00:00', '17:00:00', 1, '2026-06-17 01:49:14'),
(330, 39, 1, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(331, 39, 2, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(332, 39, 3, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(333, 39, 4, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(334, 39, 5, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(335, 39, 6, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(336, 39, 7, '09:00:00', '17:00:00', 1, '2026-06-17 08:03:04'),
(337, 41, 1, '09:00:00', '17:00:00', 1, '2026-06-17 08:49:59'),
(338, 41, 2, '09:00:00', '17:00:00', 1, '2026-06-17 08:49:59'),
(339, 41, 3, '09:00:00', '17:00:00', 1, '2026-06-17 08:49:59'),
(340, 41, 4, '09:00:00', '17:00:00', 1, '2026-06-17 08:49:59'),
(341, 41, 5, '09:00:00', '17:00:00', 1, '2026-06-17 08:49:59'),
(342, 41, 6, '09:00:00', '17:00:00', 0, '2026-06-17 08:49:59'),
(343, 41, 7, '09:00:00', '17:00:00', 0, '2026-06-17 08:49:59');

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
  `max_concurrent` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('available','full','blocked') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_activity_logs`
--

CREATE TABLE `staff_activity_logs` (
  `id` bigint(20) NOT NULL,
  `staff_id` bigint(20) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_payroll`
--

CREATE TABLE `staff_payroll` (
  `id` bigint(20) NOT NULL,
  `staff_id` bigint(20) NOT NULL,
  `salary_id` bigint(20) NOT NULL,
  `period_month` tinyint(2) NOT NULL,
  `period_year` smallint(4) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonus_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_amount` decimal(10,2) NOT NULL,
  `net_amount` decimal(10,2) NOT NULL,
  `approvals_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','approved','paid') NOT NULL DEFAULT 'draft',
  `approved_by` bigint(20) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_ref` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `staff_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `performance_score` decimal(5,2) DEFAULT 0.00,
  `total_actions` int(11) DEFAULT 0,
  `total_approvals` int(11) DEFAULT 0,
  `total_rejections` int(11) DEFAULT 0,
  `total_bookings_handled` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_salaries`
--

CREATE TABLE `staff_salaries` (
  `id` bigint(20) NOT NULL,
  `staff_id` bigint(20) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonus_per_approval` decimal(8,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'MYR',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `set_by` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `user_id`, `shop_name`, `description`, `status`, `verified_by`, `approved_by`, `verify_url`, `agreement_accepted`, `agreement_accepted_at`, `agreement_version`, `payment_status`, `is_available`, `created_at`, `deleted_at`) VALUES
(20, 24, 'JV', 'we sell dress', 'verified', NULL, 1, 'https://www.facebook.com/jv230', 1, '2026-06-10 02:08:51', 'supplier-v1', 'paid', 1, '2026-06-10 06:38:51', NULL),
(21, 29, 'Wyndham Grand Yangon Hotel', 'ဝင်ဒမ်ဂရန်းရန်ကုန်ဟိုတယ်ရဲ့ Wedding Tea Package များကို US$ 7 တောင် လျော့ပေးမယ့်အပြင် မိမိရွေး ချယ်တဲ့ Package ပေါ် မူတည်၍ Walkway နဲ့ LED အသုံးပြုခွင့်များပါ ရရှိနိုင်မှာပဲဖြစ်ပါတယ်...\r\n\r\nဒါ့အပြင် Wedding Dinner Packages ဝယ်ယူသူတိုင်းအတွက် အခမဲ့ Complimentary Table များ (သိုမဟုတ်) Walkway အသုံးပြုခွင့် (သိုမဟုတ်)  LED အသုံးပြုခွင့်ဆိုပြီး မိမိ နှစ်သက်ရာ အကျိုးခံစားခွင့်ကို ရွေးချယ်ရယူနိုင်မှာပါ...\r\n\r\n သင့်စိတ်ကူးထဲကအတိုင်း ကြီးကျယ်ခမ်းနားလှပတဲ့ Wedding ပွဲကြီးကို စိတ်တိုင်းကျဖန်တီးနိုင်ဖိုအတွက် ဝင်ဒမ်ဂရန်းရန်ကုန်ဟိုတယ်ရဲ့ Wedding Venue Area များက အသင့်တော်ဆုံးရွေးချယ်မှုဖြစ်စေမှာပါ...Wedding Period ကိုလည်း ၂၀၂၇ ခုနှစ် နှစ်ကုန်အထိ ပေးထားတာမို တအားတန်တဲ့ ဒီအခွင့်အရေးကို လက်မလွတ်ရလေအောင် အမိအရဖမ်းဆုပ်လိုက်တော့နော်...🤍', 'verified', 1, 1, 'htpps://www.wyndhamgrandyangon.com', 1, '2026-06-11 00:31:15', 'supplier-v1', 'paid', 1, '2026-06-11 05:01:15', NULL);

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
(7, 21, 6, 'manual', NULL, '2026-06-11 05:01:15');

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
(11, 21, 'http://localhost/GP/public/uploads/suppliers/21-wyndham-grand-yangon-hotel/documents/business-license-20260611070115-21f65a81.pdf', 'business_license', '2026-06-11 05:01:15');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_packages`
--

CREATE TABLE `supplier_packages` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `categories_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_packages`
--

INSERT INTO `supplier_packages` (`id`, `supplier_id`, `name`, `description`, `total_price`, `thumbnail_url`, `is_active`, `categories_json`, `created_at`, `deleted_at`) VALUES
(1, 20, 'test', 'this is test', 9800.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/package/20260610183630-3f83ec40.jpg', 1, '[\"Accessories\",\"Dress\",\"Food\",\"Others\"]', '2026-06-10 16:36:02', '2026-06-14 07:50:30');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_package_items`
--

CREATE TABLE `supplier_package_items` (
  `id` bigint(20) NOT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(88, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:18:39', '2026-06-17 04:18:39', NULL, '2026-06-17 04:18:39');

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
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `address`, `status`, `lock_reason`, `locked_until`, `failed_password_attempts`, `failed_otp_attempts`, `last_failed_at`, `last_login`, `is_online`, `created_at`, `deleted_at`, `google_id`, `avatar`, `facebook_id`, `updated_at`, `email_verified_at`, `remember_token`) VALUES
(1, 'Hsu Myat Moe', 'hsumyatm7308@gmail.com', '$2y$10$GmYwyGUldx18yjwvk2VtYuwtOnF.IWftmffSvfVUH.OrjjF85dd/i', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-17 04:17:34', 1, '2026-05-21 17:36:05', NULL, '108175427434445055275', 'https://lh3.googleusercontent.com/a/ACg8ocJe2tVcu-OZRevJWFdEJzRQYM7rUvS-PP7VTfvv54W2K70gmX2v=s96-c', NULL, '2026-06-17 04:17:34', '2026-06-11 14:19:36', NULL),
(24, 'J V', 'mhsu537@gmail.com', '$2y$10$m23y02SGxPewmFgVlKP1uO8ZJL7vKzUNIwf8YL31VTz6BDcc4QWJm', '09771471462', 'ကန်တော်ကြီး ကရဝိတ်၊ မျှော်စင်ကျွန်းဝင်ပေါက်အနီး၊ မင်္ဂလာတောင်ညွန့်မြို့နယ်၊ ရန်ကုန်မြို့။ ', 'active', NULL, NULL, 3, 0, '2026-06-13 05:50:22', NULL, 0, '2026-06-10 06:38:38', NULL, '112808788643014027786', 'https://lh3.googleusercontent.com/a/ACg8ocIXClMfEn5duPuil8ov2K8LCsnUDcK7DYKGSo2DuULXo1tqaHi2=s96-c', NULL, '2026-06-17 01:22:53', '2026-06-17 01:22:53', NULL),
(27, 'HsuHive', 'hsuhive38@gmail.com', '$2y$10$yHn.drr2Pu2Qg0ICyiSwWOZ6.zeppA14QazMNS7qtypWYhVl215WG', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-11 12:40:57', 1, '2026-06-11 02:32:31', NULL, '106937788818804252855', 'https://lh3.googleusercontent.com/a/ACg8ocJSYHRoiZxk9x5f8qT8EPb8deKr6ae5wTdn7NyvRyuab_iEpg=s96-c', NULL, '2026-06-14 14:33:04', '2026-06-14 14:33:04', NULL),
(29, 'Saen', 'saenintiktok@gmail.com', '$2y$10$MmR6sJNtdIOP7v.4wbW.OeaeTdp4G5.G0.ZQ3bCW6oZRm0JjI4JXS', '09451777705', 'no.11, corner of Kan Yeik Thar Road &amp; U Aung Myat Road, Mingalar Taung township', 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-11 04:43:46', NULL, '113883451541620508706', 'https://lh3.googleusercontent.com/a/ACg8ocKa0OVagjb-Z034lNGR1feDM9cWYi9krO4byxaDck2Fzyjv1w=s96-c', NULL, '2026-06-17 09:33:11', '2026-06-17 09:33:11', NULL);

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
(31, 29, 1, '2026-06-13 17:31:21');

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
(16, 21, 32, 'Governor\'s Residence', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'governor', '2026-06-14 12:53:44'),
(17, 20, 36, 'Golden Inya Restaurant', 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Golden Inya Restaurant ကအမြင်လှ အဆင့်မြင့်စားသောက်ဆိုင်တစ်ခုဖြစ်ပြီး weeding, Engagement, Reception, Corporate Eventတွေအတွက်လူကြိုက်များ ပြီးအင်ယားကန်ကိုတိုင်ရိုက်မြင်ရလို့  Evening weeding အတွက် ကိုက်ညီတယ် indoor outdoor ပေါင်းသုံးလို့ရတဲ့အဆင့်မြင့်စား သောက် ဆိုင်တစ်ခုဖြစ်သည်။', '2026-06-15 18:15:07'),
(18, 20, NULL, 'Hotel Yangon', 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', 'Hotel Yangon, a luxurious business as well as leisure hotel sits majesticallyon a beautifully landscaped garden with a panoramic view of Yangon City.It is strategically located at 8th Mile junction area which is situated with many businessand commercial offices. Our hotel is close to Junction 8 Shopping Center and , just 10 minutes drivefrom Yangon International Airport &amp; 30 minutes driveto famous landmark of Yangon, Myanmar, Shwedagon Pagoda.', '2026-06-17 08:29:07'),
(19, 20, 41, 'Hotel Yangon', 'အမှတ်(91/93)၊ ပြည်လမ်းနှင့် ကမ္ဘာအေးဘုရားလမ်းထောင့်၊ ၈မိုင်လမ်းဆုံ၊ မရမ်းကုန်းမြို့နယ်၊ ရန်ကုန်မြို့။', 'Hotel Yangon, a luxurious business as well as leisure hotel sits majesticallyon a beautifully landscaped garden with a panoramic view of Yangon City.It is strategically located at 8th Mile junction area which is situated with many businessand commercial offices. Our hotel is close to Junction 8 Shopping Center and , just 10 minutes drivefrom Yangon International Airport &amp;amp;amp;amp;amp; 30 minutes driveto famous landmark of Yangon, Myanmar, Shwedagon Pagoda.', '2026-06-17 08:31:38');

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
(1, 1, 'Golden Inya', 700, 1500000.00, '2026-06-11 06:45:46', NULL, NULL),
(2, 2, 'Room 1', 300, 35000.00, '2026-06-11 06:54:59', NULL, NULL),
(3, 2, 'Room 2', 230, 20000.00, '2026-06-11 06:54:59', NULL, NULL),
(4, 3, 'Governor&#039;s Residence', 700, 500000.00, '2026-06-11 08:20:20', NULL, NULL),
(5, 4, 'Governor&#039;s Residence', 699, 500000.00, '2026-06-11 08:32:32', NULL, NULL),
(6, 6, 'Roof Top', 500, 2000000.00, '2026-06-11 14:28:51', NULL, NULL),
(7, 6, 'Room 1', 300, 500000.00, '2026-06-12 13:42:39', NULL, NULL),
(8, 9, 'Room 1', 300, 40000.00, '2026-06-14 07:11:01', NULL, NULL),
(9, 10, 'roof top', 300, 40000.00, '2026-06-14 07:25:22', NULL, NULL),
(10, 11, 'Room 1', 300, 4000.00, '2026-06-14 09:28:17', NULL, NULL),
(11, 12, 'room 1', 3000, 50000.00, '2026-06-14 09:48:10', NULL, NULL),
(12, 13, 'room 1', 3000, 50000.00, '2026-06-14 09:48:15', NULL, NULL),
(13, 14, 'Grand Hall', 300, 35000.00, '2026-06-14 12:28:41', NULL, NULL),
(14, 15, 'Grand Hall', 300, 35000.00, '2026-06-14 12:49:18', NULL, NULL),
(15, 16, 'Grand Hall', 300, 30000.00, '2026-06-14 12:53:44', 30, NULL),
(16, 16, 'Roof top', 700, 2000000.00, '2026-06-15 03:43:20', 30, NULL),
(17, 17, 'Hall 1', 300, 2000000.00, '2026-06-15 18:15:07', 90, NULL),
(18, 19, 'Royal Ball Room', 220, 600000.00, '2026-06-17 08:31:38', 60, 'http://localhost/GP/public/uploads/suppliers/20/service-management/hall/20260617110322-fa0d6af8.jpg'),
(19, 19, 'Grand Hall', 500, 800000.00, '2026-06-17 09:03:22', 36, 'http://localhost/GP/public/uploads/suppliers/20/service-management/hall/20260617110322-881773ae.jpg');

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
(1, 4, NULL, '09:00:00', '17:00:00', 1),
(2, 5, NULL, '09:00:00', '17:00:00', 1),
(6, 6, '2026-06-12', NULL, NULL, 0),
(7, 6, NULL, '09:00:00', '23:00:00', 1),
(8, 7, NULL, '09:00:00', '17:00:00', 1),
(9, 8, NULL, '01:00:00', '12:00:00', 1),
(10, 9, NULL, '01:00:00', '12:00:00', 1),
(11, 10, NULL, '09:00:00', '17:00:00', 1),
(12, 11, NULL, '09:00:00', '17:00:00', 1),
(13, 12, NULL, '09:00:00', '17:00:00', 1),
(16, 13, NULL, '09:00:00', '17:00:00', 1),
(18, 14, NULL, '09:00:00', '17:00:00', 1),
(31, 15, NULL, '09:00:00', '17:00:00', 1),
(32, 16, NULL, '09:00:00', '12:00:00', 1),
(35, 17, NULL, '07:00:00', '21:00:00', 1),
(37, 18, NULL, '09:00:00', '17:00:00', 1),
(38, 19, NULL, '09:00:00', '17:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint(20) NOT NULL,
  `wallet_id` bigint(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `type` enum('credit','debit') DEFAULT NULL,
  `reference_type` enum('payment','withdrawal','refund') DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `bi_ibfk_slot` (`slot_id`);

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
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_status` (`status`);

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
  ADD KEY `cart_items_venue_room_id` (`venue_room_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`item_type`,`item_id`);

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
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_permission_id` (`permission_id`);

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
-- Indexes for table `staff_activity_logs`
--
ALTER TABLE `staff_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `staff_payroll`
--
ALTER TABLE `staff_payroll`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_period` (`staff_id`,`period_month`,`period_year`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_salary_id` (`salary_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `sp_ibfk_approved_by` (`approved_by`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staff_salaries`
--
ALTER TABLE `staff_salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_effective_from` (`effective_from`),
  ADD KEY `ss_ibfk_set_by` (`set_by`);

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
-- Indexes for table `supplier_packages`
--
ALTER TABLE `supplier_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_package_items`
--
ALTER TABLE `supplier_package_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `service_id` (`service_id`);

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
  ADD UNIQUE KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_id` (`wallet_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_lockout_logs`
--
ALTER TABLE `account_lockout_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `booking_items`
--
ALTER TABLE `booking_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `booking_status_logs`
--
ALTER TABLE `booking_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `booking_suppliers`
--
ALTER TABLE `booking_suppliers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `booking_vouchers`
--
ALTER TABLE `booking_vouchers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `decoration_styles`
--
ALTER TABLE `decoration_styles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `event_details`
--
ALTER TABLE `event_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `package_items`
--
ALTER TABLE `package_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `service_availability`
--
ALTER TABLE `service_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `service_media`
--
ALTER TABLE `service_media`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `service_rental_pricing`
--
ALTER TABLE `service_rental_pricing`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_schedules`
--
ALTER TABLE `service_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=344;

--
-- AUTO_INCREMENT for table `service_time_slots`
--
ALTER TABLE `service_time_slots`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff_activity_logs`
--
ALTER TABLE `staff_activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_payroll`
--
ALTER TABLE `staff_payroll`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  MODIFY `staff_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_salaries`
--
ALTER TABLE `staff_salaries`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `supplier_bans`
--
ALTER TABLE `supplier_bans`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_categories`
--
ALTER TABLE `supplier_categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supplier_documents`
--
ALTER TABLE `supplier_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `supplier_packages`
--
ALTER TABLE `supplier_packages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_package_items`
--
ALTER TABLE `supplier_package_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_warnings`
--
ALTER TABLE `supplier_warnings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `venue_rooms`
--
ALTER TABLE `venue_rooms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `venue_room_availability`
--
ALTER TABLE `venue_room_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `booking_items_ibfk_2` FOREIGN KEY (`venue_room_id`) REFERENCES `venue_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `staff_activity_logs`
--
ALTER TABLE `staff_activity_logs`
  ADD CONSTRAINT `staff_activity_logs_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`staff_id`);

--
-- Constraints for table `staff_payroll`
--
ALTER TABLE `staff_payroll`
  ADD CONSTRAINT `sp_ibfk_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sp_ibfk_salary` FOREIGN KEY (`salary_id`) REFERENCES `staff_salaries` (`id`),
  ADD CONSTRAINT `sp_ibfk_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`staff_id`);

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `staff_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `staff_salaries`
--
ALTER TABLE `staff_salaries`
  ADD CONSTRAINT `ss_ibfk_set_by` FOREIGN KEY (`set_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ss_ibfk_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`staff_id`);

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
-- Constraints for table `supplier_packages`
--
ALTER TABLE `supplier_packages`
  ADD CONSTRAINT `supplier_packages_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `supplier_package_items`
--
ALTER TABLE `supplier_package_items`
  ADD CONSTRAINT `supplier_package_items_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `supplier_packages` (`id`),
  ADD CONSTRAINT `supplier_package_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

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
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`);

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
