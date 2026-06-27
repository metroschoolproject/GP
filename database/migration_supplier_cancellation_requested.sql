-- Add supplier_cancellation_requested status to booking_suppliers
-- This allows suppliers to request cancellation of a confirmed booking.
-- Admin then reviews and processes the refund.

ALTER TABLE `booking_suppliers`
  MODIFY `status` ENUM(
    'pending','confirmed','in_progress','completed','cancelled',
    'rejected','needs_replacement','replaced','declined_again',
    'cancellation_pending','cancellation_approved',
    'supplier_cancellation_requested'
  ) NOT NULL DEFAULT 'pending';
