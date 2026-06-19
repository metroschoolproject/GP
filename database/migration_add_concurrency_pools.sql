-- ═══════════════════════════════════════════════════════════════
-- Migration: Split concurrency into package & customize pools
-- Date: 2026-06-19
-- Purpose: Each service gets separate concurrent limits for
--          package/add-on bookings vs direct/custom bookings.
--          Packages also get their own max_concurrent cap.
-- ═══════════════════════════════════════════════════════════════

-- ── services: split concurrency into two pools ──────────────────
ALTER TABLE services
  ADD COLUMN max_concurrent_package   SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER max_concurrent,
  ADD COLUMN max_concurrent_customize SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER max_concurrent_package;

-- Migrate existing data: keep current max_concurrent as customize-only
-- (package pool stays 0 — must be explicitly enabled per service)
UPDATE services
   SET max_concurrent_customize = max_concurrent,
       max_concurrent_package   = 0;

-- ── service_time_slots: track confirmations per pool ────────────
ALTER TABLE service_time_slots
  ADD COLUMN confirmed_package_count   TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER confirmed_count,
  ADD COLUMN confirmed_customize_count TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER confirmed_package_count,
  ADD COLUMN max_concurrent_package    SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER max_concurrent,
  ADD COLUMN max_concurrent_customize  SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER max_concurrent_package;

-- Migrate existing: assume all existing confirmed bookings are custom
UPDATE service_time_slots
   SET confirmed_customize_count = confirmed_count,
       confirmed_package_count   = 0,
       max_concurrent_customize  = max_concurrent,
       max_concurrent_package    = 0;

-- ── packages: global per-date concurrency cap ───────────────────
ALTER TABLE packages
  ADD COLUMN max_concurrent SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER base_price;

-- ── package_items: per-item concurrency override ────────────────
ALTER TABLE package_items
  ADD COLUMN max_concurrent SMALLINT(5) UNSIGNED DEFAULT NULL AFTER customize_price;
