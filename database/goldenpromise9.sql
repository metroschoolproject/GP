-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 19, 2026 at 06:00 AM
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
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attire_items`
--

INSERT INTO `attire_items` (`id`, `service_id`, `name`, `description`, `photo_url`, `borrow_package_price`, `borrow_customize_price`, `buy_package_price`, `buy_customize_price`, `return_days`, `sort_order`, `created_at`) VALUES
(3, 55, 'Long Sleve', '', 'http://localhost/GP/public/uploads/suppliers/21/service-management/attire-item/20260619054200-81b2bc18.jpg', 40000.00, 410000.00, 50000.00, 500000.00, 3, 0, '2026-06-19 03:43:09');

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
  `status` enum('draft','pending_supplier_response','pending_payment','payment_submitted','payment_verified','paid','suppliers_responding','confirmed','pending_final_payment','finalized','completed','cancelled','cancellation_requested') NOT NULL DEFAULT 'draft',
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
(49, 30, 3, 1473360.00, 294671.00, 'partial', 'confirmed', NULL, NULL, NULL, '2026-06-18 11:10:23'),
(50, 30, 3, 76860.00, 0.00, 'unpaid', 'cancellation_requested', NULL, NULL, NULL, '2026-06-18 14:36:16');

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
  `slot_id` bigint(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `booking_type` enum('fullday','slot','flexible') DEFAULT NULL,
  `package_booking_item_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_items`
--

INSERT INTO `booking_items` (`id`, `booking_id`, `item_type`, `source`, `item_id`, `booking_date`, `price`, `item_name`, `supplier_name`, `category_name`, `thumbnail_url`, `status`, `venue_room_id`, `slot_id`, `start_time`, `end_time`, `booking_type`, `package_booking_item_id`) VALUES
(99, 48, 'service', 'custom', 42, '2026-09-24 06:00:00', 600000.00, NULL, NULL, NULL, NULL, 'accepted', 20, NULL, '06:00:00', '17:00:00', 'slot', NULL),
(100, 49, 'package', 'package', 19, NULL, 1473360.00, NULL, NULL, NULL, NULL, 'accepted', NULL, NULL, NULL, NULL, 'fullday', NULL),
(101, 50, 'package', 'package', 20, NULL, 76860.00, 'Standard Wedding Package', 'Golden Promise', NULL, 'http://localhost/GP/public/uploads/admin/packages/20260618152115-7d249ee0.png', 'pending', NULL, NULL, NULL, NULL, 'fullday', NULL);

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
(88, 50, 'payment_submitted', 'cancellation_requested', NULL, 'Cancellation requested: ငါတို့ မဂ်လာဆောင်မယ့်ရက် ပြောင်းသွားလို့ပါ', '2026-06-18 15:15:29');

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
(44, 48, 21, 'confirmed', '2026-06-18 09:33:50', NULL, 'unpaid', '2026-06-18 09:32:46', '2026-06-18 09:33:50'),
(45, 49, 20, 'confirmed', '2026-06-18 11:11:38', NULL, 'unpaid', '2026-06-18 11:10:23', '2026-06-18 11:11:38'),
(46, 49, 21, 'confirmed', '2026-06-18 11:11:38', NULL, 'unpaid', '2026-06-18 11:10:23', '2026-06-18 11:11:38'),
(48, 50, 20, 'pending', NULL, NULL, 'unpaid', '2026-06-18 14:36:16', '2026-06-18 14:36:16'),
(49, 50, 21, 'pending', NULL, NULL, 'unpaid', '2026-06-18 14:36:16', '2026-06-18 14:36:16');

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
(11, 49, 'VCH-PKG-D262B5AD', NULL, 20, 'Standard Wedding Package', 'Service', NULL, NULL, NULL, 'No 39. Hnin Si Street', 1473360.00, 'active', '2026-06-18 11:11:38');

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
(3, 30, '2026-06-18 09:56:57');

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
  `package_cart_item_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `user_id`, `item_type`, `item_id`, `selected_date`, `price`, `source`, `slot_id`, `start_time`, `end_time`, `venue_room_id`, `package_cart_item_id`) VALUES
