-- ========================================================
-- Migration: Food per-person pricing + guest count on cart
-- Date: 2026-06-27
-- ========================================================

-- 1. Food items: add pricing model (flat vs per-person)
ALTER TABLE `food_items`
  ADD COLUMN `pricing_model` ENUM('flat','per_person') NOT NULL DEFAULT 'flat'
    AFTER `customize_price`;

-- 2. Cart items: store guest count so it persists through checkout
ALTER TABLE `cart_items`
  ADD COLUMN `guest_count` INT DEFAULT NULL
    AFTER `cake_design_id`;

-- 3. Package items: add cake_design_id for food item locking
ALTER TABLE `package_items`
  ADD COLUMN `cake_design_id` BIGINT DEFAULT NULL
    AFTER `decoration_style_id`;
