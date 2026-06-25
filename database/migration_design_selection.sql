-- Migration: Add design selection support for Attire, Decoration, and Cake categories
-- Date: 2026-06-22

-- 1. Create cake_designs table (mirrors decoration_styles)
CREATE TABLE IF NOT EXISTS `cake_designs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0,
  `package_price` decimal(10,2) DEFAULT NULL,
  `customize_price` decimal(10,2) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cake_designs_service` (`service_id`),
  CONSTRAINT `fk_cake_designs_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add design selection columns to cart_items
ALTER TABLE `cart_items`
  ADD COLUMN `attire_item_id` bigint(20) DEFAULT NULL AFTER `venue_room_id`,
  ADD COLUMN `decoration_style_id` bigint(20) DEFAULT NULL AFTER `attire_item_id`,
  ADD COLUMN `cake_design_id` bigint(20) DEFAULT NULL AFTER `decoration_style_id`;

-- 3. Add design selection columns to booking_items
ALTER TABLE `booking_items`
  ADD COLUMN `attire_item_id` bigint(20) DEFAULT NULL AFTER `venue_room_id`,
  ADD COLUMN `decoration_style_id` bigint(20) DEFAULT NULL AFTER `attire_item_id`,
  ADD COLUMN `cake_design_id` bigint(20) DEFAULT NULL AFTER `decoration_style_id`;
