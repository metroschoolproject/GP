-- ═══════════════════════════════════════════════════════════════
-- Migration: Replacement audit Phase 1
-- Date: 2026-06-21
-- Purpose:
--   L-C1: allow a verified replacement-delta payment to be reversed
--         (refunded) when the replacement later falls through. Needs a
--         'refunded' state on payments.status.
--   L-H1: remember which services a customer/supplier already rejected
--         for a replacement, so the finder can exclude them on re-pick.
-- ═══════════════════════════════════════════════════════════════

-- ── payments: add 'refunded' state ──────────────────────────────
ALTER TABLE payments
  MODIFY COLUMN `status` enum('pending','processing','success','failed','refunded') DEFAULT NULL;

-- ── booking_supplier_replacements: remember rejected services ───
ALTER TABLE booking_supplier_replacements
  ADD COLUMN `rejected_service_ids` TEXT DEFAULT NULL AFTER `decline_reason`;

-- ═══════════════════════════════════════════════════════════════
-- ROLLBACK (manual):
--   ALTER TABLE booking_supplier_replacements DROP COLUMN rejected_service_ids;
--   ALTER TABLE payments MODIFY COLUMN status enum('pending','processing','success','failed') DEFAULT NULL;
-- ═══════════════════════════════════════════════════════════════
