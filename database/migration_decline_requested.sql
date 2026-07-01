-- Migration: Add 'decline_requested' status and decline_reason column to booking_suppliers
-- This allows suppliers to request a decline for package bookings within the 7-day cutoff window

-- Step 1: Add 'decline_requested' to the status enum
ALTER TABLE `booking_suppliers`
  MODIFY COLUMN `status` enum(
    'pending',
    'confirmed',
    'in_progress',
    'completed',
    'cancelled',
    'rejected',
    'needs_replacement',
    'replaced',
    'declined_again',
    'cancellation_pending',
    'cancellation_approved',
    'supplier_cancellation_requested',
    'decline_requested'
  ) NOT NULL DEFAULT 'pending';

-- Step 2: Add decline_reason column
ALTER TABLE `booking_suppliers`
  ADD COLUMN `decline_reason` VARCHAR(500) DEFAULT NULL AFTER `declined_at`;
