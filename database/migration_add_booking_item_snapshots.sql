-- Migration: Add snapshot columns to booking_items
-- Date: 2026-06-18
-- Purpose: Freeze package/service name, supplier, category, and thumbnail at booking time
-- so that admin edits to packages/services don't retroactively change past bookings.

ALTER TABLE booking_items
  ADD COLUMN item_name VARCHAR(255) DEFAULT NULL AFTER price,
  ADD COLUMN supplier_name VARCHAR(255) DEFAULT NULL AFTER item_name,
  ADD COLUMN category_name VARCHAR(255) DEFAULT NULL AFTER supplier_name,
  ADD COLUMN thumbnail_url VARCHAR(500) DEFAULT NULL AFTER category_name;
