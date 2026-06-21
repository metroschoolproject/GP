-- Payout request lifecycle and provider reconciliation.
-- Apply after migration_booking_integrity_hardening.sql.

ALTER TABLE payments
  MODIFY COLUMN status ENUM(
    'pending','processing','success','failed','refunded'
  ) DEFAULT NULL;

ALTER TABLE payments
  ADD COLUMN IF NOT EXISTS payout_batch_id VARCHAR(64) NULL AFTER transaction_ref,
  ADD COLUMN IF NOT EXISTS payout_provider_ref VARCHAR(120) NULL AFTER payout_batch_id,
  ADD COLUMN IF NOT EXISTS payout_requested_at TIMESTAMP NULL DEFAULT NULL AFTER payout_provider_ref;

CREATE INDEX IF NOT EXISTS idx_payments_payout_batch
  ON payments (payout_batch_id, type, status);
