-- ========================================================
-- Migration: Split "Food" into "Cake" + "Food & Drinks"
-- Date: 2026-06-28
-- ========================================================

-- 1. Add new "Cake" category (if not exists)
INSERT INTO categories (name, slug)
SELECT 'Cake', 'cake'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'cake');

-- 2. Rename "Food" → "Food & Drinks" (slug: food_drinks)
UPDATE categories SET name = 'Food & Drinks', slug = 'food_drinks' WHERE slug = 'food';

-- 3. Add food_type discriminator to food_items
ALTER TABLE `food_items`
  ADD COLUMN `food_type` ENUM('cake','catering') NOT NULL DEFAULT 'catering'
    AFTER `pricing_model`;

-- 4. Existing food_items are catering (default already set)
-- No UPDATE needed — default is 'catering'

-- 5. Add index for fast filtering by service + type
ALTER TABLE `food_items`
  ADD KEY `idx_food_items_type` (`service_id`, `food_type`);
