-- Add payout tracking columns to payments table for manual payout flow
-- bank_account and bank_code already exist on suppliers table
ALTER TABLE `payments`
  ADD COLUMN `payout_batch_id` varchar(100) DEFAULT NULL AFTER `verified_note`,
  ADD COLUMN `payout_requested_at` timestamp NULL DEFAULT NULL AFTER `payout_batch_id`;

CREATE INDEX `idx_payments_payout_batch` ON `payments`(`payout_batch_id`);
