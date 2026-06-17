-- Migration: Add manual payment fields for Myanmar bank transfer verification
-- Run once: ALTER TABLE payments ADD new columns for manual transfer details

ALTER TABLE payments
  ADD COLUMN IF NOT EXISTS bank_name     VARCHAR(100)  NULL AFTER method,
  ADD COLUMN IF NOT EXISTS account_name  VARCHAR(150)  NULL AFTER bank_name,
  ADD COLUMN IF NOT EXISTS mobile_number VARCHAR(20)   NULL AFTER account_name,
  ADD COLUMN IF NOT EXISTS paid_amount   DECIMAL(10,2) NULL AFTER mobile_number,
  ADD COLUMN IF NOT EXISTS paid_at       DATETIME      NULL AFTER paid_amount,
  ADD COLUMN IF NOT EXISTS payment_slip_path VARCHAR(255) NULL AFTER paid_at,
  ADD COLUMN IF NOT EXISTS verified_note TEXT NULL AFTER verified_at;
