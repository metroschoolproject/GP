-- Migration: Dual-Flow Booking System (Packages Auto-Confirm, Custom Services Supplier-First)
-- Date: 2026-06-17

-- 1. Add source column to booking_items (mirrors cart_items.source)
ALTER TABLE booking_items
ADD COLUMN IF NOT EXISTS source enum('package','custom') NOT NULL DEFAULT 'custom'
AFTER item_type;

-- 2. Add pending_supplier_response status and paid status to bookings table
ALTER TABLE bookings
MODIFY COLUMN status enum(
  'draft',
  'pending_supplier_response',
  'pending_payment',
  'payment_submitted',
  'payment_verified',
  'paid',
  'suppliers_responding',
  'confirmed',
  'pending_final_payment',
  'finalized',
  'completed',
  'cancelled'
) NOT NULL DEFAULT 'draft';

-- 3. Add supplier_response_deadline for 48-hour auto-expiry
ALTER TABLE bookings
ADD COLUMN IF NOT EXISTS supplier_response_deadline timestamp NULL DEFAULT NULL
AFTER status;
