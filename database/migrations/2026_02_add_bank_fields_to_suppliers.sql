-- Migration: Add bank account fields to suppliers table for 2C2P payouts
-- Date: 2026-06-16
-- Purpose: Store supplier bank details for 2C2P payout processing

ALTER TABLE suppliers
ADD COLUMN IF NOT EXISTS bank_code VARCHAR(10) DEFAULT 'AYA' COMMENT 'Bank code: AYA, KBZ, AGD, etc.',
ADD COLUMN IF NOT EXISTS bank_account VARCHAR(50) COMMENT 'Bank account number for payouts';

-- Optional: Create index for bank lookups
CREATE INDEX IF NOT EXISTS idx_suppliers_bank_code ON suppliers(bank_code);

-- Notes for suppliers:
-- - bank_code: Should be set during supplier onboarding (AYA, KBZ, AGD, etc.)
-- - bank_account: Should be collected and validated during onboarding
-- - These fields are required before supplier payouts can be processed
-- - 2C2P will use these details to disburse earnings to supplier bank account

-- Verify the update:
-- SELECT supplier_id, bank_code, bank_account FROM suppliers LIMIT 5;
