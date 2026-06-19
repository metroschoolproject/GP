-- ═══════════════════════════════════════════════════════════════
-- Migration: Admin-driven supplier replacement for package bookings
-- Date: 2026-06-19
-- Purpose: When a supplier on a CONFIRMED package booking declines a
--          date, the platform (admin) swaps in another available
--          same-category supplier instead of cancelling the booking.
--
--          Pricing rule (hybrid):
--            - replacement <= original price  -> auto-swap, platform
--              absorbs the difference, customer pays nothing.
--            - replacement >  original price  -> propose to customer;
--              customer approves + pays the delta via Stripe; then
--              swap finalizes. Candidates capped at +MAX_UPCHARGE_PCT
--              (default 25%) above the original item price.
--
-- Depends on: migration_add_concurrency_pools.sql (per-pool slot cols)
-- ═══════════════════════════════════════════════════════════════

-- ── bookings: new lifecycle state ──────────────────────────────
-- 'replacement_pending' = at least one supplier declined and the
-- swap is in progress (admin pick / customer approval / new-supplier
-- acceptance). Booking stays alive throughout.
ALTER TABLE bookings
  MODIFY COLUMN `status` enum(
    'draft','pending_supplier_response','pending_payment','payment_submitted',
    'payment_verified','paid','suppliers_responding','confirmed',
    'replacement_pending','pending_final_payment','finalized','completed',
    'cancelled','cancellation_requested'
  ) NOT NULL DEFAULT 'draft';

-- ── booking_suppliers: per-supplier replacement tracking ────────
--   needs_replacement = this supplier declined; awaiting swap
--   replaced          = this row superseded by a replacement supplier
ALTER TABLE booking_suppliers
  MODIFY COLUMN `status` enum(
    'pending','confirmed','in_progress','completed',
    'cancelled','rejected','needs_replacement','replaced'
  ) NOT NULL DEFAULT 'pending';

-- Link each booking_supplier row to WHAT it covers, so the
-- replacement search knows category/service/price to match.
-- (booking_suppliers previously only stored supplier_id.)
ALTER TABLE booking_suppliers
  ADD COLUMN `service_id`      bigint(20)     DEFAULT NULL AFTER `supplier_id`,
  ADD COLUMN `category_id`     bigint(20)     DEFAULT NULL AFTER `service_id`,
  ADD COLUMN `package_item_id` bigint(20)     DEFAULT NULL AFTER `category_id`,
  ADD COLUMN `item_price`      decimal(10,2)  DEFAULT NULL AFTER `package_item_id`,
  ADD COLUMN `replaced_by_id`  bigint(20)     DEFAULT NULL AFTER `payout_status`,
  ADD COLUMN `declined_at`     timestamp      NULL DEFAULT NULL AFTER `confirmed_at`;

-- Backfill service/category/price for existing package booking rows
-- from the package definition (best-effort; custom bookings unaffected).
UPDATE booking_suppliers bs
  INNER JOIN booking_items bi
    ON bi.booking_id = bs.booking_id AND bi.item_type = 'package'
  INNER JOIN package_items pi
    ON pi.package_id = bi.item_id AND pi.default_supplier_id = bs.supplier_id
  SET bs.service_id      = pi.service_id,
      bs.category_id     = pi.category_id,
      bs.package_item_id = pi.id,
      bs.item_price      = COALESCE(pi.default_price, pi.customize_price)
  WHERE bs.service_id IS NULL;

-- ── booking_supplier_replacements: audit + state machine per swap ─
CREATE TABLE IF NOT EXISTS `booking_supplier_replacements` (
  `id`                      bigint(20)    NOT NULL AUTO_INCREMENT,
  `booking_id`              bigint(20)    NOT NULL,
  `booking_supplier_id`     bigint(20)    NOT NULL,           -- the declined row
  `category_id`             bigint(20)    DEFAULT NULL,
  `old_supplier_id`         bigint(20)    NOT NULL,
  `old_service_id`          bigint(20)    DEFAULT NULL,
  `old_price`               decimal(10,2) DEFAULT NULL,
  `new_supplier_id`         bigint(20)    DEFAULT NULL,        -- NULL until admin picks
  `new_service_id`          bigint(20)    DEFAULT NULL,
  `new_price`               decimal(10,2) DEFAULT NULL,
  `price_delta`             decimal(10,2) DEFAULT NULL,        -- new - old (signed)
  -- pricing/approval gating:
  `requires_customer_approval` tinyint(1) NOT NULL DEFAULT 0, -- 1 when new_price > old_price
  `customer_approved_at`    timestamp     NULL DEFAULT NULL,
  `delta_payment_id`        bigint(20)    DEFAULT NULL,        -- FK -> payments (Stripe charge for delta)
  `status` enum(
      'pending_admin',     -- waiting for admin to choose a candidate
      'pending_customer',  -- pricier pick proposed; awaiting customer approve+pay
      'assigned',          -- finalized; new supplier notified, awaiting their accept
      'accepted',          -- new supplier accepted; swap complete
      'declined_again',    -- new supplier declined; back to admin
      'rejected_by_customer', -- customer declined the pricier proposal
      'cancelled'
  ) NOT NULL DEFAULT 'pending_admin',
  `chosen_by_admin_id`      bigint(20)    DEFAULT NULL,
  `decline_reason`          varchar(500)  DEFAULT NULL,        -- why original supplier declined
  `created_at`              timestamp     NOT NULL DEFAULT current_timestamp(),
  `assigned_at`             timestamp     NULL DEFAULT NULL,
  `resolved_at`             timestamp     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_repl_booking`      (`booking_id`),
  KEY `idx_repl_booking_supp` (`booking_supplier_id`),
  KEY `idx_repl_status`       (`status`),
  KEY `idx_repl_new_supplier` (`new_supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── payments: tag a charge as a replacement price-delta ─────────
ALTER TABLE payments
  MODIFY COLUMN `type` enum('deposit','remaining','full','supplier_fee','replacement_delta') DEFAULT NULL;

-- ── candidate-search speed indexes (uncomment if missing) ───────
-- ALTER TABLE services           ADD INDEX `idx_service_category_active` (`category_id`,`is_active`);
-- ALTER TABLE service_time_slots ADD INDEX `idx_slot_service_date` (`service_id`,`date`);

-- ═══════════════════════════════════════════════════════════════
-- CONFIG (set in app/config/config.php, not DB):
--   define('MAX_REPLACEMENT_UPCHARGE_PCT', 25);  // candidate ceiling
-- ═══════════════════════════════════════════════════════════════
-- ROLLBACK (manual):
--   DROP TABLE booking_supplier_replacements;
--   ALTER TABLE booking_suppliers
--     DROP COLUMN service_id, DROP COLUMN category_id,
--     DROP COLUMN package_item_id, DROP COLUMN item_price,
--     DROP COLUMN replaced_by_id, DROP COLUMN declined_at;
--   (revert status enums to prior definitions)
-- ═══════════════════════════════════════════════════════════════
