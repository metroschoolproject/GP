-- Migration: Add customer opt-out deadline and accepted_by_customer status
-- for the three-tier replacement assignment logic.

-- 1. Add opt-out deadline column
ALTER TABLE `booking_supplier_replacements`
  ADD COLUMN `customer_opt_out_deadline` DATETIME DEFAULT NULL AFTER `customer_approved_at`;

-- 2. Extend the status enum to include the new terminal status
--    (includes all existing values: accepted, cancelled)
ALTER TABLE `booking_supplier_replacements`
  MODIFY COLUMN `status` ENUM(
    'pending_admin',
    'pending_customer',
    'assigned',
    'accepted',
    'declined_again',
    'rejected_by_customer',
    'cancelled',
    'accepted_by_customer',
    'resolved'
  ) NOT NULL DEFAULT 'pending_admin';
