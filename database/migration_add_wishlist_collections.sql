-- =============================================================================
-- Migration: Add wishlist collections + extend favorites table
-- Description: Enables folder-based wishlist organization for customers
-- Target: MariaDB 10.4+ / MySQL 5.7+
-- Safe to run multiple times — uses IF NOT EXISTS / IF EXISTS guards
-- =============================================================================

-- 1. Create wishlist_collections table
CREATE TABLE IF NOT EXISTS `wishlist_collections` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `wishlist_collections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Add collection_id, notes, and created_at to existing favorites table
-- Uses a stored procedure for idempotent column additions (safe to re-run)

DELIMITER //
DROP PROCEDURE IF EXISTS add_favorites_columns//
CREATE PROCEDURE add_favorites_columns()
BEGIN
  -- collection_id: nullable FK to wishlist_collections (NULL = "All Saved")
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'goldenpromise'
      AND TABLE_NAME = 'favorites'
      AND COLUMN_NAME = 'collection_id'
  ) THEN
    ALTER TABLE `favorites`
      ADD COLUMN `collection_id` bigint(20) DEFAULT NULL AFTER `item_id`;
  END IF;

  -- notes: optional free-text note on the saved item
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'goldenpromise'
      AND TABLE_NAME = 'favorites'
      AND COLUMN_NAME = 'notes'
  ) THEN
    ALTER TABLE `favorites`
      ADD COLUMN `notes` text DEFAULT NULL AFTER `collection_id`;
  END IF;

  -- created_at: when this item was saved
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'goldenpromise'
      AND TABLE_NAME = 'favorites'
      AND COLUMN_NAME = 'created_at'
  ) THEN
    ALTER TABLE `favorites`
      ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp() AFTER `notes`;
  END IF;

  -- Index on collection_id for fast lookup
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = 'goldenpromise'
      AND TABLE_NAME = 'favorites'
      AND INDEX_NAME = 'idx_favorites_collection'
  ) THEN
    ALTER TABLE `favorites`
      ADD INDEX `idx_favorites_collection` (`collection_id`);
  END IF;

  -- FK from favorites.collection_id → wishlist_collections.id
  -- (skip if the constraint already exists)
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = 'goldenpromise'
      AND TABLE_NAME = 'favorites'
      AND CONSTRAINT_NAME = 'favorites_ibfk_collection'
  ) THEN
    ALTER TABLE `favorites`
      ADD CONSTRAINT `favorites_ibfk_collection`
        FOREIGN KEY (`collection_id`)
        REFERENCES `wishlist_collections` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE;
  END IF;
END//
DELIMITER ;

CALL add_favorites_columns();
DROP PROCEDURE IF EXISTS add_favorites_columns;
