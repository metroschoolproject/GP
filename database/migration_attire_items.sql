-- Migration: Add individual attire items (like venue halls for attire services)
-- Date: 2026-06-18
-- Purpose: Attire services can have multiple individual dress/accessory items.
-- Each item has its own name, photo, borrow/buy pricing, and return days.
-- Package builders pick a specific attire item (like picking a hall for a venue).

CREATE TABLE attire_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  service_id BIGINT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT DEFAULT NULL,
  photo_url VARCHAR(500) DEFAULT NULL,
  borrow_package_price DECIMAL(12,2) DEFAULT NULL,
  borrow_customize_price DECIMAL(12,2) DEFAULT NULL,
  buy_package_price DECIMAL(12,2) DEFAULT NULL,
  buy_customize_price DECIMAL(12,2) DEFAULT NULL,
  return_days INT DEFAULT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_service (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add attire_item_id to package_items (like venue_room_id for venues)
ALTER TABLE package_items
  ADD COLUMN attire_item_id BIGINT DEFAULT NULL AFTER venue_room_id;
