-- Allow one event_details row per booking item.
-- The old UNIQUE index on booking_id only allowed one service detail row per booking.
ALTER TABLE event_details
  ADD INDEX idx_event_details_booking_id (booking_id),
  DROP INDEX booking_id;
