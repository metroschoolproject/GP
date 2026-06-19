-- Migration: Add draft/publish workflow to packages
-- Date: 2026-06-18
-- Purpose: Packages are never edited in-place when live.
-- Admin must clone a published package into a draft, edit the draft,
-- then publish (atomically replacing the old version).

ALTER TABLE packages
  ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'published' AFTER is_active,
  ADD COLUMN replaces_package_id BIGINT DEFAULT NULL AFTER status;

-- All existing packages are already published
UPDATE packages SET status = 'published' WHERE status IS NULL OR status = '';