(75, 3, 30, 'service', 47, '2026-07-03', 1000000.00, 'custom', NULL, '09:00:00', '17:00:00', NULL, NULL);

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
(7, 46, 'Ballon Arh', 1000000.00, NULL, NULL, 'http://localhost/GP/public/uploads/suppliers/20/service-management/decoration-style/20260618123229-ae63d4c6.png', 0, '2026-06-18 10:37:12'),
(8, 46, 'Premium', 750000.00, NULL, NULL, 'http://localhost/GP/public/uploads/suppliers/20/service-management/decoration-style/20260618123712-769732da.png', 1, '2026-06-18 10:37:12'),
(11, 48, 'Flower Deco', 2100000.00, 2100000.00, 2100000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/decoration-style/20260618210222-a1b9542f.png', 0, '2026-06-18 19:15:57'),
(12, 48, 'Golden Deco', 1800000.00, 1800000.00, 1800000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/decoration-style/20260618211557-3e2f52ad.png', 1, '2026-06-18 19:15:57'),
(13, 43, 'Ballon Arch', 3400000.00, 3400000.00, 3400000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/decoration-style/20260619054808-272a7a46.png', 0, '2026-06-19 03:48:08');

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
(71, 48, 99, '2026-09-24', '06:00:00', '17:00:00', 300, NULL, '35, Taw Win Road, Dagon Township, Yangon', NULL, NULL, 'HsuHive', '09123456789', '', NULL, '2026-06-18 09:32:46'),
(72, 49, 100, '2026-09-16', '09:00:00', '17:00:00', 200, NULL, 'No 39. Hnin Si Street', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-18 11:10:23'),
(73, 50, 101, '2026-07-18', '09:00:00', '17:00:00', 220, NULL, 'အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', NULL, NULL, 'zaw moe', '09123456789', '', NULL, '2026-06-18 14:36:16');

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
(93, 27, 'Payment Verified', 'Your payment has been verified! Suppliers are now reviewing your booking.', 'payment', 'booking', 48, 0, '2026-06-18 09:35:07'),
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
(115, 29, 'Publish request sent', 'Your request to publish \"Aphrodite Wedding Planning & Decoration\" was sent to admin.', 'approval', 'service', 43, 0, '2026-06-19 03:48:09');

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
  `status` varchar(20) NOT NULL DEFAULT 'published',
  `replaces_package_id` bigint(20) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`package_id`, `name`, `category_id`, `slug`, `type`, `description`, `tagline`, `base_price`, `image_url`, `is_active`, `status`, `replaces_package_id`, `sort_order`, `created_at`, `deleted_at`) VALUES
(19, 'Standard Wedding Package', 4, 'standard-wedding-package', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 1500000.00, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-18 09:55:29', '2026-06-19 02:52:44'),
(20, 'Standard Wedding Package', 4, 'standard-wedding-package-2', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 70000.00, 'http://localhost/GP/public/uploads/admin/packages/20260618152115-7d249ee0.png', 1, 'published', NULL, 0, '2026-06-18 10:30:53', NULL),
(23, 'Standard Wedding Package', 4, 'standard-wedding-package-3', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 4700000.00, 'http://localhost/GP/public/uploads/admin/packages/20260618115529-0e427d26.jpg', 1, 'published', NULL, 0, '2026-06-18 19:16:38', NULL),
(24, 'Standard Wedding Package', 4, 'standard-wedding-package-2-draft-1781838758', 'curated', 'ရိုးရှင်းလှပပြီး အမှတ်တရပြည့်ဝသော မင်္ဂလာပွဲတစ်ခုကို သင့်တင့်သော Budget ဖြင့် ကျင်းပလိုသော စုံတွဲများအတွက် အထူးသင့်လျော်သော Package ဖြစ်ပါသည်။ မင်္ဂလာပွဲအတွက် လိုအပ်သော အခြေခံဝန်ဆောင်မှုများကို Professional အဖွဲ့မှ စနစ်တကျ စီစဉ်ဆောင်ရွက်ပေးကာ သင့်၏ အရေးကြီးဆုံးနေ့ရက်ကို စိတ်အေးချမ်းသာစွာ ဖြတ်သန်းနိုင်စေရန် အကောင်းဆုံး ပံ့ပိုးပေးပါသည်။', 'Every detail, every moment, perfectly planned', 18080000.00, 'http://localhost/GP/public/uploads/admin/packages/20260618152115-7d249ee0.png', 0, 'draft', 20, 0, '2026-06-19 03:12:38', NULL);

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
  `default_supplier_id` bigint(20) DEFAULT NULL,
  `default_price` decimal(10,2) DEFAULT NULL,
  `customize_price` decimal(10,2) DEFAULT NULL,
  `quantity_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_items`
--

INSERT INTO `package_items` (`id`, `package_id`, `category_id`, `service_id`, `venue_room_id`, `attire_item_id`, `decoration_style_id`, `default_supplier_id`, `default_price`, `customize_price`, `quantity_type`, `quantity`, `deleted_at`) VALUES
(65, 20, 6, 42, 21, NULL, NULL, 21, 70000.00, NULL, 'fixed', 1, NULL),
(67, 19, 2, 47, NULL, NULL, NULL, 20, 750000.00, 1000000.00, 'guests', 2, NULL),
(71, 23, 2, 47, NULL, NULL, NULL, 20, 750000.00, 1000000.00, 'guests', 2, NULL),
(73, 23, 12, 48, NULL, NULL, 11, 20, 2100000.00, NULL, 'guests', 1, NULL),
(74, 23, 6, 49, 22, NULL, NULL, 20, 900000.00, 910000.00, 'fixed', 1, NULL),
(75, 23, 5, 50, NULL, NULL, NULL, 20, 200000.00, 2100000.00, 'guests', 1, NULL),
(77, 24, 12, 48, NULL, NULL, 12, 20, 1800000.00, 2100000.00, 'guests', 10, NULL),
(78, 24, 2, 55, NULL, 3, NULL, 21, 40000.00, 500000.00, 'guests', 2, NULL);

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
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `payment_slip_path` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `supplier_id`, `amount`, `platform_fee`, `supplier_amount`, `escrow_status`, `type`, `method`, `bank_name`, `account_name`, `mobile_number`, `paid_amount`, `paid_at`, `status`, `transaction_ref`, `payment_slip_path`, `verified_by`, `verified_at`, `verified_note`, `created_at`) VALUES
(47, 48, NULL, 120000.00, NULL, NULL, 'held', 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 120000.00, '2026-06-18 11:34:51', 'success', 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260618113451-2fe925b3.jpg', 1, '2026-06-18 09:35:07', '', '2026-06-18 09:34:51'),
(48, 49, NULL, 294671.00, NULL, NULL, 'held', 'deposit', 'AYA Pay', 'AYA Pay', 'Ko Kyaw Zin', '09123456789', 294671.00, '2026-06-18 13:10:53', 'success', 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260618131053-55929485.jpg', 1, '2026-06-18 11:11:38', '', '2026-06-18 11:10:53'),
(49, 50, NULL, 15372.00, NULL, NULL, 'held', 'deposit', 'AYA Pay', 'AYA Pay', 'U Kyaw Kyaw', '09123456789', 15372.00, '2026-06-18 16:37:10', 'pending', 'transction-id-123456789', 'public/uploads/payment-slips/2026/06/slip-20260618163710-6140d9cb.jpg', NULL, NULL, NULL, '2026-06-18 14:37:10');

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
  `min_lead_days` int(11) DEFAULT 0 COMMENT 'Minimum days in advance customer must book (0 = same day allowed)',
  `default_start_time` time DEFAULT NULL,
  `default_end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `supplier_id`, `category_id`, `name`, `description`, `price`, `price_min`, `price_max`, `thumbnail_url`, `is_active`, `booking_type`, `duration_minutes`, `pricing_unit`, `buffer_minutes`, `max_concurrent`, `created_at`, `min_lead_days`, `default_start_time`, `default_end_time`) VALUES
(42, 21, 6, 'Governor\'s Residence', 'ရန်ကုန်မြိုမှာ ကိုလိုနီခေတ်က တည်ရှိခဲ့တဲ့ အဆောက်အအုံများစွာအနက် Governor’s Residence ကို ၁၉၂၀ ပြည့်လွန် နှစ်များက တန်ဖိုးကြီး မြန်မာ့ ကျွန်းသစ်၊ မြန်မာ့ လက်မှုပညာတွေနဲ့ ပေါင်းစပ် တည်ဆောက်ခဲ့တဲ့ အဆောက်အအုံတစ်ခုဖြစ်သည်။\n\nသံရုံးများတည်ရှိရာ ရန်ကုန်မြိုရဲ့ အေးဆေးတိတ်ဆိတ်တဲ့ နေရာ၊ သမိုင်းဝင်အဆောက်အအုံများရဲ့ အလှတရားနှင့် ခေတ်မှီဇိမ်ခံပစ္စည်းများနဲ့ ပြန်လည်ပေါင်းစပ် တည်ဆောက်ထားတာ ဖြစ်ပါတယ်။ ကျယ်ဝန်းတဲ့ အိပ်ခန်းဆောင်များတွင် သစ်သား၊ ပိုးသားချည်မျှင်များနဲ့ အလှဆင်ထားတဲ့အပြင် စိမ်းလန်းစိုပြေပြီး ဝေဆာပွင့်လန်းနေတဲ့ ဥယျာဉ်ရဲ့ အလှကိုလည်း မြင်တွေ့ရဦးမှာ ဖြစ်ပါတယ်။ ဒါ့ပြင် ရေကူးကန်ကိုလည်း စပိန်မှ တင်သွင်းထားတဲ့ ကြွေပြားများနဲ့ ပြန်လည် အလှဆင် တည်ဆောက် ထားပါသေးတယ်။\n\nGovernor’s Residence ရဲ့ The Monkey Bar၊ The State Room နှင့် The Peacock Portico တိုမှာလည်း ခမ်းနားတဲ့ ညစာစားပွဲများကို တည်ခင်းရောင်းချပေးတာဖြစ်ပြီး Outlets တစ်ခုချင်းစီတိုင်းမှ မတူကွဲပြားတဲ့ ပရိဘောဂများရဲ့ အလှတွေကလည်း လာရောက်တဲ့ ဧည့်သည်တိုင်းအတွက် အမှတ်တရ ဖြစ်စေမှာပဲ ဖြစ်ပါတယ်။\nကိုလိုခေတ် မြန်မာ့ လက်မှုပညာရဲ့ ခန့်ညားထည်ဝါမှုအပြင် ရှေးခေတ် အငွေ့အသက်တွေကို အပြည့်အဝ ခံစားနိုင်ဖို Governor’s Residence သို ဖိတ်ခေါ်လိုက်ပါတယ်။', 70000.00, 70000.00, 600000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260618102917-41aebacc.jpg', 1, 'slot', 720, 'per_session', 0, 300, '2026-06-18 08:29:17', 90, NULL, NULL),
(43, 21, 12, 'Aphrodite Wedding Planning &amp; Decoration', 'မိမိတိုရဲ့ အလှပဆုံး မင်္ဂလာအချိန်လေးကို လစ်ဟာမှုတွေ၊ လိုအပ်ချက်တွေမရှိဘဲ အချိုမြိန်ဆုံးအခိုက်အတန့်တွေကိုသာ အမှတ်တရဖြစ် နေစေဖို ကျွမ်းကျင်တဲ့ Wedding Professional တွေနဲ့အတူ မိမိတိုရဲ့ မင်္ဂလာနေ့ရက်လေးကို အပြည့်အစုံဆုံး ပုံဖော်လိုက်ပါ။\nမိမိတိုရဲ့ တစ်သက်မှတစ်ခါ ရင်အခုန်ရဆုံးနဲ့ အလှပဆုံး နေ့ရက် လေးအတွက် အကောင်းဆုံး Service အကောင်းဆုံး Quality တွေအပြင် ကျွမ်းကျင် Professional Wedding Planner တွေနဲ့ မိမိတိုရဲ့ ပွဲ ကို စိတ်အေးရချင်တယ်ဆိုရင်တော့ Aphrodite Wedding Planning and Decoration ကို အခုပဲရွေးချယ်လိုက်ပါ။', 3400000.00, 3400000.00, 3400000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260618104902-604f0759.jpg', 0, 'fullday', 60, 'per_session', 0, 1, '2026-06-18 08:49:02', 3, NULL, NULL),
(44, 20, 8, 'Elegance Star', 'မင်္ဂလာပါရှင့် 𝗘𝗹𝗲𝗴𝗮𝗻𝘁 𝗦𝘁𝗮𝗿 𝗪𝗲𝗱𝗱𝗶𝗻𝗴 𝗦𝘁𝗮𝘁𝗶𝗼𝗻𝗲𝗿𝘆 𝗦𝗲𝗿𝘃𝗶𝗰𝗲 မှ ကြိုဆိုပါတယ်။\nMarriage Certificates ၊လက်ထပ်စာချုပ် ၊\nInvitation cards ဖိတ်စာ ၊\nWedding Gift Box ငွေသား ၊  လက်ဖွဲ့ပုံး ၊\nမင်္ဂလာပြန်ကမ်း ၊\nWedding Guest Book ၊\nVows Books ၊\nSigning pens၊ \nCanvas Fingerprint Tree ၊(Customization avaliable) ၊\nAcrylic Photobooth &amp; Welcomeboard Services များကို Customized အပ်နှံနိုင်ပါတယ်။ \nOpening hours 9:00 AM - 5:30 PM', 3200.00, 3200.00, 4000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618112551-21cc8a57.jpg', 1, 'slot', 720, 'per_session', 0, 1, '2026-06-18 09:25:51', 4, NULL, NULL),
(45, 20, 2, 'Dear Brides', 'ဝတ်စုံနှင့် ဝန်ဆောင်မှုများစုံလင်သော ဝတ်စုံဒီဇိုင်းများ: Wedding Gowns, Mermaid Dresses, Evening Dresses နဲ့ Pre-Wedding အတွက် ဝတ်စုံလှလှလေးများကို စိတ်ကြိုက်ငှားရမ်းနိုင်ပါတယ်။\n\nနောက်ဆုံးပေါ် ဒီဇိုင်းသစ်များ: နိုင်ငံခြား Wedding Dress Industry ရှိ စက်ရုံကြီးများမှ နောက်ဆုံးပေါ် Dress များကို မိမိကိုယ်တိုင်း၊ မိမိစိတ်ကြိုက် ရွေးချယ်ပြီး အငှား/အဝယ် မှာယူနိုင်ပါတယ်။\n\nအမှတ်တရ သိမ်းဆည်းလိုသူများအတွက်: အသစ်စက်စက် Dress များကို Studio မှာ ကိုယ်တိုင်ဝတ်ကြည့်ပြီး ဝယ်ယူနိုင်သလို၊ Bridal Veil (သတို့သမီးခေါင်းခြုံပုဝါ) များကိုလည်း မိမိစိတ်ကြိုက် Customized မှာယူနိုင်ပါတယ်ရှင်။\n\n🌸 မြန်မာ့ရိုးရာ ဝတ်စုံဝန်ဆောင်မှုခေတ်မီဝတ်စုံများသာမက ရိုးရာထိုင်မသိမ်း၊ တောင်ရှည်ဝတ်စုံများကိုလည်း အငှား/အရောင်းအပြင် အသစ်ချုပ်အငှား ဝန်ဆောင်မှုပါ ရရှိနိုင်ပါတယ်။ (အသားအရောင်နှင့် ကိုယ်လုံးအချိုးအစားပေါ်မူတည်၍ ဒီဇိုင်းသီးသန့် ဆွဲပေးပါတယ်ရှင်)\n\n💐 ပြီးပြည့်စုံသော Wedding Packagesဝတ်စုံများအပြင် Floral Decoration၊ လက်ကိုင်ပန်း၊ Hotel &amp; Makeup Booking နှင့် မင်္ဂလာကားအလှဆင်ခြင်းအထိ အစုံအလင် ဝန်ဆောင်မှုပေးနေတာကြောင့် Dear Brides ကို ယုံကြည်စွာ လှမ်းလာခဲ့ဖို့ ဖိတ်ခေါ်လိုက်ပါတယ်ရှင်။', 800000.00, 800000.00, 1200000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618120107-9e4d4636.jpg', 1, 'fullday', NULL, 'per_session', 0, 1, '2026-06-18 10:01:07', 90, NULL, NULL),
(46, 20, 12, 'Aphrodite Wedding Planning &amp; Decoration', 'မိမိတိုရဲ့ အလှပဆုံး မင်္ဂလာအချိန်လေးကို လစ်ဟာမှုတွေ၊ လိုအပ်ချက်တွေမရှိဘဲ အချိုမြိန်ဆုံးအခိုက်အတန့်တွေကိုသာ အမှတ်တရဖြစ် နေစေဖို ကျွမ်းကျင်တဲ့ Wedding Professional တွေနဲ့အတူ မိမိတိုရဲ့ မင်္ဂလာနေ့ရက်လေးကို အပြည့်အစုံဆုံး ပုံဖော်လိုက်ပါ။\nမိမိတိုရဲ့ တစ်သက်မှတစ်ခါ ရင်အခုန်ရဆုံးနဲ့ အလှပဆုံး နေ့ရက် လေးအတွက် အကောင်းဆုံး Service အကောင်းဆုံး Quality တွေအပြင် ကျွမ်းကျင် Professional Wedding Planner တွေနဲ့ မိမိတိုရဲ့ ပွဲ ကို စိတ်အေးရချင်တယ်ဆိုရင်တော့ Aphrodite Wedding Planning and Decoration ကို အခုပဲရွေးချယ်လိုက်ပါ။', 750000.00, 750000.00, 1000000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618123229-3fd3faea.jpg', 0, 'fullday', 60, 'per_session', 0, 1, '2026-06-18 10:32:29', 3, NULL, NULL),
(47, 20, 2, 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ', 'ကျွန်မတို့ဆီမှာ မင်္ဂလာမောင်နှံအတွက် ထိုင်မသိမ်း၊ ဆွမ်းကပ်/လက်မှတ်ထိုးဝတ်စုံ၊ တိုက်ပုံ၊ တောင်ရှည် တို့အပြင် မိဘဝတ်စုံများကိုပါ ထိုင်းပိုးချိတ်၊ စီးကရက်ပိုးချိတ်၊ ဘရိုကိတ်ပိုးချိတ် စသည့် ပိုးထည်အမျိုးမျိုးဖြင့် စုံလင်စွာ ရရှိနိုင်ပါတယ်။ ထို့အပြင် အရံ၊ ပန်းကြဲ နှင့် ဗန်းကိုင်များအတွက်လည်း အသင့်ငှားရမ်းနိုင်သလို စိတ်ကြိုက်ဒီဇိုင်းများလည်း ဖန်တီးချုပ်လုပ်ပေးပါတယ်ရှင်။\n\nအသစ်ချုပ်အငှားနှင့် စိတ်ကြိုက်ဖန်တီးမှုဝတ်စုံများကို အငှားရော အရောင်းပါ ရရှိနိုင်ပြီး အသစ်ချုပ်အငှား (Custom-made Rental) ဝန်ဆောင်မှုလည်း ရှိပါတယ်ရှင်။ သတို့သမီးရဲ့ အသားအရောင်၊ ခန္ဓာကိုယ်အချိုးအစားတို့နှင့် လိုက်ဖက်မည့် အရောင်နှင့် စီးကွင့်ဒီဇိုင်းများကို သီးသန့်ဆွဲပေးတာကြောင့် ပွဲနေ့မှာ အထူးခြားဆုံး ဖြစ်နေစေမှာပါ။ (အသစ်ချုပ်အငှားအတွက် ၃ လမှ ၆ လကြိုတင် အပ်နှံပေးရန် လိုအပ်ပါတယ်ရှင်)\n\n💰 ဈေးနှုန်းနှင့် အထူးဝန်ဆောင်မှုများဈေးနှုန်း: ထိုင်မသိမ်း (မောင်နှံစုံ) အငှားကို ၃ သိန်းခွဲမှ သိန်း ၂၀ ဝန်းကျင် အထိလည်းကောင်း၊ ဆွမ်းကပ်ဝတ်စုံ (မောင်နှံစုံ) ကို ၂ သိန်းဝန်းကျင် မှ စတင်၍လည်းကောင်း စိတ်ကြိုက် ရွေးချယ်နိုင်ပါတယ်။\n\nFitting: အငှားထည်များကိုလည်း သတို့သား/သတို့သမီး ကိုယ်တိုင်းယူကာ Fitting ကွက်တိ ဖြစ်အောင် ပြင်ဆင်ပေးပြီး၊ ပွဲမတိုင်ခင် ၄ ရက်အလိုမှာ Final Fitting ပြန်လည် စစ်ဆေးပေးပါတယ်။\n\nPackage ဝန်ဆောင်မှု: Package ယူထားသော ရန်ကုန်နှင့် မန္တလေးမြို့တွင်း ပွဲများအတွက် Charges ပေးစရာမလိုဘဲ လူကိုယ်တိုင် လိုက်လံဝတ်ဆင်ပေးပါတယ်ရှင်။နယ်ဝေးဝန်ဆောင်မှု: နယ်မှ ငှားရမ်းသူများအတွက် နယ်ကြေးထပ်ပေးစရာမလိုဘဲ ၄ ရက် အချိန်ပေးထားပါတယ်ရှင်။', 750000.00, 750000.00, 1000000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618152953-60c74c06.jpg', 1, 'slot', 480, 'per_session', 0, 3, '2026-06-18 13:29:53', 7, NULL, NULL),
(48, 20, 12, 'H&amp;H Floral and Wedding Service', 'H&amp;H floral မှာဈေးနှုန်း ချိုချိုသာသာလေးတွေနဲ့\nအလှဆုံးတွေပြင်ဆင်ပေးမှာပါနော်\nလိုချင်တဲ့ရက်လေးရဖို booking လေးတွေ\nကြိုယူထားဖိုလိုပါမယ်ရှင်', 1800000.00, 1800000.00, 2100000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618210245-d5b57c03.jpg', 1, 'slot', 240, 'per_session', 0, 1, '2026-06-18 19:02:22', 3, NULL, NULL),
(49, 20, 6, 'Zephyr Sein Lann So pyay', 'Zephyr (Sein Lann So Pyay Garden)ကရန်ကုန်မြို့အတွင်းတည်ရှိတဲ့အေးချမ်းပြီးသဘာဝပတ်ဝန်းကျင်နဲ့ကိုက်ညီတဲ့ fine dining &amp; event venue တစ်ခုဖြစ်ပါတယ်။Sein Lann So Pyay Gardenအနားမှာရှိလို့ မိသားစုစားသောက်မှု၊ မင်္ဂလာပွဲ၊ အခမ်းအနားများအတွက်လူကြိုက်များပါတယ်။သဘာဝအလှနဲ့ လှပနဲ့background ကြောင့်pre-weeding/ event-photo ရိုက်ရအဆင်ပြေစေပါတယ်။outdoor garden weeding နဲ့ အေးချမ်းတဲ့weeding လုပ်ချင်သူများ Decoration+ food+ Serviceကိုတစ်နေရာထဲမှာpackageလိုချင်သူများအတွက်အဆင်ပြေပြီး ရွေးချယ်ဖို့သင့်တော်တဲ့နေရာတစ်ခုဖြစ်ပါတယ်။', 900000.00, 900000.00, 910000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260618212654-323d369a.jpg', 1, 'slot', 480, 'per_session', 0, 1, '2026-06-18 19:26:54', 8, NULL, NULL),
(50, 20, 5, 'H &amp; H Wedding Studio', 'Capturing your the most meaningful moments with elegance &amp; style             H&amp;H Photo Studio ကို ယုံကြည်ပြီးအရေးကြီးတဲ့ အမှတ်တရနေ့ရက်တွေကို အပ်နှံပေးတဲ့ client တိုင်းကို အထူးကျေးဇူးတင်ရှိပါတယ် 💛ရိုက်ကူးမှုတိုင်းမှာcomfortable experience, clear communication, pose guidance နဲ့quality result ကို အရေးထားပြီး detail ကျကျ ဂရုစိုက်ဆောင်ရွက်ပေးနေပါတယ် ✨', 200000.00, 200000.00, 2100000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260619040841-541df810.jpg', 1, 'slot', 480, 'per_session', 0, 3, '2026-06-19 02:08:41', 7, NULL, NULL),
(54, 20, 6, 'Western Park Ruby – People’s Park', 'မြို့အလယ်မှာရှိပေမဲ့ပန်းခြံဖြစ်လို့ ရှုပ်ထွေးမှုမရှိ၊မြက်ခင်းပြင်ကျယ် သဘာဝစိမ်းလန်းမှူများ၊နေရာကျယ်ဝန်းလို့ weeding, event venue အဖြစ်လူကြိုက်များပြီးဧည့်သည်အရေအတွက်များတဲ့eventများတွက်အဆင်   ပြေအောင်ဆောင်ရွက်ပေးနေပြီဖြစ်ပါတယ်။', 500000.00, 500000.00, 500000.00, 'http://localhost/GP/public/uploads/suppliers/20/service-management/service/20260619051941-7b6ccd02.jpg', 0, 'fullday', 60, 'per_session', 0, 300, '2026-06-19 03:18:58', 3, NULL, NULL),
(55, 21, 2, 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN', 'ဝတ်စုံနှင့် ဝန်ဆောင်မှုများစုံလင်သော ဝတ်စုံဒီဇိုင်းများ: Wedding Gowns, Mermaid Dresses, Evening Dresses နဲ့ Pre-Wedding အတွက် ဝတ်စုံလှလှလေးများကို စိတ်ကြိုက်ငှားရမ်းနိုင်ပါတယ်။\n\nနောက်ဆုံးပေါ် ဒီဇိုင်းသစ်များ: နိုင်ငံခြား Wedding Dress Industry ရှိ စက်ရုံကြီးများမှ နောက်ဆုံးပေါ် Dress များကို မိမိကိုယ်တိုင်း၊ မိမိစိတ်ကြိုက် ရွေးချယ်ပြီး အငှား/အဝယ် မှာယူနိုင်ပါတယ်။\n\nအမှတ်တရ သိမ်းဆည်းလိုသူများအတွက်: အသစ်စက်စက် Dress များကို Studio မှာ ကိုယ်တိုင်ဝတ်ကြည့်ပြီး ဝယ်ယူနိုင်သလို၊ Bridal Veil (သတို့သမီးခေါင်းခြုံပုဝါ) များကိုလည်း မိမိစိတ်ကြိုက် Customized မှာယူနိုင်ပါတယ်ရှင်။\n\n🌸 မြန်မာ့ရိုးရာ ဝတ်စုံဝန်ဆောင်မှုခေတ်မီဝတ်စုံများသာမက ရိုးရာထိုင်မသိမ်း၊ တောင်ရှည်ဝတ်စုံများကိုလည်း အငှား/အရောင်းအပြင် အသစ်ချုပ်အငှား ဝန်ဆောင်မှုပါ ရရှိနိုင်ပါတယ်။ (အသားအရောင်နှင့် ကိုယ်လုံးအချိုးအစားပေါ်မူတည်၍ ဒီဇိုင်းသီးသန့် ဆွဲပေးပါတယ်ရှင်)\n\n💐 ပြီးပြည့်စုံသော Wedding Packagesဝတ်စုံများအပြင် Floral Decoration၊ လက်ကိုင်ပန်း၊ Hotel &amp; Makeup Booking နှင့် မင်္ဂလာကားအလှဆင်ခြင်းအထိ အစုံအလင် ဝန်ဆောင်မှုပေးနေတာကြောင့် Dear Brides ကို ယုံကြည်စွာ လှမ်းလာခဲ့ဖို့ ဖိတ်ခေါ်လိုက်ပါတယ်ရှင်။', 40000.00, 40000.00, 500000.00, 'http://localhost/GP/public/uploads/suppliers/21/service-management/service/20260619054309-45b53c74.jpg', 1, 'slot', 60, 'per_session', 0, 1, '2026-06-19 03:42:00', 3, NULL, NULL);

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
(72, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103236-1661e8a3.jpg', 'image'),
(73, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103239-fb232b73.jpg', 'image'),
(74, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103243-cf567784.jpg', 'image'),
(75, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103247-23bbfb51.jpg', 'image'),
(76, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103251-c8b843d7.jpg', 'image'),
(77, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103259-e7cfcd2b.jpg', 'image'),
(78, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103303-62e2f364.jpg', 'image'),
(79, 42, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260618103307-951e5978.jpg', 'image'),
(80, 44, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618112626-b5fa1681.jpg', 'image'),
(81, 44, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618112629-0f06d57e.jpg', 'image'),
(82, 44, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618112634-d5d458b6.jpg', 'image'),
(83, 44, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618112639-08d32d46.jpg', 'image'),
(85, 45, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618120120-c4be27c5.jpg', 'image'),
(86, 45, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618120123-72685357.jpg', 'image'),
(87, 45, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618120127-985feb11.jpg', 'image'),
(88, 46, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618123316-51c01436.png', 'image'),
(89, 46, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618123320-3b184584.png', 'image'),
(90, 46, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618123324-32b56870.png', 'image'),
(91, 46, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618123328-6ffeb74a.png', 'image'),
(92, 46, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618123332-023d315f.png', 'image'),
(95, 47, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618153527-97b60c51.jpg', 'image'),
(96, 47, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618153540-64fe93cc.jpg', 'image'),
(97, 47, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618153544-9bd4b5f7.jpg', 'image'),
(99, 47, 'http://localhost/GP/public/uploads/suppliers/20/service-management/media/20260618153558-73496044.jpg', 'image'),
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
(125, 43, 'http://localhost/GP/public/uploads/suppliers/21/service-management/media/20260619054754-91f4eb68.png', 'image');

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
(4, 45, 800000.00, 830000.00, 800000.00, 1, 1000000.00, 1200000.00, 1000000.00, '2026-06-18 10:01:07'),
(5, 47, 750000.00, 800000.00, 750000.00, 3, 989999.85, 1000000.00, 989999.85, '2026-06-18 13:29:53'),
(9, 55, 400000.00, 410000.00, 400000.00, 1, 500000.00, 500000.00, 500000.00, '2026-06-19 03:42:00');

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
(372, 44, 1, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(373, 44, 2, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(374, 44, 3, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(375, 44, 4, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(376, 44, 5, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(377, 44, 6, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(378, 44, 7, '09:00:00', '17:00:00', 1, '2026-06-18 09:27:12'),
(393, 47, 1, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(394, 47, 2, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(395, 47, 3, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(396, 47, 4, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(397, 47, 5, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(398, 47, 6, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(399, 47, 7, '09:00:00', '17:00:00', 1, '2026-06-18 18:10:37'),
(421, 48, 1, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(422, 48, 2, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(423, 48, 3, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(424, 48, 4, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(425, 48, 5, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(426, 48, 6, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(427, 48, 7, '09:00:00', '17:00:00', 1, '2026-06-18 19:16:14'),
(435, 49, 1, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(436, 49, 2, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(437, 49, 3, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(438, 49, 4, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(439, 49, 5, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(440, 49, 6, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(441, 49, 7, '09:00:00', '17:00:00', 1, '2026-06-18 19:27:46'),
(442, 50, 1, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(443, 50, 2, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(444, 50, 3, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(445, 50, 4, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(446, 50, 5, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(447, 50, 6, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(448, 50, 7, '09:00:00', '17:00:00', 1, '2026-06-19 02:09:30'),
(449, 55, 1, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43'),
(450, 55, 2, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43'),
(451, 55, 3, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43'),
(452, 55, 4, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43'),
(453, 55, 5, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43'),
(454, 55, 6, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43'),
(455, 55, 7, '09:00:00', '17:00:00', 1, '2026-06-19 03:42:43');

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

--
-- Dumping data for table `service_time_slots`
--

INSERT INTO `service_time_slots` (`id`, `service_id`, `date`, `start_time`, `end_time`, `confirmed_count`, `max_concurrent`, `status`, `created_at`) VALUES
(3, 44, '2026-09-16', '09:00:00', '17:00:00', 1, 1, 'full', '2026-06-18 11:10:23'),
(4, 42, '2026-09-16', '09:00:00', '17:00:00', 1, 1, 'full', '2026-06-18 11:10:23'),
(5, 44, '2026-07-18', '09:00:00', '17:00:00', 1, 1, 'full', '2026-06-18 14:36:16'),
(6, 42, '2026-07-18', '09:00:00', '17:00:00', 1, 1, 'full', '2026-06-18 14:36:16');

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
  `warning_level` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=none, 1=warning, 2=final_warning',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `user_id`, `shop_name`, `description`, `status`, `verified_by`, `approved_by`, `verify_url`, `agreement_accepted`, `agreement_accepted_at`, `agreement_version`, `payment_status`, `is_available`, `warning_level`, `admin_note`, `created_at`, `deleted_at`) VALUES
(20, 24, 'JV', 'we sell dress', 'verified', NULL, 1, 'https://www.facebook.com/jv230', 1, '2026-06-10 02:08:51', 'supplier-v1', 'paid', 1, 0, NULL, '2026-06-10 06:38:51', NULL),
(21, 29, 'Wyndham Grand Yangon Hotel', 'ဝင်ဒမ်ဂရန်းရန်ကုန်ဟိုတယ်ရဲ့ Wedding Tea Package များကို US$ 7 တောင် လျော့ပေးမယ့်အပြင် မိမိရွေး ချယ်တဲ့ Package ပေါ် မူတည်၍ Walkway နဲ့ LED အသုံးပြုခွင့်များပါ ရရှိနိုင်မှာပဲဖြစ်ပါတယ်...\r\n\r\nဒါ့အပြင် Wedding Dinner Packages ဝယ်ယူသူတိုင်းအတွက် အခမဲ့ Complimentary Table များ (သိုမဟုတ်) Walkway အသုံးပြုခွင့် (သိုမဟုတ်)  LED အသုံးပြုခွင့်ဆိုပြီး မိမိ နှစ်သက်ရာ အကျိုးခံစားခွင့်ကို ရွေးချယ်ရယူနိုင်မှာပါ...\r\n\r\n သင့်စိတ်ကူးထဲကအတိုင်း ကြီးကျယ်ခမ်းနားလှပတဲ့ Wedding ပွဲကြီးကို စိတ်တိုင်းကျဖန်တီးနိုင်ဖိုအတွက် ဝင်ဒမ်ဂရန်းရန်ကုန်ဟိုတယ်ရဲ့ Wedding Venue Area များက အသင့်တော်ဆုံးရွေးချယ်မှုဖြစ်စေမှာပါ...Wedding Period ကိုလည်း ၂၀၂၇ ခုနှစ် နှစ်ကုန်အထိ ပေးထားတာမို တအားတန်တဲ့ ဒီအခွင့်အရေးကို လက်မလွတ်ရလေအောင် အမိအရဖမ်းဆုပ်လိုက်တော့နော်...🤍', 'verified', 1, 1, 'htpps://www.wyndhamgrandyangon.com', 1, '2026-06-11 00:31:15', 'supplier-v1', 'paid', 1, 0, NULL, '2026-06-11 05:01:15', NULL);

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
(88, 1, 'verifyOTP_success', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 04:18:39', '2026-06-17 04:18:39', NULL, '2026-06-17 04:18:39'),
(89, 29, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-18 05:07:54', '2026-06-18 05:07:54', '2026-06-18 05:07:54', '2026-06-18 05:07:54'),
(90, 27, 'logout', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-18 09:43:17', '2026-06-18 09:43:17', '2026-06-18 09:43:17', '2026-06-18 09:43:17');

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
(24, 'J V', 'mhsu537@gmail.com', '$2y$10$m23y02SGxPewmFgVlKP1uO8ZJL7vKzUNIwf8YL31VTz6BDcc4QWJm', '09771471462', 'ကန်တော်ကြီး ကရဝိတ်၊ မျှော်စင်ကျွန်းဝင်ပေါက်အနီး၊ မင်္ဂလာတောင်ညွန့်မြို့နယ်၊ ရန်ကုန်မြို့။ ', 'active', NULL, NULL, 3, 0, '2026-06-13 05:50:22', NULL, 0, '2026-06-10 06:38:38', NULL, '112808788643014027786', 'https://lh3.googleusercontent.com/a/ACg8ocIXClMfEn5duPuil8ov2K8LCsnUDcK7DYKGSo2DuULXo1tqaHi2=s96-c', NULL, '2026-06-18 18:10:19', '2026-06-18 18:10:19', NULL),
(27, 'HsuHive', 'hsuhive38@gmail.com', '$2y$10$yHn.drr2Pu2Qg0ICyiSwWOZ6.zeppA14QazMNS7qtypWYhVl215WG', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, '2026-06-11 12:40:57', 0, '2026-06-11 02:32:31', NULL, '106937788818804252855', 'https://lh3.googleusercontent.com/a/ACg8ocJSYHRoiZxk9x5f8qT8EPb8deKr6ae5wTdn7NyvRyuab_iEpg=s96-c', NULL, '2026-06-18 09:43:17', '2026-06-14 14:33:04', NULL),
(29, 'Saen', 'saenintiktok@gmail.com', '$2y$10$MmR6sJNtdIOP7v.4wbW.OeaeTdp4G5.G0.ZQ3bCW6oZRm0JjI4JXS', '09451777705', 'no.11, corner of Kan Yeik Thar Road &amp; U Aung Myat Road, Mingalar Taung township', 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-11 04:43:46', NULL, '113883451541620508706', 'https://lh3.googleusercontent.com/a/ACg8ocKa0OVagjb-Z034lNGR1feDM9cWYi9krO4byxaDck2Fzyjv1w=s96-c', NULL, '2026-06-19 02:20:43', '2026-06-19 02:20:43', NULL),
(30, 'zaw moe', '7zawzawmoe8@gmail.com', '$2y$10$hWc5wp2enmIoN5pBYfhpLuFt6W95k6W5Hw414AN4YAqBEorO6Hw2O', NULL, NULL, 'active', NULL, NULL, 0, 0, NULL, NULL, 0, '2026-06-18 09:44:03', NULL, '105962240867007474645', 'https://lh3.googleusercontent.com/a/ACg8ocJ3JrvFxn1cRzuotErkuS0lsXh9eb2rdG8kLIL3S3pQEJYCGg=s96-c', NULL, '2026-06-18 09:44:03', '2026-06-18 09:44:03', NULL);

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
(32, 30, 1, '2026-06-18 09:44:03');

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
(21, 20, 49, 'Zephyr Sein Lann So Pyay Garden', 'အမှတ်-(28) စိမ်းလန်းစိုပြေပန်းခြံ၊ အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။', 'Zephyr (Sein Lann So Pyay Garden)ကရန်ကုန်မြို့အတွင်းတည်ရှိတဲ့အေးချမ်းပြီးသဘာဝပတ်ဝန်းကျင်နဲ့ကိုက်ညီတဲ့ fine dining &amp; event venue တစ်ခုဖြစ်ပါတယ်။Sein Lann So Pyay Gardenအနားမှာရှိလို့ မိသားစုစားသောက်မှု၊ မင်္ဂလာပွဲ၊ အခမ်းအနားများအတွက်လူကြိုက်များပါတယ်။သဘာဝအလှနဲ့ လှပနဲ့background ကြောင့်pre-weeding/ event-photo ရိုက်ရအဆင်ပြေစေပါတယ်။outdoor garden weeding နဲ့ အေးချမ်းတဲ့weeding လုပ်ချင်သူများ Decoration+ food+ Serviceကိုတစ်နေရာထဲမှာpackageလိုချင်သူများအတွက်အဆင်ပြေပြီး ရွေးချယ်ဖို့သင့်တော်တဲ့နေရာတစ်ခုဖြစ်ပါတယ်။', '2026-06-18 19:26:54'),
(22, 20, 54, 'Western Park Ruby – People’s Park', 'ပြည်သူ့ရင်ပြင်ဝန်းအတွင်း၊ ဒဂုံမြို့နယ်၊ ရန်ကုန်မြို့။', 'မြို့အလယ်မှာရှိပေမဲ့ပန်းခြံဖြစ်လို့ ရှုပ်ထွေးမှုမရှိ၊မြက်ခင်းပြင်ကျယ် သဘာဝစိမ်းလန်းမှူများ၊နေရာကျယ်ဝန်းလို့ weeding, event venue အဖြစ်လူကြိုက်များပြီးဧည့်သည်အရေအတွက်များတဲ့eventများတွက်အဆင်   ပြေအောင်ဆောင်ရွက်ပေးနေပြီဖြစ်ပါတယ်။', '2026-06-19 03:18:58');

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
(23, 22, 'Grand Hall', 300, 500000.00, '2026-06-19 03:18:58', 4, 'http://localhost/GP/public/uploads/suppliers/20/service-management/hall/20260619051858-41a7dc9a.jpg');

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
(46, 22, NULL, '09:00:00', '17:00:00', 1),
(48, 23, NULL, '09:00:00', '17:00:00', 1);

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
  ADD KEY `cart_items_venue_room_id` (`venue_room_id`),
  ADD KEY `idx_cart_package_addon` (`package_cart_item_id`);

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
  ADD UNIQUE KEY `idx_supplier_id` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_lockout_logs`
--
ALTER TABLE `account_lockout_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attire_items`
--
ALTER TABLE `attire_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `booking_items`
--
ALTER TABLE `booking_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `booking_status_logs`
--
ALTER TABLE `booking_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `booking_suppliers`
--
ALTER TABLE `booking_suppliers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `booking_vouchers`
--
ALTER TABLE `booking_vouchers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `decoration_styles`
--
ALTER TABLE `decoration_styles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `event_details`
--
ALTER TABLE `event_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `package_items`
--
ALTER TABLE `package_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `service_availability`
--
ALTER TABLE `service_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `service_media`
--
ALTER TABLE `service_media`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `service_rental_pricing`
--
ALTER TABLE `service_rental_pricing`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `service_schedules`
--
ALTER TABLE `service_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=456;

--
-- AUTO_INCREMENT for table `service_time_slots`
--
ALTER TABLE `service_time_slots`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `venue_rooms`
--
ALTER TABLE `venue_rooms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `venue_room_availability`
--
ALTER TABLE `venue_room_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
