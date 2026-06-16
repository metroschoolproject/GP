-- Migration: Remove KBZ/AYA and add 2C2P payment tracking
-- Date: 2026-06-16
-- Purpose: Add 2C2P gateway reference tracking and clean up old payment methods

-- Add 2C2P gateway tracking columns if they don't exist
ALTER TABLE payments
ADD COLUMN IF NOT EXISTS gateway_reference VARCHAR(255) COMMENT '2C2P transaction ID',
ADD COLUMN IF NOT EXISTS gateway_response JSON COMMENT '2C2P API response payload';

-- Update existing payment records to normalize method names
-- (In case any old test data exists with different naming)
UPDATE payments
SET method = '2c2p_mmqr'
WHERE method IN ('MM QR', 'mm_qr', 'mmqr', 'MMQR');

UPDATE payments
SET method = '2c2p_card'
WHERE method IN ('Visa Card', 'Visa', 'Card', 'VISA', 'credit_card', 'cc');

-- Remove any old KBZ/AYA records from test data (optional, comment out if you want to keep them)
-- DELETE FROM payments WHERE method IN ('KBZ Pay', 'AYA Bank Transfer', 'KBZ', 'AYA');

-- Verify the update
-- SELECT DISTINCT method FROM payments ORDER BY method;
