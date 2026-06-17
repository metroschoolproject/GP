-- Migration: Dual-Flow Booking System (Packages Auto-Confirm, Custom Services Supplier-First)
-- Date: 2026-06-17
-- Compatibility: MySQL 5.7+ and MariaDB 10.0+

-- 1. Add source column to booking_items (mirrors cart_items.source)
-- Uses portable syntax: check information_schema before adding
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'booking_items'
    AND COLUMN_NAME = 'source'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE booking_items ADD COLUMN source enum(''package'',''custom'') NOT NULL DEFAULT ''custom'' AFTER item_type',
    'SELECT 1 AS skipped');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Add pending_supplier_response status and paid status to bookings table
ALTER TABLE bookings
MODIFY COLUMN status enum(
  'draft',
  'pending_supplier_response',
  'pending_payment',
  'payment_submitted',
  'payment_verified',
  'paid',
  'suppliers_responding',
  'confirmed',
  'pending_final_payment',
  'finalized',
  'completed',
  'cancelled'
) NOT NULL DEFAULT 'draft';

-- 3. Add supplier_response_deadline for 48-hour auto-expiry
SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'bookings'
    AND COLUMN_NAME = 'supplier_response_deadline'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE bookings ADD COLUMN supplier_response_deadline timestamp NULL DEFAULT NULL AFTER status',
    'SELECT 1 AS skipped');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
