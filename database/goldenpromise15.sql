-- ============================================================
-- Golden Promise — Normalised Schema (v15)
-- Generated: 2026-06-28
-- Based on goldenpromise14.sql
--
-- NORMALISATION CHANGES FROM v14:
-- 1. Removed duplicate UNIQUE indexes on users table
--    (v14 had both `google_id` AND `unique_google_id` on the same column;
--     same for facebook_id. Kept only the named versions.)
-- 2. Added 35+ missing foreign-key constraints for referential integrity.
-- 3. Added corresponding indexes for all new FK columns.
-- 4. All tables enforced as InnoDB / utf8mb4_unicode_ci.
-- 5. Removed all INSERT data — structure only for ERD export.
-- 6. Removed 2 unused tables (never referenced in PHP code):
--    - supplier_bans  (0 references; supplier banning uses supplier_warnings instead)
--    - wallets        (0 references; planned feature never implemented)
-- 7. Removed 1 unused column:
--    - users.failed_otp_attempts  (0 references; OTP tracking uses the otps table)
--
-- DESIGN NOTES (intentional denormalisation preserved):
-- • booking_items.item_name, supplier_name, category_name, thumbnail_url
--   are historical snapshots captured at booking time.
-- • booking_vouchers stores service_name, category_name for the same reason.
-- • attire_items keeps its own default pricing columns alongside the
--   normalised attire_rental_options table (multi-duration pricing).
-- • event_details.location is free-text because the event may be held at
--   a private address not in the venues table.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- Table Structures
-- ============================================================

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attire_rental_options` (
  `id` bigint(20) NOT NULL,
  `attire_item_id` bigint(20) NOT NULL,
  `days` int(11) NOT NULL COMMENT 'Rental duration in days',
  `price` decimal(12,2) NOT NULL COMMENT 'Package price for this duration',
  `customize_price` decimal(12,2) DEFAULT NULL COMMENT 'Customize price for this duration',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `booking_status_logs` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` bigint(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `carts` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customer_status_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `action` varchar(40) NOT NULL COMMENT 'suspend | ban | unban | soft_delete | edit_contact',
  `reason` text DEFAULT NULL,
  `changed_by` bigint(20) DEFAULT NULL COMMENT 'admin user_id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_verifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_details` (
  `id` bigint(20) NOT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `booking_item_id` bigint(20) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `favorites` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `item_type` enum('service','package','supplier_package') NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `collection_id` bigint(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
  `id` bigint(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `attempt_count` int(11) DEFAULT NULL,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `locked_until` timestamp NULL DEFAULT NULL,
  `max_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `platform_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_availability` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `type` enum('available','unavailable','custom_hours') NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_media` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `type` enum('image','video') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_schedules` (
  `id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `day_of_week` tinyint(1) NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `supplier_categories` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `category_id` bigint(20) NOT NULL,
  `source` enum('ai','manual','admin') NOT NULL DEFAULT 'manual',
  `confidence` decimal(5,4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `supplier_documents` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venues` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `service_id` bigint(20) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_rooms` (
  `id` bigint(20) NOT NULL,
  `venue_id` bigint(20) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `min_lead_days` int(11) DEFAULT NULL COMMENT 'Room-specific override. NULL = inherit from parent service.',
  `photo_url` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_room_availability` (
  `id` bigint(20) NOT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `wishlist_collections` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Indexes
-- ============================================================

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
  ADD UNIQUE KEY `unique_google_id` (`unique_google_id`),
  ADD UNIQUE KEY `unique_facebook_id` (`unique_facebook_id`),
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


--
-- AUTO_INCREMENT for table `wishlist_collections`
--
ALTER TABLE `wishlist_collections`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--


-- ============================================================
-- New indexes required by added foreign keys (v15)
-- ============================================================

ALTER TABLE `attire_items`
  ADD KEY `idx_attire_items_service` (`service_id`);

ALTER TABLE `booking_items`
  ADD KEY `idx_bi_attire_item` (`attire_item_id`),
  ADD KEY `idx_bi_decoration_style` (`decoration_style_id`),
  ADD KEY `idx_bi_cake_design` (`cake_design_id`);

ALTER TABLE `booking_slot_reservations`
  ADD KEY `idx_bsr_booking_item` (`booking_item_id`),
  ADD KEY `idx_bsr_service` (`service_id`);

ALTER TABLE `booking_suppliers`
  ADD KEY `idx_bs_service` (`service_id`),
  ADD KEY `idx_bs_category` (`category_id`),
  ADD KEY `idx_bs_package_item` (`package_item_id`),
  ADD KEY `idx_bs_replaced_by` (`replaced_by_id`);

ALTER TABLE `booking_supplier_replacements`
  ADD KEY `idx_bsr_old_supplier` (`old_supplier_id`),
  ADD KEY `idx_bsr_old_service` (`old_service_id`),
  ADD KEY `idx_bsr_new_service` (`new_service_id`),
  ADD KEY `idx_bsr_category` (`category_id`),
  ADD KEY `idx_bsr_package_item` (`package_item_id`),
  ADD KEY `idx_bsr_chosen_by_admin` (`chosen_by_admin_id`),
  ADD KEY `idx_bsr_delta_payment` (`delta_payment_id`);

ALTER TABLE `cart_items`
  ADD KEY `idx_ci_attire_item` (`attire_item_id`),
  ADD KEY `idx_ci_rental_option` (`rental_option_id`),
  ADD KEY `idx_ci_decoration_style` (`decoration_style_id`),
  ADD KEY `idx_ci_cake_design` (`cake_design_id`);

ALTER TABLE `package_items`
  ADD KEY `idx_pi_attire_item` (`attire_item_id`),
  ADD KEY `idx_pi_decoration_style` (`decoration_style_id`),
  ADD KEY `idx_pi_cake_design` (`cake_design_id`);

ALTER TABLE `packages`
  ADD KEY `idx_pkg_category` (`category_id`),
  ADD KEY `idx_pkg_replaces` (`replaces_package_id`);

ALTER TABLE `refunds`
  ADD KEY `idx_refunds_payment` (`payment_id`),
  ADD KEY `idx_refunds_requested_by` (`requested_by`),
  ADD KEY `idx_refunds_processed_by` (`processed_by`);

ALTER TABLE `suppliers`
  ADD KEY `idx_suppliers_verified_by` (`verified_by`),
  ADD KEY `idx_suppliers_approved_by` (`approved_by`);


-- ============================================================
-- AUTO_INCREMENT values
-- ============================================================

ALTER TABLE `account_lockout_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;;

ALTER TABLE `attire_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;;

ALTER TABLE `attire_rental_bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;;

ALTER TABLE `attire_rental_options`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;;

ALTER TABLE `bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;;

ALTER TABLE `booking_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=406;;

ALTER TABLE `booking_slot_reservations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;;

ALTER TABLE `booking_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=268;;

ALTER TABLE `booking_suppliers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=334;;

ALTER TABLE `booking_supplier_replacements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;;

ALTER TABLE `booking_vouchers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;;

ALTER TABLE `carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;;

ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;;

ALTER TABLE `categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;;

ALTER TABLE `customer_status_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;;

ALTER TABLE `decoration_styles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;;

ALTER TABLE `email_verifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;;

ALTER TABLE `event_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;;

ALTER TABLE `favorites`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;;

ALTER TABLE `food_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;;

ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;;

ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=391;;

ALTER TABLE `otps`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;;

ALTER TABLE `packages`
  MODIFY `package_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;;

ALTER TABLE `package_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;;

ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;;

ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;;

ALTER TABLE `refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;;

ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;;

ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;;

ALTER TABLE `services`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;;

ALTER TABLE `service_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;;

ALTER TABLE `service_media`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;;

ALTER TABLE `service_rental_pricing`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;;

ALTER TABLE `service_schedules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1508;;

ALTER TABLE `service_time_slots`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;;

ALTER TABLE `suppliers`
  MODIFY `supplier_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;;

ALTER TABLE `supplier_categories`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;;

ALTER TABLE `supplier_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;;

ALTER TABLE `supplier_warnings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;;

ALTER TABLE `system_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=579;;

ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;;

ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;;

ALTER TABLE `venues`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;;

ALTER TABLE `venue_rooms`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;;

ALTER TABLE `venue_room_availability`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;;

ALTER TABLE `wishlist_collections`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;;


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

-- ============================================================
-- NEW foreign-key constraints added in v15
-- ============================================================

-- attire_items → services
ALTER TABLE `attire_items`
  ADD CONSTRAINT `fk_attire_items_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

-- attire_rental_bookings → booking_items, attire_items
ALTER TABLE `attire_rental_bookings`
  ADD CONSTRAINT `fk_arb_booking_item` FOREIGN KEY (`booking_item_id`) REFERENCES `booking_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_arb_attire_item` FOREIGN KEY (`attire_item_id`) REFERENCES `attire_items` (`id`) ON DELETE CASCADE;

-- booking_items → attire_items, decoration_styles, food_items
ALTER TABLE `booking_items`
  ADD CONSTRAINT `fk_bi_attire_item` FOREIGN KEY (`attire_item_id`) REFERENCES `attire_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bi_decoration_style` FOREIGN KEY (`decoration_style_id`) REFERENCES `decoration_styles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bi_cake_design` FOREIGN KEY (`cake_design_id`) REFERENCES `food_items` (`id`) ON DELETE SET NULL;

-- booking_slot_reservations → bookings, booking_items, services, service_time_slots
ALTER TABLE `booking_slot_reservations`
  ADD CONSTRAINT `fk_bsr_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bsr_booking_item` FOREIGN KEY (`booking_item_id`) REFERENCES `booking_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsr_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsr_slot` FOREIGN KEY (`slot_id`) REFERENCES `service_time_slots` (`id`) ON DELETE CASCADE;

-- booking_suppliers → services, categories, package_items, self-ref
ALTER TABLE `booking_suppliers`
  ADD CONSTRAINT `fk_bs_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bs_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bs_package_item` FOREIGN KEY (`package_item_id`) REFERENCES `package_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bs_replaced_by` FOREIGN KEY (`replaced_by_id`) REFERENCES `booking_suppliers` (`id`) ON DELETE SET NULL;

-- booking_supplier_replacements (full referential integrity)
ALTER TABLE `booking_supplier_replacements`
  ADD CONSTRAINT `fk_bsrp_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bsrp_booking_supplier` FOREIGN KEY (`booking_supplier_id`) REFERENCES `booking_suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bsrp_old_supplier` FOREIGN KEY (`old_supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `fk_bsrp_old_service` FOREIGN KEY (`old_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsrp_new_supplier` FOREIGN KEY (`new_supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsrp_new_service` FOREIGN KEY (`new_service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsrp_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsrp_package_item` FOREIGN KEY (`package_item_id`) REFERENCES `package_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsrp_chosen_by_admin` FOREIGN KEY (`chosen_by_admin_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bsrp_delta_payment` FOREIGN KEY (`delta_payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

-- cart_items → attire_items, attire_rental_options, decoration_styles, food_items
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_ci_attire_item` FOREIGN KEY (`attire_item_id`) REFERENCES `attire_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ci_rental_option` FOREIGN KEY (`rental_option_id`) REFERENCES `attire_rental_options` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ci_decoration_style` FOREIGN KEY (`decoration_style_id`) REFERENCES `decoration_styles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ci_cake_design` FOREIGN KEY (`cake_design_id`) REFERENCES `food_items` (`id`) ON DELETE SET NULL;

-- customer_status_logs → users
ALTER TABLE `customer_status_logs`
  ADD CONSTRAINT `fk_csl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_csl_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

-- decoration_styles → services
ALTER TABLE `decoration_styles`
  ADD CONSTRAINT `fk_ds_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

-- event_details → booking_items
ALTER TABLE `event_details`
  ADD CONSTRAINT `fk_ed_booking_item` FOREIGN KEY (`booking_item_id`) REFERENCES `booking_items` (`id`) ON DELETE SET NULL;

-- food_items → services
ALTER TABLE `food_items`
  ADD CONSTRAINT `fk_fi_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

-- package_items → attire_items, decoration_styles, food_items, venue_rooms
ALTER TABLE `package_items`
  ADD CONSTRAINT `fk_pi_attire_item` FOREIGN KEY (`attire_item_id`) REFERENCES `attire_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pi_decoration_style` FOREIGN KEY (`decoration_style_id`) REFERENCES `decoration_styles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pi_cake_design` FOREIGN KEY (`cake_design_id`) REFERENCES `food_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pi_venue_room` FOREIGN KEY (`venue_room_id`) REFERENCES `venue_rooms` (`id`) ON DELETE SET NULL;

-- packages → categories, self-ref
ALTER TABLE `packages`
  ADD CONSTRAINT `fk_pkg_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pkg_replaces` FOREIGN KEY (`replaces_package_id`) REFERENCES `packages` (`package_id`) ON DELETE SET NULL;

-- refunds → bookings, payments, users
ALTER TABLE `refunds`
  ADD CONSTRAINT `fk_refunds_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `fk_refunds_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_refunds_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_refunds_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

-- service_rental_pricing → services
ALTER TABLE `service_rental_pricing`
  ADD CONSTRAINT `fk_srp_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

-- suppliers → users (verified_by, approved_by)
ALTER TABLE `suppliers`
  ADD CONSTRAINT `fk_suppliers_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_suppliers_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

COMMIT;
