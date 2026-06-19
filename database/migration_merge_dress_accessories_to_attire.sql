-- Migration: Merge Dress + Accessories into Attire
-- Date: 2026-06-18
-- Purpose: "Dress" and "Accessories" are the same type (attire/wardrobe rental).
-- Merge into a single "Attire" category.

-- 1. Reassign any services under Accessories (category_id=1) to Dress (category_id=2)
UPDATE services SET category_id = 2 WHERE category_id = 1;

-- 2. Rename Dress to Attire
UPDATE categories SET name = 'Attire', slug = 'attire' WHERE id = 2;

-- 3. Delete the now-empty Accessories category
DELETE FROM categories WHERE id = 1;
