-- Add publish_status column to services table
-- Tracks the review lifecycle: draft → pending_review → published

ALTER TABLE services
  ADD COLUMN publish_status ENUM('draft','pending_review','published') NOT NULL DEFAULT 'draft'
  AFTER is_active;

-- Backfill existing data
UPDATE services SET publish_status = 'published' WHERE is_active = 1;
UPDATE services SET publish_status = 'draft' WHERE is_active = 0;
