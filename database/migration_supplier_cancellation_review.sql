-- Migration: Add cancellation review statuses to booking_suppliers
-- Purpose: Allow suppliers to accept/decline cancellation requests on customize bookings
-- New values: 'cancellation_pending' (awaiting supplier review), 'cancellation_approved' (supplier accepted)

ALTER TABLE booking_suppliers
  MODIFY status ENUM(
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
    'cancellation_approved'
  ) NOT NULL DEFAULT 'pending';
