-- ========================================================
-- Migration: Day-Based Attire Rental Booking System
-- Date: 2026-06-26
-- ========================================================

-- 1. New table: attire_rental_options
-- Suppliers define multiple rental duration tiers per attire item
CREATE TABLE IF NOT EXISTS `attire_rental_options` (
  `id`              BIGINT(20) NOT NULL AUTO_INCREMENT,
  `attire_item_id`  BIGINT(20) NOT NULL,
  `days`            INT(11) NOT NULL COMMENT 'Rental duration in days',
  `price`           DECIMAL(12,2) NOT NULL COMMENT 'Package price for this duration',
  `customize_price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Customize price for this duration',
  `sort_order`      INT(11) DEFAULT 0,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_attire_item_id` (`attire_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add buffer_days to attire_items
ALTER TABLE `attire_items`
  ADD COLUMN `buffer_days` INT(11) NOT NULL DEFAULT 1 COMMENT 'Days blocked after return for cleaning/alteration'
  AFTER `return_days`;

-- 3. New table: attire_rental_bookings
-- Tracks which items are borrowed for which date ranges (availability blocking ledger)
CREATE TABLE IF NOT EXISTS `attire_rental_bookings` (
  `id`               BIGINT(20) NOT NULL AUTO_INCREMENT,
  `booking_item_id`  BIGINT(20) NOT NULL,
  `attire_item_id`   BIGINT(20) NOT NULL,
  `rental_type`      ENUM('borrow','buy') NOT NULL,
  `borrow_date`      DATE DEFAULT NULL,
  `return_date`      DATE DEFAULT NULL,
  `rental_days`      INT(11) DEFAULT NULL,
  `buffer_until`     DATE DEFAULT NULL COMMENT 'return_date + buffer_days — end of blocked range',
  `status`           ENUM('reserved','picked_up','returned','cancelled') NOT NULL DEFAULT 'reserved',
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_item_id` (`booking_item_id`),
  KEY `idx_attire_item_id` (`attire_item_id`),
  KEY `idx_attire_dates` (`attire_item_id`, `borrow_date`, `buffer_until`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Add rental fields to cart_items
ALTER TABLE `cart_items`
  ADD COLUMN `rental_type` ENUM('borrow','buy') DEFAULT NULL AFTER `attire_item_id`,
  ADD COLUMN `borrow_date` DATE DEFAULT NULL AFTER `rental_type`,
  ADD COLUMN `rental_option_id` BIGINT(20) DEFAULT NULL COMMENT 'References attire_rental_options.id' AFTER `borrow_date`;

-- 5. Add rental fields to booking_items
ALTER TABLE `booking_items`
  ADD COLUMN `rental_type` ENUM('borrow','buy') DEFAULT NULL AFTER `attire_item_id`,
  ADD COLUMN `borrow_date` DATE DEFAULT NULL AFTER `rental_type`,
  ADD COLUMN `return_date` DATE DEFAULT NULL AFTER `borrow_date`;

-- 6. Seed: Create rental options for existing attire items
-- Based on existing return_days and borrow_package_price from attire_items
INSERT INTO `attire_rental_options` (`attire_item_id`, `days`, `price`, `customize_price`, `sort_order`)
SELECT
  `id`,
  `return_days`,
  `borrow_package_price`,
  `borrow_customize_price`,
  0
FROM `attire_items`
WHERE `return_days` IS NOT NULL
  AND `borrow_package_price` IS NOT NULL
  AND `borrow_package_price` > 0;
